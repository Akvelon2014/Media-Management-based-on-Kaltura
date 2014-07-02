#!/bin/bash

USER="etl"
PW="etl"
HOST=localhost
PORT=3306

while getopts "u:p:d:h:P:s:k:" o
do      case "$o" in
        u)      USER="$OPTARG";;
        p)      PW="$OPTARG";;
        h)      HOST="$OPTARG";;
        P)      PORT="$OPTARG";;
        [?])    echo >&2 "Usage: $0 [-u username] [-p password] [-h host-name] [-P port] "
                exit 1;;
        esac
done


mysql -u$USER -p$PW -h$HOST -P$PORT -e "update kalturadw_ds.locks set lock_state=0"
echo "Locks released, Output of locks table:"
mysql -u$USER -p$PW -h$HOST -P$PORT -e "select * from kalturadw_ds.locks"
