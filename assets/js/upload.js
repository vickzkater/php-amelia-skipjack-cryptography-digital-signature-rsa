var unitCrypto = 3707;

function resetProgress() {
	$(".progress").attr("style", "display:none");
	$(".progress-bar").attr("class", "progress-bar");
	$(".progress-bar").html("");
	$(".progress-bar").attr("style", "width: " + 0 + "%");
	$(".progress-bar").attr("aria-value-now", 0);
}

function progressFail(err) {
	$(".progress-bar").attr("class", "progress-bar progress-bar-danger");
	$(".progress-bar").attr("style", "width:100%");
	$(".progress-bar").html(err);
	$(".progress").attr("style", "display: block");
}

var countload = 0;
function progressing(filesize) {
	var currentload = $(".progress-bar").attr("aria-value-now");
	
	var countPercent = countload;
	
	var percentComplete = (countPercent * unitCrypto) / filesize * 100;
	percentComplete = Math.floor(percentComplete);
	if(percentComplete >= 100){
		percentComplete = 99;
	}else if(percentComplete < 0){
		percentComplete = 0;
	}
	
	var estimated = (Math.floor(filesize / unitCrypto)) - countload + 3; // in seconds
	if(estimated > 59){
		var secs = estimated;
		estimated = Math.floor(estimated / 60);
		secs = secs - (estimated * 60);
		estimated = estimated+" mins "+secs;
	}
	if(estimated < 1){
		estimated = "few";
	}
	$("#est_time").html(estimated);
	
	var elapsed = countload;
	if(countload > 59){
		secs = countload;
		elapsed = Math.floor(countload / 60);
		secs = secs - (elapsed * 60);
		elapsed = elapsed+" mins "+secs;
	}
	$("#elapsed_time").html(elapsed);
	
	$(".progress").attr("style", "display: block"); 
	$(".progress").attr("class", "progress active"); 
	$(".progress-bar").attr("class", "progress-bar progress-bar-primary progress-bar-striped");
	$(".progress-bar").html(percentComplete+"%");
	$(".progress-bar").attr("style", "width: " + percentComplete + "%");
	$(".progress-bar").attr("aria-value-now", percentComplete);
	
	countload++;
}

function completeProgress(msg) {
	$(".progress-bar").attr("class", "progress-bar progress-bar-primary progress-bar-striped");
	$(".progress-bar").html(msg);
	$(".progress-bar").attr("style", "width: 100%");
	$(".progress-bar").attr("aria-value-now", 100);
}

function lockForm() {
	$("#objfile").attr("disabled", true);
	$("#objtitle").attr("disabled", true);
	$("#objdesc").attr("disabled", true);
	$("#submit-upload").hide();
}

function unlockForm() {
	$("#objfile").attr("disabled", false);
	$("#objtitle").attr("disabled", false);
	$("#objdesc").attr("disabled", false);
	$("#submit-upload").show();
}

$(document).ready(function(){
	// get url then set it
	var url = window.location.href.replace("&res=success", "") + "&res=success";
	
	var loadingbar = "";
	
	$("#submit-upload").click(function(){
		window.onbeforeunload = confirmExit;
		
		lockForm();
		
		var idpeg = $("#idpegawai").val();
		
		if(idpeg == ""){
			alert("Invalid Employee ID");
			unlockForm();
			return;
		}
		
		var judul = $("#objtitle").val();
		
		if(judul == ""){
			alert("Title must be filled");
			unlockForm();
			$("#objtitle").focus();
			return;
		}
		
		var uploaded = $("#objfile")[0].files[0];
		
		if(typeof uploaded == "undefined"){
			alert("File must be selected");
			unlockForm();
			$("#objfile").focus();
			return;
		}
		
		var ket = $("#objdesc").val();
		
		// calculate percentage progress
		countload = 0;
		loadingbar = setInterval(function(){ progressing(uploaded.size); }, 1000); // 1 seconds
		$("#msg-encrypting").show();
		
		var dataSet = new FormData();
		resetProgress();
		dataSet.append("id", idpeg);
		dataSet.append("title", judul);
		dataSet.append("file", uploaded);
		dataSet.append("desc", ket);
		dataSet.append("method", "upload");
		
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
				// console.log(res);
				window.onbeforeunload = null;
				clearInterval(loadingbar);
				if(res != "Successfully uploaded & encrypted file"){
					alert(res);
					progressFail(res);
					unlockForm();
					$("#msg-encrypting").hide();
				}
				else{
					completeProgress(res);
					$("#msg-encrypting").hide();
					setTimeout(function(){
						window.location.replace(url);
					}, 1000);
				}
			},
			error: function(request, status, error){
				window.onbeforeunload = null;
				alert(request.responseText);
				progressFail(res);
				unlockForm();
				$("#msg-encrypting").hide();
			}
		});
		
	});
});
