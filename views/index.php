<?php
if(!$user->id){
	App::redirect(VSITE."?m=login");
}

$total_emp_in = Pegawai::getTotal(true);
$total_emp_out = Pegawai::getTotal(false);
$total_emp = Pegawai::getAll(true);
$total_files = Files::getAll(null, true);
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo $pageTitle;?></h1>
  <ol class="breadcrumb">
	<li class="active"><i class="fa fa-dashboard"></i> <?php echo $pageTitle;?></li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <!-- Small boxes (Stat box) -->
  <div class="row">
	<div class="col-lg-3 col-xs-6">
	  <!-- small box -->
	  <div class="small-box bg-green">
		<div class="inner">
		  <h3><?=number_format($total_emp_in,0,".",",");?></h3>

		  <p>New Employee</p>
		</div>
		<div class="icon">
		  <i class="ion ion-person-add"></i>
		</div>
	  </div>
	</div>
	<div class="col-lg-3 col-xs-6">
	  <!-- small box -->
	  <div class="small-box bg-red">
		<div class="inner">
		  <h3><?=number_format($total_emp_out,0,".",",");?></h3>

		  <p>Employee Out</p>
		</div>
		<div class="icon">
		  <i class="fa fa-minus-square"></i>
		</div>
	  </div>
	</div>
	<!-- ./col -->
	<div class="col-lg-3 col-xs-6">
	  <!-- small box -->
	  <div class="small-box bg-aqua">
		<div class="inner">
		  <h3><?=number_format($total_emp,0,".",",");?></h3>

		  <p>Total Employee</p>
		</div>
		<div class="icon">
		  <i class="fa fa-group"></i>
		</div>
	  </div>
	</div>
	<!-- ./col -->
	<div class="col-lg-3 col-xs-6">
	  <!-- small box -->
	  <div class="small-box bg-purple">
		<div class="inner">
		  <h3><?=number_format($total_files,0,".",",");?></h3>

		  <p>Total Files</p>
		</div>
		<div class="icon">
		  <i class="fa fa-paste"></i>
		</div>
	  </div>
	</div>
	<!-- ./col -->
  </div>
  <!-- /.row -->
</section>
<!-- /.content -->