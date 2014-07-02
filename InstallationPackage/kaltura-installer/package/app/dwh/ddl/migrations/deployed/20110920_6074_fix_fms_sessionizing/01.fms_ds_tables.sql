ALTER TABLE kalturadw_ds.fms_incomplete_sessions
	ADD is_connected_ind int(11),
	ADD is_disconnected_ind int(11);

ALTER TABLE kalturadw_ds.fms_stale_sessions
        ADD is_connected_ind int(11),
        ADD is_dissconnected_ind int(11);
