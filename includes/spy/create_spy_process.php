<?php
//---SDKs and API credentials---[]

//load SDKs (eBay-SDK-PHP)
require_once '../../vendor/autoload.php';

//load config
include_once("../../includes/config.php");

//setup SDKs
use \DTS\eBaySDK\Constants; //eBay-SDK-PHP
use \DTS\eBaySDK\Finding\Services;
use \DTS\eBaySDK\Finding\Types;
use \DTS\eBaySDK\Finding\Enums;

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
$eBay_seller_ID = filter_fields($_POST["eBay_seller_ID"]);

//grab listings
$service = new Services\FindingService([
   'credentials' => $eBay_credentials,
   'globalId'    => Constants\GlobalIds::GB
]);

$request = new Types\FindItemsAdvancedRequest();

//---

$itemFilter = new Types\ItemFilter();

$itemFilter->name = 'Seller';
$itemFilter->value[] = $eBay_seller_ID;
$request->itemFilter[] = $itemFilter;

$itemFilter = new Types\ItemFilter();

$itemFilter->name = 'LocatedIn';
$itemFilter->value[] = 'WorldWide';
$request->itemFilter[] = $itemFilter;

$request->paginationInput = new Types\PaginationInput();
$request->paginationInput->entriesPerPage = 100;
$request->paginationInput->pageNumber = 1;

//---

$response = $service->findItemsAdvanced($request);
$response = json_decode($response); //convert object response into array
$response = json_decode(json_encode($response), true);

//if no listings found
if (empty($response["searchResult"]["item"]))
{
   echo 0;
}
else
{
   echo $response["paginationOutput"]["totalPages"];
}
?>

<?php
//filter and store fields
function filter_fields($data)
{
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>