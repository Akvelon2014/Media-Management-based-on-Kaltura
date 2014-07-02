<?php
if (count($argv)<1){
        echo "Usage: ".__FILE__ ." <entry_id>\n";
        exit (1);
}
require_once('KalturaClient.php');
$userId = null;
$expiry = null;
$privileges = null;
$secret = '';
$type = KalturaSessionType::ADMIN;
$partnerId=;
$config = new KalturaConfiguration($partnerId);
$service_url= '';
$config->serviceUrl = $service_url;
$client = new KalturaClient($config);
$ks = $client->session->start($secret, $userId, $type, $partnerId, $expiry, $privileges);
$client->setKs($ks);
$entryId = $argv[1];
$conversionProfileId = ;
$dynamicConversionAttributes=null;
$results = $client->media->convert($entryId, $conversionProfileId, $dynamicConversionAttributes);
?>
