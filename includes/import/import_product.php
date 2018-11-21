<?php
session_start();

//---SDKs and API credentials---[]

//load SDKs (amazon-mws, hQuery, eBay-SDK-PHP)
require_once '../../vendor/autoload.php';

//load config
include_once("../../includes/config.php");

//setup SDKs
$client = new MCS\MWSClient($amazon_mws_credentials);

use \DTS\eBaySDK\Constants; //eBay-SDK-PHP
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

hQuery::$cache_path = "../hquery_cache"; //hQuery

$server_name = $_SERVER['SERVER_NAME'];

//---[]

//connect to the database
include_once("../../includes/connection.php");

$eBay_username = $_SESSION["eBay_username"];
$eBay_access_token = $_SESSION["eBay_access_token"];

//grab form data
$asin = $_POST["asin"];

//grab settings from database
$query = $connection->prepare("SELECT * FROM settings WHERE value_8 = '$eBay_username'");
$query->execute();
$settings = $query->fetchAll();

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "quantity")
   {
      $settings_quantity = (int)$settings_ind["value_1"];
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "product_location")
   {
      $settings_city = $settings_ind["value_1"];
      $settings_post_code = $settings_ind["value_2"];
      
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "paypal_address")
   {
      $settings_paypal_address = $settings_ind["value_1"];
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "max_dispatch_time")
   {
      $settings_max_dispatch_time = (int)$settings_ind["value_1"];
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "shipping")
   {
      $settings_shipping_priority = (int)$settings_ind["value_1"];
      $settings_shipping_service = $settings_ind["value_2"];
      $settings_shipping_cost = (double)$settings_ind["value_3"];
      $settings_shipping_additional_cost = (double)$settings_ind["value_4"];
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "listing_duration")
   {
      $settings_listing_duration = $settings_ind["value_1"];
      break;
   }
}

foreach ($settings as $settings_ind)
{
   if ($settings_ind["detail"] == "round_price_by")
   {
      $settings_round_price_by = $settings_ind["value_1"];
      break;
   }
}

$query = $connection->prepare("SELECT * FROM settings WHERE detail = 'profit_margin' and value_8 = '$eBay_username'");
$query->execute();
$profit_margins = $query->fetchAll();

if (isset($_SESSION["business_policies_on"]))
{
   //grab seller profiles
   $query = $connection->prepare("SELECT value_2 FROM settings WHERE detail = 'seller_profile' and value_1 = 'payment' and value_8 = '$eBay_username'");
   $query->execute();
   $seller_profile_payment_ID = $query->fetchColumn();
   
   $query = $connection->prepare("SELECT value_2 FROM settings WHERE detail = 'seller_profile' and value_1 = 'return_policy' and value_8 = '$eBay_username'");
   $query->execute();
   $seller_profile_return_policy_ID = $query->fetchColumn();
   
   $query = $connection->prepare("SELECT value_2 FROM settings WHERE detail = 'seller_profile' and value_1 = 'shipping' and value_8 = '$eBay_username'");
   $query->execute();
   $seller_profile_shipping_ID = $query->fetchColumn();
}

//grab product info from Amazon API
$searchField = 'ASIN'; // Can be GCID, SellerSKU, UPC, EAN, ISBN, or JAN

$product_info_amazon_API = $client->GetMatchingProductForId([$asin], $searchField);

