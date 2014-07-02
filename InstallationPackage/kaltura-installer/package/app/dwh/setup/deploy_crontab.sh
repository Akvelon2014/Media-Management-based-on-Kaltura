#!/bin/bash

CRONTAB_OS_DIR=/etc/cron.d/
ROOT_DIR=/opt/kaltura/dwh

while getopts "c:d:" o
do      case "$o" in
        d)  ROOT_DIR="$OPTARG";;
        c)  CRONTAB_OS_DIR="$OPTARG";;
        [?])    echo >&2 "Usage: $0 [-d dwh-path] [-c crontab_os_dir]"
                exit 1;;
        esac
done


if [ -h "$CRONTAB_OS_DIR/dwh_crontab" ]; then
	unlink "$CRONTAB_OS_DIR/dwh_crontab"
fi

ln -s $ROOT_DIR/crontab/dwh_crontab $CRONTAB_OS_DIR/dwh_crontab
