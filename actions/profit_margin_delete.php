<?php
session_start();

//connect to the database
include_once("../includes/connection.php");

$profit_margin_ID = $_GET["id"];

//delete profit margin
$query = $connection->prepare("DELETE FROM settings WHERE id = '$profit_margin_ID'");
$query->execute();

//go back to settings page
$_SESSION["msg_not"] = "Profit margin deleted!";
header('location: ../pages/settings.php');
?>