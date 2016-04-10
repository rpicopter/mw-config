<?php

$cmd="/sbin/ifconfig $1 | grep \"inet addr\" | awk -F: '{print $2}' | awk '{print $1}'";
$out = exec($cmd,$ret);
for ($i=0;$i<count($ret);$i++) {
        if (substr_compare($ret[$i],'127.',0,4)!=0)
                $host = $ret[$i];
}

/*
$host = getHostName();
$host = $ret[1];
*/
//$host = "127.0.0.1";
//$host = "192.168.10.20";
?>

