<?php
//establish folder path
$server_name = $_SERVER['SERVER_NAME'];

if (strpos($server_name, "codio") !== false)
{
   //hquery cache folder file path
   $hquery_cache_folder_path = "../includes/hquery_cache";
}
else
{
   //hquery cache folder file path
   $hquery_cache_folder_path = "/home/dh_9vcw8d/arhamrasool.com/includes/hquery_cache";
}

//delete all files in folder
$files = glob($hquery_cache_folder_path . '/*');//get all file names

foreach($files as $file)
{
  if (is_file($file))
  {
     unlink($file);
  }
}
?>