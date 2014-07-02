#!/bin/bash
if [ -L $0 ];then
	REAL_SCRIPT=`readlink $0`
else
	REAL_SCRIPT=$0
fi
. `dirname $REAL_SCRIPT`/../configurations/system.ini

KP=$(pgrep -P 1 -f KGenericBatchMgr.class.php)
MAINT=$BASE_DIR/maintenance
if [ "X$KP" = "X" ]
   then
      sleep 10
      KP=$(pgrep -P 1 -f KGenericBatchMgr.class.php)
      if [[ "X$KP" = "X" && ! -f $MAINT ]]
         then
            echo "KGenericBatchMgr.class.php `hostname` was restarted"
            $APP_DIR/scripts/serviceBatchMgr.sh restart
         fi
fi
