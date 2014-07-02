<?php
/**
 * @package Admin
 * @subpackage Partners
 */
class Form_Partner_StorageHelper
{
	public static function addProtocolsToForm(Zend_Form $form)
	{
		$arr = array(
			Kaltura_Client_Enum_StorageProfileProtocol::FTP => 'FTP',
			Kaltura_Client_Enum_StorageProfileProtocol::SFTP => 'SFTP',
			Kaltura_Client_Enum_StorageProfileProtocol::SCP => 'SCP',
			Kaltura_Client_Enum_StorageProfileProtocol::S3 => 'Amazon S3'
		);
		$form->getElement('protocol')->setMultiOptions($arr);
	}
	
	public static function addPathManagersToForm(Zend_Form $form)
	{
		$arr = array(
			'kPathManager' => 'Kaltura Path',
			'kExternalPathManager' => 'External Path',
		    'kXslPathManager' => 'XSL Path',
		);
		$form->getElement('pathManagerClass')->setMultiOptions($arr);
	}
	
	public static function addUrlManagersToForm(Zend_Form $form)
	{
		$arr = array(
			'' => 'Kaltura Delivery URL Format',
			'kLocalPathUrlManager' => 'QA FMS Server',
			'kLimeLightUrlManager' => 'Lime Light CDN',
			'kAkamaiUrlManager' => 'Akamai CDN',
			'kLevel3UrlManager' => 'Level 3 CDN',
		    'kMirrorImageUrlManager' => 'Mirror Image CDN',
		);
		$form->getElement('urlManagerClass')->setMultiOptions($arr);
	}
	
	public static function addTriggersToForm(Zend_Form $form)
	{
		$arr = array(
			3 => 'Flavor Ready',
			2 => 'Moderation Approved',
		);
		$form->getElement('trigger')->setMultiOptions($arr);
	}

	public static function addFlavorParamsToForm(Zend_Form $form, $flavorParams)
	{
//		$arr = array();
//		$arr[-1] = "N/A";
//		foreach($packages as $package)
//		{
//			$arr[$package->id] = $package->name;
//		}
//		$form->getElement('partner_package')->setMultiOptions($arr);
	}
}