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


LOCKS_SEIZED=$(mysql --skip-column-names -u$USER -p$PW -h$HOST -P$PORT -e "select count(*) from kalturadw_ds.locks where lock_state = 1")
if [ $LOCKS_SEIZED -gt 0 ]; then
	echo
	echo "***** Unable to seize all locks *****"
	echo
	mysql --skip-column-names -u$USER -p$PW -h$HOST -P$PORT -e "select CONCAT('The ',lock_name, ' is currently seized since ', lock_time) from kalturadw_ds.locks where lock_state = 1" | awk '{print}'
	echo
	exit 1
fi

mysql -u$USER -p$PW -h$HOST -P$PORT -e "update kalturadw_ds.locks set lock_state=1, lock_time=now()"
echo "Locks seized. Output of locks table"
mysql -u$USER -p$PW -h$HOST -P$PORT -e "select * from kalturadw_ds.locks"
