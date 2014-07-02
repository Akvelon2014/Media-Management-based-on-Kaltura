#!/bin/bash

USER="etl"
PW="etl"
KITCHEN=/usr/local/pentaho/pdi
ROOT_DIR=/opt/kaltura/dwh
HOST=localhost
PORT=3306

while getopts "u:p:k:d:h:P:" o
do      case "$o" in
        u)      USER="$OPTARG";;
        p)      PW="$OPTARG";;
    	k)  	KITCHEN="$OPTARG";;
    	d)  	ROOT_DIR="$OPTARG";;
        h)      HOST="$OPTARG";;
        P)      PORT="$OPTARG";;
        [?])    echo >&2 "Usage: $0 [-u username] [-p password] [-k  pdi-path] [-d dwh-path] [-h host-name] [-P port]"
                exit 1;;
        esac
done

SETUP_ROOT_DIR=$ROOT_DIR/setup
ETL_ROOT_DIR=$ROOT_DIR/etlsource
INSTALLATION_LOG=$SETUP_ROOT_DIR/installation_log.log

# Create the DWH
$SETUP_ROOT_DIR/dwh_ddl_install.sh -u$USER -p$PW -k$KITCHEN -d$ROOT_DIR -h$HOST -P$PORT | tee -a $INSTALLATION_LOG

# Populate time dimension
export KETTLE_HOME=$ROOT_DIR

# Check that the command didn't fail
ret_val=$?
if [ $ret_val -ne 0 ];then
	echo $ret_val
       	echo "Error - bailing out!"
       	exit
fi

# Note that setup skips svn update and registers all files from migrations as if they were run (the changes are already incorporated in ddl).
$SETUP_ROOT_DIR/update.sh -k $KITCHEN -d $ROOT_DIR -u $USER -p $PW -h $HOST -P $PORT -r 1 -v 0

OS_CRONTAB_DIR=/etc/cron.d

#cp crontab file
if [ "$(whoami)" != 'root' ]; then
        echo "Installtion finished." 
		echo "You must have root priveleges to configure cron."
        su -c "/bin/bash $ROOT_DIR/setup/deploy_crontab.sh -d $ROOT_DIR -c $OS_CRONTAB_DIR" root
        if [ ! $? == "0" ]; then
		echo "Not root. exiting"
		echo "To retry configuring cron - Execute as root: /bin/bash $ROOT_DIR/setup/deploy_crontab.sh -d $ROOT_DIR -c $OS_CRONTAB_DIR"
	else
		echo "Deployed cron successfully"
        fi
else
	/bin/bash $ROOT_DIR/setup/deploy_crontab.sh -d $ROOT_DIR -c $OS_CRONTAB_DIR
fi
