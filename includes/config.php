<?php
$server_name = $_SERVER['SERVER_NAME'];

if (strpos($server_name, "codio") !== false)
{
   $mine = 1;
}
else
{
   $mine = 0;
}

//---API---[]

if ($mine == 1)
{
   //Amazon MWS API
   $amazon_mws_credentials = array(
      'Marketplace_Id' => 'A1F83G8C2ARO7P',
      'Seller_Id' => 'A1YTZ27PNB8K1F',
      'Access_Key_ID' => 'AKIAJGE357OSD7JZPFTA',
      'Secret_Access_Key' => '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'
   );
   
   //eBay API
   $eBay_credentials = [
      'devId' => 'ffd010ba-95e1-4882-a4de-6fe181b065f0',
      'appId' => 'HaroonAl-test-SBX-55d7504c4-574524d9',
      'certId' => 'SBX-5d7504c496ba-4caa-45fc-93a6-c8c1'
   ];
   
   $eBay_credentials_p = [
      'devId' => 'ffd010ba-95e1-4882-a4de-6fe181b065f0', //production
      'appId' => 'HaroonAl-test-PRD-351ca6568-ba1191ce',
      'certId' => 'PRD-51ca65686c2b-595d-4fab-bef5-418a'
   ];
   
   $sandbox_active = true;
   
   $eBay_login_endpoint = 'https://api.sandbox.ebay.com/ws/api.dll';
   $eBay_login_runame = 'Haroon_Ali-HaroonAl-test-S-nfkhkz';
   $eBay_login_url = 'https://signin.sandbox.ebay.com/ws/eBayISAPI.dll?SignIn&runame=';
}
else
{
   //connect to the database
   include_once("connection.php");
   
   //grab settings
   $query = $connection->prepare("SELECT * FROM settings");
   $query->execute();
   $settings = $query->fetchAll();
   
   //Amazon MWS API
   $amazon_mws_credentials = array(
      'Marketplace_Id' => $settings[1]["value_1"],
      'Seller_Id' => $settings[1]["value_2"],
      'Access_Key_ID' => $settings[1]["value_3"],
      'Secret_Access_Key' => $settings[1]["value_4"]
   );
   
   //eBay API
   $eBay_credentials = [
      'devId' => $settings[0]["value_1"],
      'appId' => $settings[0]["value_2"],
      'certId' => $settings[0]["value_3"],
   ];
   
   $sandbox_active = false;
   
   $eBay_login_endpoint = 'https://api.ebay.com/ws/api.dll';
   $eBay_login_runame = $settings[0]["value_4"];
   $eBay_login_url = 'https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=';
}

$crawlera_API_key = "c77b5f615d6b434cb9aea2ebe14e2203:";
?>