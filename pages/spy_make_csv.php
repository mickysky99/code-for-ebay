<?php
session_start();

//connect to the database
include_once("../includes/connection.php");

//grab search results
$query = $connection->prepare("SELECT * FROM spy_search_results");
$query->execute();
$spy_search_results = $query->fetchAll();

//recreate array
$spy_search_results_new = array();

foreach ($spy_search_results as $search_result)
{
   $temp_arry = array($search_result["eBay_listing_title"], $search_result["eBay_brand"], $search_result["amazon_title"], $search_result["asin"]);
   array_push($spy_search_results_new, $temp_arry);
}

//create csv
$fp = fopen('php://output', 'w');
header("Content-Type:application/csv"); 
header("Content-Disposition:attachment;filename=spy_ASINs.csv");

foreach ($spy_search_results_new as $search_result)
{
   fputcsv($fp, $search_result);
}

fclose($fp);
?>