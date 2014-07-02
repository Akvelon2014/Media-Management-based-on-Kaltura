USE kalturadw;

INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('1','1','Video');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('1','2','Image');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('1','5','Audio');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('1','101','Generic Media');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('2','6','Mix');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('5','3','Manual Playlist');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('5','10','Dynamic Playlist');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('6','-1','Data');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('7','201','Flash live stream');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('7','202','Windows media live stream');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('7','203','Real media live stream');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('7','204','Quicktime live stream');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('10','11','Document');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('10','12','SWF Document');
INSERT INTO  dwh_dim_entry_type_display  ( entry_type_id ,  entry_media_type_id ,  display ) VALUES('10','13','PDF Document');


INSERT IGNORE INTO kalturadw.`dwh_dim_entry_type_display` (entry_type_id , entry_media_type_id) SELECT DISTINCT entry_type_id, entry_media_type_id FROM dwh_dim_entries;
