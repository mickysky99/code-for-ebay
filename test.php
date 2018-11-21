<?php
//load SDKs (amazon-mws, hQuery, eBay-SDK-PHP)
require_once 'vendor/autoload.php';

//download page using Crawlera and cURL
$ch = curl_init();

$url = 'https://www.amazon.co.uk/gp/product/B01BLKZXSG';
$proxy = 'proxy.crawlera.com:8010';
$proxy_auth = 'd4ca9b647c284b6a913d1c5d618415fb:';

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
   //parse page with hQuery
   $doc = hQuery::fromHTML($scraped_page);
   
   $option_elements = $doc->find('option', 'selected=selected');
   
   $amazon_category = $option_elements->text();
   
   echo $amazon_category;
}

curl_close($ch);
?>