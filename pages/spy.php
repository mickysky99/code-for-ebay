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

//when new process button is pressed
if (isset($_GET["new_process"]))
{
   //empty spy process table
   $query = $connection->prepare("TRUNCATE spy_process");
   
   $query->execute();
   
   //empty spy search results table
   $query = $connection->prepare("TRUNCATE spy_search_results");
   
   $query->execute();
   
   //reload page
   header('location: spy.php');
}

//grab spy process titles
$query = $connection->prepare("SELECT * FROM spy_process");
$query->execute();
$spy_process_items = $query->fetchAll();

//grab spy process titles are not processed
$query = $connection->prepare("SELECT * FROM spy_process WHERE processed = 'no'");
$query->execute();
$spy_process_items_not_processed = $query->fetchAll();

//grab how many spy process titles are processed
$query = $connection->prepare("SELECT count(*) FROM spy_process WHERE processed = 'yes'");
$query->execute();
$spy_process_items_processed = $query->fetchColumn();

//grab search results
$query = $connection->prepare("SELECT * FROM spy_search_results");
$query->execute();
$spy_search_results = $query->fetchAll();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Spy | Data Feed</title>
      
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
                                <a href="import_hydra.php" class="waves-effect"><i class="zmdi zmdi-cloud-upload"></i> <span> Import Hydra</span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="imported_products.php?offset=0" class="waves-effect"><i class="zmdi zmdi-shopping-basket"></i> <span> Imported Products </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="import_fail_history.php" class="waves-effect"><i class="zmdi zmdi-close-circle"></i> <span> Import Fail History </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="tracking.php" class="waves-effect"><i class="zmdi zmdi-rotate-right"></i> <span> Tracking </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="" class="waves-effect"><i class="zmdi zmdi-search-for"></i> <span> Spy </span> </a>
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
                          <h4 class="page-title">Spy</h4>
                      </div>
                  </div>
            
                  <!---NEW SPY PROCESS--->
                  <?php if (empty($spy_process_items)) { ?>
                     <div class="row">
                        <div class="col-sm-5">
                           <div class="card-box">
                              <h4 class="header-title m-t-0 m-b-30">Create Spy Process</h4>
                              <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
                                 <div class="form-group">
                                     <label for="eBay_seller_ID" class="col-sm-3 control-label">eBay Seller ID</label>
                                     <div class="col-sm-9">
                                       <input type="text" class="form-control" id="eBay_seller_ID" placeholder="eBay Seller ID" value="<?php if (isset($eBay_seller_ID)) { echo $eBay_seller_ID; } ?>" required>
                                     </div>
                                 </div>
                                 <div class="form-group m-b-0">
                                     <div class="col-sm-offset-3 col-sm-9">
                                        <button type="button" class="btn btn-info waves-effect waves-light" onclick="create_spy_process()">Create</button>
                                     </div>
                                 </div>
                              </form>
                              <h2 style="display:inline-block;" class="text-custom" id="pages_processed"></h2><h2 style="display:inline-block;" class="text-custom" id="pages_total"></h2><h2 style="display:inline-block;" class="text-custom" id="pages_heading"></h2>
                           </div>
                        </div>
                        <div class="col-sm-3">
                           
                        </div>
                        <div class="col-sm-4">
                           
                        </div>
                     </div>
                  <!---RUN SPY PROCESS--->
                  <?php } else if (!empty($spy_process_items) and sizeof($spy_process_items_not_processed) > 0) { ?>
                     <div class="row">
                        <div class="col-sm-5">
                           <div class="card-box">
                              <h4 class="header-title m-t-0 m-b-30">Run Spy Process</h4>
                              <h2 style="display:inline-block;" class="text-custom" data-plugin="counterup" id="titles_processed"><?php echo $spy_process_items_processed; ?></h2><h2 style="display:inline-block;" class="text-custom">/<?php echo sizeof($spy_process_items); ?></h2>
                              <br/><br/>
                              <?php if ($spy_process_items_processed == 0) { ?>
                                 <button class="btn btn-success waves-effect waves-light" id="start_btn" type="button" onclick='start_process()'>Start</button>
                                 <button class="btn btn-info waves-effect waves-light" id="pause_btn" type="button" disabled>Pause</button>
                                 <button class="btn btn-danger waves-effect waves-light" id="end_btn" type="button" onclick='end_process()'>End</button>
                              <?php } else { ?>
                                 <button class="btn btn-success waves-effect waves-light" id="start_btn" type="button" onclick='start_process()'>Continue</button>
                                 <button class="btn btn-info waves-effect waves-light" id="pause_btn" type="button" disabled>Pause</button>
                                 <button class="btn btn-danger waves-effect waves-light" id="end_btn" type="button" onclick='end_process()'>End</button>
                              <?php } ?>
                           </div>
                        </div>
                        <div class="col-sm-7">
                           <div class="card-box" id="error_box" hidden>
                              
                           </div>
                        </div>
                     </div>
                  <!---SPY PROCESS SEARCH RESULTS--->
                  <?php } else if (!empty($spy_process_items) and sizeof($spy_process_items_not_processed) == 0) { ?>
                     <div class="row">
                        <div class="col-sm-5">
                           <div class="card-box widget-user">
                              <div class="text-center">
                                 <h2 class="text-custom" data-plugin="counterup"><?php echo sizeof($spy_search_results); ?></h2>
                                 <h5>Found</h5>
                              </div>
                           </div>
                        </div>
                        <div class="col-sm-3">
                           
                        </div>
                        <div class="col-sm-4">
                           
                        </div>
                     </div>
                  
                     <a type="button" class="btn btn-success waves-effect waves-light" href="spy_make_csv.php" target="_blank">Download CSV</a><a type="button" class="btn btn-danger waves-effect waves-light" style="margin-left:10px;" href="spy.php?new_process=1">New Process</a>
                     <br/><br/>
                     <?php foreach ($spy_search_results as $search_result) { ?>
                        <div class="row">
                           <div class="col-sm-12">
                              <div class="panel panel-color panel-inverse">
                                  <div class="panel-heading">
                                      <h3 class="panel-title">eBay Listing Title<br/><?php echo $search_result["eBay_listing_title"]; ?></h3>
                                  </div>
                                  <div class="panel-body">
                                      <h4>eBay Brand</h4>
                                      <h5><?php echo $search_result["eBay_brand"]; ?></h5>
                                      <h4>Amazon Title</h4>
                                      <h5><?php echo $search_result["amazon_title"]; ?></h5>
                                      <h4>Amazon ASIN</h4>
                                      <h5><?php echo $search_result["asin"]; ?></h5>
                                  </div>
                              </div>
                           </div>
                        </div>
                     <?php } ?>
                  <?php } ?>
                  
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
      
      <script src="../includes/spy/js.js"></script>
      
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