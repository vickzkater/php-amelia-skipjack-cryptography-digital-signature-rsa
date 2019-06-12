<?php
if(!$user->id){
	App::redirect(VSITE."?m=login");
}

if(!App::checkAccess("MANAGE_EMPLOYEE")){
	App::message("Sorry, you are not authorized to manage employee", "warning");
	App::redirect(VSITE."?access=denied");
}

// default page
$url = "index.php?m=master_employee";
$locurl = $url;

// set form
$inputmode = false;
$edit = false;
$id = null;
$mode = "";
if(isset($_GET['mode'])){
	// new data
	$inputmode = true;
	$details = null;
	$mode = $_GET['mode'];
	
	if(isset($_GET['id']) && is_numeric($_GET['id'])){
		$id = (int)$_GET['id'];
		$locurl .= "&mode=edit&id=".$id;
		// edit or delete data
		switch($mode){
			case "edit":
				$edit = true;
				$details = new Pegawai($id);
				$career = Karier::getCareer($id);
				break;
			case "delete":
				$details = Pegawai::delete($id);
				if($details){
					App::redirect($url);
				}
				break;
			case "deletefile":
				$file = Files::getInstance($id);
				$details = Files::delete($id);
				if($details){
					App::redirect($url."&mode=edit&id=".$file->idpegawai."&res=success");
				}
				break;
		}
	}
}

// if form submitted
if($_SERVER['REQUEST_METHOD'] == "POST"){
	$task = Jinput::post("task", null, "words");
	
	switch($task){
		case 'savepegawai':
			$result = Pegawai::save();
			if($result) App::redirect($locurl);
			break;
		case 'savekarier':
			$result = Karier::save();
			if($result) App::redirect($locurl);
			break;
	}
}

$pathdir = __DIR__."/../".$cfg->path."/";

