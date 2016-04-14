<?php
@include "def.php";

header("content-type:application/json; charset=UTF-8");
$ret = array(
	'status' => 1
);


if($_POST['action'] == "checkPHP") {
	$ret = 0;


	if (!defined('PHP_VERSION_ID')) {
 	   	$version = explode('.', PHP_VERSION);

	    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
	}

	if (PHP_VERSION_ID >= 50200) {
		$ret = 1;
    }	

    $ret = array(
		'status' => $ret
	);
}

if($_POST['action'] == "flashMW") {
	$user = exec('whoami');
	$file = ($_POST['file']);
	$ret = 1;
	$log = "";
	$log1 = "";

	if (file_exists($spidev_path)===FALSE) { //check if we got hex file
		$ret = 0;
		$log1 = "SPIDEV device does not seem to exist (".$spidev_path.").\nEnsure you have spidev module loaded.";
	}

	if ($ret && (is_writable('/sys/class/gpio/export')===FALSE)) {
		$ret = 0;
		$log1 = "No access to GPIO (/sys/class/gpio/export)\nEnsure user '".$user."' has write access to it (see 'usermod' command).";		
	}

	if ($ret) {
		$cmd = 'avrdude -c linuxspi -P '.$spidev_path.' -p '.$avrdude_part.' -F -U flash:w:'.$file.' > '.$mw_sources_path.'/mw_flash.log 2>&1';
		exec($cmd, $v, $r);
		if ($r) $ret = 0;
		$log = file_get_contents($mw_sources_path."/mw_flash.log");

		if (($ret==0) && (strpos($log,'linuxspi_gpio_op_wr(): Unable to open file /sys/class/gpio/')>0)) {
			$log1="Please retry and if this does not help check if '".$user."' has write access to it (see 'usermod' command).";
		}

		if (($ret==0) && (strpos($log,'Unable to open SPI port ')>0)) {
			$log1="\nEnsure user '".$user."' has write access to it (see 'usermod' command).";
		}

	}


	$ret = array(
			'status' => $ret,
			'log' => $log,
			'log1' => $log1
		);
}

function is_process_running($PID)
{
   if ($PID) {
   	exec("ps $PID", $ProcessState);
   	return(count($ProcessState) >= 2);
   }
   return FALSE;
}

function run_background_process($Command)
{
   $PID = shell_exec($Command." & echo $!");
   return($PID);
}

if($_POST['action'] == "compileMW_check") {
	$ret = 1; //running
	$f = "";

	$pid = ($_POST['pid']);

	if (is_process_running($pid)==FALSE) $ret = 0;

	if ($ret==0) {
		$log = file_get_contents($mw_sources_path."/mw_compile.log");

		$f = $mw_sources_path.$mw_extract_folder."/build-".$arduino_board."/".$mw_extract_folder.".hex";
		if (file_exists($f)===FALSE) { //check if we got hex file
			$ret = 2;
		} else {
			//$ret = 0; //its set anyway
		}
	}

	$ret = array(
			'status' => $ret, //2 - something's wrong; 1 - running; 0 - done
			'log' => $log,
			'file' => $f
		);
}

