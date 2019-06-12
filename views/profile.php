<?php
if(!$user->id){
	App::redirect(VSITE."?m=login");
}

// default page
$url = "index.php?m=profile";

// set form
$inputmode = true;
$edit = true;
$id = $user->id;
$details = new User($id);
$levels = Level::getAll();
foreach($levels as $b){
	if($b->id == $details->level)
		$lvlname = ucwords($b->nama);
}

// if form submitted
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$result = User::save();
	if($result) App::redirect($url);
}
?>
<!-- dataTables -->
<link rel="stylesheet" href="<?php echo VSITE;?>/plugins/datatables/dataTables.bootstrap.css">

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo $pageTitle;?></h1>
  <ol class="breadcrumb">
	<li><a href="<?php echo VSITE;?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
	<li class="active"><?php echo $pageTitle;?></li>
  </ol>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
	<div class="col-md-3">
		<div class="box box-widget widget-user">
			<div class="widget-user-header bg-aqua-active">
			  <h4 class="widget-user-desc"><?php if($edit){ echo $details->nama; }else{ echo "[FULLNAME]"; } ?></h4>
			</div>
			<div class="widget-user-image">
			  <img alt="Profile Picture" src="/images/avatar.png" class="img-circle">
			</div>
			<div class="box-footer">
			  <div class="row">
				<div class="col-md-12">
					<div class="description-block">
						<span class="description-text"><b>[ <?=$lvlname;?> ]</b></span>
						<h5 class="description-header">
							<span class="badge bg-orange-active"><?=($edit)?date("d M Y", $details->dibuat):'[DIBUAT]';?></span>
						</h5>
					</div>
				</div>
			  </div>
			</div>
		</div>
		<!-- /.box -->
		
		<!-- About Box -->
		<div class="box box-primary box-solid wrap">
			<div class="box-header with-border">
			  <h3 class="box-title">About</h3>
			</div>
			<div class="box-body">
				<b><i class="fa fa-envelope margin-r-5"></i> Email</b>
				<p><b class="text-blue"><?php if($edit){ echo $details->email; }else{ echo "[EMAIL]"; } ?></b></p>
			</div>
		</div>
		<!-- /.box -->
	</div>
	<!-- /.col-md-3 -->
	<div class="col-md-9">
	  <div class="nav-tabs-custom">
		<ul class="nav nav-tabs">
			<?php $tab2 = "details"; ?>
			<li id="tab-<?=$tab2;?>" class="active">
			  <a data-toggle="tab" href="#<?=$tab2;?>" aria-expanded="false"><i class="fa fa-user"></i> Details</a>
			</li>
			<?php $tab3 = "password"; ?>
			<li id="tab-<?=$tab3;?>" class="">
			  <a data-toggle="tab" href="#<?=$tab3;?>" aria-expanded="false"><i class="fa fa-lock"></i> Password</a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="<?=$tab2;?>" class="tab-pane active">
				<!-- Details -->
				<div class="row">
					<div class="col-lg-12">
					  <form action="" method="post" enctype="multipart/form-data">
						<div class="box box-success box-solid">
							<div class="box-header pointer" onclick="document.getElementById('btnPerDtl').click()">
							  <h3 class="box-title"><i class="fa fa-user"></i> &nbsp;User Details</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse" id="btnPerDtl"><i class="fa fa-minus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<div class="form-group">
									<label for="objname">Fullname <span class="required">*</span></label>
									<input type="text" name="objname" value="<?php if($edit){ echo $details->nama; } ?>" id="objname" class="form-control" placeholder="e.g. Vicky Budiman" required>
								</div>
								<div class="form-group">
									<label for="objemail">Email <span class="required">*</span></label>
									<input type="email" name="objemail" value="<?php if($edit){ echo $details->email; } ?>" id="objemail" class="form-control" placeholder="e.g. vicky@kiniditech.com" required>
								</div>
								<div class="form-group">
								  <label for="objlevel">Level <span class="required">*</span></label>
								  <select name="objlevel" id="objlevel" class="form-control" required>
									<option value="" checked>- Please Choose One -</option>
									<?php
									$levels = Level::getAll();
									if($levels){
										foreach($levels as $b){
											$y = "";
											if($edit && $b->id == $details->level) $y = "selected";
											echo '<option value="'.$b->id.'" '.$y.'>'.ucwords($b->nama).'</option>';
										}
									}else{
										echo '<option value="" disabled>**NO DATA AVAILABLE**</option>';
									}
									?>
								  </select>
								</div>
							</div>
							<!-- /.box-body -->
							<div class="box-footer text-center">
								<?php if($edit) echo '<input type="hidden" name="id" value="'.$id.'">'; ?>
								<button class="btn btn-success btn-lg" type="submit"><i class="fa fa-save"></i> &nbsp;SAVE</button>
							</div>
							<!-- /.box-footer -->
						</div>
						<!-- /.box -->
					  </form>
					</div>
				</div>
			</div>
			<div id="<?=$tab3;?>" class="tab-pane">
				<div class="row">
					<div class="col-lg-12">
					  <form action="" method="post" enctype="multipart/form-data">
						<div class="box box-success box-solid">
							<div class="box-header">
							  <h3 class="box-title"><i class="fa fa-unlock"></i> &nbsp;Change Password</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<div class="form-group">
									<label for="objpass">New Password <span class="required">*</span></label>
									<input type="password" name="objpass" value="" id="objpass" class="form-control" placeholder="*****" required>
								</div>
								<div class="form-group">
									<label for="objpass2">Confirm Password <span class="required">*</span></label></label>
									<input type="password" name="objpass2" value="" id="objpass2" class="form-control" placeholder="Must be the same as New Password" required onblur="confirmPass()">
								</div>
							</div>
							<!-- /.box-body -->
							<div class="box-footer text-center">
								<input type="hidden" name="id" value="<?=$id;?>">
								<input type="hidden" name="objlevel" value="<?=$user->level;?>">
								<button class="btn btn-success btn-lg" type="submit">
									<i class="fa fa-save"></i> &nbsp;Change Password
								</button>
							</div>
							<!-- /.box-footer -->
						</div>
						<!-- /.box -->
					  </form>
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
    <!-- /.col-md-3 -->
  </div>
  <!-- /.row -->
</section>
<!-- /.content -->

<!-- DataTables -->
<script src="<?php echo VSITE;?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo VSITE;?>/plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- page script -->
<script>
  $(function () {
    $('.table-data').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true
    });
	
	function confirmPass(){
		if($("#objpass").val()!="" && $("#objpass").val() != $("#objpass2").val()){
			alert("Password & Confirm Password must be same, please retype again");
			$("#objpass").val("");
			$("#objpass2").val("");
			$("#objpass").focus();
		}
	}
  });
</script>