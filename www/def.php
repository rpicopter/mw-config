<?php

$cfg_path='/usr/local/etc/mw/';

function getServiceDescription($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return $lines[0];
}

function getServiceName($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return $lines[1];
}

function getServiceArgs($f) {
	global $cfg_path;
	$lines = file($cfg_path.$f);
	return $lines[2];
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
