<!DOCTYPE html>
<html lang="en">

<?php
session_start();
@include "def.php";
@include "my_ip.php";
?>

<head>
<?php @include "header.php" ?>
</head>

<body>

<?php
@include "nav.php";
?>



<div class="container">
<div class="starter-template">

<div id="confirm" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
  			<div class="modal-body" id="modal_text">
    			Are you sure?
  			</div>
  			<div class="modal-footer">
    			<button type="button" data-dismiss="modal" class="btn btn-primary" id="modal_yes">Yes</button>
   				<button type="button" data-dismiss="modal" class="btn" id="modal_no">No</button>
  			</div>
		</div>
	</div>
</div>

<p class="lead">
	MultiWii Flasher
</p>

<div id="config_form" class="container" style="display: none;">
	<textarea class="form-control config-text-area" id="config"></textarea>
	<div style="margin-top:20px; margin-bottom:20px;">
		<button id="config_save" type="button" class="btn btn btn-success btn-xs">Save &amp; compile <span class="glyphicon glyphicon-play"/></button>
		<button id="config_reset" type="button" class="btn btn btn-success btn-xs">Reset <span class="glyphicon glyphicon-repeat"/></button>
	</div>
</div>

<div id="compile_form" class="container" style="display: none;">
	<textarea class="form-control compile-text-area" id="compile_log"></textarea>
	<div style="margin-top:20px; margin-bottom:20px">
		<button id="compile_back" type="button" class="btn btn btn-success btn-xs">Go back <span class="glyphicon glyphicon-arrow-left"/></button>
		<button id="compile_flash" type="button" class="btn btn btn-success btn-xs">Flash <span class="glyphicon glyphicon-play"/></button>
		<button id="compile_retry" type="button" class="btn btn btn-success btn-xs">Retry <span class="glyphicon glyphicon-repeat"/></button>
	</div>
</div>

<div id="flash_form" class="container" style="display: none;">
	<textarea class="form-control flash-text-area" id="flash_log"></textarea>
	<div style="margin-top:20px; margin-bottom:20px">
		<button id="flash_retry" type="button" class="btn btn btn-success btn-xs">Retry <span class="glyphicon glyphicon-repeat"/></button>
	</div>
</div>

<div class="container">
	<div id="info" class="alert alert-info" style="display: none;white-space:pre-wrap;"></div>
	<div id="danger" class="alert alert-danger" style="display: none;white-space:pre-wrap;"></div>
</div>

</div>
</div>


<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="jquery/jquery-2.2.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>

