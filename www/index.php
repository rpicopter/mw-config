<!DOCTYPE html>
<html lang="en">

<?php
session_start();
@include "def.php";
@include "my_ip.php";
?>

<head>
<?php @include "header.php" ?>
<link href="lightbox2/css/lightbox.min.css" rel="stylesheet">
</head>


<body>

<?php
@include "nav.php";
?>

<div class="container">
<div class="starter-template">
<div id="modal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
  			<div class="modal-body log" id="modal_text">
    			Hello!
  			</div>
  			<div class="modal-footer">
    			<button type="button" data-dismiss="modal" class="btn btn-primary">OK</button>
  			</div>
		</div>
	</div>
</div>

<p>Loaded: <span id="update_time"/></p>
<p class="lead">
	Service status <a href="index.php?"><span class="glyphicon glyphicon-refresh top" title="Refresh page"></span></a>
</p>


<?php
	$files = glob($cfg_path.'/*.{start}', GLOB_BRACE);

	foreach ($files as $key => $value) {
	$f = basename($value);
	$name = getServiceName($f);

  	echo '<div id="'.$name.'" class="row" style="height:40px">';
  	echo '<div class="col-xs-3 text-right">';
    echo '<span class="top" title="'.getServiceDescription($f).'">'.$name.'</span>';
    echo '</div>';
  	echo '<div class="col-xs-1 text-center">';
  	$status = getServiceStatus($f);
  	if ($status==0) echo '<span data-service="'.$name.'" data-index="'.$key.'" data-status="0" class="top glyphicon glyphicon-remove" title="Off. Show log"/>';
  	else echo '<span data-service="'.$name.'" data-index="'.$key.'" data-status="1" class="top glyphicon glyphicon-ok" title="Running. Show log"/>';
    echo '</div>';    
  	echo '<div class="col-xs-2 text-center">';
    echo '<button name="restart" data-index="'.$key.'" data-service="'.$f.'" type="button" class="btn btn btn-success btn-xs">Restart <span class="glyphicon glyphicon-refresh"/></button>';
    echo '</div>';     
  	echo '<div class="col-xs-6 text-left">';
    echo '<input name="args" type="text" data-index="'.$key.'" class="form-control input-sm" value="'.getServiceArgs($f).'" placeholder="Optional arguments"/>';
    echo '</div>';                  
    echo '</div>';
	}
?>
<hr/>
<div class="row" style="height:40px">
<div class="col-xs-6 text-right">
	<span class="top" title="This requires mw and mw-ws (mw-www) to be installed and running">MultiWii communication status:</span>
</div>
<div class="col-xs-6 text-left">
	<span id="mwstatus" class="top glyphicon" title="vv"></span>
</div>
</div>
<hr/>

<div id="info" class="alert alert-info" style="display: none;"></div>
<div id="danger" class="alert alert-danger" style="display: none;"></div>
<div id="warning" class="alert alert-warning" style="display: none;white-space:pre-wrap;"></div>

<button name="reboot" type="button" class="btn btn btn-success btn-xs">Reboot <span class="glyphicon glyphicon-off"/></button>

</div>
</div>



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="jquery/jquery-2.2.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>
<script src="lightbox2/js/lightbox.min.js"></script>

<script src="websockify/util.js"></script>
<script src="websockify/base64.js"></script>
<script src="websockify/websock.js"></script>
<script src="routines.js"></script>
<script src="multiwii.js"></script>

