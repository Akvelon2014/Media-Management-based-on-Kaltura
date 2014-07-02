#!/bin/bash
if [ -L $0 ];then
	REAL_SCRIPT=`readlink $0`
else
	REAL_SCRIPT=$0
fi
. `dirname $REAL_SCRIPT`/../../../configurations/system.ini

# Store PID of script:
# Match script without arguments
LCK_FILE=/tmp/`basename $0`.lck
LOG_FILE=/opt/kaltura/log/clear_cache.log

if [ -f "${LCK_FILE}" ]; then

  # The file exists so read the PID
  # to see if it is still running
  MYPID=`head -n 1 $LCK_FILE`

  if [ -n "`ps -p ${MYPID} | grep ${MYPID}`" ]; then
    echo `basename $0` is already running [$MYPID].
    exit
  fi
fi

# Echo current PID into lock file
echo $$ > $LCK_FILE




echo "`date +%s` start clean v3 `date`" >> $LOG_FILE 2>&1
#nice -n 19 find $RESPONSE_CACHE_DIR/cache_v3-600 -type f -mmin +1440 -name "cache*" -delete
/usr/bin/ionice -c3 find $RESPONSE_CACHE_DIR/cache_v3-600 -type f -mmin +1440 -name "cache*" -delete
echo "`date +%s` end clean v3 `date`" >> $LOG_FILE 2>&1
#nice -n 19 find $RESPONSE_CACHE_DIR/cache_v2 -type f -mmin +1440 -name "cache*" -delete
/usr/bin/ionice -c3 find $RESPONSE_CACHE_DIR/cache_v2 -type f -mmin +1440 -name "cache*" -delete
echo "`date +%s` end clean v2 `date`" >> $LOG_FILE 2>&1
#nice -n 19 find /tmp -maxdepth 0 -type f -mmin +1440 -name "php*" -delete
/usr/bin/ionice -c3 find /tmp -maxdepth 1 -type f -mmin +1440 -name "php*" -delete
echo "`date +%s` end clean php `date`" >> $LOG_FILE 2>&1