<script type="text/javascript">


	function stage4() { //flash
		$("#info").text("Flashing file: "+flash_file+"\nPlease wait...");
		$("#info").show();
		$('#danger').hide();
		$('#compile_form').hide();

	    $.ajax({ //timeout question?
	      url: 'save.php', type: 'post',
	      data: {'action': 'flashMW', 'file': flash_file},
	      success: function(data, status) {
	      	console.log(data);
	      	$('#info').hide();
	      	$('#flash_form').show();
	      	$("#flash_log").val(data.log);
	      	resizeTextArea($("#flash_log"))
	      	$("#flash_log").attr("readonly", "readonly");
	      	$('html,body').animate({ scrollTop: $("#flash_form")[0].scrollHeight}, 500);
	      	if (data.status==1) { 
	      		$("#info").text("All done!");
	      		setTimeout(function() {$('#info').hide();},5000);	
	      	} else {
	        	$('#danger').text("Error flashing.\n"+data.log1);
	        	$('#danger').show();	     		
	      	}
	      },
	      error: ajax_error
	    }); // end ajax call
	}

	function stage_3_back() {
		$('#compile_form').hide();
		stage2();
	}	

	function stage3_1() {
		$("#danger").hide();
	    $.ajax({ //timeout question?
	      url: 'save.php', type: 'post',
	      data: {'action': 'compileMW_check', 'pid': pid},
	      success: function(data, status) {
	      	console.log(data);
	      	if (data.status!=1) { //done with error or ok
	      		clearInterval(interval);
				$('#info').hide();
	      		$('#compile_form').show();
	      		$("#compile_log").val(data.log);
	      		resizeTextArea($("#compile_log"))
	      		$("#compile_log").attr("readonly", "readonly");
	      		$('html,body').animate({ scrollTop: $("#compile_form")[0].scrollHeight}, 500);
	      		if (data.status>1) { //error
	        		$('#danger').text("Error compiling.");
	        		$('#danger').show();
	      		} else {
	      			flash_file = data.file;
	      		}
	      	} else if (data.status==1) { //still running
	      	} 
	      },
	      error: ajax_error
	    }); // end ajax call		
		//clearInterval(interval);
	}

	function stage3() { //compile
		//flashes in the ajax call and shows output
		$("#info").text("Starting compilation...");
		$("#info").show();
		$("#danger").hide();
		$('#config_form').hide();
		$('#compile_form').hide(); //in case we have it through retry 

		var cfg = $("#config").val();

	    $.ajax({ //timeout question?
	      url: 'save.php', type: 'post',
	      data: {'action': 'compileMW', 'config': cfg},
	      success: function(data, status) {
	      	if(data.status == 1) {
	      		$("#info").text("Compilation started (PID: "+data.pid+").\nPlease wait, this might take up to a few minutes.");
	      		interval = setInterval(stage3_1,5000); 
	      		pid = data.pid;
	      	} else {
	      		$("#info").hide();
	        	$('#danger').text("Error initializing compilation. "+data.log);
	        	$('#danger').show();	      		
	      	}
	      },
	      error: ajax_error
	    }); // end ajax call
	}

	function stage2() { //edit config
		$("#info").text("Opening MW config...");
		$("#info").show();
		$('#config_form').show();
		//$('#config').autoGrow();

	    $.ajax({ //timeout question?
	      url: 'save.php', type: 'post', timeout: 30000,
	      data: {'action': 'loadMWConfig'},
	      success: function(data, status) {
	      	$('#info').hide();
	      	//console.log(data);
	        if(data.status == 1) {
	        	$("#config").val(data.config);
	        	resizeTextArea($("#config"));
	        } else {
	        	$('#danger').text("Error opening config. "+data.msg);
	        	$('#danger').show();
	        }
	      },
	      error: ajax_error
	    }); // end ajax call			
	}

	function stage1_1() { //download sources
		$("#info").text("Please wait. Downloading...");
		$('#info').show();
	    $.ajax({ //timeout question?
	      url: 'save.php', type: 'post', timeout: 30000,
	      data: {'action': 'downloadMWSources'},
	      success: function(data, status) {
	        if(data.status == 1) {
	        	$("#info").text("Done. Sources location: "+data.msg);
				setTimeout(function() {$('#info').hide(); stage2();},3000);		
	        } else {
	        	$('#info').hide();
	        	$('#danger').text("Error downloading sources. "+data.msg);
	        	$('#danger').show();
	        }
	      },
	      error: ajax_error
	    }); // end ajax call			
	}
	
	function stage1() { //checking if sources exist
		$("#info").text("Please wait. Checking MultiWii source files...");
		$('#info').show();
	    $.ajax({
	      url: 'save.php', type: 'post',
	      data: {'action': 'checkMWSources'},
	      success: function(data, status) {
	      	$('#info').hide();
	        if(data.status == 1) {
	        	stage2();	
	        } else {
	        	$('#modal_text').text("MultiWii sources not found. Should I download them?");
	        	$('#confirm').modal({ backdrop: 'static', keyboard: false })
        			.one('click', '#modal_yes', stage1_1);
	        }
	      },
	      error: ajax_error
	    }); // end ajax call	
	}

	function stage0() { //check php version
		$("#info").text("Checking PHP version...");
		$('#info').show();
	    $.ajax({
	      url: 'save.php', type: 'post',
	      data: {'action': 'checkPHP'},
	      success: function(data, status) {
	      	$('#info').hide();
	        if(data.status == 0) {	
	        	$("#danger").text("You seem to have an old version of PHP. This might not work.");
	        	$("#danger").show();
	        	setTimeout(function() {stage1();},3000);		
	        } else {
	        	stage1();
	        }
	      },
	      error: ajax_error
	    }); // end ajax call	
	}

	function ajax_error(xhr, desc, err) {
	    console.log(xhr); console.log("Details: " + desc + "\nError:" + err);
	    $("#danger").text("Something went wrong!"); $('#danger').show();
		//setTimeout(function(){$('#danger').hide();},10000);	
	}


	function resizeTextArea(i) {
		i.css('height','auto'); //adjust the height of the scroll area to 100%
		i.height(i[0].scrollHeight); //adjust the height of the scroll area to 100%
	}

    //the ready function requests a status from mw, configured the UI to show/hide pages and once finished runs on_ready
	$(document).ready(function() {
		$(".top").tooltip({
			placement: "top"
		});

		$("#config_reset").on("click",stage2);
		$("#config_save").on("click",stage3);
		$("#compile_retry").on("click",stage3);
		$("#compile_back").on("click",stage_3_back);
		$("#compile_flash").on("click",stage4);
		$("#flash_retry").on("click",stage4);

		stage0();

		//flash_file="/tmp/multiwii-firmware-upstream_shared/build-atmega328/multiwii-firmware-upstream_shared.hex";
		//stage4();

	});
</script>
</body>
</html>
