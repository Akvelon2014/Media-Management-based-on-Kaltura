#!/bin/bash
if [ -L $0 ];then
	REAL_SCRIPT=`readlink $0`
else
	REAL_SCRIPT=$0
fi
. `dirname $REAL_SCRIPT`/../../configurations/system.ini

# $1 is the action (start|stop|restart)
# $2 is the batch name (optional) 
cd $(dirname $0)
source kaltura_env.sh
$PHP_PATH runBatch.php $1 $2
