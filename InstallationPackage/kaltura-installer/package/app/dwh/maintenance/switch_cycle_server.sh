#!/bin/bash

##################################### switch_cycle_server.sh ###################################
## This script switches a cycle to a different server. ##
########################################################################################

CYCLE_ID=default
HOSTNAME=127.0.0.1
ETL_SERVER=127.0.0.1
PORT=3306
USER=etl
PASSWORD=etl

while getopts "n:i:s:h:P:u:p" o
do	case "$o" in
    i)	CYCLE_ID="$OPTARG";;
    s)  ETL_SERVER="$OPTARG";;
    h)	HOSTNAME="$OPTARG";;
    P)	PORT="$OPTARG";;
    u)	USER="$OPTARG";;
    p)	PASSWORD="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-i cycle_id] [-h db_host] [-P db_port] [-u db_user] [-p password] [-s etl_server]"
		exit 1;;
	esac
done

if ! [ "$CYCLE_ID" -eq "$CYCLE_ID" 2> /dev/null ]
then
    echo cycle_id "($CYCLE_ID)" must be numeric 
    exit
fi

ASSIGN_CYCLE_SQL="UPDATE kalturadw_ds.cycles c, (SELECT etl_server_id FROM kalturadw_ds.etl_servers WHERE etl_server_name = '$ETL_SERVER') e SET c.assigned_server_id = e.etl_server_id,STATUS='REGISTERED' WHERE cycle_id=$CYCLE_ID"
DELETED_SPLITTED_CYCLE="DELETE kalturadw_ds.files FROM kalturadw_ds.files,  (SELECT file_name FROM kalturadw_ds.files WHERE cycle_id = $CYCLE_ID AND file_status = 'SPLITTED') splitted_files WHERE SUBSTR(files.file_name,1,6) = 'split_' AND SUBSTR(files.file_name,7,LENGTH(files.file_name) - 9) = splitted_files.file_name  AND files.cycle_id = $CYCLE_ID"
SET_IN_CYCLE_STATUS="UPDATE kalturadw_ds.files SET file_status = 'IN_CYCLE' where cycle_id = $CYCLE_ID"

mysql -u$USER -p$PASSWORD -h$HOSTNAME -P$PORT -e "$ASSIGN_CYCLE_SQL"
mysql -u$USER -p$PASSWORD -h$HOSTNAME -P$PORT -e "$DELETED_SPLITTED_CYCLE"
mysql -u$USER -p$PASSWORD -h$HOSTNAME -P$PORT -e "$SET_IN_CYCLE_STATUS"