if($mode != "download")
{
?>
<!-- dataTables -->
<link rel="stylesheet" href="<?php echo VSITE;?>/plugins/datatables/dataTables.bootstrap.css">
<!-- Bootstrap datepicker -->
<link rel="stylesheet" href="<?php echo VSITE;?>/plugins/datepicker/datepicker3.css">

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?php echo $pageTitle;?></h1>
  <ol class="breadcrumb">
	<li><a href="<?php echo VSITE;?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
	<li class="<?=($inputmode)?'':'active';?>"><?php echo $pageTitle;?></li>
	<?php if($inputmode){ ?>
	<li class="active"><?php echo ucwords($mode);?></li>
	<?php } ?>
  </ol>
</section>
<?php } ?>
<!-- Main content -->
<section class="content">
<?php if($inputmode && $mode != "download"){
		if($mode == "new"){ // form - add new data
?>
  <div class="row" id="formdetails">
	<div class="col-lg-12">
	  <form action="" method="post" enctype="multipart/form-data">
		<div class="box box-success">
            <div class="box-header">
              <h3 class="box-title"><i class="fa fa-edit"></i> &nbsp;Form Details | <?php echo ucwords($_GET['mode']);?></h3>
			  <div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			  </div>
			  <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			  <div class="box box-primary box-solid">
				<div class="box-header">
				  <h3 class="box-title"><i class="fa fa-user"></i> &nbsp;Personal Data</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objfullname">Fullname <span class="required">*</span></label>
						<input type="text" name="objfullname" value="" id="objfullname" class="form-control" placeholder="e.g. Vicky Budiman" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objname">Nickname <span class="required">*</span></label>
						<input type="text" name="objname" value="" id="objname" class="form-control" placeholder="e.g. Vicky" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objpob">Place of Birth <span class="required">*</span></label>
						<input type="text" name="objpob" value="" id="objpob" class="form-control" placeholder="e.g. Jakarta" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objdob">Date of Birth <span class="required">*</span></label>
						<input type="text" name="objdob" value="01 Jan 1990" id="objdob" class="form-control datepicker" placeholder="e.g. dd MMM yyyy" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objphone">Phone</label>
						<input type="text" name="objphone" value="" id="objphone" class="form-control" placeholder="e.g. 021-XXXXXXX">
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objmobile">Mobile <span class="required">*</span></label>
						<input type="text" name="objmobile" value="" id="objmobile" class="form-control" placeholder="e.g. 081XXXXXXXXX" onkeyup="numbers_only(this)" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objemail">Personal Email <span class="required">*</span></label>
						<input type="email" name="objemail" value="" id="objemail" class="form-control" placeholder="e.g. vicky@kiniditech.com" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objaddr">Address <span class="required">*</span></label>
						<textarea name="objaddr" id="objaddr" class="form-control" required></textarea>
					</div>
				  </div>
				</div>
				<!-- /.box-body -->
			  </div>
			  <!-- /.box -->
			  <div class="box box-primary box-solid">
				<div class="box-header">
				  <h3 class="box-title"><i class="fa fa-bank"></i> &nbsp;Office Data</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objnip">NIP (Nomor Induk Pegawai) <span class="required">*</span></label>
						<input type="text" name="objnip" value="" id="objnip" class="form-control" placeholder="e.g. 012" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objemail2">Office Email <span class="required">*</span></label>
						<input type="email" name="objemail2" value="" id="objemail2" class="form-control" placeholder="e.g. vicky@company.com" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objnote">Notes</label>
						<textarea name="objnote" id="objnote" class="form-control" placeholder="*) optional"></textarea>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
					  <label>Status <span class="required">*</span></label><br>
					  <div class="radio-inline">
						<label class="text-green">
						  <input type="radio" name="objstatus" value="aktif" required checked>
						  Enabled <i class="fa fa-check-circle"></i>
						</label>
					  </div>
					  <div class="radio-inline">
						<label class="text-danger" style="font-style:italic">
						  <input type="radio" name="objstatus" value="non-aktif" required>
						  Disabled <i class="fa fa-times-circle"></i>
						</label>
					  </div>
					</div>
				  </div>
				</div>
				<!-- /.box-body -->
			  </div>
			  <!-- /.box -->
			  <div class="box box-primary box-solid">
				<div class="box-header">
				  <h3 class="box-title"><i class="fa fa-briefcase"></i> &nbsp;First Career</h3>
				</div>
				<!-- /.box-header -->
				<div class="box-body">
				  <div class="col-lg-6">
					<div class="form-group">
					  <label for="objdiv">Division <span class="required">*</span></label>
					  <select name="objdiv" id="objdiv" class="form-control" required>
						<option value="" checked>- Please Choose One -</option>
						<?php
						$divisi = Divisi::getAll();
						if($divisi){
							foreach($divisi as $b){
								echo '<option value="'.$b->id.'">'.ucwords($b->nama).'</option>';
							}
						}else{
							echo '<option value="" disabled>**NO DATA AVAILABLE**</option>';
						}
						?>
					  </select>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
					  <label for="objlevel">Job Level <span class="required">*</span></label>
					  <select name="objlevel" id="objlevel" class="form-control" required>
						<option value="" checked>- Please Choose One -</option>
						<?php
						$jabatan = Jabatan::getAll();
						if($jabatan){
							foreach($jabatan as $b){
								echo '<option value="'.$b->id.'">'.ucwords($b->nama).'</option>';
							}
						}else{
							echo '<option value="" disabled>**NO DATA AVAILABLE**</option>';
						}
						?>
					  </select>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objpos">Position <span class="required">*</span></label>
						<input type="text" name="objpos" value="" id="objpos" class="form-control" placeholder="e.g. Senior Analyst" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objdos">Date of Start <span class="required">*</span></label>
						<input type="text" name="objdos" value="<?=date('d M Y');?>" id="objdos" class="form-control datepicker" placeholder="e.g. dd MMM yyyy" required>
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objdoe">Date of End</label>
						<input type="text" name="objdoe" value="" id="objdoe" class="form-control datepicker" placeholder="*only set if this career has the end of contract / resign / get promotion">
					</div>
				  </div>
				  <div class="col-lg-6">
					<div class="form-group">
						<label for="objnotejob">Notes</label>
						<textarea name="objnotejob" id="objnotejob" class="form-control"></textarea>
					</div>
				  </div>
				</div>
				<!-- /.box-body -->
			  </div>
			  <!-- /.box -->
			</div>  
			<!-- /.box-body -->
			<div class="box-footer text-center">
                <input type="hidden" name="task" value="savepegawai">
				<span class="btn btn-danger btn-lg" onclick="backToList()">
					<i class="fa fa-backward"></i> &nbsp;BACK
				</span>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn btn-success btn-lg" type="submit">
					<i class="fa fa-save"></i> &nbsp;SAVE
				</button>
            </div>
        </div>
		<!-- /.box -->
		</form>
	</div>
	<!-- /.col -->
  </div>
  <!-- /.row -->
<?php 	}else{ // form - edit existing data ?>
  <div class="row">
	<div class="col-md-3">
		<div class="box box-widget widget-user">
			<div class="widget-user-header bg-aqua-active">
			  <h4 class="widget-user-desc"><?php if($edit){ echo $details->nama_lengkap; }else{ echo "[FULLNAME]"; } ?></h4>
			</div>
			<div class="widget-user-image">
			  <img alt="Profile Picture" src="/images/avatar.png" class="img-circle">
			</div>
			<div class="box-footer">
			  <div class="row">
				<div class="col-md-12">
					<div class="description-block">
						<span class="description-text"><b><?php if($edit){ echo $details->nip; }else{ echo "[NIP]"; } ?></b></span>
						<h5 class="description-header">
							<span class="badge bg-aqua-active"><?=($edit)?strtoupper($career[0]->namadivisi):'[DIVISI]';?></span><br>
							<span class="badge bg-orange-active"><?=($edit)?strtoupper($career[0]->namajabatan):'[LEVEL]';?></span><br>
							<span class="badge bg-purple-active"><?=($edit)?ucwords($career[0]->posisi):'[POSITION]';?></span>
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
				<b><i class="fa fa-envelope margin-r-5"></i> Personal Email</b>
				<p><b class="text-blue"><?php if($edit){ echo $details->email; }else{ echo "[EMAIL]"; } ?></b></p>
				<hr>
				<b><i class="fa fa-envelope margin-r-5"></i> Work Email</b>
				<p><b class="text-blue"><?php if($edit){ echo $details->email_kantor; }else{ echo "[WORK EMAIL]"; } ?></b></p>
				<hr>
				<b><i class="fa fa-phone margin-r-5"></i> Mobile</b>
				<p><b class="text-blue"><?php if($edit){ echo $details->mobile; }else{ echo "[MOBILE]"; } ?></b></p>
			</div>
		</div>
		<!-- /.box -->
	</div>
	<!-- /.col-md-3 -->
	<div class="col-md-9">
	  <div class="nav-tabs-custom">
		<ul class="nav nav-tabs">
			<?php $tab1 = "career"; ?>
			<li id="tab-<?=$tab1;?>" class="active">
			  <a data-toggle="tab" href="#<?=$tab1;?>" aria-expanded="false"><i class="fa fa-bar-chart"></i> Career</a>
			</li>
			<?php $tab2 = "details"; ?>
			<li id="tab-<?=$tab2;?>" class="">
			  <a data-toggle="tab" href="#<?=$tab2;?>" aria-expanded="false"><i class="fa fa-user"></i> Details</a>
			</li>
			<?php $tab3 = "document"; ?>
			<li id="tab-<?=$tab3;?>" class="">
			  <a data-toggle="tab" href="#<?=$tab3;?>" aria-expanded="false"><i class="fa fa-book"></i> Documents</a>
			</li>
		</ul>
		<div class="tab-content">
			<div id="<?=$tab1;?>" class="tab-pane active">
				<button class="btn btn-success" id="add_career" onclick="showFormCareer()"><i class="fa fa-plus-circle"></i> &nbsp;ADD NEW</button>
				<form action="" method="post">
				  <div class="box box-success box-solid" id="form_career" style="display:none">
					<div class="box-header">
					  <h3 class="box-title"><i class="fa fa-briefcase"></i> &nbsp;Add Career</h3>
					</div>
					<!-- /.box-header -->
					<div class="box-body">
					  <div class="col-lg-6">
						<div class="form-group">
						  <label for="objdiv">Division <span class="required">*</span></label>
						  <select name="objdiv" id="objdiv" class="form-control" required>
							<option value="" checked>- Please Choose One -</option>
							<?php
							$divisi = Divisi::getAll();
							if($divisi){
								foreach($divisi as $b){
									echo '<option value="'.$b->id.'">'.ucwords($b->nama).'</option>';
								}
							}else{
								echo '<option value="" disabled>**NO DATA AVAILABLE**</option>';
							}
							?>
						  </select>
						</div>
					  </div>
					  <div class="col-lg-6">
						<div class="form-group">
						  <label for="objlevel">Job Level <span class="required">*</span></label>
						  <select name="objlevel" id="objlevel" class="form-control" required>
							<option value="" checked>- Please Choose One -</option>
							<?php
							$jabatan = Jabatan::getAll();
							if($jabatan){
								foreach($jabatan as $b){
									echo '<option value="'.$b->id.'">'.ucwords($b->nama).'</option>';
								}
							}else{
								echo '<option value="" disabled>**NO DATA AVAILABLE**</option>';
							}
							?>
						  </select>
						</div>
					  </div>
					  <div class="col-lg-6">
						<div class="form-group">
							<label for="objpos">Position <span class="required">*</span></label>
							<input type="text" name="objpos" value="" id="objpos" class="form-control" placeholder="e.g. Senior Analyst" required>
						</div>
					  </div>
					  <div class="col-lg-6">
						<div class="form-group">
							<label for="objnotejob">Notes</label>
							<textarea name="objnotejob" id="objnotejob" class="form-control" placeholder="*) optional"></textarea>
						</div>
					  </div>
					  <div class="col-lg-12">
					    <hr>
						<div class="form-group text-center">
						  <div class="checkbox">
							<label>
							  <input type="checkbox" id="yes_input_prev" onclick="setEndPrevCareer()">
							  Set <b>"Date of End"</b> of previous career?
							</label>
						  </div>
						</div>
						<div class="form-group" id="input_prev" style="display:none">
							<label for="objdoe_prev">Date of End <i>(Previous Career)</i></label>
							<input type="text" name="objdoe_prev" value="<?=date('d M Y');?>" id="objdoe_prev" class="form-control datepicker" placeholder="">
						</div>
						<hr>
					  </div>
					  <div class="col-lg-6">
						<div class="form-group">
							<label for="objdos">Date of Start <span class="required">*</span></label>
							<input type="text" name="objdos" value="<?=date('d M Y');?>" id="objdos" class="form-control datepicker" placeholder="e.g. dd MMM yyyy" required>
						</div>
					  </div>
					  <div class="col-lg-6">
						<div class="form-group">
							<label for="objdoe">Date of End</label>
							<input type="text" name="objdoe" value="" id="objdoe" class="form-control datepicker" placeholder="*only set if this career has the end of contract / resign / get promotion">
						</div>
					  </div>
					  
					</div>
					<!-- /.box-body -->
					<div class="box-footer text-center">
						<input type="hidden" name="id" value="">
						<input type="hidden" name="emp_id" value="<?=$id;?>">
						<input type="hidden" name="task" value="savekarier">
						<span class="btn btn-danger btn-lg" onclick="showFormCareer('cancel')">
							<i class="fa fa-times"></i> &nbsp;CANCEL
						</span>
						&nbsp;&nbsp;&nbsp;&nbsp;
						<button class="btn btn-success btn-lg" type="submit">
							<i class="fa fa-save"></i> &nbsp;SAVE
						</button>
					</div>
				  </div>
				</form>
				<!-- /.box -->
				<hr>
			<?php
			if($career){
				$last = '';
				echo '<ul class="timeline timeline-inverse">';
				foreach($career as $log){
					$icon = '<i class="fa fa-briefcase bg-purple"></i>';
					if($last != $log->bergabung){
						$berakhir = "Now";
						if($log->berakhir > 0){
							$berakhir = date("d M Y", $log->berakhir);
						}
						
						echo '<li class="time-label">
									<span class="bg-aqua">
									'.date("d M Y", $log->bergabung).' - '.$berakhir.'
									</span>
								</li>';
						$last = $log->bergabung;
					}
					echo '<li>
							'.$icon.'
							<div class="timeline-item">
							  <h3 class="timeline-header">
								'.$log->namadivisi.' - '.$log->namajabatan.' : '.$log->posisi.'
								<!--<button class="btn btn-xs btn-primary pull-right edit_career" data-id="'.$log->id.'"><i class="fa fa-edit"></i></button>-->
							  </h3>
							</div>
						  </li>';
				}
				echo '</ul>';
			}
			?>
			</div>
			<div id="<?=$tab2;?>" class="tab-pane">
				<!-- Details -->
				<div class="row">
					<div class="col-lg-12">
					  <form action="" method="post" enctype="multipart/form-data">
						<div class="box box-success box-solid collapsed-box">
							<div class="box-header pointer" onclick="document.getElementById('btnPerDtl').click()">
							  <h3 class="box-title"><i class="fa fa-user"></i> &nbsp;Personal Details</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse" id="btnPerDtl"><i class="fa fa-plus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<div class="form-group">
									<label for="objfullname">Fullname <span class="required">*</span></label>
									<input type="text" name="objfullname" value="<?php if($edit){ echo $details->nama_lengkap; } ?>" id="objfullname" class="form-control" placeholder="e.g. Vicky Budiman" required>
								</div>
								<div class="form-group">
									<label for="objname">Nickname <span class="required">*</span></label>
									<input type="text" name="objname" value="<?php if($edit){ echo $details->nama_panggilan; } ?>" id="objname" class="form-control" placeholder="e.g. Vicky" required>
								</div>
								<div class="form-group">
									<label for="objpob">Place of Birth <span class="required">*</span></label>
									<input type="text" name="objpob" value="<?php if($edit){ echo $details->tempat_lahir; } ?>" id="objpob" class="form-control" placeholder="e.g. Jakarta" required>
								</div>
								<div class="form-group">
									<label for="objdob">Date of Birth <span class="required">*</span></label>
									<input type="text" name="objdob" value="<?php if($edit){ echo date("d M Y", $details->tgl_lahir); } ?>" id="objdob" class="form-control datepicker" placeholder="" required>
								</div>
								<div class="form-group">
									<label for="objphone">Phone</label>
									<input type="text" name="objphone" value="<?php if($edit){ echo $details->telp; } ?>" id="objphone" class="form-control" placeholder="e.g. 021-XXXXXXX">
								</div>
								<div class="form-group">
									<label for="objmobile">Mobile <span class="required">*</span></label>
									<input type="text" name="objmobile" value="<?php if($edit){ echo $details->mobile; } ?>" id="objmobile" class="form-control" placeholder="e.g. 081XXXXXXXXX" required>
								</div>
								<div class="form-group">
									<label for="objemail">Personal Email <span class="required">*</span></label>
									<input type="email" name="objemail" value="<?php if($edit){ echo $details->email; } ?>" id="objemail" class="form-control" placeholder="e.g. vicky@kiniditech.com" required>
								</div>
								<div class="form-group">
									<label for="objaddr">Address <span class="required">*</span></label>
									<textarea name="objaddr" id="objaddr" class="form-control" required><?php if($edit){ echo $details->alamat; } ?></textarea>
								</div>
							</div>
							<!-- /.box-body -->
							<div class="box-footer text-center">
								<input type="hidden" name="id" value="<?=$id;?>">
								<input type="hidden" name="task" value="savepegawai">
								<button class="btn btn-success btn-lg" type="submit"><i class="fa fa-save"></i> &nbsp;SAVE</button>
							</div>
							<!-- /.box-footer -->
						</div>
						<!-- /.box -->
						<div class="box box-success box-solid">
							<div class="box-header pointer" onclick="document.getElementById('btnOffDtl').click()">
							  <h3 class="box-title"><i class="fa fa-bank"></i> &nbsp;Office Details</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse" id="btnOffDtl"><i class="fa fa-minus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<div class="form-group">
									<label for="objnip">NIP (Nomor Induk Pegawai) <span class="required">*</span></label>
									<input type="text" name="objnip" value="<?php if($edit){ echo $details->nip; } ?>" id="objnip" class="form-control" placeholder="e.g. 16062225" required>
								</div>
								<div class="form-group">
									<label for="objemail2">Office Email <span class="required">*</span></label>
									<input type="email" name="objemail2" value="<?php if($edit){ echo $details->email_kantor; } ?>" id="objemail2" class="form-control" placeholder="e.g. vicky@company.com" required>
								</div>
								<div class="form-group">
									<label for="objnote">Notes</label>
									<textarea name="objnote" id="objnote" class="form-control" placeholder="*optional"><?php if($edit){ echo $details->keterangan; } ?></textarea>
								</div>
								<div class="form-group">
								  <label>Status <span class="required">*</span></label><br>
								  <div class="radio-inline">
									<label class="text-green">
									  <input type="radio" name="objstatus" value="aktif" required <?=(!$edit || ($edit && $details->status=="aktif"))?'checked':'';?>>
									  Enabled <i class="fa fa-check-circle"></i>
									</label>
								  </div>
								  <div class="radio-inline">
									<label class="text-danger" style="font-style:italic">
									  <input type="radio" name="objstatus" value="non-aktif" required <?=($edit && $details->status!="aktif")?'checked':'';?>>
									  Disabled <i class="fa fa-times-circle"></i>
									</label>
								  </div>
								</div>
							</div>
							<!-- /.box-body -->
							<div class="box-footer text-center">
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
						<div class="box box-primary box-solid">
							<div class="box-header">
							  <h3 class="box-title"><i class="fa fa-clipboard"></i> &nbsp;List File</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body table-responsive">
								<table class="table-data table table-bordered table-hover">
									<thead>
										<tr style="font-size:9px">
										  <th>No</th>
										  <th>Filename</th>
										  <th>Actual Size</th>
										  <th>Compressed Size</th>
										  <th>Ratio</th>
										  <th>Encrypted Size</th>
										  <!-- <th>Uploaded</th> -->
										  <th>Signed Time</th>
										  <th>Encrypted Time</th>
										  <th>Decrypted Time</th>
										  <th>Verified Time</th>
										  <th>Action</th>
										</tr>
									</thead>
									<tbody>
									<?php
									$files = Files::getAll($id);
									if($files){
										$no = 0;
										$MB = 1000000;
										$KB = 1000;
										foreach($files as $c){
											$no++;
											echo "<tr style='font-size:12px'>";
											echo "<td>".$no."</td>";
											echo "<td>".$c->nama.".".$c->jenis."</td>";
											
											$size = $c->ukuran;
											if($size > $MB){ // MB
												$size = number_format($size/$MB, 2, '.', ',') . " MB";
											}else if($size > $KB){ // KB
												$size = number_format($size/$KB, 2, '.', ',') . " KB";
											}else{
												$size .= " Bytes";
											}
											echo "<td>".$size."</td>";
											
											$size = $c->kompresi;
											if($size > $MB){ // MB
												$size = number_format($size/$MB, 2, '.', ',') . " MB";
											}else if($size > $KB){ // KB
												$size = number_format($size/$KB, 2, '.', ',') . " KB";
											}else{
												$size .= " Bytes";
											}
											echo "<td>".$size."</td>";
											
											echo "<td>".number_format(($c->kompresi / $c->ukuran * 100), 2, '.', ',')."%</td>";
											
											// encrypted size
											$enc_file = $pathdir.$c->idpegawai."_".$c->id."_".$c->nama.$cfg->ext;
											if(file_exists($enc_file)){
												$size = filesize($enc_file);
												if($size > $MB){ // MB
													$size = number_format($size/$MB, 2, '.', ',') . " MB";
												}else if($size > $KB){ // KB
													$size = number_format($size/$KB, 2, '.', ',') . " KB";
												}else{
													$size .= " Bytes";
												}
											}else{
												$size = 0;
											}
											echo "<td>".$size."</td>";
											
											/* if($c->waktu > 0){
												echo "<td class='text-center'><i class='fa fa-2x fa-calendar icondate' data-toggle='tooltip' event='".$c->waktu."' title='".App::showDate($c->waktu)."'></i></td>";
											}else{
												echo "<td class='text-center'><i class='fa fa-2x fa-times-circle' data-toggle='tooltip' title='Failed to Encrypted'></i></td>";
											} */
											
											// signature time
											$selisih = $c->signed - $c->upload;
											$secs = $selisih % 60;
											$mins = ($selisih - $secs) / 60;
											if($mins > 0){
												$selisih = $mins." min(s)";
												if($secs > 0){
													$selisih .= "<br>".$secs." sec(s)";
												}
											}else{
												if($secs > 0){
													$selisih = $secs." sec(s)";
												}else{
													$selisih = "<0 sec(s)";
												}
											}
											echo "<td>".$selisih."</td>";
											
											// encrypted time
											if($c->signed > 0 && $c->waktu > 0 && $c->kompresi > 0){
												$selisih = $c->waktu - $c->signed;
												$secs = $selisih % 60;
												$mins = ($selisih - $secs) / 60;
												if($mins > 0){
													$selisih = $mins." min(s)";
													if($secs > 0){
														$selisih .= "<br>".$secs." sec(s)";
													}
												}else if($secs > 0){
													$selisih = $secs." sec(s)";
												}else{
													$selisih = "<0 sec(s)";
												}
												echo "<td>".$selisih."</td>";
											}else{
												echo "<td class='text-center'><i class='fa fa-2x fa-times-circle' data-toggle='tooltip' title='Failed to Encrypted'></i></td>";
											}
											
											// decrypted time
											if($c->dekrip_selesai > 0){
												$selisih = $c->dekrip_selesai - $c->dekrip_mulai;
												$secs = $selisih % 60;
												$mins = ($selisih - $secs) / 60;
												if($mins > 0){
													$selisih = $mins." min(s)";
													if($secs > 0){
														$selisih .= "<br>".$secs." sec(s)";
													}
												}else{
													if($secs > 0){
														$selisih = $secs." sec(s)";
													}else{
														$selisih = "<0 sec(s)";
													}
												}
											}else if($c->dekrip_mulai > 0){
												$selisih = "<center><i class='fa fa-2x fa-hourglass-half' data-toggle='tooltip' title='On Proccessing (".App::timeAgo($c->dekrip_mulai).")'></i></center>";
											}else{
												$selisih = "-";
											}
											echo "<td>".$selisih."</td>";
											
											// verified time
											if($c->dekrip_selesai > 0){
												$selisih = $c->verifikasi - $c->dekrip_selesai;
												$secs = $selisih % 60;
												$mins = ($selisih - $secs) / 60;
												if($mins > 0){
													$selisih = $mins." min(s)";
													if($secs > 0){
														$selisih .= "<br>".$secs." sec(s)";
													}
												}else{
													if($secs > 0){
														$selisih = $secs." sec(s)";
													}else{
														$selisih = "<0 sec(s)";
													}
												}
											}else{
												$selisih = "-";
											}
											echo "<td>".$selisih."</td>";
											echo "<td class='text-center'>";
											echo "<a class='btn btn-success' href='".$url."&mode=download&id=".$c->id."' target='_blank' title='Download'><i class='fa fa-download'></i></a>";
											echo "<span class='btn btn-danger' onclick='deleteFile(".$c->id.")' title='Delete'><i class='fa fa-trash'></i></span>";
											echo "</td>";
											echo "</tr>";
										}
									}
									?>
									</tbody>
								</table>
							</div>
							<!-- /.box-body -->
						</div>
						<!-- /.box -->
						<div class="box box-success box-solid">
							<div class="box-header">
							  <h3 class="box-title"><i class="fa fa-upload"></i> &nbsp;Upload File</h3>
							  <div class="box-tools pull-right">
								<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
							  </div>
							  <!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<div class="form-group">
									<label for="objfile">File <span class="required">*</span></label>
									<input type="file" name="objfile" value="" id="objfile" class="form-control" required>
								</div>
								<div class="form-group">
									<label for="objtitle">Title <span class="required">*</span></label>
									<input type="text" name="objtitle" value="" id="objtitle" class="form-control" placeholder="" onkeydown="submitData(event);" required>
								</div>
								<div class="form-group">
									<label for="objdesc">Descriptions</label>
									<textarea name="objdesc" id="objdesc" class="form-control" placeholder="*optional" onkeydown="submitData(event);"></textarea>
								</div>
							</div>
							<!-- /.box-body -->
							<div class="box-footer text-center">
								<h4 id="msg-encrypting" style="display:none">
									<i class="fa fa-spin fa-spinner"></i> &nbsp;Please wait, encrypting on progress . . .<br><br>
									<b class="text-red"><i class="fa fa-warning"></i> PLEASE DO NOT CLOSE THIS WINDOW BEFORE IT'S DONE!</b><br><br>
									<center>
										<table>
											<tr>
												<td class="text-right">Estimated Time</td>
												<td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
												<td><i id="est_time">0</i> <i>seconds</i></td>
											</tr>
											<tr>
												<td class="text-right">Elapsed Time</td>
												<td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
												<td><i id="elapsed_time">0</i> <i>seconds</i></td>
											</tr>
										</table>
									</center>
								</h4>
								<div class="progress col-sm-12" style="display:none">
									<div class="progress-bar progress-bar-striped active" role="progressbar" aria-value-now="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
									</div>
								</div>
								<?php
								if($edit) echo '<input type="hidden" id="idpegawai" name="id" value="'.$id.'">';
								?>
								<button id="submit-upload" class="btn btn-success btn-lg" type="submit"><i class="fa fa-save"></i> &nbsp;SAVE</button>
							</div>
							<!-- /.box-footer -->
						</div>
						<!-- /.box -->
					</div>
				</div>
			</div>
		</div>
	  </div>
	</div>
    <!-- /.col-md-3 -->
  </div>
  <!-- /.row -->
<?php 	}
	  }else if($inputmode && $mode == "download"){ ?>
  <div class="row">
	<div class="col-lg-12">
		<div class="box box-warning box-solid">
            <div class="box-header">
              <h3 class="box-title" id="box-title"><i class="fa fa-hourglass-half"></i> &nbsp;Decrypting On Progress . . .</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body text-center">
				<div class="progress col-sm-12">
					<div class="progress-bar progress-bar-striped active" role="progressbar" aria-value-now="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
					</div>
				</div>
				<h3 id="msg-load">
					<i class="fa fa-spin fa-spinner"></i> &nbsp;Please wait, decrypting "<i id="dec_filename">nama_file</i>" on progress . . .<br><br>
					<b class="text-red"><i class="fa fa-warning"></i> PLEASE DO NOT CLOSE THIS WINDOW BEFORE IT'S DONE!</b><br><br>
					<center>
						<table>
							<tr>
								<td class="text-right">Estimated Time</td>
								<td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
								<td><i id="est_time">0</i> <i>seconds</i></td>
							</tr>
							<tr>
								<td class="text-right">Elapsed Time</td>
								<td>&nbsp;&nbsp;:&nbsp;&nbsp;</td>
								<td><i id="elapsed_time">0</i> <i>seconds</i></td>
							</tr>
						</table>
					</center>
				</h3>
			</div>
			<!-- /.box-body -->
        </div>
		<!-- /.box -->
	</div>
  </div>
  <!-- /.row -->
<?php } ?>
  <div class="row" id="datalist" style="<?php if($inputmode) echo 'display:none';?>">
	<div class="col-lg-12">
		<div class="box box-primary box-solid">
            <div class="box-header">
              <h3 class="box-title"><i class="fa fa-database"></i> &nbsp;Data List</h3>
			  <div class="box-tools pull-right">
				<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
			  </div>
			  <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
			  <a href="<?php echo $url;?>&mode=new">
				<button class="btn btn-success">
					<i class="fa fa-plus-circle"></i> &nbsp;ADD NEW
				</button>
			  </a>
			  <hr>
			  <table class="table-data table table-bordered table-hover">
                <thead>
					<tr>
					  <th>No</th>
					  <th>Fullname</th>
					  <th>Nickname</th>
					  <th>Mobile</th>
					  <th>Work Email</th>
					  <th>Division</th>
					  <th>Job Level</th>
					  <th>Position</th>
					  <th>Status</th>
					  <th>Action</th>
					</tr>
                </thead>
                <tbody>
				<?php
				$content = Pegawai::getAll();
				if($content){
					$no = 0;
					foreach($content as $c){
						$no++;
						echo "<tr>";
						echo "<td>".$no."</td>";
						echo "<td>".$c->nama_lengkap."</td>";
						echo "<td>".$c->nama_panggilan."</td>";
						echo "<td>".$c->mobile."</td>";
						echo "<td>".$c->email_kantor."</td>";
						echo "<td>".ucwords($c->namadivisi)."</td>";
						echo "<td>".ucwords($c->namajabatan)."</td>";
						echo "<td>".ucwords($c->posisi)."</td>";
						echo "<td>".ucwords($c->status)."</td>";
						echo "<td>";
						echo "<a class='btn btn-primary' href='".$url."&mode=edit&id=".$c->id."' title='Edit'><i class='fa fa-pencil'></i></a> &nbsp;";
						echo "<span class='btn btn-danger' onclick='deleteData(".$c->id.")' title='Delete'><i class='fa fa-trash'></i></span>";
						echo "</td>";
						echo '</tr>';
					}
				}
				?>
				</tbody>
              </table>
			</div>
            <!-- /.box-body -->
        </div>
		<!-- /.box -->
	</div>
	<!-- /.col -->
  </div>
  <!-- /.row -->
