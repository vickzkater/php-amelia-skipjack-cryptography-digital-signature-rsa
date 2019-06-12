<?php
if(!$user->id){
	App::redirect(VSITE."?m=login");
}

if(!App::checkAccess("MANAGE_JOBLEVEL")){
	App::message("Sorry, you are not authorized to manage job level", "warning");
	App::redirect(VSITE."?access=denied");
}

// default page
$url = "index.php?m=master_joblevel";

// set form
$inputmode = false;
$edit = false;
if(isset($_GET['mode'])){
	// new data
	$inputmode = true;
	$details = null;
	
	if(isset($_GET['id']) && is_numeric($_GET['id'])){
		$id = (int)$_GET['id'];
		// edit or delete data
		switch($_GET['mode']){
			case "edit":
				$edit = true;
				$details = new Jabatan($id);
				break;
			case "delete":
				$details = Jabatan::delete($id);
				if($details){
					App::redirect($url);
				}
				break;
		}
	}
}

// if form submitted
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(Jabatan::save()){
		App::redirect($url);
	}
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
<?php if($inputmode){ ?>
  <div class="row" id="formdetails">
	<div class="col-lg-12">
		<div class="box box-success box-solid">
            <div class="box-header">
              <h3 class="box-title"><i class="fa fa-edit"></i> &nbsp;Form Details | <?php echo ucwords($_GET['mode']);?></h3>
            </div>
            <!-- /.box-header -->
			<form action="" method="post" enctype="multipart/form-data">
            <div class="box-body">
			  <div class="col-lg-12">
				<div class="form-group">
					<label for="objname">Job Level Name <span class="required">*</span></label>
					<input type="text" name="objname" value="<?php if($edit){ echo $details->nama; } ?>" id="objname" class="form-control" placeholder="must be unique" required>
				</div>
			  </div>
			</div>
            <!-- /.box-body -->
			<div class="box-footer text-center">
               <?php if($edit) echo '<input type="hidden" name="id" value="'.$id.'">'; ?>
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
				<button class="btn btn-success"><i class="fa fa-plus-circle"></i> &nbsp;ADD NEW</button>
			  </a>
			  <hr>
			  <table id="table-data" class="table table-bordered table-hover">
                <thead>
					<tr>
					  <th>No</th>
					  <th>Name</th>
					  <th>Action</th>
					</tr>
                </thead>
                <tbody>
				<?php
				$content = Jabatan::getAll();
				if($content){
					$no = 0;
					foreach($content as $c){
						$no++;
						echo "<tr>";
						echo "<td class='text-center'>".$no."</td>";
						echo "<td>".$c->nama."</td>";
						echo "<td>";
						echo "<a class='btn btn-primary' href='".$url."&mode=edit&id=".$c->id."' title='Edit'><i class='fa fa-pencil'></i></a> &nbsp;";
						echo "<span class='btn btn-danger' onclick='deleteData(".$c->id.")' title='Delete'><i class='fa fa-trash'></i></span>";
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
	</div>
	<!-- /.col -->
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
    $('#table-data').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": true
    });
  });
  
  function backToList(){
	$("#formdetails").hide();
	$("#datalist").show();
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
</script>