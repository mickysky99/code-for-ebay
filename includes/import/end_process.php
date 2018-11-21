<?php
//connect to the database
include_once("../../includes/connection.php");

$query = $connection->prepare("UPDATE import_process SET processed = 'yes'");
$query->execute();
?>