<?php
session_start();
@include "def.php";
@include "my_ip.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
<title>MultiWii Configurator</title>
<!-- Bootstrap -->
<link href="bootstrap-3.3.6-dist/css/bootstrap.min.css" rel="stylesheet">


<link href="styles/style.css" rel="stylesheet">

</head>

<body>

<div class="container">
<div class="starter-template">
<p>Loaded: <span id="update_time"/></p>
<p class="lead">
	Service status <a href="index.php?"><span class="glyphicon glyphicon-refresh top" title="Refresh page"></span></a>
</p>


<?php
	$files = glob($cfg_path.'/*.{start}', GLOB_BRACE);

	foreach ($files as $key => $value) {
	$f = basename($value);
	$name = getServiceName($f);

  	echo '<div class="row" style="height:40px">';
  	echo '<div class="col-sm-3 text-right">';
    echo '<span class="top" title="'.getServiceDescription($f).'">'.$name.'</span>';
    echo '</div>';
  	echo '<div class="col-sm-1 text-center">';
  	$status = getServiceStatus($f);
  	if ($status==0) echo '<span class="top glyphicon glyphicon-remove" title="Off"/>';
  	else echo '<span class="top glyphicon glyphicon-ok" title="Running"/>';
    echo '</div>';    
  	echo '<div class="col-sm-2 text-center">';
    echo '<button name="restart" data-index="'.$key.'" data-service="'.$f.'" type="button" class="btn btn btn-success btn-xs">Restart <span class="glyphicon glyphicon-refresh"/></button>';
    echo '</div>';     
  	echo '<div class="col-sm-6 text-left">';
    echo '<input name="args" type="text" data-index="'.$key.'" class="form-control input-sm" value="'.getServiceArgs($f).'" placeholder="Optional arguments"/>';
    echo '</div>';                  
    echo '</div>';
	}
?>
<hr/>
<div class="row" style="height:40px">
<div class="col-sm-4 text-right">
	<span class="top" title="This requires mw and mw-ws (mw-www) to be installed and running">MultiWii communication status:</span>
</div>
<div class="col-sm-2 text-center">
	<span id="mwstatus" class="top glyphicon" title="vv"></span>
</div>
</div>
<hr/>
<button name="reboot" type="button" class="btn btn btn-success btn-xs">Reboot <span class="glyphicon glyphicon-off"/></button>
<div id="info" class="alert alert-info" style="display: none;"></div>
<div id="danger" class="alert alert-danger" style="display: none;"></div>
</div>
</div>



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="jquery/jquery-2.2.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>

<script src="websockify/util.js"></script>
<script src="websockify/base64.js"></script>
<script src="websockify/websock.js"></script>
<script src="routines.js"></script>
<script src="multiwii.js"></script>

<script type="text/javascript">
    var proxy_ip = '<?php echo $host; ?>';
    var proxy_port = 8888;

    function reboot_host() {
	    $.ajax({
	      url: 'save.php',
	      type: 'post',
	      data: {'action': 'reboot'},
	      success: function(data, status) {
	      	$("#info").text("Reboot started...");
	      },
	      error: function(xhr, desc, err) {
	        console.log(xhr);
	        console.log("Details: " + desc + "\nError:" + err);
	        $("#info").text("Reboot started...");
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

    	counter = 5; //sec for status to change
    }

    function mw_communication_failed() {
    	$("#mwstatus").removeClass('glyphicon-remove');
    	$("#mwstatus").addClass('glyphicon-remove');
    	
    	var tooltip = "Updated: "+get_time();
    	$("#mwstatus").attr('data-original-title',tooltip);
    }

    //the ready function requests a status from mw, configured the UI to show/hide pages and once finished runs on_ready
	$(document).ready(function() {
		$("#update_time").text(get_time());
		$(".top").tooltip({
			placement: "top"
		});

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
		console.log("error",arguments);
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

