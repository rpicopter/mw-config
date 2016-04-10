<?php
@include "def.php";

header("content-type:application/json");

$ret = array(
	'status' => 1
);

if($_POST['action'] == "reboot") {
	$ret = 0;
	rebootHost();
	$ret = array(
			'status' => $ret
		);
}

if($_POST['action'] == "save") {
	$ret = updateArgs( $_POST['service'],$_POST['args'] );
	$ret = array(
			'status' => $ret
		);
}

if($_POST['action'] == "restart") {

	$ret = updateArgs( $_POST['service'],$_POST['args'] );

	if ($ret==0) {
		killService( $_POST['service'] );
		//all the services are run in a loop hence killing them will restart them
	}



	$ret = array(
			'status' => $ret
		);

}

	echo json_encode($ret);
?>
