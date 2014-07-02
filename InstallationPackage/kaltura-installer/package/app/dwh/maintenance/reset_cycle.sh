#!/bin/bash

##################################### reset_cycle.sh ###################################
## This script reset cycles to the generated status based on a combinaton of filters. ##
########################################################################################

PROCESS_NAME=default
CYCLE_ID=default
CYCLE_STATUS=FAILED
HOSTNAME=127.0.0.1
PORT=3306
USER=etl
PASSWORD=etl

while getopts "n:i:s:h:P:u:p" o
do	case "$o" in
    n)	PROCESS_NAME="$OPTARG";;
    i)	CYCLE_ID="$OPTARG";;
    s)	CYCLE_STATUS="$OPTARG";;
    h)	HOSTNAME="$OPTARG";;
    P)	PORT="$OPTARG";;
    u)	USER="$OPTARG";;
    p)	PASSWORD="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-n process_name] [-i cycle_id] [-s cycle_status] [-h db_host] [-P db_port] [-u db_user] [-p password]"
		exit 1;;
	esac
done

SQL="UPDATE kalturadw_ds.cycles c, kalturadw_ds.processes p set c.status = 'GENERATED' where c.process_id = p.id and status='$CYCLE_STATUS' and (process_name='$PROCESS_NAME' or 'default'='$PROCESS_NAME') and (cycle_id='$CYCLE_ID' or 'default'='$CYCLE_ID')"


mysql -u$USER -p$PASSWORD -h$HOSTNAME -P$PORT -e "$SQL"
