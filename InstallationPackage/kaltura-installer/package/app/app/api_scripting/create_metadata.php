<?php
require_once('KalturaClient.php');
$userId = null;
$expiry = null;
$privileges = null;
$secret = '';
$type = KalturaSessionType::ADMIN;
$partnerId=100;
$config = new KalturaConfiguration($partnerId);
$config->serviceUrl = '';
$client = new KalturaClient($config);
$ks = $client->session->start($secret, $userId, $type, $partnerId, $expiry, $privileges);
$client->setKs($ks);
$metadataObjectType=1;
$partnerId=100;
$viewsData = null;

$pager=null;
$filter = new KalturaMetadataProfileFilter();
$filter->partnerIdEqual = 100;
$profile_name= 'Disclaimer Schema';
$metadataProfile->name = $profile_name;
$filter->nameEqual = $metadataProfile->name;
$results = $client->metadataProfile->listAction($filter, $pager);
if ($results->totalCount){
	echo "NOTICE: We alreadt have $profile_name. Exiting w/o adding.\n";
	return true;
}
$metadataProfile=new KalturaMetadataProfile(); 
$metadataProfile->name = $profile_name;

$metadataProfile->createMode = KalturaMetadataProfileCreateMode::KMC;
$metadataProfile->systemName = 'For KMS disclaimer module';
$metadataProfile->objectType = 1;
$metadataProfile->metadataObjectType = 1;

$xsdData = '<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema"> <xsd:element name="metadata"> <xsd:complexType> <xsd:sequence> <xsd:element id="md_1E3990C2-50D0-877E-B2D1-6665F6A3B6DB" name="Disclaimer" minOccurs="0" maxOccurs="1" type="textType"> <xsd:annotation> <xsd:documentation></xsd:documentation> <xsd:appinfo> <label>Disclaimer</label> <key>Disclaimer</key> <searchable>true</searchable> <timeControl>false</timeControl> <description></description> </xsd:appinfo> </xsd:annotation> </xsd:element> </xsd:sequence> </xsd:complexType> </xsd:element> <xsd:complexType name="textType"> <xsd:simpleContent> <xsd:extension base="xsd:string"/> </xsd:simpleContent> </xsd:complexType> <xsd:complexType name="dateType"> <xsd:simpleContent> <xsd:extension base="xsd:long"/> </xsd:simpleContent> </xsd:complexType> <xsd:complexType name="objectType"> <xsd:simpleContent> <xsd:extension base="xsd:string"/> </xsd:simpleContent> </xsd:complexType> <xsd:simpleType name="listType"> <xsd:restriction base="xsd:string"/> </xsd:simpleType> </xsd:schema>';
$results = $client->metadataProfile->add($metadataProfile, $xsdData, $viewsData);
?>
