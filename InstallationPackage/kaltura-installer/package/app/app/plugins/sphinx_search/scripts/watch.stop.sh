#!/bin/bash
if [ -L $0 ];then
	REAL_SCRIPT=`readlink $0`
else
	REAL_SCRIPT=$0
fi
. `dirname $REAL_SCRIPT`/../../../configurations/system.ini

echo -n "Stopping Sphinx Watch Daemon: "
#Kills the watch.dameon
KP=$(pgrep watch.daemon.sh)
if [[ "X$KP" != "X" ]]
      then
		kill -9 $KP
fi
echo

echo -n "Stopping populateFromLog.php script: "
#kills the populate from log
KP=$(pgrep -f populateFromLog.php)
if [[ "X$KP" != "X" ]]
      then
		kill -9 $KP
fi
echo

echo -n "Stopping searchd service: "
#kills the search service
KP=$(pgrep searchd)
if [[ "X$KP" != "X" ]]
      then
		kill -9 $KP
fi
echo