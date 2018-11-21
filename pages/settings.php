<?php
session_start();

//if not logged in
if (!isset($_SESSION["logged_in"]))
{
   header('location: ../index.php');
}

//connect to the database
include_once("../includes/connection.php");

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

//notification msgs
if (isset($_SESSION["msg_not"]))
{
   $msg_not = $_SESSION["msg_not"];
   unset($_SESSION["msg_not"]);
}

if (isset($_SESSION["eBay_access_token"]))
{
   $eBay_username = $_SESSION["eBay_username"];
}

//---settings change---[]

//password
if (isset($_POST["password"]))
{
   $password = filter_fields($_POST["password"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'password'");
   $query->bindValue(1, $password);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//quantity
if (isset($_POST["quantity"]))
{
   $quantity = filter_fields($_POST["quantity"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'quantity' and value_8 = '$eBay_username'");
   $query->bindValue(1, $quantity);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//product location
if (isset($_POST["product_location_city"]))
{
   $city = filter_fields($_POST["product_location_city"]);
   $post_code = filter_fields($_POST["product_location_post_code"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ?, value_2 = ? WHERE detail = 'product_location' and value_8 = '$eBay_username'");
   $query->bindValue(1, $city);
   $query->bindValue(2, $post_code);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//paypal address
if (isset($_POST["paypal_address"]))
{
   $paypal_address = filter_fields($_POST["paypal_address"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'paypal_address' and value_8 = '$eBay_username'");
   $query->bindValue(1, $paypal_address);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//max dispatch time
if (isset($_POST["max_dispatch_time"]))
{
   $max_dispatch_time = filter_fields($_POST["max_dispatch_time"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'max_dispatch_time' and value_8 = '$eBay_username'");
   $query->bindValue(1, $max_dispatch_time);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//shipping
if (isset($_POST["shipping_priority"]))
{
   $shipping_priority = filter_fields($_POST["shipping_priority"]);
   $shipping_service = filter_fields($_POST["shipping_service"]);
   $shipping_cost = filter_fields($_POST["shipping_cost"]);
   $shipping_additional_cost = filter_fields($_POST["shipping_additional_cost"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ?, value_2 = ?, value_3 = ?, value_4 = ? WHERE detail = 'shipping' and value_8 = '$eBay_username'");
   $query->bindValue(1, $shipping_priority);
   $query->bindValue(2, $shipping_service);
   $query->bindValue(3, $shipping_cost);
   $query->bindValue(4, $shipping_additional_cost);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//listing duration
if (isset($_POST["listing_duration"]))
{
   $listing_duration = filter_fields($_POST["listing_duration"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'listing_duration' and value_8 = '$eBay_username'");
   $query->bindValue(1, $listing_duration);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//round price by
if (isset($_POST["round_price_by"]))
{
   $round_price_by = filter_fields($_POST["round_price_by"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ? WHERE detail = 'round_price_by' and value_8 = '$eBay_username'");
   $query->bindValue(1, $round_price_by);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//profit margins---[]

//add
if (isset($_POST["profit_margin_add_from"]))
{
   $profit_margin_add_from = filter_fields($_POST["profit_margin_add_from"]);
   $profit_margin_add_to = filter_fields($_POST["profit_margin_add_to"]);
   $profit_margin_add_margin = filter_fields($_POST["profit_margin_add_margin"]);
   $profit_margin_add_fixed_margin = filter_fields($_POST["profit_margin_add_fixed_margin"]);
   $profit_margin_add_paypal_fees_percentage = filter_fields($_POST["profit_margin_add_paypal_fees_percentage"]);
   $profit_margin_add_paypal_fees_fixed = filter_fields($_POST["profit_margin_add_paypal_fees_fixed"]);
   $profit_margin_add_eBay_fees = filter_fields($_POST["profit_margin_add_eBay_fees"]);
   
   $query = $connection->prepare("INSERT INTO settings (value_1, value_2, value_3, value_4, value_5, value_6, value_7, detail, value_8)
                                                 VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");
   
   $query->bindValue(1, $profit_margin_add_from);
   $query->bindValue(2, $profit_margin_add_to);
   $query->bindValue(3, $profit_margin_add_margin);
   $query->bindValue(4, $profit_margin_add_fixed_margin);
   $query->bindValue(5, $profit_margin_add_paypal_fees_percentage);
   $query->bindValue(6, $profit_margin_add_paypal_fees_fixed);
   $query->bindValue(7, $profit_margin_add_eBay_fees);#
   $query->bindValue(8, "profit_margin");
   $query->bindValue(9, $eBay_username);
   
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "Profit margin added!";
   header('location: settings.php');
}

//edit
if (isset($_POST["profit_margin_edit_from"]))
{
   $profit_margin_edit_ID = $_POST["profit_margin_edit_ID"];
   
   $profit_margin_add_from = filter_fields($_POST["profit_margin_edit_from"]);
   $profit_margin_add_to = filter_fields($_POST["profit_margin_edit_to"]);
   $profit_margin_add_margin = filter_fields($_POST["profit_margin_edit_margin"]);
   $profit_margin_add_fixed_margin = filter_fields($_POST["profit_margin_edit_fixed_margin"]);
   $profit_margin_add_paypal_fees_percentage = filter_fields($_POST["profit_margin_edit_paypal_fees_percentage"]);
   $profit_margin_add_paypal_fees_fixed = filter_fields($_POST["profit_margin_edit_paypal_fees_fixed"]);
   $profit_margin_add_eBay_fees = filter_fields($_POST["profit_margin_edit_eBay_fees"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ?, value_2 = ?, value_3 = ?, value_4 = ?, value_5 = ?, value_6 = ?, value_7 = ? WHERE id = '$profit_margin_edit_ID'");
   
   $query->bindValue(1, $profit_margin_add_from);
   $query->bindValue(2, $profit_margin_add_to);
   $query->bindValue(3, $profit_margin_add_margin);
   $query->bindValue(4, $profit_margin_add_fixed_margin);
   $query->bindValue(5, $profit_margin_add_paypal_fees_percentage);
   $query->bindValue(6, $profit_margin_add_paypal_fees_fixed);
   $query->bindValue(7, $profit_margin_add_eBay_fees);
   
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//seller profiles
if (isset($_POST["seller_profile_payment"]))
{
   $seller_profile_payment_ID = $_POST["seller_profile_payment"];
   $seller_profile_return_policy_ID = $_POST["seller_profile_return_policy"];
   $seller_profile_shipping_ID = $_POST["seller_profile_shipping"];
   
   $query = $connection->prepare("UPDATE settings SET value_2 = '$seller_profile_payment_ID' WHERE detail = 'seller_profile' and value_1 = 'payment' and value_8 = '$eBay_username'");
   $query->execute();
   
   $query = $connection->prepare("UPDATE settings SET value_2 = '$seller_profile_return_policy_ID' WHERE detail = 'seller_profile' and value_1 = 'return_policy' and value_8 = '$eBay_username'");
   $query->execute();
   
   $query = $connection->prepare("UPDATE settings SET value_2 = '$seller_profile_shipping_ID' WHERE detail = 'seller_profile' and value_1 = 'shipping' and value_8 = '$eBay_username'");
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//---[]

//eBay API
if (isset($_POST["eBay_API_DevID"]))
{
   $eBay_API_DevID = filter_fields($_POST["eBay_API_DevID"]);
   $eBay_API_AppID = filter_fields($_POST["eBay_API_AppID"]);
   $eBay_API_CertID = filter_fields($_POST["eBay_API_CertID"]);
   $eBay_API_runame = filter_fields($_POST["eBay_API_runame"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ?, value_2 = ?, value_3 = ?, value_4 = ? WHERE detail = 'eBay_API'");
   $query->bindValue(1, $eBay_API_DevID);
   $query->bindValue(2, $eBay_API_AppID);
   $query->bindValue(3, $eBay_API_CertID);
   $query->bindValue(4, $eBay_API_runame);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//eBay out of stock setting enable
if (isset($_POST["enable_eBay_out_of_stock_setting"]))
{
   //set site ID
   $siteId = Constants\SiteIds::GB;
   
   //create service object
   $service = new Services\TradingService([
      'credentials' => $eBay_credentials,
      'sandbox'     => $sandbox_active,
      'siteId'      => $siteId
   ]);
   
   $request = new Types\SetUserPreferencesRequestType();
   
   //set access token
   $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
   $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
   
   $request->OutOfStockControlPreference = true;
   
   $response = $service->setUserPreferences($request);
   $response = json_decode($response); //convert object response into array
   $response = json_decode(json_encode($response), true);
   
   if ($response["Ack"] != "Success")
   {
      echo '<pre>',print_r($response),'</pre>';
   }
   else
   {
      $_SESSION["msg_not"] = "eBay 'Out Of Stock' setting enabled!";
      header('location: settings.php');
   }
}

//Amazon API
if (isset($_POST["amazon_API_marketplace_ID"]))
{
   $amazon_API_marketplace_ID = filter_fields($_POST["amazon_API_marketplace_ID"]);
   $amazon_API_seller_ID = filter_fields($_POST["amazon_API_seller_ID"]);
   $amazon_API_access_key_ID = filter_fields($_POST["amazon_API_access_key_ID"]);
   $amazon_API_secret_access_key = filter_fields($_POST["amazon_API_secret_access_key"]);
   
   $query = $connection->prepare("UPDATE settings SET value_1 = ?, value_2 = ?, value_3 = ?, value_4 = ? WHERE detail = 'amazon_API'");
   $query->bindValue(1, $amazon_API_marketplace_ID);
   $query->bindValue(2, $amazon_API_seller_ID);
   $query->bindValue(3, $amazon_API_access_key_ID);
   $query->bindValue(4, $amazon_API_secret_access_key);
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "All changes saved!";
   header('location: settings.php');
}

//---[]

//update prices button
if (isset($_POST["update_prices"]))
{
   //change original_price to 1.00 in imported_products table
   $query = $connection->prepare("UPDATE imported_products SET original_price = '1.00'");
   $query->execute();
   
   //reload page
   $_SESSION["msg_not"] = "Prices will be updated within 24-48 hours!";
   header('location: settings.php');
}

//---grab eBay shipping services, 'out of stock' feature and seller profiles---[]

if (isset($_SESSION["eBay_access_token"]))
{
   //set site ID
   $siteId = Constants\SiteIds::GB;
   
   //create service object
   $service = new Services\TradingService([
      'credentials' => $eBay_credentials,
      'sandbox'     => $sandbox_active,
      'siteId'      => $siteId
   ]);
   
   $request = new Types\GeteBayDetailsRequestType();
   
   //set access token
   $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
   $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
   
   $request->DetailName = ['ShippingServiceDetails'];
   
   $response = $service->geteBayDetails($request);
   $response = json_decode($response); //convert object response into array
   $response = json_decode(json_encode($response), true);
   
   $eBay_shipping_services = $response["ShippingServiceDetails"];
   
   //---
   
   $request = new Types\GetUserPreferencesRequestType();
   
   //set access token
   $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
   $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
   
   $request->ShowOutOfStockControlPreference = true;
   $request->ShowSellerProfilePreferences = true;
   
   $response = $service->getUserPreferences($request);
   $response = json_decode($response); //convert object response into array
   $response = json_decode(json_encode($response), true);
   
   $out_of_stock_setting = $response["OutOfStockControlPreference"];
   
   $seller_profiles = $response["SellerProfilePreferences"]["SupportedSellerProfiles"]["SupportedSellerProfile"];
   
   //---
   
   if (isset($_SESSION["business_policies_on"]))
   {
      //grab a seller profile row
      $query = $connection->prepare("SELECT * FROM settings WHERE detail = 'seller_profile' and value_8 = '$eBay_username'");
      $query->execute();
      $settings_profiles = $query->fetchAll();
      
      //if missing, create rows
      if (empty($settings_profiles))
      {
         $query = $connection->prepare("INSERT INTO settings (detail, value_1 ,value_8)
                                                       VALUES(?, ?, ?)");
         $query->bindValue(1, 'seller_profile');
         $query->bindValue(2, 'payment');
         $query->bindValue(3, $eBay_username);
         
         $query->execute();
         
         //---
         
         $query = $connection->prepare("INSERT INTO settings (detail, value_1 ,value_8)
                                                       VALUES(?, ?, ?)");
         $query->bindValue(1, 'seller_profile');
         $query->bindValue(2, 'return_policy');
         $query->bindValue(3, $eBay_username);
         
         $query->execute();
         
         //---
         
         $query = $connection->prepare("INSERT INTO settings (detail, value_1 ,value_8)
                                                       VALUES(?, ?, ?)");
         $query->bindValue(1, 'seller_profile');
         $query->bindValue(2, 'shipping');
         $query->bindValue(3, $eBay_username);
         
         $query->execute();
      }
      
      //grab profile IDs
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
}

//---[]

//---create eBay login button URL---[]

$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, 1); //set request to POST (GET by default)
curl_setopt($ch, CURLOPT_URL, $eBay_login_endpoint);

$headers = array(
'Content-Type: text/xml',
'X-EBAY-API-COMPATIBILITY-LEVEL: ' . '1047',
'X-EBAY-API-DEV-NAME: ' . $eBay_credentials["devId"],
'X-EBAY-API-APP-NAME: ' . $eBay_credentials["appId"],
'X-EBAY-API-CERT-NAME: ' . $eBay_credentials["certId"],
'X-EBAY-API-CALL-NAME: GetSessionID',
'X-EBAY-API-SITEID: 3');

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$requestBody = '<?xml version="1.0" encoding="utf-8" ?>';
$requestBody .= '<GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
$requestBody .= "<RuName>" . $eBay_login_runame . "</RuName>";
$requestBody .= '</GetSessionIDRequest>';

curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
$result = curl_exec($ch);
curl_close($ch);

$result = simplexml_load_string($result); //convert XML response to object
$eBay_session_ID = (string)$result->SessionID;
$_SESSION["eBay_session_ID"] = $eBay_session_ID;

$eBay_login_btn_URL = $eBay_login_url . $eBay_login_runame . "&SessID=" . urlencode($eBay_session_ID);

//---[]

//grab settings
$query = $connection->prepare("SELECT * FROM settings");
$query->execute();
$settings = $query->fetchAll();

$eBay_API_DevID = $settings[0]["value_1"];
$eBay_API_AppID = $settings[0]["value_2"];
$eBay_API_CertID = $settings[0]["value_3"];
$eBay_API_runame = $settings[0]["value_4"];

$amazon_API_marketplace_ID = $settings[1]["value_1"];
$amazon_API_seller_ID = $settings[1]["value_2"];
$amazon_API_access_key_ID = $settings[1]["value_3"];
$amazon_API_secret_access_key = $settings[1]["value_4"];

if (isset($_SESSION["eBay_access_token"]))
{
   //---import process settings---[]
   
   //grab quantity setting
   $query = $connection->prepare("SELECT * FROM settings WHERE detail = 'quantity' and value_8 = '$eBay_username'");
   $query->execute();
   $quantity_setting = $query->fetch();
   
   //if missing, create rows
   if (empty($quantity_setting))
   {
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('quantity', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('product_location', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('paypal_address', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('max_dispatch_time', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('shipping', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('listing_duration', '$eBay_username')");
      $query->execute();
      
      $query = $connection->prepare("INSERT INTO settings (detail, value_8)
                                                    VALUES('round_price_by', '$eBay_username')");
      $query->execute();
      
      $quantity = "";
      $city = "";
      $post_code = "";
      $paypal_address = "";
      $max_dispatch_time = "";
      $shipping_priority = "";
      $shipping_service = "";
      $shipping_cost = "";
      $shipping_additional_cost = "";
      $listing_duration = "";
      $round_price_by = "";
   }
   else
   {
      //grab import process settings
      $query = $connection->prepare("SELECT * FROM settings WHERE value_8 = '$eBay_username'");
      $query->execute();
      $settings = $query->fetchAll();
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "quantity")
         {
            $quantity = $settings_ind["value_1"];
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "product_location")
         {
            $city = $settings_ind["value_1"];
            $post_code = $settings_ind["value_2"];
            
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "paypal_address")
         {
            $paypal_address = $settings_ind["value_1"];
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "max_dispatch_time")
         {
            $max_dispatch_time = $settings_ind["value_1"];
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "shipping")
         {
            $shipping_priority = $settings_ind["value_1"];
            $shipping_service = $settings_ind["value_2"];
            $shipping_cost = $settings_ind["value_3"];
            $shipping_additional_cost = $settings_ind["value_4"];
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "listing_duration")
         {
            $listing_duration = $settings_ind["value_1"];
            break;
         }
      }
      
      foreach ($settings as $settings_ind)
      {
         if ($settings_ind["detail"] == "round_price_by")
         {
            $round_price_by = $settings_ind["value_1"];
            break;
         }
      }
   }
   
   //---[]
   
   //grab profit margins from settings
   $query = $connection->prepare("SELECT * FROM settings WHERE detail = 'profit_margin' and value_8 = '$eBay_username'");
   $query->execute();
   $profit_margins = $query->fetchAll();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Settings | Data Feed</title>
      
      <!-- Sweet Alert css -->
        <link href="../assets/plugins/bootstrap-sweetalert/sweet-alert.css" rel="stylesheet" type="text/css" />

		<link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/menu.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/responsive.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="../assets/js/modernizr.min.js"></script>

	</head>

	<body class="fixed-left">
      
      <!---alert modal--->
      <div class="sweet-overlay" tabIndex="-1"></div><div class="sweet-alert" tabIndex="-1"><div class="icon error"><span class="x-mark"><span class="line left"></span><span class="line right"></span></span></div><div class="icon warning"> <span class="body"></span> <span class="dot"></span> </div> <div class="icon info"></div> <div class="icon success"> <span class="line tip"></span> <span class="line long"></span> <div class="placeholder"></div> <div class="fix"></div> </div> <div class="icon custom"></div> <h2>Title</h2><p class="lead text-muted">Text</p><p><button class="cancel btn btn-lg" tabIndex="2">Cancel</button> <button class="confirm btn btn-lg" tabIndex="1">OK</button></p></div>

		<!-- Begin page -->
		<div id="wrapper">

            <!-- Top Bar Start -->
            <div class="topbar">

                <!-- LOGO -->
                <div class="topbar-left">
                    <div class="text-center">
                        <a href="" class="logo">
                            <i class="zmdi zmdi-toys icon-c-logo"></i><span>Data Feed</span>
                            <!--<span><img src="assets/images/logo.png" alt="logo" style="height: 20px;"></span>-->
                        </a>
                    </div>
                </div>

                <!-- Button mobile view to collapse sidebar menu -->
                <div class="navbar navbar-default" role="navigation">
                    <div class="container">
                        <div class="">
                            <div class="pull-left">
                                <button class="button-menu-mobile open-left waves-effect waves-light">
                                    <i class="zmdi zmdi-menu"></i>
                                </button>
                                <span class="clearfix"></span>
                            </div>


                            <ul class="nav navbar-nav navbar-right pull-right">
                                
                            </ul>

                        </div>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
            <!-- Top Bar End -->


            <!-- ========== Left Sidebar Start ========== -->

            <div class="left side-menu">
                <div class="sidebar-inner slimscrollleft">
                    <!--- Divider -->
                    <div id="sidebar-menu">
                        <ul>

                        	<li class="text-muted menu-title">Navigation</li>

                           <li class="has_sub">
                                <a href="dashboard.php" class="waves-effect"><i class="zmdi zmdi-view-dashboard"></i> <span> Dashboard </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="import.php" class="waves-effect"><i class="zmdi zmdi-cloud-upload"></i> <span> Import </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="import_hydra.php" class="waves-effect"><i class="zmdi zmdi-cloud-upload"></i> <span> Import Hydra</span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="imported_products.php?offset=0" class="waves-effect"><i class="zmdi zmdi-shopping-basket"></i> <span> Imported Products </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="import_fail_history.php" class="waves-effect"><i class="zmdi zmdi-close-circle"></i> <span> Import Fail History </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="tracking.php" class="waves-effect"><i class="zmdi zmdi-rotate-right"></i> <span> Tracking </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="spy.php" class="waves-effect"><i class="zmdi zmdi-search-for"></i> <span> Spy </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="" class="waves-effect"><i class="zmdi zmdi-settings"></i> <span> Settings </span> </a>
                            </li>
                           
                            <li class="has_sub">
                                <a href="../actions/logout.php" class="waves-effect"><i class="fa fa-user"></i> <span> Logout </span> </a>
                            </li>

                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="clearfix"></div>

                </div>
            </div>
			<!-- Left Sidebar End -->

			<!-- ============================================================== -->
			<!-- Start right Content here -->
			<!-- ============================================================== -->
			<div class="content-page">
				<!-- Start content -->
				<div class="content">
					<div class="container">

						<!-- Page-Title -->
                  <div class="row">
                      <div class="col-sm-12">
                          <h4 class="page-title">Settings</h4>
                      </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-sm-4">
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">Personal</h4>
                           <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                               <div class="form-group">
                                   <label for="password" class="col-sm-3 control-label">Password</label>
                                   <div class="col-sm-9">
                                     <input type="password" class="form-control" name="password" placeholder="Password" required>
                                   </div>
                               </div>
                               <div class="form-group m-b-0">
                                   <div class="col-sm-offset-3 col-sm-9">
                                     <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                   </div>
                               </div>
                           </form>
                        </div>
                     </div>
                     <div class="col-sm-4">
                        
                     </div>
                     <div class="col-sm-4">
                        
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-sm-6">
                        <!---Amazon API--->
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">Amazon API</h4>
                           <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                               <div class="form-group">
                                   <label for="amazon_API_marketplace_ID" class="col-sm-3 control-label">Marketplace ID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="amazon_API_marketplace_ID" placeholder="Marketplace ID" value="<?php echo $amazon_API_marketplace_ID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="amazon_API_seller_ID" class="col-sm-3 control-label">Seller ID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="amazon_API_seller_ID" placeholder="Seller ID" value="<?php echo $amazon_API_seller_ID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="amazon_API_access_key_ID" class="col-sm-3 control-label">Access Key ID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="amazon_API_access_key_ID" placeholder="Access Key ID" value="<?php echo $amazon_API_access_key_ID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="amazon_API_secret_access_key" class="col-sm-3 control-label">Secret Access Key</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="amazon_API_secret_access_key" placeholder="Secret Access Key" value="<?php echo $amazon_API_secret_access_key; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group m-b-0">
                                   <div class="col-sm-offset-3 col-sm-9">
                                     <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                   </div>
                               </div>
                           </form>
                        </div>
                     </div>
                     <div class="col-sm-6">
                        <!---eBay API--->
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">eBay API</h4>
                           <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                               <div class="form-group">
                                   <label for="eBay_API_DevID" class="col-sm-3 control-label">DevID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="eBay_API_DevID" placeholder="DevID" value="<?php echo $eBay_API_DevID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="eBay_API_AppID" class="col-sm-3 control-label">AppID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="eBay_API_AppID" placeholder="AppID" value="<?php echo $eBay_API_AppID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="eBay_API_CertID" class="col-sm-3 control-label">CertID</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="eBay_API_CertID" placeholder="CertID" value="<?php echo $eBay_API_CertID; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group">
                                   <label for="eBay_API_runame" class="col-sm-3 control-label">RuName</label>
                                   <div class="col-sm-9">
                                     <input type="text" class="form-control" name="eBay_API_runame" placeholder="RuName" value="<?php echo $eBay_API_runame; ?>" required>
                                   </div>
                               </div>
                               <div class="form-group m-b-0">
                                   <div class="col-sm-offset-3 col-sm-9">
                                     <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                   </div>
                               </div>
                           </form>
                           
                           <br/>
                           <div class="panel panel-color panel-inverse">
                               <div class="panel-heading">
                                   <h3 class="panel-title">Login Redirect Accept URL</h3>
                               </div>
                               <div class="panel-body">
                                   <p>https://arhamrasool.com/pages/settings_eBay_login_func.php</p>
                               </div>
                           </div>
                           
                           <a type="button" class="btn btn-inverse waves-effect w-md m-b-5" href="<?php echo $eBay_login_btn_URL; ?>">Login/Switch</a>
                           <?php if (isset($_SESSION["eBay_access_token"])) { ?>
                              <button type="button" class="btn btn-success waves-effect w-md m-b-5">Logged in as: <?php echo $_SESSION["eBay_username"]; ?></button>
                           <?php } else { ?>
                              <button type="button" class="btn btn-danger waves-effect w-md m-b-5">Not logged in</button>
                           <?php } ?>
                           
                           <br/><br/>
                           <div class="panel panel-color panel-inverse">
                               <div class="panel-heading">
                                   <h3 class="panel-title">Out Of Stock Setting</h3>
                               </div>
                               <div class="panel-body">
                                   <?php if (!isset($_SESSION["eBay_access_token"])) { ?>
                                      <button type="button" class="btn btn-danger waves-effect w-md m-b-5">Not logged in</button>
                                   <?php } else if (isset($_SESSION["eBay_access_token"]) and (empty($out_of_stock_setting) or $out_of_stock_setting == 0)) { ?>
                                      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                         <input type="hidden" name="enable_eBay_out_of_stock_setting" value="1">
                                         <button type="submit" class="btn btn-danger waves-effect w-md m-b-5">Enable</button>
                                      </form>
                                   <?php } else if (isset($_SESSION["eBay_access_token"]) and (!empty($out_of_stock_setting) or $out_of_stock_setting == 1)) { ?>
                                      <button type="submit" class="btn btn-success waves-effect w-md m-b-5">Enabled</button>
                                   <?php } ?>
                               </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <!---Import Process--->
                  <div class="row <?php if (!isset($_SESSION["eBay_access_token"])) { echo "hidden"; } ?>">
                     <div class="col-sm-12">
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">Import Process</h4>
                           <div class="row">
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Quantity</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="quantity" class="col-sm-3 control-label">(Whole Number)</label>
                                         <div class="col-sm-9">
                                           <input type="text" class="form-control" name="quantity" placeholder="(Whole Number)" value="<?php echo $quantity; ?>" required>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Product Location</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="product_location_city" class="col-sm-3 control-label">City</label>
                                         <div class="col-sm-9">
                                           <input type="text" class="form-control" name="product_location_city" placeholder="City" value="<?php echo $city; ?>" required>
                                         </div>
                                     </div>
                                    <div class="form-group">
                                         <label for="product_location_post_code" class="col-sm-3 control-label">Post Code</label>
                                         <div class="col-sm-9">
                                           <input type="text" class="form-control" name="product_location_post_code" placeholder="Post Code" value="<?php echo $post_code; ?>" required>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">PayPal Address</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="paypal_address" class="col-sm-3 control-label">PayPal Address</label>
                                         <div class="col-sm-9">
                                           <input type="text" class="form-control" name="paypal_address" placeholder="PayPal Address" value="<?php echo $paypal_address; ?>" required>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                           </div>
                           <br/><br/>
                           <div class="row">
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Max Dispatch Time</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="max_dispatch_time" class="col-sm-3 control-label">(Whole Number)</label>
                                         <div class="col-sm-9">
                                           <input type="text" class="form-control" name="max_dispatch_time" placeholder="(Whole Number)" value="<?php echo $max_dispatch_time; ?>" required>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Listing Duration</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="max_dispatch_time" class="col-sm-3 control-label">Options</label>
                                         <div class="col-sm-9">
                                           <select class="form-control" name="listing_duration" required>
                                               <option value="">Select...</option>
                                               <option <?php if ($listing_duration == "Days_1") { ?>  selected="selected" <?php } ?> >Days_1</option>
                                               <option <?php if ($listing_duration == "Days_3") { ?>  selected="selected" <?php } ?> >Days_3</option>
                                               <option <?php if ($listing_duration == "Days_5") { ?>  selected="selected" <?php } ?> >Days_5</option>
                                               <option <?php if ($listing_duration == "Days_7") { ?>  selected="selected" <?php } ?> >Days_7</option>
                                               <option <?php if ($listing_duration == "Days_10") { ?>  selected="selected" <?php } ?> >Days_10</option>
                                               <option <?php if ($listing_duration == "Days_14") { ?>  selected="selected" <?php } ?> >Days_14</option>
                                               <option <?php if ($listing_duration == "Days_21") { ?>  selected="selected" <?php } ?> >Days_21</option>
                                               <option <?php if ($listing_duration == "Days_30") { ?>  selected="selected" <?php } ?> >Days_30</option>
                                               <option <?php if ($listing_duration == "Days_60") { ?>  selected="selected" <?php } ?> >Days_60</option>
                                               <option <?php if ($listing_duration == "Days_90") { ?>  selected="selected" <?php } ?> >Days_90</option>
                                               <option <?php if ($listing_duration == "Days_120") { ?>  selected="selected" <?php } ?> >Days_120</option>
                                               <option <?php if ($listing_duration == "GTC") { ?>  selected="selected" <?php } ?> >GTC</option>
                                           </select>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Shipping</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                    <div class="form-group">
                                        <label for="shipping_priority" class="col-sm-3 control-label">Shipping Priority</label>
                                        <div class="col-sm-9">
                                          <input type="text" class="form-control" name="shipping_priority" placeholder="Shipping Priority" value="<?php echo $shipping_priority; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="shipping_service" class="col-sm-3 control-label">Shipping Service</label>
                                        <div class="col-sm-9">
                                          <select class="form-control" name="shipping_service" <?php if (!isset($_SESSION["eBay_access_token"])) { ?> style="color:#e74c3c;" <?php } ?> required>
                                             <?php if (!isset($_SESSION["eBay_access_token"])) { ?>
                                                <option value="">NOT LOGGED IN</option>
                                             <?php } else { ?>
                                                <option value="">Select...</option>
                                             <?php } ?>
                                             <?php foreach ($eBay_shipping_services as $eBay_shipping_service) { ?>
                                                <option <?php if ($shipping_service == $eBay_shipping_service["ShippingService"]) { ?> selected="selected" <?php } ?> ><?php echo $eBay_shipping_service["ShippingService"]; ?></option>
                                             <?php } ?>
                                          </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="shipping_cost" class="col-sm-3 control-label">Shipping Cost (0.00)</label>
                                        <div class="col-sm-9">
                                          <input type="text" class="form-control" name="shipping_cost" placeholder="Shipping Cost (0.00)" value="<?php echo $shipping_cost; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="shipping_additional_cost" class="col-sm-3 control-label">Shipping Additional Cost (0.00)</label>
                                        <div class="col-sm-9">
                                          <input type="text" class="form-control" name="shipping_additional_cost" placeholder="Shipping Additional Cost (0.00)" value="<?php echo $shipping_additional_cost; ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group m-b-0">
                                        <div class="col-sm-offset-3 col-sm-9">
                                          <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                        </div>
                                    </div>
                                 </form>
                              </div>
                           </div>
                           <br/><br/>
                           <div class="row" <?php if (!isset($_SESSION["eBay_access_token"])) { ?> hidden <?php } ?> >
                              <div class="col-sm-12">
                                 <div class="panel panel-color panel-inverse">
                                     <div class="panel-heading">
                                         <h3 class="panel-title">Profit Margins</h3>
                                     </div>
                                     <div class="panel-body">
                                        <div class="row">
                                           <div class="col-sm-6">
                                              <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_from" class="col-sm-3 control-label">From (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_from" placeholder="From (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_to" class="col-sm-3 control-label">To (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_to" placeholder="To (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_margin" class="col-sm-3 control-label">Margin (%) (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_margin" placeholder="Margin (%) (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_fixed_margin" class="col-sm-3 control-label">Fixed Margin (&pound;) (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_fixed_margin" placeholder="Fixed Margin (&pound;) (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_paypal_fees_percentage" class="col-sm-3 control-label">Paypal Fees (%) (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_paypal_fees_percentage" placeholder="Paypal Fees (%) (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_paypal_fees_fixed" class="col-sm-3 control-label">Paypal Fees (&pound;) (0.00)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_paypal_fees_fixed" placeholder="Paypal Fees (&pound;) (0.00)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group">
                                                     <label for="profit_margin_add_eBay_fees" class="col-sm-3 control-label">eBay Fees (%) (Whole)</label>
                                                     <div class="col-sm-9">
                                                       <input type="text" class="form-control" name="profit_margin_add_eBay_fees" placeholder="eBay Fees (%) (Whole)" required>
                                                     </div>
                                                 </div>
                                                 <div class="form-group m-b-0">
                                                     <div class="col-sm-offset-3 col-sm-9">
                                                       <button type="submit" class="btn btn-info waves-effect waves-light">Add</button>
                                                     </div>
                                                 </div>
                                              </form>
                                           </div>
                                           <div class="col-sm-6">
                                              <?php if (empty($profit_margins)) { ?>
                                                 <p style="text-align:center;vertical-align:center;">No profit margins.</p>
                                              <?php } else { ?>
                                                 <?php foreach ($profit_margins as $profit_margin) { ?>
                                                    <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_from" class="col-sm-3 control-label">From (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_from" placeholder="From (Whole)" value="<?php echo $profit_margin["value_1"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_to" class="col-sm-3 control-label">To (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_to" placeholder="To (Whole)" value="<?php echo $profit_margin["value_2"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_margin" class="col-sm-3 control-label">Margin (%) (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_margin" placeholder="Margin (%) (Whole)" value="<?php echo $profit_margin["value_3"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_fixed_margin" class="col-sm-3 control-label">Fixed Margin (&pound;) (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_fixed_margin" placeholder="Fixed Margin (&pound;) (Whole)" value="<?php echo $profit_margin["value_4"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_paypal_fees_percentage" class="col-sm-3 control-label">Paypal Fees (%) (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_paypal_fees_percentage" placeholder="Paypal Fees (%) (Whole)" value="<?php echo $profit_margin["value_5"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_paypal_fees_fixed" class="col-sm-3 control-label">Paypal Fees (&pound;) (0.00)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_paypal_fees_fixed" placeholder="Paypal Fees (&pound;) (0.00)" value="<?php echo $profit_margin["value_6"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="profit_margin_edit_eBay_fees" class="col-sm-3 control-label">eBay Fees (%) (Whole)</label>
                                                           <div class="col-sm-9">
                                                             <input type="text" class="form-control" name="profit_margin_edit_eBay_fees" placeholder="eBay Fees (%) (Whole)" value="<?php echo $profit_margin["value_7"]; ?>" required>
                                                           </div>
                                                       </div>
                                                       <input type="hidden" name="profit_margin_edit_ID" value="<?php echo $profit_margin["id"]; ?>">
                                                       <div class="form-group m-b-0">
                                                           <div class="col-sm-offset-3 col-sm-9">
                                                             <button type="submit" class="btn btn-warning waves-effect waves-light">Edit</button>
                                                             <a type="button" class="btn btn-danger waves-effect waves-light" href="../actions/profit_margin_delete.php?id=<?php echo $profit_margin["id"]; ?>">Delete</a>
                                                           </div>
                                                       </div>
                                                    </form>
                                                    <br/><br/>
                                                 <?php } ?>
                                              <?php } ?>
                                           </div>
                                        </div>
                                        <div class="row">
                                           <div class="col-sm-12 text-center">
                                              <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                                 <input type="hidden" name="update_prices" value="1">
                                                 <button type="submit" class="btn btn-lg btn-success waves-effect w-md m-b-5">Update Prices</button>
                                              </form>
                                           </div>
                                        </div>
                                     </div>
                                 </div>
                              </div>
                           </div>
                           <br/><br/>
                           <div class="row">
                              <div class="col-sm-4">
                                 
                              </div>
                              <div class="col-sm-4">
                                 <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Round Price By</h4>
                                 <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                     <div class="form-group">
                                         <label for="round_price_by" class="col-sm-3 control-label">Options</label>
                                         <div class="col-sm-9">
                                           <select class="form-control" name="round_price_by" required>
                                               <option value="">Select...</option>
                                               <option <?php if ($round_price_by == 'Do Not Change') { ?> selected="selected" <?php } ?> >Do Not Change</option>
                                               <option <?php if ($round_price_by == 'Round To Nearest Whole Number') { ?> selected="selected" <?php } ?> >Round To Nearest Whole Number</option>
                                               <?php for ($x = 0.01;$x <= 1;$x += 0.01) { ?>
                                                  <option <?php if ((double)$round_price_by == number_format($x, 2, '.', '')) { ?> selected="selected" <?php } ?> ><?php echo number_format($x, 2, '.', ''); ?></option>
                                               <?php } ?>
                                           </select>
                                         </div>
                                     </div>
                                     <div class="form-group m-b-0">
                                         <div class="col-sm-offset-3 col-sm-9">
                                           <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                         </div>
                                     </div>
                                 </form>
                              </div>
                              <div class="col-sm-4">
                                 
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <?php if (isset($_SESSION["business_policies_on"])) { ?>
                     <!---Business Policies--->
                     <div class="row">
                        <div class="col-sm-4">
                           <div class="card-box">
                              <h4 class="header-title m-t-0 m-b-30">Business Policies</h4>
                              <h4 class="header-title m-t-0 m-b-30" style="color:black;text-align:center;">Seller Profiles</h4>
                              <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                 <div class="form-group">
                                     <label for="seller_profile_payment" class="col-sm-3 control-label">Payment</label>
                                     <div class="col-sm-9">
                                       <select class="form-control" name="seller_profile_payment" required>
                                          <option value="">Select...</option>
                                          <?php foreach ($seller_profiles as $seller_profile) { ?>
                                             <?php if ($seller_profile["ProfileType"] == "PAYMENT") { ?>
                                                <option value="<?php echo $seller_profile["ProfileID"]; ?>" <?php if ($seller_profile_payment_ID == $seller_profile["ProfileID"]) { ?> selected="selected" <?php } ?> ><?php echo $seller_profile["ProfileName"]; ?><?php if (!empty($seller_profile["ShortSummary"])) { echo " | " . $seller_profile["ShortSummary"]; } ?></option>
                                             <?php } ?>
                                          <?php } ?>
                                       </select>
                                     </div>
                                 </div>
                                 <div class="form-group">
                                     <label for="seller_profile_return_policy" class="col-sm-3 control-label">Return Policy</label>
                                     <div class="col-sm-9">
                                       <select class="form-control" name="seller_profile_return_policy" required>
                                          <option value="">Select...</option>
                                          <?php foreach ($seller_profiles as $seller_profile) { ?>
                                             <?php if ($seller_profile["ProfileType"] == "RETURN_POLICY") { ?>
                                                <option value="<?php echo $seller_profile["ProfileID"]; ?>" <?php if ($seller_profile_return_policy_ID == $seller_profile["ProfileID"]) { ?> selected="selected" <?php } ?> ><?php echo $seller_profile["ProfileName"]; ?><?php if (!empty($seller_profile["ShortSummary"])) { echo " | " . $seller_profile["ShortSummary"]; } ?></option>
                                             <?php } ?>
                                          <?php } ?>
                                       </select>
                                     </div>
                                 </div>
                                 <div class="form-group">
                                     <label for="seller_profile_shipping" class="col-sm-3 control-label">Shipping</label>
                                     <div class="col-sm-9">
                                       <select class="form-control" name="seller_profile_shipping" required>
                                          <option value="">Select...</option>
                                          <?php foreach ($seller_profiles as $seller_profile) { ?>
                                             <?php if ($seller_profile["ProfileType"] == "SHIPPING") { ?>
                                                <option value="<?php echo $seller_profile["ProfileID"]; ?>" <?php if ($seller_profile_shipping_ID == $seller_profile["ProfileID"]) { ?> selected="selected" <?php } ?> ><?php echo $seller_profile["ProfileName"]; ?><?php if (!empty($seller_profile["ShortSummary"])) { echo " | " . $seller_profile["ShortSummary"]; } ?></option>
                                             <?php } ?>
                                          <?php } ?>
                                       </select>
                                     </div>
                                 </div>
                                 <div class="form-group m-b-0">
                                     <div class="col-sm-offset-3 col-sm-9">
                                       <button type="submit" class="btn btn-info waves-effect waves-light">Change</button>
                                     </div>
                                 </div>
                              </form>
                           </div>
                        </div>
                        <div class="col-sm-4">
                           
                        </div>
                        <div class="col-sm-4">
                           
                        </div>
                     </div>
                  <?php } ?>


                    </div> <!-- container -->

                </div> <!-- content -->

            </div>
            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->


            


        </div>
        <!-- END wrapper -->

        <script>
            var resizefunc = [];
        </script>

        <!-- jQuery  -->
        <script src="../assets/js/jquery.min.js"></script>
        <script src="../assets/js/bootstrap.min.js"></script>
        <script src="../assets/js/detect.js"></script>
        <script src="../assets/js/fastclick.js"></script>
        <script src="../assets/js/jquery.slimscroll.js"></script>
        <script src="../assets/js/jquery.blockUI.js"></script>
        <script src="../assets/js/waves.js"></script>
        <script src="../assets/js/wow.min.js"></script>
        <script src="../assets/js/jquery.nicescroll.js"></script>
        <script src="../assets/js/jquery.scrollTo.min.js"></script>
      
      
      <!-- Sweet Alert js -->
        <script src="../assets/plugins/bootstrap-sweetalert/sweet-alert.js"></script>
        <script src="../assets/pages/jquery.sweet-alert.init.js"></script>
        

        <!-- Counter Up  -->
        <script src="../assets/plugins/waypoints/lib/jquery.waypoints.js"></script>
        <script src="../assets/plugins/counterup/jquery.counterup.min.js"></script>

        


        <!-- App js -->
        <script src="../assets/js/jquery.core.js"></script>
        <script src="../assets/js/jquery.app.js"></script>
      
      <!---ERRORS AND NOTIFICATIONS--->
      <?php if (!isset($_SESSION["eBay_access_token"])) { ?>
         <script>
            swal({
                title: "eBay Login",
                text: "Please login to an eBay account!",
                type: "error",
                showCancelButton: false,
                confirmButtonClass: 'btn-danger waves-effect waves-light',
                confirmButtonHref: 'settings.php',
                confirmButtonText: 'Ok'
            });
         </script>
      <?php } ?>
      
      <?php if (isset($msg_error)) { ?>
         <script>
            swal({
                title: "Error",
                text: "<?php echo $msg_error; ?>",
                type: "error",
                showCancelButton: false,
                confirmButtonClass: 'btn-danger waves-effect waves-light',
                confirmButtonText: 'Ok'
            });
         </script>
      <?php } ?>
      
      <?php if (isset($msg_not)) { ?>
         <script>
            swal("Success",
                 "<?php echo $msg_not; ?>",
                 "success");
         </script>
      <?php } ?>
	</body>
</html>

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