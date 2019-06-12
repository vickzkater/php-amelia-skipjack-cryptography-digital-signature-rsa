  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="images/avatar.png" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?=$user->nama;?></p>
		  <a href="javascript:void(0)"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MAIN MENU</li>
        <li <?php if(!isset($_GET["m"]) || $_GET["m"]=="home") echo "class='active'" ?>>
          <a href="index.php">
            <i class="fa fa-dashboard"></i> <span>Dashboard</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="profile") echo "class='active'" ?>>
          <a href="index.php?m=profile">
            <i class="fa fa-user"></i> <span>Profile</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_division") echo "class='active'" ?>>
          <a href="index.php?m=master_division">
            <i class="fa fa-bank"></i> <span>Master Division</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_joblevel") echo "class='active'" ?>>
          <a href="index.php?m=master_joblevel">
            <i class="fa fa-briefcase"></i> <span>Master Job Level</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_employee") echo "class='active'" ?>>
          <a href="index.php?m=master_employee">
            <i class="fa fa-black-tie"></i> <span>Master Employee</span>
          </a>
        </li>
		<li class="header">ADMIN MENU</li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="logs") echo "class='active'" ?>>
          <a href="index.php?m=logs">
            <i class="fa fa-exchange"></i> <span>Logs</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_user") echo "class='active'" ?>>
          <a href="index.php?m=master_user">
            <i class="fa fa-users"></i> <span>Master User</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_access") echo "class='active'" ?>>
          <a href="index.php?m=master_access">
            <i class="fa fa-balance-scale"></i> <span>Master Access</span>
          </a>
        </li>
		<li <?php if(isset($_GET["m"]) && $_GET["m"]=="master_level") echo "class='active'" ?>>
          <a href="index.php?m=master_level">
            <i class="fa fa-sitemap"></i> <span>Master Level</span>
          </a>
        </li>
      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>