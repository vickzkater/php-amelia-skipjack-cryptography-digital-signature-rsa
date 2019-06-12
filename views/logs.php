<?php
if(!$user->id){
	App::redirect(VSITE."?m=login");
}

if(!App::checkAccess("VIEW_LOGS")){
	App::message("Sorry, you are not authorized to view logs", "warning");
	App::redirect(VSITE."?access=denied");
}

// default page
$url = "index.php?m=logs";

$logs = Logs::getAll();
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
  <div class="row" id="datalist">
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
				<table id="table-data" class="table table-bordered table-hover">
                  <thead>
					<tr>
					  <th>No</th>
					  <th>Time</th>
					  <th>User</th>
					  <th>Action</th>
					</tr>
                  </thead>
                  <tbody>
				  <?php
					if($logs){
						foreach($logs as $log){
							echo '<tr>';
							echo '<td>'.$log->id.'</td>';
							echo '<td class="vdate" event="'.$log->waktu.'">'.App::showDate($log->waktu).'</td>';
							echo '<td>'.$log->username.'</td>';
							echo '<td>'.$log->aksi.'</td>';
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
<!-- page script -->
<script>
  $(function () {
    $('#table-data').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": true,
      "ordering": true,
	  "order": [[ 0, "desc" ]],
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