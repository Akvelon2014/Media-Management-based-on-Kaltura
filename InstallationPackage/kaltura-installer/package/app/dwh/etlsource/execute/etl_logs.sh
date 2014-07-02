#!/bin/bash
ROOT_DIR=/home/etl/
WHEN=$(date +%Y%m%d)
MAILUSERS="dummy@example.com"
LOG_DIR="/opt/kaltura/log/"
APP_DIR="/opt/kaltura/app"

while getopts "a:t:p:l:m:" o
do	case "$o" in
    a)  APP_DIR="$OPTARG";;
    t)  WHEN="$OPTARG";;
    p)	ROOT_DIR="$OPTARG";;
    l)  LOG_DIR="$OPTARG";;
    m)  MAILUSERS="$OPTARG";;
	[?])	echo >&2 "Usage: $0 [-t date] [-p dwh-path] [-l log-path] [-a app-path] [-m mail]"
		exit 1;;
	esac
done

ETLHOME=${ROOT_DIR}
ETLOGS=${ETLHOME}/logs
JOBLOG=${ETLOGS}/etl_job-$WHEN.log

if [ -s ${LOG_DIR}/kaltura_apache_access.log-$WHEN.gz ] ; then
    echo -e "\n"
    echo "-----------------------------" >>$JOBLOG
    echo "kaltura access log is processed" >>$JOBLOG
    echo "-----------------------------" >>$JOBLOG
    zcat ${LOG_DIR}/kaltura_apache_access.log-$WHEN.gz |php ${APP_DIR}/alpha/scripts/create_event_log_from_apache_access_log.php  2>>$JOBLOG > ${ETLHOME}/events/_events_log_combined_kaltura-${WHEN}
    mv ${ETLHOME}/events/_events_log_combined_kaltura-${WHEN} ${ETLHOME}/events/events_log_combined_kaltura-${WHEN}
else
    echo -e "\n"
    echo "-----------------------------" >>$JOBLOG
    echo "kaltura access log couldnt be  processed" >>$JOBLOG
    echo "-----------------------------" >>$JOBLOG
    echo "kaltura access log couldnt be  processed" | mail -s "etljob file failed : `date`" ${MAILUSERS}
fi
