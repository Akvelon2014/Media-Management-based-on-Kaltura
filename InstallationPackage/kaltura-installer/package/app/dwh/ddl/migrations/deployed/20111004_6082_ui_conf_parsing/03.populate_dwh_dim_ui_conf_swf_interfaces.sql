USE kalturadw;

TRUNCATE TABLE dwh_dim_ui_conf_swf_interfaces;
INSERT INTO dwh_dim_ui_conf_swf_interfaces (id, swf_file, tags_search_string, display_name) VALUES (-1,'','','Unknown');

INSERT INTO dwh_dim_ui_conf_swf_interfaces (swf_file, tags_search_string, display_name) 
VALUES ('ContributionWizard.swf', '', 'KCW (Kaltura Contribution Wizard)'),
 ('KUpload.swf', '', 'KSU (Kaltura Simple Uploader)'),
 ('KRecord.swf', '', 'KRecord (Webcam recorder)'),
 ('simpleeditor.swf', '', 'KSE (Kaltura Simple Editor)'),
 ('KalturaAdvancedVideoEditor.swf', '', 'KAE (Kaltura Advanced Editor)'),
 ('KClip.swf', '', 'Clipping tool'),
 ('kdp.swf', 'Playlist', 'KDP – single video player'),
 ('kdp.swf', 'Player', 'KDP – playlist player'),
 ('kdp3.swf', 'Playlist', 'KDP3 – single video player'),
 ('kdp3.swf', 'Player', 'KDP3 – playlist player');

UPDATE kalturadw.dwh_dim_ui_conf ui_conf LEFT OUTER JOIN kalturadw.dwh_dim_ui_conf_swf_interfaces swf_interfaces
ON (SUBSTRING_INDEX(ui_conf.swf_url, '/', -1) = swf_interfaces.swf_file AND tags LIKE CONCAT('%',tags_search_string,'%'))
SET ui_conf.VERSION = SUBSTRING_INDEX(SUBSTRING_INDEX(swf_url, '/', -2),'/',1),
ui_conf.swf_interface_id = IFNULL(swf_interfaces.id, -1);