if($_POST['action'] == "compileMW") {

	$ret = 1;
	$pid = 0;
	$log = '';
	$config = ($_POST['config']);

	//save config
	if (file_put_contents ($mw_sources_path.$mw_extract_folder."/config.h", $config)===FALSE) {
		$ret = 0;
		$log = "Error saving config";
	}

	//get rid of MultiWii.ino (it does not work with Arduini makefile)
	if ($ret && file_exists($mw_sources_path.$mw_extract_folder."/MultiWii.ino"))
		if (rename($mw_sources_path.$mw_extract_folder."/MultiWii.ino",$mw_sources_path.$mw_extract_folder."/MultiWii.ino.old")===FALSE) {
			$ret = 0;
			$log = "Error renaming MultiWii.ino file";
		}

	//put new Makefile file
	if ($ret && file_put_contents ($mw_sources_path.$mw_extract_folder."/Makefile", "\nBOARD_TAG=".$arduino_board)===FALSE) {
		$ret = 0;
		$log = "Error creating Makefile";
	}
	if ($ret && file_put_contents ($mw_sources_path.$mw_extract_folder."/Makefile", "\nF_CPU=".$arduino_cpu."\n" , FILE_APPEND)===FALSE) {
		$ret = 0;
		$log = "Error creating Makefile";
	}
	if ($ret && file_put_contents ($mw_sources_path.$mw_extract_folder."/Makefile", "include ".$arudino_mk_file."\n", FILE_APPEND)===FALSE) {
		$ret = 0;
		$log = "Error creating Makefile";
	}

	//compile
	if ($ret) {
		$cmd = "make -C ".$mw_sources_path.$mw_extract_folder." > ".$mw_sources_path."/mw_compile.log 2>&1";
		$pid = run_background_process($cmd);
	}

	$ret = array(
			'status' => $ret,
			'pid' => $pid,
			'log' => $log
		);
}

function br2nl($string)
{
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

if($_POST['action'] == "loadMWConfig") {
	$ret = 1; //assume it is fine

	$config = file_get_contents($mw_sources_path.$mw_extract_folder."/config.h");
	if ($config === FALSE) $ret = 0;
	else {
		//$config = preg_replace("/\r\n|\r|\n/",'',$config);	
		//$config = nl2br($config);
		//$config = br2nl($config);
	}

	$ret = array(
			'status' => $ret,
			'config' => $config
		);
}

function checkMWSources() { //we check if folder exist and config.h exist; at the same time we 
	global $mw_sources_path;
	global $mw_extract_folder;
	$ret = file_exists($mw_sources_path.$mw_extract_folder);

	if ($ret==TRUE)
		$ret = file_exists($mw_sources_path.$mw_extract_folder."/config.h");

	return $ret;
};

if($_POST['action'] == "downloadMWSources") { //download & extract sources
	$ret = 1; //assume we are ok
	$msg = '';

	if (!file_exists($mw_sources_path)) 
		if (!mkdir($mw_sources_path)) {
			$ret = 0;
			$msg = 'Parent directory does not exist! ('.$mw_sources_path.')';
		}
	
	if ($ret)
		if (file_put_contents($mw_sources_path."multiwii.zip", fopen($mw_download_url, 'r'))===FALSE) {
			$ret = 0;
			$msg = 'Unable to download '.$mw_download_url.' into '.$mw_sources_path.' folder.';			
		}

	if ($ret) {
		$zip = new ZipArchive;
		if ($zip->open($mw_sources_path."multiwii.zip") === TRUE) {
	    	if ($zip->extractTo($mw_sources_path) === FALSE) {
	    		$ret = 0;
	    		$msg = "Error extracting MultiWii archive: ".$mw_sources_path."multiwii.zip";
	    	} else {
	    		$zip->close();
	    		$ret = 1;
	    		$msg = $mw_sources_path;
	    	}
		} else {
	    	$ret = 0;
	    	$msg = "Error reading archive: ".$mw_sources_path."multiwii.zip";
		}
	}

	$ret = checkMWSources();
	if ($ret === FALSE) $msg = 'The extracted sources do no seem to be usable.';

	$ret = array(
			'status' => $ret,
			'msg' => $msg
		);
}

if($_POST['action'] == "checkMWSources") { //we check if folder exist and config.h exist; at the same time we 
	$ret = checkMWSources();

	$ret = array(
			'status' => $ret
		);
}

if($_POST['action'] == "reboot") {
	$ret = 0;
	rebootHost();
	$ret = array(
			'status' => $ret
		);
}

if($_POST['action'] == "getLog") {
	$ret = 1;
	$name = $_POST['name'];

	$log_file = $mw_sources_path."/".$name.".log";
	$log = file_get_contents($log_file);

	if ($log===FALSE) {
		$ret = 0;
		$log = "Error loading ".$log_file;
	}

	$ret = array(
			'status' => $ret,
			'log' => $log
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
