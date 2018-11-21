<?php
//connect to the database
include_once("../../includes/connection.php");

$query = $connection->prepare("SELECT * FROM spy_process WHERE processed = 'no'");
$query->execute();
$spy_process_items_not_processed = $query->fetchAll();

echo json_encode($spy_process_items_not_processed);
?>