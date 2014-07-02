<?php
require_once('create_session.php');
if (count($argv)<4){
    echo 'Usage:' .__FILE__ .' <partner_id> <service_url> <secret> <uploader>'."\n";
    exit (1);
}
// relevant account user
$partnerId = $argv[1];
$config = new KalturaConfiguration($partnerId);
// URL of the API machine
$config->serviceUrl = $argv[2];
// sha1 secret
$secret = $argv[3];
$uploadedBy = $argv[4];
$userId = null;
$expiry = null;
$privileges = null;
//csv file to use
$csvFileData = dirname(__FILE__).'/upload_falcon_categories.csv';
// type here is CSV but can also work with XML
$bulkUploadType = 'bulkUploadCsv.CSV' ;
$client=generate_ks($config->serviceUrl,$partnerId,$secret,$type=KalturaSessionType::ADMIN,$userId=null,$expiry = null,$privileges = null);
// conversion profile to be used
$conversionProfileId = $client->conversionProfile->getDefault()->id;
$results = $client-> bulkUpload ->add($conversionProfileId, $csvFileData, $bulkUploadType, $uploadedBy);
//var_dump($results);
?>
