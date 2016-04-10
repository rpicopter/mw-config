#!/bin/sh

for f in "/etc/mw"/*.start
do 
	echo "Starting: $f"; 
	mw-config-wrapper.sh $f &
done

