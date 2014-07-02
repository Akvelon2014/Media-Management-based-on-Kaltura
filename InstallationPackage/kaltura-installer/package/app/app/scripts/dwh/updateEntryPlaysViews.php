<?php

require_once (dirname(__FILE__).'/../bootstrap.php');

$f = fopen("php://stdin", "r");
$count = 0;
$sphinxMgr = new kSphinxSearchManager();
$dbConf = kConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();
$connection = Propel::getConnection();
while($s = trim(fgets($f))){
        $sep = strpos($s, "\t") ? "\t" : " ";
        list($entryId, $plays, $views) = explode($sep, $s);
        myPartnerUtils::resetAllFilters();
        entryPeer::setDefaultCriteriaFilter();
        $entry = entryPeer::retrieveByPK ( $entryId);
        if (is_null ( $entry )) {
                KalturaLog::err ('Couldn\'t find entry [' . $entryId . ']' );
                continue;
        }
        if ($entry->getViews() != $views || $entry->getPlays() != $plays){
                $entry->setViews ( $views );
                $entry->setPlays ( $plays );
                KalturaLog::debug ( 'Successfully saved entry [' . $entryId . ']' );


		try {
			// update entry without setting the updated at
			$updateSql = "UPDATE entry set views='$views',plays='$plays' WHERE id='$entryId'";
			$stmt = $connection->prepare($updateSql);
			$stmt->execute();
			$affectedRows = $stmt->rowCount();
			KalturaLog::log("AffectedRows: ". $affectedRows);
			// update sphinx log directly
			$sql = $sphinxMgr->getSphinxSaveSql($entry, false);
			$sphinxLog = new SphinxLog();
			$sphinxLog->setEntryId($entryId);
			$sphinxLog->setPartnerId($entry->getPartnerId());
			$sphinxLog->setSql($sql);
			$sphinxLog->save(myDbHelper::getConnection(myDbHelper::DB_HELPER_CONN_SPHINX_LOG));

		} catch (Exception $e) {
			KalturaLog::log($e->getMessage(), Propel::LOG_ERR);

		}
        }
        $count++;
	if ($count % 500 === 0){
	    entryPeer::clearInstancePool ();
	}
}
?>
