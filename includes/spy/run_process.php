<?php
//---SDKs and API credentials---[]

//load SDKs (hQuery)
require_once '../../vendor/autoload.php';

//load config
include_once("../../includes/config.php");

//setup SDKs
hQuery::$cache_path = "../hquery_cache"; //hQuery

//setup SDKs
use \DTS\eBaySDK\Constants;
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

//eBay production credentials only---[]
$server_name = $_SERVER['SERVER_NAME'];

if (strpos($server_name, "codio") !== false)
{
   $eBay_credentials = $eBay_credentials_p;
}
//---[]

//---[]

//connect to the database
include_once("../../includes/connection.php");

//grab form data
$title = $_POST["title"];
$title_ID = $_POST["title_ID"];
$eBay_product_ID = $_POST["eBay_product_ID"];

//grab brand of product if exists
$eBay_brand = "";

$query = $connection->prepare("SELECT * FROM eBay_access_tokens LIMIT 1");
$query->execute();
$eBay_access_tokens_result = $query->fetch();
$eBay_access_token = $eBay_access_tokens_result["eBay_access_token"];

$siteId = Constants\SiteIds::GB;
$service = new Services\TradingService([
   'credentials' => $eBay_credentials,
   'siteId'      => $siteId
]);

$request = new Types\GetItemRequestType();

$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $eBay_access_token;

$request->ItemID = $eBay_product_ID;
$request->IncludeItemSpecifics = true;

$response = $service->getItem($request);
$response = json_decode($response); //convert object response into array
$response = json_decode(json_encode($response), true);

if (!empty($response["Item"]["ItemSpecifics"]["NameValueList"]))
{
   foreach ($response["Item"]["ItemSpecifics"]["NameValueList"] as $item_specefic)
   {
      if ($item_specefic["Name"] == "Brand")
      {
         $eBay_brand = $item_specefic["Value"]["0"];
         
         break;
      }
   }
}

//search for title on Amazon
$doc = hQuery::fromFile( 'https://www.amazon.co.uk/s/?field-keywords=' . urlencode($title), false, $http_context );

//grab list elements containing ASIN number and h2 elements containing titles
$li_elements = $doc->find('.s-result-item');
$h2_elements = $doc->find('.s-access-title');

if (!empty($li_elements))
{
   $asin = $li_elements[0]->attr('data-asin');
   $amazon_title = $h2_elements[0]->attr('data-attribute');
   
   //if titles are similar, add to array of search results
   if (similar_text($title, $amazon_title) > 40)
   {
      //add to spy process search results
      $query = $connection->prepare("INSERT INTO spy_search_results (eBay_listing_title, eBay_brand, amazon_title, asin)
                                                             VALUES(?, ?, ?, ?)");
      $query->bindValue(1, $title);
      $query->bindValue(2, $eBay_brand);
      $query->bindValue(3, $amazon_title);
      $query->bindValue(4, $asin);
      
      $query->execute();
      
      //update spy process title
      $query = $connection->prepare("UPDATE spy_process SET processed = 'yes' WHERE id = '$title_ID'");
      $query->execute();
   }
   else
   {
      //update spy process title
      $query = $connection->prepare("UPDATE spy_process SET processed = 'yes' WHERE id = '$title_ID'");
      $query->execute();
   }
}
else
{
   //update spy process title
   $query = $connection->prepare("UPDATE spy_process SET processed = 'yes' WHERE id = '$title_ID'");
   $query->execute();
}

echo 1;
?>