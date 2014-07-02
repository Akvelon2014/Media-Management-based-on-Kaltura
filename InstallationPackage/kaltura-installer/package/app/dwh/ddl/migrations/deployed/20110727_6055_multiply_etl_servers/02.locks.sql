ALTER TABLE kalturadw_ds.LOCKS CHANGE lock_id lock_id INT(11) AUTO_INCREMENT;
DELETE FROM kalturadw_ds.locks where lock_name = 'hourly_lock';
