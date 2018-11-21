<?php
session_start();

//if not logged in
if (!isset($_SESSION["logged_in"]))
{
   header('location: ../index.php');
}

//if not logged into an eBay account
if (!isset($_SESSION["eBay_access_token"]))
{
   header('location: settings.php');
}

//connect to the database
include_once("../includes/connection.php");

//load config
include_once("../includes/config.php");

//notification msgs
if (isset($_SESSION["msg_not"]))
{
   $msg_not = $_SESSION["msg_not"];
   unset($_SESSION["msg_not"]);
}

$eBay_username = $_SESSION["eBay_username"];

//when CSV is uploaded
if (isset($_FILES["csv"]))
{
   //move file
   move_uploaded_file($_FILES["csv"]["tmp_name"], $_FILES["csv"]["tmp_name"] . ".csv");
   $csv_location = $_FILES["csv"]["tmp_name"] . ".csv";
   $table = 'imported_products';
   
   //connect to database
   $server_name = $_SERVER['SERVER_NAME'];
   
   if (strpos($server_name, "codio") !== false)
   {
      $cons = mysqli_connect("localhost", "root","pass15","sanjay_data_feed") or die(mysql_error());
   }
   else
   {
      $cons = mysqli_connect("arham-data-feed.arhamrasool.com", "arham","Blazew3vu@","arham_data_feed") or die(mysql_error());
   }
   
   //insert into database
   mysqli_query($cons, '
      LOAD DATA LOCAL INFILE "'.$csv_location.'"
      INTO TABLE '.$table.'
      FIELDS TERMINATED by \',\'
      LINES TERMINATED BY \'\r\n\'
      IGNORE 1 LINES
      (@asin, @dummy, @dummy, @dummy, eBay_product_ID)
      SET asin = TRIM(LEADING "https://amazon.co.uk/dp/" FROM @asin)
   ') or die("There was a problem!");
   
   //insert eBay username
   $query = $connection->prepare("UPDATE imported_products SET eBay_username = '$eBay_username' WHERE eBay_username IS NULL");
   $query->execute();
   
   //delete file from tmp folder
   unlink($csv_location);
   
   //reload page
   $_SESSION["msg_not"] = "All products imported!";
   header('location: import_hydra.php');
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Import | Data Feed</title>
      
      <!-- Sweet Alert css -->
        <link href="../assets/plugins/bootstrap-sweetalert/sweet-alert.css" rel="stylesheet" type="text/css" />

		<link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/menu.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/responsive.css" rel="stylesheet" type="text/css" />
      
        <link href="../assets/css/loading_animation.css" rel="stylesheet" type="text/css" />

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
                                <a href="" class="waves-effect"><i class="zmdi zmdi-cloud-upload"></i> <span> Import Hydra</span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="imported_products.php?offset=0" class="waves-effect"><i class="zmdi zmdi-shopping-basket"></i> <span> Imported Products </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="tracking.php" class="waves-effect"><i class="zmdi zmdi-rotate-right"></i> <span> Tracking </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="settings.php" class="waves-effect"><i class="zmdi zmdi-settings"></i> <span> Settings </span> </a>
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
                          <h4 class="page-title">Import</h4>
                      </div>
                  </div>


                  <div class="row hidden-xs">
                     <div class="col-sm-4">
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">Import From Hydra CSV</h4>
                           <p><b>No more than 200,000 rows in CSV.</b></p>
                           <p><b>ASINs within hyperlinks need to be in FIRST ROW and eBay itemIDs in FIFTH ROW</b></p>
                           <p><b>All other columns MUST BE BLANK</b></p>
                           <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                               <div class="form-group">
                                   <label for="csv" class="col-sm-3 control-label">CSV</label>
                                   <div class="col-sm-9">
                                     <input type="file" class="form-control" name="csv" multiple required>
                                   </div>
                               </div>
                               <div class="form-group m-b-0">
                                   <div class="col-sm-offset-3 col-sm-9">
                                     <button type="submit" class="btn btn-info waves-effect waves-light">Upload</button>
                                   </div>
                               </div>
                           </form>
                        </div>
                     </div>
                     <div class="col-sm-8">
                        <div class="card-box" id="error_box" hidden>
                           
                        </div>
                     </div>
                  </div>
                  <!---LOADER ANIMATION--->
                  <div class="sk-folding-cube" id="loading_animation" hidden>
                     <div class="sk-cube1 sk-cube"></div>
                     <div class="sk-cube2 sk-cube"></div>
                     <div class="sk-cube4 sk-cube"></div>
                     <div class="sk-cube3 sk-cube"></div>
                  </div>



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
      
      
      <script src="../includes/import/js.js"></script>
      
      <!---ERRORS AND NOTIFICATIONS--->
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