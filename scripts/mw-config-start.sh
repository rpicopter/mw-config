#!/bin/sh

for f in "/usr/local/etc/mw"/*.start
do 
	echo "Starting: $f"; 
	mw-config-wrapper.sh $f &
done

