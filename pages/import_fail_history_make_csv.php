<?php
session_start();

//connect to the database
include_once("../includes/connection.php");

//grab failed products
$query = $connection->prepare("SELECT * FROM import_process_fail_history");
$query->execute();
$fail_history_records = $query->fetchAll();

//recreate array
$fail_history_records_new = array();

foreach ($fail_history_records as $fail_history_record_ind)
{
   $temp_arry = array($fail_history_record_ind["asin"], $fail_history_record_ind["reason"]);
   array_push($fail_history_records_new, $temp_arry);
}

//create csv
$fp = fopen('php://output', 'w');
header("Content-Type:application/csv"); 
header("Content-Disposition:attachment;filename=import_process_fail_history.csv");

foreach ($fail_history_records_new as $fail_history_records_new_ind)
{
   fputcsv($fp, $fail_history_records_new_ind);
}

fclose($fp);
?>