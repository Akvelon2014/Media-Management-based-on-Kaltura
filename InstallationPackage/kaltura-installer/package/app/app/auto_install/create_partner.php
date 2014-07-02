<?php
if (count($argv)<4){
    echo __FILE__ . ' <admin_secret> <partner email> <partner passwd> '."\n";
    exit (1);
}
require_once('create_session.php');
$admin_partner_id = -2;
$config = new KalturaConfiguration($admin_partner_id);
$config->serviceUrl = 'http://localhost';
$client = new KalturaClient($config);
$expiry = null;
$privileges = null;
$email=$argv[2];
$name='Kaltura test partner II';
$cmsPassword=$argv[3];
$partner_id=100;
$secret = $argv[1];
try {
        $results = $client->user->loginByLoginId($email,$cmsPassword,$partner_id,$expiry, $privileges);
}

catch (Exception $e) {
    if ($e->getMessage()==="User was not found" || $e->getMessage()==="Unknown partner_id [$partner_id]"){
        $userId = null;
        $type = KalturaSessionType::ADMIN;
        $ks = $client->session->start($secret, $userId, $type, $admin_partner_id, $expiry, $privileges);
        $client->setKs($ks);
	//$client=generate_ks($config->serviceUrl,$partner_id,$secret,$type=KalturaSessionType::ADMIN,$userId=null,$expiry = null,$privileges = null);
        $partner = new KalturaPartner();
        $partner->website="http://www.kaltura.com";
        $partner->adminName=$name;
        $partner->name=$name;
        $partner->description=" "; //cannot be empty or null
        $partner->adminEmail=$email;
        $results = $client->partner->register($partner, $cmsPassword);
	echo($results->id);
    }
}
?>
