#!/bin/sh

while [ 1 ]; do
        sleep 1;

        CMD=`sed -n '2p' $1` #this needs to be evaluated every run otherwise it's not possible to hot change parameters without reboot
        ARGS=`sed -n '3p' $1`
        LOG=/tmp/${CMD}.log

        echo "Starting $CMD $ARGS" >> /tmp/mw-config.log
        $CMD $ARGS > ${LOG} 2>&1
        echo "$CMD has stopped." >> /tmp/mw-config.log
done
