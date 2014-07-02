<?php
require_once('KalturaClient.php');
require_once('create_session.php');
$userId = null;
$expiry = null;
$privileges = null;
$secret = '';
$type = KalturaSessionType::ADMIN;
$partnerId=0;
//$config = new KalturaConfiguration($partnerId);
$service_url= '';
//$config->serviceUrl = $service_url;  
//$client = new KalturaClient($config);
//$ks = $client->session->start($secret, $userId, $type, $partnerId, $expiry, $privileges);
$client=generate_ks($service_url,$partnerId,$secret,$type=KalturaSessionType::ADMIN,$userId=null,$expiry = null,$privileges = null);
$flavor_name='VCDN Low Bitrate';
$filter = new KalturaFlavorParamsFilter();
$filter->systemNameEqual = $flavor_name;
$filter->formatEqual = KalturaContainerFormat::MP4;
$results = $client->flavorParams->listAction($filter, null);
if(!count($results->objects)){
	$flavorParams = new KalturaFlavorParams();
	$flavorParams->name = $flavor_name ;
	$flavorParams->systemName = $flavor_name;
	$flavorParams->description = 'SAP '.$flavor_name. ' stream';
	$flavorParams->tags = 'web,mbr,iphonenew,ipadnew';
	$flavorParams->videoCodec = KalturaVideoCodec::H264B;
	$flavorParams->videoBitrate = 200;
	$flavorParams->audioCodec = KalturaAudioCodec::AAC;
	$flavorParams->audioBitrate = 64;
	$flavorParams->width = 320;
	$flavorParams->height = 0;
	$flavorParams->isSystemDefault = true;
	$flavorParams->conversionEngines = '2,99,3';
	$flavorParams->conversionEnginesExtraParams = "-flags +loop+mv4 -cmp 256 -partitions +parti4x4+partp8x8+partb8x8 -trellis 1 -refs 1 -me_range 16 -keyint_min 20 -sc_threshold 40 -i_qfactor 0.71 -bt 100k -maxrate 400k -bufsize 1200k -rc_eq 'blurCplx^(1-qComp)' -level 30 -async 2 -vsync 1 -threads 4 | -flags +loop+mv4 -cmp 256 -partitions +parti4x4+partp8x8+partb8x8 -trellis 1 -refs 1 -me_range 16 -keyint_min 20 -sc_threshold 40 -i_qfactor 0.71 -bt 100k -maxrate 400k -bufsize 1200k -rc_eq 'blurCplx^(1-qComp)' -level 30 -async 2 -vsync 1 | -x264encopts qcomp=0.6:qpmin=10:qpmax=50:qpstep=4:frameref=1:bframes=0:threads=auto:level_idc=30:global_header:partitions=i4x4+p8x8+b8x8:trellis=1:me_range=16:keyint_min=20:scenecut=40:ipratio=0.71:ratetol=20:vbv-maxrate=400:vbv-bufsize=1200";
	$flavorParams->twoPass = 'true';
	$flavorParams->format = KalturaContainerFormat::MP4;

	$results = $client->flavorParams->add($flavorParams);
	$low_id=$results->id;
}else{	
	$low_id=$results->objects[0]->id;
	error_log("$flavor_name with ID $low_id already exists. Skipping.\n",3,'/tmp/flavor_errors.log');
}
error_log("post creation of $flavor_name\n", 3, "/tmp/flavor_errors.log");

$flavor_name='VCDN High Bitrate';
$filter = new KalturaFlavorParamsFilter();
$filter->systemNameEqual = $flavor_name;
$filter->formatEqual = KalturaContainerFormat::MP4;
$results = $client->flavorParams->listAction($filter, null);
if(!count($results->objects)){
	$flavorParams = new KalturaFlavorParams();
	$flavorParams->name = $flavor_name;
	$flavorParams->systemName = $flavor_name; 
	$flavorParams->description = 'SAP '.$flavor_name. ' stream';
	$flavorParams->tags = 'web,mbr,iphonenew,ipadnew';
	$flavorParams->videoCodec = KalturaVideoCodec::H264B;
	$flavorParams->videoBitrate = 600;
	$flavorParams->audioCodec = KalturaAudioCodec::AAC;
	$flavorParams->audioBitrate = 64;
	$flavorParams->width = 0;
	$flavorParams->height = 360;
	$flavorParams->isSystemDefault = true;
	$flavorParams->conversionEngines = '2,99,3';
	$flavorParams->conversionEnginesExtraParams = "-flags +loop+mv4 -cmp 256 -partitions +parti4x4+partp8x8+partb8x8 -trellis 1 -refs 1 -me_range 16 -keyint_min 20 -sc_threshold 40 -i_qfactor 0.71 -bt 200k -maxrate 600k -bufsize 1200k -rc_eq 'blurCplx^(1-qComp)' -level 30 -async 2 -vsync 1 -threads 4 | -flags +loop+mv4 -cmp 256 -partitions +parti4x4+partp8x8+partb8x8 -trellis 1 -refs 1 -me_range 16 -keyint_min 20 -sc_threshold 40 -i_qfactor 0.71 -bt 200k -maxrate 600k -bufsize 1200k -rc_eq 'blurCplx^(1-qComp)' -level 30 -async 2 -vsync 1 | -x264encopts qcomp=0.6:qpmin=10:qpmax=50:qpstep=4:frameref=1:bframes=0:threads=auto:level_idc=30:global_header:partitions=i4x4+p8x8+b8x8:trellis=1:me_range=16:keyint_min=20:scenecut=40:ipratio=0.71:ratetol=20:vbv-maxrate=600:vbv-bufsize=1200";
	$flavorParams->twoPass = 'true';
	$flavorParams->format = KalturaContainerFormat::MP4;
	$results = $client->flavorParams->add($flavorParams);
	$high_id=$results->id;
}else{	
	$high_id=$results->objects[0]->id;
	error_log("$flavor_name with ID $high_id already exists. Skipping.\n",3,'/tmp/flavor_errors.log');
}
error_log("post creation of $flavor_name\n", 3, "/tmp/flavor_errors.log\n");

// the secret of 104
$partnerId=104;
$secret = '';
$type = KalturaSessionType::ADMIN;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl =  $service_url;
$client = new KalturaClient($config);
$ks = $client->session->start($secret, $userId, $type, $partnerId, $expiry, $privileges);
$client->setKs($ks);
$filter = new KalturaConversionProfileFilter();
$filter->statusEqual = KalturaConversionProfileStatus::ENABLED;
$filter->nameEqual = 'SAP';
$filter->systemNameEqual = 'SAP';

$results = $client->conversionProfile->listAction($filter, null);
$def_profile=$client->conversionProfile->getDefault();
foreach ($results->objects as $result){
	if ($result->id !==$def_profile->id){
		echo "Will del $result->id\n";
		$client->conversionProfile->delete($result->id);
	}
}
$conversionProfile = new KalturaConversionProfile();
$conversionProfile->status = KalturaConversionProfileStatus::ENABLED;
$conversionProfile->name = 'SAP';
$conversionProfile->systemName = 'SAP';
$conversionProfile->tags = '';
$conversionProfile->description = 'SAP\'s default';
$conversionProfile->flavorParamsIds = "0,$low_id,$high_id";
error_log("Setting {$conversionProfile->name} as default.\n", 3, "/tmp/flavor_errors.log");
$conversionProfile->isDefault = true;
$conversionProfile->storageProfileId = 4;
$client->conversionProfile->add($conversionProfile);
echo "Will del $def_profile->id\n";
$client->conversionProfile->delete($def_profile->id);

?>


