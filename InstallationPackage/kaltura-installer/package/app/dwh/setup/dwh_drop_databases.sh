#!/bin/bash

USER="etl"
PW="etl"
KITCHEN=/usr/local/pentaho/pdi/kitchen.sh
ROOT_DIR=/opt/kaltura/dwh
HOST=localhost
PORT=3306

while getopts "u:p:k:d:h:P:" o
do	case "$o" in
	u)	UESR="$OPTARG";;
	p)	PW="$OPTARG";;
    k)	KITCHEN="$OPTARG";;
    d)	ROOT_DIR="$OPTARG";;
	h)	HOST="$OPTARG";;
	P)	PORT="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-u username] [-p password] [-k  pdi-path] [-d dwh-path] [-h host-name] [-P port]"
		exit 1;;
	esac
done

function mysqlexec {
        echo "now executing $1"
        mysql -u$USER -p$PW -h$HOST -P$PORT < $1 >>  $ROOT_DIR/logs/dwh_drop_databases.log

		ret_val=$?
        if [ $ret_val -ne 0 ];then
			echo $ret_val
			echo "Error - bailing out!"
			exit
        fi
}

DDL_ROOT_DIR=$ROOT_DIR/ddl/
mysqlexec $DDL_ROOT_DIR/db_drop.sql
