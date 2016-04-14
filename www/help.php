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
<div class="starter-template help-content">
<h2>Help</h2>
<hr/>
<h3>Introduction</h3>
<p>Status shows information of currently installed MultiWii services as well as shows connection status to MultiWii board</p>
<p>MultiWii flasher can be used to download, compile and flash MultiWii controller onto your board.</p>
<hr/>
<h3>Requirements</h3>
...
<hr/>
<h3>Wiring</h3>
<div style="text-align: center;">
	<a href="img/wiring.png" data-lightbox="wiring"><img src="img/wiring.png" width="50%"/></a>
</div>
<hr/>
</div>
</div>



<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="jquery/jquery-2.2.0.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="bootstrap-3.3.6-dist/js/bootstrap.min.js"></script>

<script src="lightbox2/js/lightbox.min.js"></script>

<script type="text/javascript">
    //the ready function requests a status from mw, configured the UI to show/hide pages and once finished runs on_ready
	$(document).ready(function() {
		$(".top").tooltip({
			placement: "top"
		});
	});

</script>

</body>
</html>

