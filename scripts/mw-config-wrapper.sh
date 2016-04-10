#!/bin/sh

CMD=`sed -n '2p' $1`
ARGS=`sed -n '3p' $1`
LOG=/tmp/${CMD}.log

while [ 1 ]; do
        sleep 1;
        echo "Starting $CMD $ARGS" >> /tmp/mw-config.log
        $CMD $ARGS > ${LOG} 2>&1
        echo "$CMD has stopped." >> /tmp/mw-config.log
done
