<?php
$server_name = $_SERVER['SERVER_NAME'];
echo 'fdsa'.$server_name;

if (strpos($server_name, "codio") == false)
{
   try
   {
      $connection = new PDO('mysql:host=localhost;dbname=arham_data_feed', 'root', '', [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
      $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }
      catch (PDOException $error)
   {
      echo '<h3 style="color:blue;text-align:center;">', die('Website problem, please try later'), '</h3>';
   }
}
else
{
   try
   {
      $connection = new PDO('mysql:host=arham-data-feed.arhamrasool.com;dbname=arham_data_feed', 'arham', 'Blazew3vu@', [PDO::MYSQL_ATTR_LOCAL_INFILE => true]);
      $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }
      catch (PDOException $error)
   {
      echo '<h3 style="color:red;text-align:center;">', die('Website problem, please try later111'), '</h3>';
   }
}
?>