//if product doesn't exist
if (empty($product_info_amazon_API["found"]))
{
   //update processed status
   $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
   $query->execute();
   
   //insert into import process fail history
   $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                    VALUES('$asin', 'Product does not exist in API')");
   $query->execute();
   
   echo "processed";
}
else
{
   //grab title and brand from response
   $amazon_title = $product_info_amazon_API["found"][$asin]["Title"];
   
   //check title for bad words
   $amazon_title_lower_case = strtolower($amazon_title);
   
   if (($handle = fopen('bad_words.csv', 'r')) !== false)
   {
       while(($data = fgetcsv($handle)) !== false)
       {
          $bad_word = strtolower($data[0]);
          
          if (strpos($amazon_title, $bad_word) !== false)
          {
             $bad_word_detected = 1;
             
             break;
          }
       }
      
       fclose($handle);
   }
   
   if (isset($bad_word_detected))
   {
      //update processed status
      $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
      $query->execute();
      
      //insert into import process fail history
      $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                       VALUES('$asin', ?)");
      $query->bindValue(1, 'Bad word detected: ' . $bad_word);
      
      $query->execute();
      
      echo "processed";
   }
   else
   {
      //grab brand
      if (!empty($product_info_amazon_API["found"][$asin]["Brand"]))
      {
         $brand = $product_info_amazon_API["found"][$asin]["Brand"];
      }
      else
      {
         $brand = "Unknown";
      }
      
      //grab offers from Amazon API
      $amazon_offers = $client->GetLowestOfferListingsForASIN([$asin], $ItemCondition = 'NEW');
      
      //check if prime price exists (no late dispatch or pre-order products)
      if (!empty($amazon_offers[$asin]))
      {
         //if only one offer
         if (!empty($amazon_offers[$asin]["Qualifiers"]))
         {
            if ($amazon_offers[$asin]["Qualifiers"]["FulfillmentChannel"] == "Amazon" and $amazon_offers[$asin]["Qualifiers"]["ShippingTime"]["Max"] == "0-2 days")
            {
               $lowest_prime_price = number_format((double)$amazon_offers[$asin]["Price"]["ListingPrice"]["Amount"], 2, '.', '');
            }
            else
            {
               unset($lowest_prime_price);
            }
         }
         //if more than one offer
         else
         {
            foreach ($amazon_offers[$asin] as $offer)
            {
               if ($offer["Qualifiers"]["FulfillmentChannel"] == "Amazon" and $offer["Qualifiers"]["ShippingTime"]["Max"] == "0-2 days")
               {
                  $lowest_prime_price = number_format((double)$offer["Price"]["ListingPrice"]["Amount"], 2, '.', '');
                  
                  break;
               }
               else
               {
                  unset($lowest_prime_price);
               }
            }
         }
      }
      
      //if no prime price
      if (!isset($lowest_prime_price))
      {
         //update processed status
         $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
         $query->execute();
         
         //insert into import process fail history
         $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                          VALUES('$asin', 'No prime price')");
         $query->execute();
         
         echo "processed";
      }
      else
      {
         //---grab category, images, description, quantity---[]
      
         //grab product page
         $ch = curl_init();
   
         $url = 'https://www.amazon.co.uk/gp/product/' . $asin . '?th=1&psc=1';
         $proxy = 'proxy.crawlera.com:8010';
         $proxy_auth = $crawlera_API_key;
         
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_PROXY, $proxy);
         curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
         curl_setopt($ch, CURLOPT_HEADER, 1);
         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
         curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600);
         curl_setopt($ch, CURLOPT_TIMEOUT, 600);
         curl_setopt($ch, CURLOPT_CAINFO, 'crawlera-ca.crt'); //required for HTTPS
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE); //required for HTTPS
         
         $scraped_page = curl_exec($ch);
         
         if ($scraped_page === false)
         {
             echo 'cURL error: ' . curl_error($ch);
         }
         else
         {
            $doc = hQuery::fromHTML($scraped_page);
            
            //check if product is an add-on product
            $add_on_element = $doc->find('.addOnItem-header');
            
            if (!empty($add_on_element))
            {
               //update processed status
               $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
               $query->execute();
               
               //insert into import process fail history
               $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                VALUES('$asin', 'Add-on product')");
               $query->execute();
               
               echo "processed";
            }
            else
            {
               //grab category
               $option_elements = $doc->find('option', 'selected=selected');
               
               $amazon_category = $option_elements->text();
               
               //if category is not Books
               if ($amazon_category != "Books")
               {
                  //grab script element containing image URLs
                  $script_elements = $doc->find('script');
                  
                  foreach ($script_elements as $script_element)
                  {
                     if (strpos($script_element->text(), "'colorImages': { 'initial'") !== false)
                     {
                        $images_script_element = $script_element;
                        break;
                     }
                  }
                  
                  //create array of image URLs
                  $images_array = array();
                  
                  $images_script_element_split = explode('"', $images_script_element); //grab all images
                  $images_array_temp = array();
                  
                  foreach ($images_script_element_split as $images_script_element_split_ind)
                  {
                     if (strpos($images_script_element_split_ind, 'https://images') !== false and strpos($images_script_element_split_ind, 'overlay') == false)
                     {
                        array_push($images_array_temp, $images_script_element_split_ind);
                     }
                  }
                  
                  foreach ($images_array_temp as $images_array_temp_ind) //check if any hi-res images
                  {
                     if (strpos($images_array_temp_ind, '_SL') !== false)
                     {
                        $images_large_image_detected = 1;
                        break;
                     }
                  }
                  
                  if (isset($images_large_image_detected)) //if any hi-res images
                  {
                     foreach ($images_array_temp as $images_array_temp_ind)
                     {
                        if (strpos($images_array_temp_ind, '_SL') !== false)
                        {
                           array_push($images_array, $images_array_temp_ind);
                        }
                     }
                  }
                  
                  if (!isset($images_large_image_detected)) //if no hi-res images
                  {
                     foreach ($images_array_temp as $images_array_temp_ind)
                     {
                        if (strpos($images_array_temp_ind, '_') == false)
                        {
                           array_push($images_array, $images_array_temp_ind);
                        }
                     }
                  }
                  
                  //grab div element containing features
                  $product_description = "";
                  
                  $li_elements = $doc->find('#feature-bullets > ul > li > .a-list-item');
                  
                  if (!empty($li_elements))
                  {
                     $list = "<ul>";
                  
                     foreach ($li_elements as $li_element)
                     {
                        $list_item = '<li>' . $li_element . '</li>';
                        $list .= $list_item;
                     }
                     
                     $list .= "</ul>";
                     $product_description .= $list . '<br/>';
                  }
                  
                  //grab div element containing product description
                  $product_description_element = $doc->find('#productDescription');
                  
                  $product_description .= $product_description_element;
               }
               else
               {
                  //grab script element containing image URLs
                  $script_elements = $doc->find('script');
                  
                  foreach ($script_elements as $script_element)
                  {
                     if (strpos($script_element->text(), "mainUrl") !== false)
                     {
                        $images_script_element = $script_element;
                        break;
                     }
                  }
                  
                  //create array of image URLs
                  $images_array = array();
                  
                  $temp_images_array = explode('"mainUrl":"', $images_script_element);
                  
                  foreach ($temp_images_array as $temp_image_array_item)
                  {
                     if (strpos($temp_image_array_item, "https://images-na.ssl-images-amazon.com/images/I") !== false)
                     {
                        $temp_v = explode('"', $temp_image_array_item);
                        array_push($images_array, $temp_v[0]);
                     }
                  }
                  
                  //grab div element containing features
                  $description_element = $doc->find('#bookDescription_feature_div > noscript > div');
                  
                  if (!empty($description_element))
                  {
                     $product_description = $description_element->text();
                  }
                  else
                  {
                     $product_description = "No description.";
                  }
               }
               
               //grab quantity
               $quantity_select_element = $doc->find('select', 'name=quantity');
               
               if (empty($quantity_select_element))
               {
                  $amazon_quantity = 1;
               }
               else
               {
                  $quantity_option_elements = $quantity_select_element->find('option');
                  $amazon_quantity = sizeof($quantity_option_elements);
               }
               
               //---[]
               
               //if settings quantity is greater than amazon quantity
               if ($settings_quantity > $amazon_quantity)
               {
                  //update processed status
                  $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                  $query->execute();
                  
                  //insert into import process fail history
                  $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                   VALUES('$asin', 'Not enough quantity')");
                  $query->execute();
                  
                  echo "processed";
               }
               else
               {
                  //---grab relative eBay category ID---[]
                  
                  if ($mine == 1)
                  {
                     $listing_category_ID = 88433; //Everything Else -> Every Other Thing
                  }
                  else
                  {
                     $siteId = Constants\SiteIds::GB;
                     
                     //create service object
                     $service = new Services\TradingService([
                         'credentials' => $eBay_credentials,
                         'sandbox'     => $sandbox_active,
                         'siteId'      => $siteId
                     ]);
                     
                     //create request for 'Buy It Now' listing
                     $request = new Types\GetSuggestedCategoriesRequestType();
                     
                     //set access token
                     $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
                     $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
                     
                     $request->Query = $amazon_title;
                     
                     $response = $service->getSuggestedCategories($request);
                     $response = json_decode($response); //convert object response into array
                     $response = json_decode(json_encode($response), true);
                     
                     if (!empty($response["SuggestedCategoryArray"]["SuggestedCategory"][0]["Category"]["CategoryID"]))
                     {
                        $listing_category_ID = $response["SuggestedCategoryArray"]["SuggestedCategory"][0]["Category"]["CategoryID"];
                     }
                  }
                  
                  //---[]
                  
                  //if no category matched
                  if (!isset($listing_category_ID))
                  {
                     //update processed status
                     $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                     $query->execute();
                     
                     //insert into import process fail history
                     $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                      VALUES('$asin', ?)");
                     $query->bindValue(1, 'No suggested eBay category given');
                     
                     $query->execute();
                     
                     echo "processed";
                  }
                  else
                  {
                     //cut string if characters more than 80
                     if (strlen($amazon_title) > 80)
                     {
                        $amazon_title = substr($amazon_title, 0, -(strlen($amazon_title) - 80));
                        $amazon_title_array = explode(" ", $amazon_title);
                        
                        $amazon_title = "";
                        
                        foreach ($amazon_title_array as $index => $amazon_title_array_ind)
                        {
                           if ($index < (sizeof($amazon_title_array) - 2))
                           {
                              $amazon_title .= $amazon_title_array_ind . " ";
                           }
                           else if ($index == (sizeof($amazon_title_array) - 2))
                           {
                              $amazon_title .= $amazon_title_array_ind;
                           }
                        }
                     }
                     
                     //---create eBay listing---[]
                     
                     //set site ID
                     $siteId = Constants\SiteIds::GB;
                     
                     //create service object
                     $service = new Services\TradingService([
                         'credentials' => $eBay_credentials,
                         'sandbox'     => $sandbox_active,
                         'siteId'      => $siteId
                     ]);
                     
                     //create request for 'Buy It Now' listing
                     $request = new Types\AddFixedPriceItemRequestType();
                     
                     //set access token
                     $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
                     $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
                     
                     //start creating item
                     $item = new Types\ItemType();
                     
                     //set item to be fixed price
                     $item->ListingType = Enums\ListingTypeCodeType::C_FIXED_PRICE_ITEM;
                     
                     //set duration of listing (automatically renewed every 30 days till cancelled)
                     $item->ListingDuration = $settings_listing_duration;
                     
                     //set quantity of item
                     $item->Quantity = $settings_quantity;
                     
                     //set price---[]
                     
                     foreach ($profit_margins as $profit_margin)
                     {
                        $from = (int)$profit_margin["value_1"];
                        $to = (int)$profit_margin["value_2"];
                        
                        $margin = (int)$profit_margin["value_3"];
                        $fixed_margin = (int)$profit_margin["value_4"];
                        $paypal_fees_percentage = (int)$profit_margin["value_5"];
                        $paypal_fees_fixed = (double)$profit_margin["value_6"];
                        $eBay_fees = (int)$profit_margin["value_7"];
                        
                        if ($lowest_prime_price >= $from and $lowest_prime_price <= $to)
                        {
                           //increase by margin (percent)
                           $percent_of_price = ($lowest_prime_price / 100) * $margin; //margin percent of price
                           $lowest_prime_price_sale = number_format(($percent_of_price + $lowest_prime_price), 2, '.', ''); //to 2 decimal
                           
                           //increase by margin fixed (fixed)
                           $lowest_prime_price_sale += $fixed_margin;
                           
                           //increase by paypal fee (percent)
                           $percent_of_price = ($lowest_prime_price_sale / 100) * $paypal_fees_percentage; //paypal percent of price
                           $lowest_prime_price_sale += $percent_of_price;
                           $lowest_prime_price_sale = number_format($lowest_prime_price_sale, 2, '.', ''); //to 2 decimal
                           
                           //increase by paypal fee (fixed)
                           $lowest_prime_price_sale += $paypal_fees_fixed;
                           
                           //increase by eBay fee (percent)
                           $percent_of_price = ($lowest_prime_price_sale / 100) * $eBay_fees; //paypal percent of price
                           $lowest_prime_price_sale += $percent_of_price;
                           $lowest_prime_price_sale = number_format($lowest_prime_price_sale, 2, '.', ''); //to 2 decimal
                           
                           $listing_price = $lowest_prime_price_sale;
                           
                           //round price based on settings
                           if ($settings_round_price_by == 'Round To Nearest Whole Number')
                           {
                              $listing_price = number_format(round($listing_price), 2, '.', '');
                           }
                           else if (!empty($settings_round_price_by and $settings_round_price_by != "Do Not Change"))
                           {
                              $settings_round_price_by = explode(".", $settings_round_price_by);
                              $listing_price = explode(".", $listing_price);
                              $listing_price = $listing_price[0] . "." . $settings_round_price_by[1];
                           }
                           
                           break;
                        }
                     }
                     
                     $item->StartPrice = new Types\AmountType(['value' => (double)$listing_price]);
                     
                     //---[]
                     
                     //set currency
                     $item->Currency = 'GBP';
                     
                     //set item info
                     $item->SKU = $asin;
                     $item->Title = $amazon_title;
                     
                     //---description---[]
                     
                     //with template
                     $listing_description = file_get_contents('listing_template/top.html');
                     $listing_description .= $product_description;
                     $listing_description .= file_get_contents('listing_template/bottom.html');
                     $item->Description = $listing_description;
                     
                     //without template
                     //$item->Description = $product_description;
                     
                     //---[]
                     
                     
                     $item->Country = 'GB';
                     $item->Location = $settings_city;
                     $item->PostalCode = $settings_post_code;
                     
                     //set images
                     $item->PictureDetails = new Types\PictureDetailsType();
                     $item->PictureDetails->PictureURL = $images_array;
                     
                     //set category
                     $item->PrimaryCategory = new Types\CategoryType();
                     $item->PrimaryCategory->CategoryID = (string)$listing_category_ID;
                     
                     //set condition (brand new)
                     $item->ConditionID = 1000;
                     
                     if (!isset($_SESSION["business_policies_on"]))
                     {
                        //set payment methods
                        $item->PaymentMethods = [
                           'PayPal'
                        ];
                        $item->PayPalEmailAddress = $settings_paypal_address;
                        
                        //set max dispatch time in days
                        $item->DispatchTimeMax = $settings_max_dispatch_time;
                        
                        //set shipping type (flat rate)
                        $item->ShippingDetails = new Types\ShippingDetailsType();
                        $item->ShippingDetails->ShippingType = Enums\ShippingTypeCodeType::C_FLAT;
                        
                        //set shipping option
                        $shippingService = new Types\ShippingServiceOptionsType();
                        $shippingService->ShippingServicePriority = $settings_shipping_priority;
                        $shippingService->ShippingService = $settings_shipping_service;
                        $shippingService->ShippingServiceCost = new Types\AmountType(['value' => $settings_shipping_cost]);
                        $shippingService->ShippingServiceAdditionalCost = new Types\AmountType(['value' => $settings_shipping_additional_cost]);
                        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
                        
                        //set return policy
                        $item->ReturnPolicy = new Types\ReturnPolicyType();
                        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
                        $item->ReturnPolicy->ReturnsWithinOption = 'Days_30';
                        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';
                     }
                     else
                     {
                        $item->SellerProfiles = new Types\SellerProfilesType();
                        $item->SellerProfiles->SellerPaymentProfile = new Types\SellerPaymentProfileType();
                        $item->SellerProfiles->SellerPaymentProfile->PaymentProfileID = (int)$seller_profile_payment_ID;
                        
                        $item->SellerProfiles->SellerReturnProfile = new Types\SellerReturnProfileType();
                        $item->SellerProfiles->SellerReturnProfile->ReturnProfileID = (int)$seller_profile_return_policy_ID;
                        
                        $item->SellerProfiles->SellerShippingProfile = new Types\SellerShippingProfileType();
                        $item->SellerProfiles->SellerShippingProfile->ShippingProfileID = (int)$seller_profile_shipping_ID;
                     }
                     
                     //EAN
                     $item->ProductListingDetails = new Types\ProductListingDetailsType();
                     $item->ProductListingDetails->EAN = "Does Not Apply";
                     
                     //Item Specifics
                     $item->ItemSpecifics = new Types\NameValueListArrayType();
                     
                     $specific = new Types\NameValueListType();
                     $specific->Name = 'Brand';
                     $specific->Value[] = $brand;
                     $item->ItemSpecifics->NameValueList[] = $specific;
                     
                     $specific = new Types\NameValueListType();
                     $specific->Name = 'MPN';
                     $specific->Value[] = 'Does Not Apply';
                     $item->ItemSpecifics->NameValueList[] = $specific;
                     
                     //send request
                     $request->Item = $item;
                     $response = $service->addFixedPriceItem($request);
                     $response = json_decode($response); //convert object response into array
                     $response = json_decode(json_encode($response), true);
                     
                     //---[]
                     
                     //if errors and the only error is about duplication
                     if ($response["Ack"] == "Failure" and !empty($response["Errors"]) and sizeof($response["Errors"]) == 1 and $response["Errors"][0]["ErrorCode"] == "21919067")
                     {
                        //update processed status
                        $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                        $query->execute();
                        
                        //insert into import process fail history
                        $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                         VALUES('$asin', 'Duplicate')");
                        $query->execute();
                        
                        echo "processed";
                     }
                     //if warning error
                     else if ($response["Ack"] == "Warning")
                     {
                        $eBay_product_ID = $response["ItemID"];
                        
                        //update processed status
                        $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                        $query->execute();
                        
                        //insert errors into the database
                        $error_msg = $response["Ack"];
                        $error_msg .= "<br/>";
                        
                        foreach ($response["Errors"] as $response_error)
                        {
                           $error_msg .= $response_error["LongMessage"];
                           $error_msg .= "<br/>";
                        }
                        
                        $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                         VALUES('$asin', ?)");
                        $query->bindValue(1, $error_msg);
                        
                        $query->execute();
                        
                        //insert asin, eBay product ID and other info into database
                        $query = $connection->prepare("INSERT INTO imported_products (asin, eBay_product_ID, original_price, listed_price, out_of_stock, eBay_username, processed)
                                                                               VALUES('$asin', '$eBay_product_ID', '$lowest_prime_price', '$listing_price', 'no', '$eBay_username', 'no')");
                        $query->execute();
                        
                        echo "processed";
                     }
                     //if other errors
                     else if ($response["Ack"] != "Success")
                     {
                        //update processed status
                        $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                        $query->execute();
                        
                        //insert errors into the database
                        $error_msg = $response["Ack"];
                        $error_msg .= "<br/>";
                        
                        foreach ($response["Errors"] as $response_error)
                        {
                           $error_msg .= $response_error["LongMessage"];
                           $error_msg .= "<br/>";
                        }
                        
                        $query = $connection->prepare("INSERT INTO import_process_fail_history (asin, reason)
                                                                                         VALUES('$asin', ?)");
                        $query->bindValue(1, $error_msg);
                        
                        $query->execute();
                        
                        echo "processed";
                     }
                     else if ($response["Ack"] == "Success")
                     {
                        $eBay_product_ID = $response["ItemID"];
                        
                        //update processed status
                        $query = $connection->prepare("UPDATE import_process SET processed = 'yes' WHERE asin = '$asin'");
                        $query->execute();
                        
                        //insert asin, eBay product ID and other info into database
                        $query = $connection->prepare("INSERT INTO imported_products (asin, eBay_product_ID, original_price, listed_price, out_of_stock, eBay_username, processed)
                                                                               VALUES('$asin', '$eBay_product_ID', '$lowest_prime_price', '$listing_price', 'no', '$eBay_username', 'no')");
                        $query->execute();
                        
                        echo "processed";
                     }
                  }
               }
            }
         }
         
         curl_close($ch);
      }
   }
}
?>