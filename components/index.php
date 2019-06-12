<?php
if(isset($_GET["m"])){
	switch($_GET["m"]){
		case "login":
			$pageTitle = "Login Panel";
			$content = "views/login.php";
			break;
		case "logout":
			$pageTitle = "Logout";
			
			// logging
			$user = App::getUser();
			Logs::saveLog($user->id, "Logout", null, null);
			
			if(session_destroy()) {
				header("Location: index.php");
			}
			break;
		case "master_user":
			$pageTitle = "Master User";
			$content = "views/master_user.php";
			break;
		case "master_employee":
			$pageTitle = "Master Employee";
			$content = "views/master_employee.php";
			break;
		case "master_division":
			$pageTitle = "Master Division";
			$content = "views/master_division.php";
			break;
		case "master_joblevel":
			$pageTitle = "Master Job Level";
			$content = "views/master_joblevel.php";
			break;
		case "master_access":
			$pageTitle = "Master Access";
			$content = "views/master_access.php";
			break;
		case "profile":
			$pageTitle = "Profile";
			$content = "views/profile.php";
			break;
		case "master_level":
			$pageTitle = "Master Level";
			$content = "views/master_level.php";
			break;
		case "logs":
			$pageTitle = "Logs";
			$content = "views/logs.php";
			break;
		default:
			$pageTitle = "Dashboard";
			$content = "views/index.php";
	}
}else{
	$pageTitle = "Dashboard";
	$content = "views/index.php";
}

$mode = "";
if(isset($_GET["mode"])){
	$mode = $_GET["mode"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo $cfg->sitename." | ".$pageTitle;?></title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo VSITE;?>/plugins/bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="<?php echo VSITE;?>/plugins/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="<?php echo VSITE;?>/plugins/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo VSITE;?>/assets/css/AdminLTE.min.css">
  <!-- Custom -->
  <link rel="stylesheet" href="<?php echo VSITE;?>/assets/css/custom.css">
  
 <?php if($user->id > 0){ ?>
  <link rel="stylesheet"  href="<?php echo VSITE;?>/assets/css/skins/skin-<?php echo $cfg->skin;?>.css">
 <?php }else{ ?>
 <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
 <?php } ?>

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
  
  <!-- start: Favicon -->
  <link rel="shortcut icon" href="/favicon.ico">
  <!-- end: Favicon -->
</head>
<?php
if($user->id > 0){
	if($user->collapsed > 0){
		echo '<body class="hold-transition sidebar-collapse skin-'.$cfg->skin.' sidebar-mini">';
	}else{
		echo '<body class="hold-transition skin-'.$cfg->skin.' sidebar-mini">';
	}
	echo '<div class="wrapper">';
}else{
	echo '<style>html, body {height: auto !important;}</style>';
	echo '<body class="hold-transition login-page">';
}
?>

<?php if($user->id > 0 && $pageTitle != "Logout" && $mode != "download"){ ?>
	<!-- start: Header Menu -->
	<header class="main-header">
		<!-- Logo -->
		<a href="<?php echo VSITE;?>" class="logo">
		  <!-- mini logo for sidebar mini 50x50 pixels -->
		  <span class="logo-mini"><b><?=$cfg->logomini;?></b></span>
		  <!-- logo for regular state and mobile devices -->
		  <span class="logo-lg">
			<!-- <img src="<?=$cfg->logoimg;?>" alt="<?=$cfg->copyright;?>" /> -->
			<?php echo $cfg->sitename;?>
		  </span>
		</a>	
		<?php include VCOMPONENT."/usermenu.php";?>
	</header>
	<!-- end: Header Menu -->
	
	<!-- start: Main Menu -->
	<?php include VCOMPONENT."/navmenu.php";?>
	<!-- end: Main Menu -->
	
	<div class="content-wrapper">
<?php } ?>
	<div id="alert-message"></div>

	<!-- Core Script - BEGIN -->
	<!-- jQuery 2.2.0 -->
	<script src="<?php echo VSITE;?>/plugins/jQuery/jQuery-2.2.0.min.js"></script>
	<!-- Bootstrap 3.3.6 -->
	<script src="<?php echo VSITE;?>/plugins/bootstrap/js/bootstrap.min.js"></script>
	<!-- by Developer -->
	<script src="<?php echo VSITE;?>/assets/js/global.js"></script>
	<script src="<?php echo VSITE;?>/assets/js/timeconverter.js"></script>
	<!-- Core Script - END -->
	
	<!-- start: Content -->
	<?php if($pageTitle != "Logout") include $content;?>
	<!-- end: Content -->
<?php if($user->id > 0){ ?>
  </div>
  <!-- /.content-wrapper -->
	<?php include VCOMPONENT."/footer.php";?>
</div>
<!-- ./wrapper -->
<?php } ?>


<?php
// if theres is any system message..
if(isset($_SESSION['message'])){
	echo "<script>";
	foreach($_SESSION['message'] as $mes){
		$alert = '';
		$fa = 'fa-info';
		if(isset($mes->type)){
			$alert = 'alert-'.$mes->type;
			if($mes->type == 'success') $fa = 'fa-check';
			else if($mes->type == 'error') $fa = 'fa-ban';
			else if($mes->type == 'warning') $fa = 'fa-warning';
			else if($mes->type == 'news'){
				$alert = 'alert-info';
				$fa = 'fa-bullhorn';
			}
		}
		$html = '<div class="alert '.$alert.' alert-dismissible"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button><center><h4><i class="icon fa '.$fa.'"></i> '.ucfirst($mes->type).'</h4>'.$mes->message.'</center></div>';
		echo '$("#alert-message").append(\''.$html.'\')';
	} //end foreach
	if($user->id > 0) App::clearMessages(); 
	echo "</script>";
} // end if isset session message
?>
</body>
</html>