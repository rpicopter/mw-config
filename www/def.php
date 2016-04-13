<?php

$mw_download_url = 'https://github.com/multiwii/multiwii-firmware/archive/upstream_shared.zip';
$mw_sources_path = '/tmp/';
$mw_extract_folder = 'multiwii-firmware-upstream_shared';

$arudino_mk_file = '/usr/share/arduino/Arduino.mk'; //(https://github.com/sudar/Arduino-Makefile)
$arduino_cpu = '16000000L'; //see Arduino-Makefile 
$arduino_board = 'atmega328'; //see Arduino-Makefile 

$avrdude_part = 'm328p'; //as per avrdude.config (part id) (https://github.com/kcuzner/avrdude)
$spidev_path = '/dev/spidev0.0';




$cfg_path='/usr/local/etc/mw/';

$status_page = 'index.php';
$status_name = 'Status';
$programmer_page = 'flasher.php';
$programmer_name = 'MultiWii flasher';
$help_page = 'help.php';
$help_name = 'Help';

function getServiceDescription($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return trim($lines[0]);
}

function getServiceName($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return trim($lines[1]);
}

function getServiceArgs($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return trim($lines[2]);
}

function getServicePID($f) {
	global $cfg_path;
	$name = getServiceName($f);
	exec("/bin/pidof -x ".$name,$pids);
	return $pids;
}

function killService($f) {
	$pids = getServicePID($f);
	foreach ($pids as $key => $value) {
		exec("/usr/local/bin/mw-config-task -k ".$value);
	}
}

function getServiceStatus($f) {
	$pids = getServicePID($f);
	if(empty($pids)) {
		return 0;
	} else {
		return 1;
	}	
}

function updateArgs($f,$args) {
	global $cfg_path;

	$file       = file($cfg_path.$f);
	$first_line = array_shift($file); 
	$second_line = array_shift($file);
	$third_line = array_shift($file);

	array_unshift($file, $args);
	array_unshift($file, $second_line); 
	array_unshift($file, $first_line); 

	$fp = fopen($cfg_path.$f, 'w');       // Reopen the file
	if ($fp==FALSE) return -1;
	$ret = fwrite($fp, implode("", $file));
	if ($ret==FALSE) return -1;
	fclose($fp);

	return 0;
}

function rebootHost() {
	exec("/usr/local/bin/mw-config-task -r");
}

?>
