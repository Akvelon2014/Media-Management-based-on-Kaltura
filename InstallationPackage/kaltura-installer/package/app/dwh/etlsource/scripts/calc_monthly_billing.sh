#!/bin/bash
USER="etl"
PW="etl"
HOST=localhost
PORT=3306

MONTH_ID=`date --date "-1 month" +%Y%m`

while getopts "u:p::h:P:m:" o
do	case "$o" in
	u)	USER="$OPTARG";;
	p)	PW="$OPTARG";;
	h)	HOST="$OPTARG";;
	P)	PORT="$OPTARG";;
	m)	MONTH_ID="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-u username] [-p password] [-h host-name] [-P port] -m [month_id]"
		exit 1;;
	esac
done
mysql -u$USER -p$PW -h$HOST -P$PORT -e "call kalturadw.calc_monthly_billing($MONTH_ID)"
