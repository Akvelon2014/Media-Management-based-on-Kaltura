/*Find hourly locks that have been seized for more than 3 hours, and daily locks than have been seized for 10 hours*/
SELECT CONCAT(lock_name, ' seized for ', TIMEDIFF(NOW(), lock_time)) stat FROM kalturadw_ds.LOCKS 
WHERE TIME_TO_SEC(TIMEDIFF(NOW(), lock_time)) > 
IF (lock_name = 'daily_lock', 54000, IF(lock_name LIKE 'hourly_%', 21600, 5400))
AND lock_state = 1
