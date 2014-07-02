/*Find if no new files have been processed by the system for a certain process*/
SELECT 'No event files processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id IN (1,3)
AND f.cycle_id = c.cycle_id
AND (c.process_id = 1 AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 24 HOUR)) a
WHERE amount = 0
UNION
SELECT 'No Akamai event files processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 3
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 12 HOUR) a
WHERE amount = 0
UNION
SELECT 'No FMS file processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 2
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 36 HOUR) a
WHERE amount = 0
UNION
SELECT 'No Akamai BW file processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 4
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 12 HOUR) a
WHERE amount = 0
UNION
SELECT 'No LimeLight BW file processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 5
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 36 HOUR) a
WHERE amount = 0
UNION
SELECT 'No Level3 BW file processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 6
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 36 HOUR) a
WHERE amount = 0
UNION
SELECT 'No RTMP AKAMAI file processed yesterday!' stat
FROM (
SELECT COUNT(*) amount
FROM kalturadw_ds.files f, kalturadw_ds.cycles c WHERE c.process_id = 7
AND f.cycle_id = c.cycle_id
AND c.STATUS='DONE' AND f.insert_time > NOW()-INTERVAL 12 HOUR) a
WHERE amount = 0