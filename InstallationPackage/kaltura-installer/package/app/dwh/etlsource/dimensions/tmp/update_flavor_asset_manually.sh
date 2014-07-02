#!/bin/bash

/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=550 -param:ManualEndUpdateDaysInterval=499 > /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=500 -param:ManualEndUpdateDaysInterval=449 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=450 -param:ManualEndUpdateDaysInterval=399 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=400 -param:ManualEndUpdateDaysInterval=349 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=350 -param:ManualEndUpdateDaysInterval=299 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=300 -param:ManualEndUpdateDaysInterval=249 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=250 -param:ManualEndUpdateDaysInterval=199 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=200 -param:ManualEndUpdateDaysInterval=149 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=150 -param:ManualEndUpdateDaysInterval=99 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=100 -param:ManualEndUpdateDaysInterval=49 >> /tmp/flavor_asset_sync_log.log 2>&1
/usr/local/pentaho/pdi/kitchen.sh /file /opt/kaltura/dwh/etlsource/dimensions/tmp/update_flavor_asset_manually.kjb -param:ManualStartUpdateDaysInterval=50 -param:ManualEndUpdateDaysInterval=0 >> /tmp/flavor_asset_sync_log.log 2>&1
