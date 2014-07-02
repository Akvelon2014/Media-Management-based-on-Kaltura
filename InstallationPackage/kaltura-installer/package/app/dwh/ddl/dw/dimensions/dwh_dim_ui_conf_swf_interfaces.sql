USE kalturadw;

DROP TABLE IF EXISTS kalturadw.dwh_dim_ui_conf_swf_interfaces;
CREATE TABLE kalturadw.dwh_dim_ui_conf_swf_interfaces (
	id INT NOT NULL AUTO_INCREMENT,
	swf_file varchar(255) NOT NULL,
	tags_search_string varchar(255) NOT NULL DEFAULT '',
	display_name varchar(255),
	PRIMARY KEY (id),
	UNIQUE KEY (swf_file,tags_search_string)) ENGINE=MYISAM CHARSET=latin1;

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
