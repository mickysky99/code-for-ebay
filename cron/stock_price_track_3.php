<?php
ignore_user_abort(true);

//---SDKs, API credentials and database connection---[]

$server_name = $_SERVER['SERVER_NAME'];

if (strpos($server_name, "codio") !== false)
{
   //load SDKs (amazon-mws, eBay-SDK-PHP)
   require_once '../vendor/autoload.php';
   
   //load config
   include_once("../includes/config.php");
   
   //connect to the database
   include_once("../includes/connection.php");
}
else
{
   //load SDKs (amazon-mws, eBay-SDK-PHP)
   require_once '/home/dh_9vcw8d/arhamrasool.com/vendor/autoload.php';
   
   //load config
   include_once("/home/dh_9vcw8d/arhamrasool.com/includes/config.php");
   
   //connect to the database
   include_once("/home/dh_9vcw8d/arhamrasool.com/includes/connection.php");
}

//setup SDKs
$client = new MCS\MWSClient($amazon_mws_credentials); //amazon-mws

use \DTS\eBaySDK\Constants; //eBay-SDK-PHP
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

$siteId = Constants\SiteIds::GB;

$service = new Services\TradingService([
    'credentials' => $eBay_credentials,
    'sandbox'     => $sandbox_active,
    'siteId'      => $siteId
]);

//---[]

//grab date and time
date_default_timezone_set("Europe/London");
$date = date("y-m-d");
$time = date("H:i");
$date_and_time = $date . " " . $time;

//grab total imported products
$query = $connection->prepare("SELECT count(*) FROM imported_products");
$query->execute();
$imported_products_total = $query->fetchColumn();

//grab imported products not processed
$query = $connection->prepare("SELECT * FROM imported_products WHERE (id between '500001' and '750000') and (processed = 'no') LIMIT 1000");
$query->execute();
$imported_products_not_processed = $query->fetchAll();

