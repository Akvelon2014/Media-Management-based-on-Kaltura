<?php
require_once(dirname(__FILE__).'/../../bootstrap.php');

class migrationEntry extends entry
{
	public function setId($v)
	{
		if(!$this->getId())
			parent::setId($v);
	}
}

$dbPlaylist = new migrationEntry();
$dbPlaylist->setId('_KDP_RE_PL');
$dbPlaylist->setPartnerId(0);
$dbPlaylist->setStatus ( entryStatus::READY );
$dbPlaylist->setKshowId ( null );
$dbPlaylist->setType ( entryType::PLAYLIST );
$dbPlaylist->setMediaType(entry::ENTRY_MEDIA_TYPE_XML);
$dbPlaylist->setDataContent('<?xml version="1.0"?><playlist><total_results>12</total_results><filters><filter><in_status>2,1</in_status><in_type>1,2,7</in_type><in_moderation_status>2,5,6,1</in_moderation_status><order_by>-plays</order_by><limit>12</limit></filter></filters></playlist>');
$dbPlaylist->setDisplayInSearch ( 2 );
$dbPlaylist->save();

echo 'done';



