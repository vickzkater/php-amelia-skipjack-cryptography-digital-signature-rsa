<nav class="navbar navbar-static-top">
  <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button" onclick="collapseSidebar()">
	<span class="sr-only">Toggle navigation</span>
  </a>

  <div class="navbar-custom-menu">
	<ul class="nav navbar-nav">
	  <li class="dropdown user user-menu">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown">
		  <img src="images/avatar.png" class="user-image" alt="User Image">
		  <span class="hidden-xs">Hi, <?php echo $user->nama;?></span>
		</a>
		<ul class="dropdown-menu">
		  <!-- User image -->
		  <li class="user-header">
			<img src="images/avatar.png" class="img-circle" alt="User Image">
			<p>
			  <?php echo $user->nama;?>
			</p>
		  </li>
		  <!-- Menu Footer-->
		  <li class="user-footer">
			<div class="pull-left">
			  <a href="index.php?m=profile" class="btn btn-default btn-flat"><i class="fa fa-user"></i> &nbsp;Profile</a>
			</div>
			<div class="pull-right">
			  <a href="index.php?m=logout" class="btn btn-default btn-flat">Logout &nbsp;<i class="fa fa-sign-out"></i></a>
			</div>
		  </li>
		</ul>
	  </li>
	</ul>
  </div>
</nav>