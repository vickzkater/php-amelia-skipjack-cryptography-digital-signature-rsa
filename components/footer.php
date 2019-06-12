<footer class="main-footer">
	<div class="pull-right hidden-xs">
	  <b>Version</b> <?=$cfg->version;?>
	</div>
	<strong>Copyright &copy; <?=date("Y");?> <?php echo $cfg->copyright;?></strong> | All rights reserved.
</footer>

<!-- Slimscroll -->
<script src="<?php echo VSITE;?>/plugins/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="<?php echo VSITE;?>/plugins/fastclick/lib/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="<?php echo VSITE;?>/assets/js/adminlte.min.js"></script>
<script>
$(document).ready(function() {
    $("body").tooltip({ selector: '[data-toggle=tooltip]' });
	convertTimeThis();
});
function collapseSidebar(){
	var dataSet = new FormData();
		dataSet.append("id", "<?=$user->id;?>");
		dataSet.append("method", "collapsesidebar");
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
			console.log(res);
			if(res != "success"){
				alert("Failed to change status 'Collapsed-Sidebar'");
			}
		},
		error: function(request, status, error){
			console.log(request);
			console.log(status);
			console.log(error);
			alert(request.responseText);
		}
	});
}
</script>