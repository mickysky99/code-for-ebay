<?php
session_start();

//if not logged in
if (!isset($_SESSION["logged_in"]))
{
   header('location: ../index.php');
}

//connect to the database
include_once("../includes/connection.php");

//grab tracking eBay errors
$query = $connection->prepare("SELECT * FROM tracking_eBay_errors ORDER BY id DESC LIMIT 1000");
$query->execute();
$tracking_records = $query->fetchAll();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Tracking | Data Feed</title>
      
      <!-- Sweet Alert css -->
        <link href="../assets/plugins/bootstrap-sweetalert/sweet-alert.css" rel="stylesheet" type="text/css" />

		<link href="../assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/menu.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/core.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/components.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="../assets/css/responsive.css" rel="stylesheet" type="text/css" />

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
                                <a href="" class="waves-effect"><i class="zmdi zmdi-rotate-right"></i> <span> Tracking </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="spy.php" class="waves-effect"><i class="zmdi zmdi-search-for"></i> <span> Spy </span> </a>
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
                          <h4 class="page-title">Tracking eBay Errors</h4>
                      </div>
                  </div>
            
                  
                  <div class="row">
                     <div class="col-sm-9">
                        <div class="card-box">
                           <?php if (empty($tracking_records)) { ?>
                              <p>No records.</p>
                           <?php } else { ?>
                              <h4 class="header-title m-t-0 m-b-30">Last 1000</h4>
                              <table class="table table-striped m-0">
                                 <thead>
                                    <tr>
                                        <th>eBay Product ID</th>
                                        <th>Error</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <?php foreach ($tracking_records as $tracking_record) { ?>
                                       <tr>
                                          <td><?php echo $tracking_record["eBay_product_ID"]; ?></td>
                                          <td><?php echo $tracking_record["error"]; ?></td>
                                       </tr>
                                    <?php } ?>
                                 </tbody>
                              </table>
                           <?php } ?>
                        </div>
                     </div>
                     <div class="col-sm-3">
                        
                     </div>
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