</section>
<!-- /.content -->

<!-- DataTables -->
<script src="<?php echo VSITE;?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo VSITE;?>/plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- Bootstrap datepicker -->
<script src="<?php echo VSITE;?>/plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- page script -->
<script src="<?php echo VSITE;?>/assets/js/upload.js"></script>
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
  });
  
  // Datepicker
  $('.datepicker').datepicker({
	format: 'dd M yyyy',
	autoclose: true
  });
  
  function submitData(e){
	var code = (e.keyCode ? e.keyCode : e.which);
	if(code == 13) { // Enter keycode
		$("#submit-upload").click();
	}
  }
  
  function showFormCareer(action=null){
	if(action){
		$("#form_career").hide();
		$("#add_career").show();
	}else{
		$("#form_career").show();
		$("#add_career").hide();
	}
  }
  
  function setEndPrevCareer(){
	if ($('#yes_input_prev').is(':checked')){
		$("#input_prev").show();
		$("#objdoe_prev").attr('required', true);
	}else{
		$("#input_prev").hide();
		$("#objdoe_prev").attr('required', false);
	}
  }
  
  function confirmPass(){
	if($("#objpass").val()!="" && $("#objpass").val() != $("#objpass2").val()){
		alert("Password & Confirm Password must be same, please retype again");
		$("#objpass").val("");
		$("#objpass2").val("");
		$("#objpass").focus();
	}
  }
  
  function deleteData(id){
	var yesno = confirm("Are you sure to delete this data?");
	if(yesno){
		location.href="<?=$url;?>&mode=delete&id="+id;
	}
  }
  
  function deleteFile(id){
	var yesno = confirm("Are you sure to delete this file?");
	if(yesno){
		location.href="<?=$url;?>&mode=deletefile&id="+id;
	}
  }
