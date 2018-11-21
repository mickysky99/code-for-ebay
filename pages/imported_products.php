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

//---SDKs and API credentials---[]

//load SDKs (eBay-SDK-PHP)
require_once '../vendor/autoload.php';

//load config
include_once("../includes/config.php");

use \DTS\eBaySDK\Constants; //eBay-SDK-PHP
use \DTS\eBaySDK\Trading\Services;
use \DTS\eBaySDK\Trading\Types;
use \DTS\eBaySDK\Trading\Enums;

//---[]

//when create purchase link form is submitted
if (isset($_POST["eBay_product_ID"]))
{
   $eBay_product_ID = filter_fields($_POST["eBay_product_ID"]);
   
   //grab asin from database
   $query = $connection->prepare("SELECT asin FROM imported_products WHERE eBay_product_ID = '$eBay_product_ID'");
   $query->execute();
   $asin = $query->fetchColumn();
   
   //---grab product info from eBay---[]
   
   //set site ID
   $siteId = Constants\SiteIds::GB;
   
   //create service object
   $service = new Services\TradingService([
      'credentials' => $eBay_credentials,
      'sandbox'     => $sandbox_active,
      'siteId'      => $siteId
   ]);
   
   $request = new Types\GetItemRequestType();
   
   //set access token
   $request->RequesterCredentials = new Types\CustomSecurityHeaderType();
   $request->RequesterCredentials->eBayAuthToken = $_SESSION["eBay_access_token"];
   
   $request->ItemID = $eBay_product_ID;
   
   $response = $service->getItem($request);
   $response = json_decode($response); //convert object response into array
   $response = json_decode(json_encode($response), true);
   
   //---[]
   
   //if asin does not exist in database
   if (empty($asin))
   {
      $msg_error = "ASIN/itemID does not exist in database!";
   }
   //if info from eBay could not be obtained
   else if ($response["Ack"] != "Success")
   {
      $msg_error = "Could not grab product info from eBay. Please try again!";
   }
   else
   {
      $purchase_link = 'https://www.amazon.co.uk/gp/product/' . $asin . '?th=1&psc=1&tag=thepriceworth21';
      
      //if quantity on eBay is 0
      if ($response["Item"]["Quantity"] - $response["Item"]["SellingStatus"]["QuantitySold"] <= 0)
      {
         $query = $connection->prepare("UPDATE imported_products SET out_of_stock = 'yes' WHERE eBay_product_ID = '$eBay_product_ID'");
         $query->execute();
      }
   }
}

$requested_records = 20;
$offset = $_GET["offset"];

$eBay_username = $_SESSION["eBay_username"];

//grab total number of imported products
$query = $connection->prepare("SELECT count(*) FROM imported_products WHERE eBay_username = '$eBay_username'");
$query->execute();
$imported_products_total = $query->fetchColumn();

//grab imported products
$query = $connection->prepare("SELECT * FROM imported_products WHERE eBay_username = '$eBay_username' ORDER BY id DESC LIMIT " . $requested_records . " OFFSET " . $offset);
$query->execute();
$imported_products = $query->fetchAll();
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title>Imported Products | Data Feed</title>
      
      <!-- Sweet Alert css -->
        <link href="../assets/plugins/bootstrap-sweetalert/sweet-alert.css" rel="stylesheet" type="text/css" />
      
      <!---Switchery--->
      <link href="../assets/plugins/switchery/switchery.min.css" rel="stylesheet" />

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
                                <a href="" class="waves-effect"><i class="zmdi zmdi-shopping-basket"></i> <span> Imported Products </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="import_fail_history.php" class="waves-effect"><i class="zmdi zmdi-close-circle"></i> <span> Import Fail History </span> </a>
                            </li>
                           
                           <li class="has_sub">
                                <a href="tracking.php" class="waves-effect"><i class="zmdi zmdi-rotate-right"></i> <span> Tracking </span> </a>
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
                          <h4 class="page-title">Imported Products</h4>
                      </div>
                  </div>
            
                  
                  <div class="row">
                     <div class="col-sm-3">
                        <div class="card-box widget-user">
                           <div class="text-center">
                              <h2 class="text-custom" data-plugin="counterup"><?php echo $imported_products_total; ?></h2>
                              <h5>Total</h5>
                           </div>
                        </div>
                     </div>
                     <div class="col-sm-5">
                        <div class="card-box">
                           <h4 class="header-title m-t-0 m-b-30">Create Purchase Link</h4>
                           <form class="form-horizontal" role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?offset=' . $offset;?>" method="POST">
                              <div class="form-group">
                                  <label for="eBay_product_ID" class="col-sm-3 control-label">eBay Product ID</label>
                                  <div class="col-sm-9">
                                    <input type="text" class="form-control" name="eBay_product_ID" placeholder="eBay Product ID" value="<?php if (isset($eBay_product_ID)) { echo $eBay_product_ID; } ?>" required>
                                  </div>
                              </div>
                              <div class="form-group m-b-0">
                                  <div class="col-sm-offset-3 col-sm-9">
                                     <button type="submit" class="btn btn-info waves-effect waves-light">Create</button>
                                     <?php if (isset($asin) and !empty($asin)) { ?>
                                        <a type="button" class="btn btn-inverse waves-effect waves-light" href="<?php echo $purchase_link; ?>" target="_blank">Amazon Product Link</a>
                                     <?php } ?>
                                  </div>
                              </div>
                           </form>
                        </div>
                     </div>
                     <div class="col-sm-4">
                        
                     </div>
                  </div>
                  
                  <div class="row">
                     <div class="col-sm-9">
                        <div class="card-box">
                           <?php if (empty($imported_products)) { ?>
                              <p>No imported products.</p>
                           <?php } else { ?>
                              <h4 class="header-title m-t-0 m-b-30">Showing <?php echo $offset; ?> - <?php echo ($offset + sizeof($imported_products)); ?></h4>
                              <table class="table table-striped m-0">
                                 <thead>
                                    <tr>
                                        <th>Amazon ASIN</th>
                                        <th>eBay Product ID</th>
                                        <th>Original Price</th>
                                        <th>Listed Price</th>
                                        <th>Out Of Stock</th>
                                        <th>Last Tracked</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <?php foreach ($imported_products as $imported_product) { ?>
                                       <tr>
                                          <td><?php echo $imported_product["asin"]; ?></td>
                                          <td><?php echo $imported_product["eBay_product_ID"]; ?></td>
                                          <td><?php echo $imported_product["original_price"]; ?></td>
                                          <td><?php echo $imported_product["listed_price"]; ?></td>
                                          <td><?php echo $imported_product["out_of_stock"]; ?></td>
                                          <td><?php echo $imported_product["time_last_processed"]; ?></td>
                                       </tr>
                                    <?php } ?>
                                 </tbody>
                              </table>
                              <?php if ($imported_products_total != 0 and $imported_products_total > ($requested_records + $offset)) { ?>
                                 <br/>
                                 <a style="color:black;" href="imported_products.php?offset=<?php echo ($requested_records + $offset); ?>">Next</a>
                              <?php } ?>
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

        <!---Switchery--->
        <script src="../assets/plugins/switchery/switchery.min.js"></script>


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