<?php
if($user->id > 0){
	App::redirect(VSITE);
}

// if form submitted
if($_SERVER['REQUEST_METHOD'] == "POST"){
	if(User::login()){
		App::redirect();
	}
}
?>
<div class="login-box">
  <div class="login-logo" style="color:white">
    <?php echo $cfg->sitefullname;?>
	<br>
	<b><?=$cfg->sitename?></b>
  </div>
  <!-- /.login-logo -->
  <div class="login-box-body">
    <p class="login-box-msg">Login Panel</p>
    <form action="" method="POST">
      <div class="form-group has-feedback">
        <input type="email" class="form-control" name="logname" id="logname" placeholder="Email" required autofocus>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" name="logpass" id="logpass" placeholder="Password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <!-- /.col -->
        <div class="col-xs-12">
          <input type="hidden" name="task" value="login">
		  <button type="submit" id="login" class="btn bg-orange btn-block btn-lg">L O G I N &nbsp;<i class="fa fa-sign-in"></i></button>
        </div>
        <!-- /.col -->
      </div>
    </form>
<?php if($cfg->forgotpass){ ?>
    <br><center><a href="index.php?m=forgotpass" style="color:white !important;">&nbsp;<i class="fa fa-unlock-alt"></i>&nbsp;&nbsp;&nbsp;Forgot Password</a></center>
<?php } ?>
  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->

<script>
$(function (){
	$('#login').click(function() {
		$('.alert').hide();
		if($('#logname').val() != '' && $('#logpass').val() != ''){
			$('#login').html('<i class="fa fa-spinner fa-spin"></i>&nbsp;&nbsp;<i>Authenticating ..</i>');
			$('#login').addClass('btn-success disabled');
			$('#login').removeClass('bg-orange');
			$('#logname').attr('readonly', true);
			$('#logpass').attr('readonly', true);
		}
	});
});
</script>