//if there are imported products but none that are not processed
if ($imported_products_total > 0 and empty($imported_products_not_processed))
{
   //change all products to processed
   $query = $connection->prepare("UPDATE imported_products SET processed = 'no' WHERE (id between '500001' and '750000')");
   $query->execute();
}
//if there are imported products and also products that are not processed
else if ($imported_products_total > 0 and !empty($imported_products_not_processed))
{
   //track stock and price of all products
   $changes_array = array();
   $amount_requested_MWS = 10;
   $products_processed = 0;
   $time_request_sent = date('h:i:s');
   $time_loop_started = date('h:i:s');
   
   while ($products_processed < sizeof($imported_products_not_processed) and (strtotime(date('h:i:s')) - strtotime($time_loop_started) < 45))
   {
      //how many products to grab next
      if (($products_processed + $amount_requested_MWS) <= sizeof($imported_products_not_processed))
      {
         $to_grab = $amount_requested_MWS;
      }
      else
      {
         $to_grab = sizeof($imported_products_not_processed) - $products_processed;
      }
      
      //create arrays of products to process and ASINs to send to Amazon
      $products_to_process = array();
      $asins = array();
      
      for ($x = $products_processed;$x < ($products_processed + $to_grab);$x++) 
      {
         array_push($products_to_process, $imported_products_not_processed[$x]);
      }
      
      for ($x = $products_processed;$x < ($products_processed + $to_grab);$x++)
      {
         array_push($asins, $imported_products_not_processed[$x]["asin"]);
      }
      
      //remove duplicates from both arrays
      $products_to_process_2 = $products_to_process;
      
      for ($x = 0;$x < sizeof($products_to_process);$x++)
      {
         foreach ($products_to_process_2 as $index => $products_to_process_2_ind)
         {
            if ($x != $index and $products_to_process[$x]["asin"] == $products_to_process_2_ind["asin"])
            {
               unset($products_to_process_2[$x]);
            }
         }
      }
      
      $products_to_process = $products_to_process_2;
      
      $asins_2 = $asins;
      
      for ($x = 0;$x < sizeof($asins);$x++)
      {
         foreach ($asins_2 as $index => $asins_2_ind)
         {
            if ($x != $index and $asins[$x] == $asins_2_ind)
            {
               unset($asins_2[$x]);
            }
         }
      }
      
      $asins = $asins_2;
      
      //grab prices
      if (date('h:i:s') != $time_request_sent)
      {
         $offers = $client->GetLowestOfferListingsForASIN($asins, $ItemCondition = 'NEW');
         
         //---process products---[]
         
         foreach ($products_to_process as $product_to_process)
         {
            $eBay_product_ID = $product_to_process["eBay_product_ID"];
            
            //if gone over 45 seconds, stop
            if ((strtotime(date('h:i:s')) - strtotime($time_loop_started) > 45))
            {
               break 2;
            }
            
            //grab lowest prime price (Amazon fulfillment, no late dispatch and no pre-order products)
            if (!empty($offers[$product_to_process["asin"]]))
            {
               //if only one offer
               if (!empty($offers[$product_to_process["asin"]]["Qualifiers"]))
               {
                  if ($offers[$product_to_process["asin"]]["Qualifiers"]["FulfillmentChannel"] == "Amazon" and $offers[$product_to_process["asin"]]["Qualifiers"]["ShippingTime"]["Max"] == "0-2 days")
                  {
                     $lowest_prime_price = number_format((double)$offers[$product_to_process["asin"]]["Price"]["ListingPrice"]["Amount"], 2, '.', '');
                  }
                  else
                  {
                     unset($lowest_prime_price);
                  }
               }
               //if more than one offer
               else
               {
                  foreach ($offers[$product_to_process["asin"]] as $offer)
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
            else
            {
               unset($lowest_prime_price);
            }
            
            //if eBay listing is out of stock and no prime price found then move on
            if ($product_to_process["out_of_stock"] == "yes" and !isset($lowest_prime_price))
            {
               $table_ID = $product_to_process["id"];
               
               //update product to processed with time
               $query = $connection->prepare("UPDATE imported_products SET processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
               $query->execute();
            }
            //if eBay listing is out of stock and prime price found
            else if ($product_to_process["out_of_stock"] == "yes" and isset($lowest_prime_price))
            {
               //change listing quantity to 1
               $eBay_username = $product_to_process["eBay_username"];
               
               $query = $connection->prepare("SELECT eBay_access_token FROM eBay_access_tokens WHERE eBay_username = '$eBay_username'");
               $query->execute();
               $eBay_access_token = $query->fetchColumn();
               
               $change_array = array("eBay_product_ID"=>$product_to_process["eBay_product_ID"],"eBay_username"=>$eBay_username,"eBay_access_token"=>$eBay_access_token,"table_ID"=>$product_to_process["id"],"type_of_change"=>"in_stock","database_original_price"=>$product_to_process["original_price"]);
               array_push($changes_array, $change_array);
            }
            //if eBay listing is in stock and no prime price found
            else if ($product_to_process["out_of_stock"] == "no" and !isset($lowest_prime_price))
            {
               //make out of stock
               $eBay_username = $product_to_process["eBay_username"];
               
               $query = $connection->prepare("SELECT eBay_access_token FROM eBay_access_tokens WHERE eBay_username = '$eBay_username'");
               $query->execute();
               $eBay_access_token = $query->fetchColumn();
               
               $change_array = array("eBay_product_ID"=>$product_to_process["eBay_product_ID"],"eBay_username"=>$eBay_username,"eBay_access_token"=>$eBay_access_token,"table_ID"=>$product_to_process["id"],"type_of_change"=>"out_of_stock","database_original_price"=>$product_to_process["original_price"]);
               array_push($changes_array, $change_array);
            }
            //if eBay listing is in stock and prime price found
            else if ($product_to_process["out_of_stock"] == "no" and isset($lowest_prime_price))
            {
               $listing_original_price = (double)$product_to_process["original_price"];
               
               if ((($listing_original_price - $lowest_prime_price) > 1 or ($lowest_prime_price - $listing_original_price) > 1) and $listing_original_price != $lowest_prime_price)
               {
                  //change price
                  $eBay_username = $product_to_process["eBay_username"];
                  
                  $query = $connection->prepare("SELECT eBay_access_token FROM eBay_access_tokens WHERE eBay_username = '$eBay_username'");
                  $query->execute();
                  $eBay_access_token = $query->fetchColumn();
                  
                  //---set price based on profit margins---[]
                  
                  $query = $connection->prepare("SELECT * FROM settings WHERE detail = 'profit_margin' and value_8 = '$eBay_username'");
                  $query->execute();
                  $profit_margins = $query->fetchAll();
                  
                  $query = $connection->prepare("SELECT value_1 FROM settings WHERE detail = 'round_price_by' and value_8 = '$eBay_username'");
                  $query->execute();
                  $settings_round_price_by = $query->fetchColumn();
                  
                  $listing_price = set_price($profit_margins, $lowest_prime_price, $settings_round_price_by);
                  
                  //---[]
                  
                  $change_array = array("eBay_product_ID"=>$product_to_process["eBay_product_ID"],"eBay_username"=>$eBay_username,"eBay_access_token"=>$eBay_access_token,"table_ID"=>$product_to_process["id"],"type_of_change"=>"change_price","price"=>$listing_price,"original_price"=>$lowest_prime_price,"database_original_price"=>$product_to_process["original_price"]);
                  array_push($changes_array, $change_array);
               }
               else
               {
                  $table_ID = $product_to_process["id"];
                  
                  //update product to processed
                  $query = $connection->prepare("UPDATE imported_products SET processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
                  $query->execute();
               }
            }
         }
         
         //---[]
         
         //---process changes array---[]
         
         if (!empty($changes_array))
         {
            //make array of all users
            $users = array();
            
            foreach ($changes_array as $changes_array_ind)
            {
               array_push($users, $changes_array_ind["eBay_username"]);
            }
            
            $users = array_unique($users);
            
            //for each user, work out how many groups of up to 4 changes
            foreach ($users as $users_ind)
            {
               $total_user_changes = 0;
               
               foreach ($changes_array as $changes_array_ind)
               {
                  if ($changes_array_ind["eBay_username"] == $users_ind)
                  {
                     $total_user_changes++;
                  }
               }
               
               if ($total_user_changes <= 4)
               {
                  $user_change_group = 1;
               }
               else if ($total_user_changes > 4 and $total_user_changes <= 8)
               {
                  $user_change_group = 2;
               }
               else if ($total_user_changes > 8)
               {
                  $user_change_group = 3;
               }
               
               //loop through the groups, sending requests
               for ($x = 0;$x < $user_change_group;$x++)
               {
                  $requests_array = array();
                  $total_added_to_requests_array = 0;
                  
                  foreach ($changes_array as $index => $changes_array_ind)
                  {
                     if ($users_ind == $changes_array_ind["eBay_username"])
                     {
                        array_push($requests_array, $changes_array_ind);
                        unset($changes_array[$index]);
                        $total_added_to_requests_array++;
                     }
                     
                     if ($total_added_to_requests_array >= 4)
                     {
                        break;
                     }
                  }
                  
                  //--update products on eBay---[]
                  $request = new Types\ReviseInventoryStatusRequestType();
                  
                  $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
                  $request->RequesterCredentials->eBayAuthToken = $requests_array[0]["eBay_access_token"];
                  
                  //---
                  
                  foreach ($requests_array as $requests_array_ind)
                  {
                     if ($requests_array_ind["type_of_change"] == "in_stock")
                     {
                        $inventoryStatus = new Types\InventoryStatusType();
                        $inventoryStatus->ItemID = $requests_array_ind["eBay_product_ID"];
                        $inventoryStatus->Quantity = 1;
                        $request->InventoryStatus[] = $inventoryStatus;
                     }
                     else if ($requests_array_ind["type_of_change"] == "out_of_stock")
                     {
                        $inventoryStatus = new Types\InventoryStatusType();
                        $inventoryStatus->ItemID = $requests_array_ind["eBay_product_ID"];
                        $inventoryStatus->Quantity = 0;
                        $request->InventoryStatus[] = $inventoryStatus;
                     }
                     else if ($requests_array_ind["type_of_change"] == "change_price")
                     {
                        $inventoryStatus = new Types\InventoryStatusType();
                        $inventoryStatus->ItemID = $requests_array_ind["eBay_product_ID"];
                        $inventoryStatus->StartPrice = new Types\AmountType(['value' => (double)$requests_array_ind["price"]]);
                        $request->InventoryStatus[] = $inventoryStatus;
                     }
                  }
                  
                  //---
                  
                  $response = $service->reviseInventoryStatus($request);
                  $response = json_decode($response); //convert object response into array
                  $response = json_decode(json_encode($response), true);
                  
                  //---[]
                  
                  //---process eBay response---[]
                  
                  if (!empty($response["Ack"]))
                  {
                     $query = $connection->prepare("SELECT count(*) FROM tracking_eBay_errors");
                     $query->execute();
                     $total_eBay_tracking_errors = $query->fetchColumn();
                     
                     //loop through each product in request
                     foreach ($requests_array as $requests_array_ind)
                     {
                        $response_product_ID = $requests_array_ind["eBay_product_ID"];
                        $type_of_change = $requests_array_ind["type_of_change"];
                        $table_ID = $requests_array_ind["table_ID"];
                        $change_failed = "no";
                        
                        if ($type_of_change == "change_price")
                        {
                           $changes_array_original_price = $requests_array_ind["original_price"];
                           $changes_array_listed_price = $requests_array_ind["price"];
                        }
                        
                        //check if a tracking error with same product ID exists
                        $query = $connection->prepare("SELECT count(*) FROM tracking_eBay_errors WHERE eBay_product_ID = '$response_product_ID'");
                        $query->execute();
                        $tracking_error_exists = $query->fetchColumn();
                        
                        //check if error with change
                        if (!empty($response["Errors"]))
                        {
                           foreach ($response["Errors"] as $response_error)
                           {
                              foreach ($response_error["ErrorParameters"] as $response_error_parameter)
                              {
                                 if ($response_error_parameter["Value"] == $response_product_ID)
                                 {
                                    $change_failed = "yes";
                                    $error_long_message = $response_error["LongMessage"];
                                    break 2;
                                 }
                              }
                           }
                        }
                        
                        //if error
                        if ($change_failed == "yes")
                        {
                           //if quantity redundant error and type of change is back into stock
                           if (strpos($error_long_message, 'The existing quantity value is identical to the quantity specified in the request') !== false and $type_of_change == "in_stock")
                           {
                              $query = $connection->prepare("UPDATE imported_products SET out_of_stock = 'no' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                           //if quantity redundant error and type of change is make out of stock
                           else if (strpos($error_long_message, 'The existing quantity value is identical to the quantity specified in the request') !== false and $type_of_change == "out_of_stock")
                           {
                              $query = $connection->prepare("UPDATE imported_products SET out_of_stock = 'yes' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                           //if price redundant error
                           else if (strpos($error_long_message, 'The existing price value is identical to the price specified in the request') !== false)
                           {
                              $query = $connection->prepare("UPDATE imported_products SET original_price = '$changes_array_original_price', listed_price = '$changes_array_listed_price' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                           else if ($total_eBay_tracking_errors < 1000 and $tracking_error_exists != 1)
                           {
                              //insert error into database
                              $query = $connection->prepare("INSERT INTO tracking_eBay_errors (eBay_product_ID, error)
                                                                                        VALUES(?, ?)");
                              $query->bindValue(1, $response_product_ID);
                              $query->bindValue(2, $error_long_message);
                              
                              $query->execute();
                           }
                           
                           //update product to 'processed'
                           $query = $connection->prepare("UPDATE imported_products SET processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
                           $query->execute();
                        }
                        //if success
                        else
                        {
                           if ($type_of_change == "in_stock")
                           {
                              $query = $connection->prepare("UPDATE imported_products SET out_of_stock = 'no', processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                           else if ($type_of_change == "out_of_stock")
                           {
                              $query = $connection->prepare("UPDATE imported_products SET out_of_stock = 'yes', processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                           else if ($type_of_change == "change_price")
                           {
                              $listing_price = $requests_array_ind["price"];
                              $lowest_prime_price = $requests_array_ind["original_price"];
                              
                              $query = $connection->prepare("UPDATE imported_products SET original_price = '$lowest_prime_price', listed_price = '$listing_price', processed = 'yes', time_last_processed = '$date_and_time' WHERE id = '$table_ID'");
                              $query->execute();
                           }
                        }
                     }
                  }
                  
                  //echo '<pre>',print_r($response),'</pre>'; die();
                  
                  //---[]
               }
            }
         }
         
         //---[]
         
         $time_request_sent = date('h:i:s');
         
         $products_processed += $to_grab;
      }
   }
}

//echo '<pre>',print_r($changes_array),'</pre>';

echo "Done";
?>

<?php
//set price based on profit margins
function set_price($profit_margins, $lowest_prime_price, $settings_round_price_by)
{
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
         
         return $listing_price;
         
         break;
      }
   }
}
?>