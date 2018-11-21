<?php
session_start();

//---SDKs and API credentials---[]

//load SDKs (eBay-SDK-PHP)
require_once '../vendor/autoload.php';

//load config
include_once("../includes/config.php");

use \DTS\eBaySDK\Constants; //eBay-SDK-PHP
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

//---[]

//connect to the database
include_once("../includes/connection.php");

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1); //set request to POST (GET by default)
curl_setopt($ch, CURLOPT_URL, $eBay_login_endpoint);

$headers = array(
'Content-Type: text/xml',
'X-EBAY-API-COMPATIBILITY-LEVEL: ' . '1047',
'X-EBAY-API-DEV-NAME: ' . $eBay_credentials["devId"],
'X-EBAY-API-APP-NAME: ' . $eBay_credentials["appId"],
'X-EBAY-API-CERT-NAME: ' . $eBay_credentials["certId"],
'X-EBAY-API-CALL-NAME: FetchToken',
'X-EBAY-API-SITEID: 3');

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
$requestBody .= '<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
$requestBody .= "<SessionID>" . $_SESSION["eBay_session_ID"] . "</SessionID>";
$requestBody .= "</FetchTokenRequest>";

curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
$result = curl_exec($ch);
curl_close($ch);

$result = simplexml_load_string($result); //convert XML response to object

$eBay_username = $_GET["username"];
$eBay_access_token = (string)$result->eBayAuthToken;

//if access token is empty, go back to settings page and show error
if (empty($eBay_access_token))
{
   unset($_SESSION["eBay_username"]);
   unset($_SESSION["eBay_access_token"]);
   
   header('location: settings.php');
}

//create or update access token in database
$_SESSION["eBay_username"] = $eBay_username;
$_SESSION["eBay_access_token"] = $eBay_access_token;

$query = $connection->prepare("SELECT * FROM eBay_access_tokens WHERE eBay_username = '$eBay_username'");
$query->execute();
$database_access_token = $query->fetch();

if (empty($database_access_token))
{
   $query = $connection->prepare("INSERT INTO eBay_access_tokens (eBay_username, eBay_access_token)
                                                           VALUES(?, ?)");
   $query->bindValue(1, $eBay_username);
   $query->bindValue(2, $eBay_access_token);
   
   $query->execute();
}
else
{
   $query = $connection->prepare("UPDATE eBay_access_tokens SET eBay_access_token = '$eBay_access_token' WHERE eBay_username = '$eBay_username'");
   $query->execute();
}

//---check setting for business policies---[]

//set site ID
$siteId = Constants\SiteIds::GB;
   
//create service object
$service = new Services\TradingService([
   'credentials' => $eBay_credentials,
   'sandbox'     => $sandbox_active,
   'siteId'      => $siteId
]);

$request = new Types\GetUserPreferencesRequestType();
   
//set access token
$request->RequesterCredentials = new Types\CustomSecurityHeaderType();
$request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];

$request->ShowSellerProfilePreferences = true;

$response = $service->getUserPreferences($request);
$response = json_decode($response); //convert object response into array
$response = json_decode(json_encode($response), true);

$business_policies_setting = $response["SellerProfilePreferences"]["SellerProfileOptedIn"];

if (empty($business_policies_setting) or $business_policies_setting == 0)
{
   unset($_SESSION["business_policies_on"]);
}
else
{
   $_SESSION["business_policies_on"] = 1;
}

//---[]

//go to settings page
header('location: settings.php');
?>