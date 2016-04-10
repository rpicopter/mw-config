#!/bin/sh

while [ 1 ]; do
	CMD=`sed -n '2p' $1`
	ARGS=`sed -n '3p' $1`
	sleep 1;
	$CMD $ARGS
done