</script>
<?php
if(isset($_GET['res']) && $_GET['res'] == "success"){
	$script = '<script>
	$(".nav-tabs li").removeClass("active");
	$("#tab-document").addClass("active");
	$(".tab-pane").removeClass("active");
	$("#document").addClass("active");
	</script>';
	echo $script;
}

if(isset($_GET['mode']) && $_GET['mode'] == "download" && isset($_GET['id']) && is_numeric($_GET['id'])){
	$file = new Files($id);
	$filename = $file->nama.".".$file->jenis;
	
	$script = '<script>
	$(document).ready(function(){
		window.onbeforeunload = confirmExit;
		
		$("#dec_filename").html("'.$filename.'");
		
		// calculate percentage progress
		countload = 0;
		loadingbar = setInterval(function(){ progressing('.$file->ukuran.'); }, 1000); // 1 seconds
		
		var dataSet = new FormData();
		dataSet.append("id", '.$id.');
		dataSet.append("method", "download");
		
		$.ajax({
			type: "POST",
			data: dataSet,
			dataType: "json",
			url: "ajax.php",
			contentType: false,
			cache: false,      
			processData:false, 
			async: true,
			success: function(res){
				window.onbeforeunload = null;
				clearInterval(loadingbar);
				resetProgress();
				if(res == "File is unverified by Digital Signature" || res == "File is failed to be decrypted"){
					// alert(res);
					$("#msg-load").addClass("text-danger");
					$("#box-title").html("<i class=\'fa fa-times-circle\'></i> &nbsp;Failed to download file");
					$("#msg-load").html("<i class=\'fa fa-times-circle\'></i> &nbsp;ERROR: "+res);
				}else{
					$("#msg-load").addClass("text-success");
					$("#box-title").html("<i class=\'fa fa-check-circle\'></i> &nbsp;Successfully decrypting file");
					$("#msg-load").html("<i class=\'fa fa-check-circle\'></i> &nbsp;Successfully decrypting file \"<i>'.$filename.'\"</i>, generating the file to be downloaded...<br><br>Please close this page after download the decrypted file.");
					
					setTimeout(function(){ window.location.replace(res); }, 3000);
					setTimeout(function(){ window.close(); }, 5000);
				}
			},
			error: function(request, status, error){
				window.onbeforeunload = null;
				alert(request.responseText);
			}
		});
	});
	</script>';
	echo $script;
}
?>