<script type="text/javascript">
    var proxy_ip = '<?php echo $host; ?>';
    var proxy_port = 8888;

    function show_log(e) {
    	var args = e.target.dataset.service;
    	
    	$.ajax({
	      url: 'save.php',
	      type: 'post',
	      data: {'action': 'getLog', 'name':args},
	      success: function(data, status) {
	      	//console.log(data);
			$('#modal_text').text(data.log);
			$('#modal').modal({ backdrop: 'static', keyboard: false });
	      },
	      error: function(xhr, desc, err) {
	        console.log(xhr);
	        console.log("Details: " + desc + "\nError:" + err);
	      }
	    }); // end ajax call	


    }

    function reboot_host() {
    	$("#info").text("Rebooting..."); //the ajax call never returns 
    	$('#info').show();
	    $.ajax({
	      url: 'save.php',
	      type: 'post',
	      data: {'action': 'reboot'},
	      success: function(data, status) {
	      	
	      },
	      error: function(xhr, desc, err) {
	        console.log(xhr);
	        console.log("Details: " + desc + "\nError:" + err);
	      }
	    }); // end ajax call	    	
    }

    function restart_service(data) {
    	console.log(data);
    	//get args for service
    	var args = $("input[name='args']")[data.index].value;
    	console.log(args);
	    $.ajax({
	      url: 'save.php',
	      type: 'post',
	      data: {'action': 'restart', 'service': data.service, 'args': args},
	      success: function(data, status) {
	        if(data.status == 0) {
	        	$("#info").text("Service has been restarted.\nRefreshing...");
				$('#info').show();
				setTimeout(function(){$('#info').hide();location.reload();},2000);	
	        } else {
	        	console.log(data);
	        	$("#danger").text("Something went wrong!");
				$('#danger').show();
				setTimeout(function(){$('#danger').hide();},10000);		        	
	        }
	      },
	      error: function(xhr, desc, err) {
	        console.log(xhr);
	        console.log("Details: " + desc + "\nError:" + err);
	       	$("#danger").text("Something went wrong!");
			$('#danger').show();
			setTimeout(function(){$('#danger').hide();},10000);	
	      }
	    }); // end ajax call	
    }

    function mw_communication_ok() {
    	$("#mwstatus").removeClass('glyphicon-remove');
    	$("#mwstatus").addClass('glyphicon-ok');

    	var tooltip = "Updated: "+get_time();
    	$("#mwstatus").attr('data-original-title',tooltip);

    	$("#mwstatus").attr('status','1');

    	counter = 5; //sec for status to change
    }

    function mw_communication_failed() {
    	$("#mwstatus").removeClass('glyphicon-remove');
    	$("#mwstatus").addClass('glyphicon-remove');
    	
    	var tooltip = "Updated: "+get_time();
    	$("#mwstatus").attr('data-original-title',tooltip);

    	$("#mwstatus").attr('status','0');

    	if (installation_ok && ws_connection) {
	       	$("#warning").text("Unable to communicate with MultiWii board.\nPlease check the wiring to your MultiWii board, the UART port configuration and ensure your have compiled & flashed successfully the MultiWii board");
			$('#warning').show();    		
    	}
    }

    function check_installation() {
		var service = $("#mw");
		if (service.length==0) {
	       	$("#warning").text("It looks like MultiWii service has not been installed. Please install mw-service package and re-try.");
			$('#warning').show();
			return -1;		
		}

		service = $("span[data-service='mw']");
		if (service[0].dataset.status==0) {
	       	$("#warning").text("MultiWii service is not running.\nCheck the logs in /tmp folder.\nYou might also want to run it manually to check for any errors.");
			$('#warning').show();
			return -1;				
		}

		service = $("#mw-ws");
		if (service.length==0) {
	       	$("#warning").text("It looks like MultiWii WebSocket service has not been installed.\nPlease install mw-www (mw-ws) package.");
			$('#warning').show();
			//setTimeout(function(){$('#warning').hide();},4000);	
			return -1;		
		}

		service = $("span[data-service='mw-ws']");
		if (service[0].dataset.status==0) {
	       	$("#warning").text("MultiWii WebSocket service is not running.\nCheck the logs in /tmp folder.\nYou might also want to run it manually to check for any errors.");
			$('#warning').show();
			return -1;		
		}


		return 0;
    }

    //the ready function requests a status from mw, configured the UI to show/hide pages and once finished runs on_ready
	$(document).ready(function() {
		$("#update_time").text(get_time());
		$(".top").tooltip({
			placement: "top"
		});

		 $("span[data-status]").on("click",show_log);


		installation_ok = !check_installation();
		ws_connection = 0;


		$("button[name='restart']").click(
    		function(e) { 
    			if (e.target.dataset.index) restart_service(e.target.dataset); 
    		} 
    	);
		$("button[name='reboot']").click(
    		function(e) { reboot_host(); } 
    	);    	

		ws = new Websock();
        ws.on('error',_error);
		ws.on('message',_received);
		ws.on('open',_connected);
        ws.open("ws://"+proxy_ip+":"+proxy_port);

    	mw = new MultiWii();

    	counter = 10;
	});

	function _error() {
		if (installation_ok) {
	    	$("#warning").text("Unable to connect to MultiWii WebSocket service.\nCheck any firewalls you might have.\nEnsure it is accessible on "+proxy_ip+":"+proxy_port);
			$('#warning').show();
		}

		mw_communication_failed();
	}

	function _received() {
		var data;
		do { //receive messages in a loop to ensure we got all of them
			data = mw_recv();
			if (data.err == undefined) { //if err is set it means there was a genuine error or we haven't received enough data to proceed yet
				if (data.id==101) {
					mw_communication_ok();
				}
			}
		} while (data.err == undefined); 		
	};

	function _connected() {
		var msg;

		msg = mw.filters([101]); //filters need to be sent as the first message on a new connection to mw proxy
		ws.send( msg );

		setInterval(update,1000); //keep sending the requests every second
		ws_connection = 1;
	}

	function update() {
		if (counter>0) counter--;
		else mw_communication_failed();

		var msg;
		msg = mw.serialize({ //prepere a request message
			"id": 101
		});
		ws.send(msg); //send it
	}


</script>

</body>
</html>

