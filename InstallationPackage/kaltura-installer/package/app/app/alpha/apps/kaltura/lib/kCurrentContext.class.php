<?php
/**
 * Will hold the current context of the API call / current running batch.
 * The inforamtion is static per call and can be used from anywhare in the code. 
 */
class kCurrentContext
{
	/**
	 * @var string
	 */
	public static $ks;
	
	/**
	 * @var ks
	 */
	public static $ks_object;
	
	/**
	 * @var string
	 */
	public static $ks_hash;
	
	/**
	 * @var int
	 */
	public static $partner_id;

	/**
	 * @var int
	 */
	public static $ks_partner_id;

	/**
	 * @var int
	 */
	public static $master_partner_id;
	
	/**
	 * @var string
	 */
	public static $uid;
	
	/**
	 * @var string
	 */
	public static $kuser_id;
	
	/**
	 * @var string
	 */
	public static $ks_uid;
	
	/**
	 * @var string
	 */
	public static $ks_kuser_id;

	/**
	 * @var string
	 */
	public static $ps_vesion;
	
	/**
	 * @var string
	 */
	public static $call_id;
	
	/**
	 * @var string
	 */
	public static $service;
	
	/**
	 * @var string
	 */
	public static $action;
	
	/**
	 * @var string
	 */
	public static $host;
	
	/**
	 * @var string
	 */
	public static $client_version;
	
	/**
	 * @var string
	 */
	public static $client_lang;
	
	/**
	 * @var string
	 */
	public static $user_ip;
	
	/**
	 * @var bool
	 */
	public static $is_admin_session;
	
	/**
	 * @var bool
	 */
	public static $ksPartnerUserInitialized = false;
	
	/**
	 * @var int
	 */
	public static $multiRequest_index = 1;
	
	public static function getEntryPoint()
	{
		if(self::$service && self::$action)
			return self::$service . '::' . self::$action;
			
		if(isset($_SERVER['SCRIPT_NAME']))
			return $_SERVER['SCRIPT_NAME'];
			
		if(isset($_SERVER['PHP_SELF']))
			return $_SERVER['PHP_SELF'];
			
		if(isset($_SERVER['SCRIPT_FILENAME']))
			return $_SERVER['SCRIPT_FILENAME'];
			
		return '';
	}
	
	public static function isApiV3Context()
	{		
		if (kCurrentContext::$ps_vesion == 'ps3') {
			return true;
		}
		
		return false;
	}
	
	public static function initPartnerByEntryId($entryId)
	{		
		KalturaCriterion::disableTags(array(KalturaCriterion::TAG_ENTITLEMENT_ENTRY, KalturaCriterion::TAG_WIDGET_SESSION));
		$entry = entryPeer::retrieveByPKNoFilter($entryId);
		KalturaCriterion::restoreTags(array(KalturaCriterion::TAG_ENTITLEMENT_ENTRY, KalturaCriterion::TAG_WIDGET_SESSION));
		
		if(!$entry)
			return null;
			
		kCurrentContext::$ks = null;
		kCurrentContext::$ks_object = null;
		kCurrentContext::$ks_hash = null;
		kCurrentContext::$ks_partner_id = $entry->getPartnerId();
		kCurrentContext::$ks_uid = null;
		kCurrentContext::$ks_kuser_id = 0;
		kCurrentContext::$master_partner_id = null;
		kCurrentContext::$partner_id = $entry->getPartnerId();
		kCurrentContext::$uid = null;
		kCurrentContext::$is_admin_session = false;
		kCurrentContext::$kuser_id = null;
		
		return $entry;
	}

	public static function initPartnerByAssetId($assetId)
	{
		KalturaCriterion::disableTags(array(KalturaCriterion::TAG_ENTITLEMENT_ENTRY, KalturaCriterion::TAG_WIDGET_SESSION));
		$asset = assetPeer::retrieveByIdNoFilter($assetId);
		KalturaCriterion::restoreTags(array(KalturaCriterion::TAG_ENTITLEMENT_ENTRY, KalturaCriterion::TAG_WIDGET_SESSION));

		if(!$asset)
			return null;

		kCurrentContext::$ks = null;
		kCurrentContext::$ks_object = null;
		kCurrentContext::$ks_hash = null;
		kCurrentContext::$ks_partner_id = $asset->getPartnerId();
		kCurrentContext::$ks_uid = null;
		kCurrentContext::$master_partner_id = null;
		kCurrentContext::$partner_id = $asset->getPartnerId();
		kCurrentContext::$uid = null;
		kCurrentContext::$is_admin_session = false;
		kCurrentContext::$kuser_id = null;

		return $asset;
	}
	
	public static function initKsPartnerUser($ksString, $requestedPartnerId = null, $requestedPuserId = null)
	{		
		if (!$ksString)
		{
			kCurrentContext::$ks = null;
			kCurrentContext::$ks_object = null;
			kCurrentContext::$ks_hash = null;
			kCurrentContext::$ks_partner_id = null;
			kCurrentContext::$ks_uid = null;
			kCurrentContext::$ks_kuser_id = 0;
			kCurrentContext::$master_partner_id = null;
			kCurrentContext::$partner_id = $requestedPartnerId;
			kCurrentContext::$uid = $requestedPuserId;
			kCurrentContext::$is_admin_session = false;
			kCurrentContext::$kuser_id = null;
		}
		else
		{
			try { $ksObj = kSessionUtils::crackKs ( $ksString ); }
			catch(Exception $ex)
			{
				if (strpos($ex->getMessage(), "INVALID_STR") !== null)
					throw new kCoreException($ex->getMessage(), kCoreException::INVALID_KS, $ksString);
				else 
					throw $ex;
			}
		
			kCurrentContext::$ks = $ksString;
			kCurrentContext::$ks_object = $ksObj;
			kCurrentContext::$ks_hash = $ksObj->getHash();
			kCurrentContext::$ks_partner_id = $ksObj->partner_id;
			kCurrentContext::$ks_uid = $ksObj->user;
			kCurrentContext::$ks_kuser_id = 0;
			kCurrentContext::$master_partner_id = $ksObj->master_partner_id ? $ksObj->master_partner_id : kCurrentContext::$ks_partner_id;
			kCurrentContext::$is_admin_session = $ksObj->isAdmin();
			kCurrentContext::$partner_id = $requestedPartnerId;
			kCurrentContext::$uid = $requestedPuserId;
			kCurrentContext::$kuser_id = null;
				
			$ksKuser = kuserPeer::getKuserByPartnerAndUid(kCurrentContext::$ks_partner_id, kCurrentContext::$ks_uid, true);
			if($ksKuser)
				kCurrentContext::$ks_kuser_id = $ksKuser->getId();
		}

		// set partner ID for logger
		if (kCurrentContext::$partner_id) {
			$GLOBALS["partnerId"] = kCurrentContext::$partner_id;
		}
		else if (kCurrentContext::$ks_partner_id) {
			$GLOBALS["partnerId"] = kCurrentContext::$ks_partner_id;
		}
		
		self::$ksPartnerUserInitialized = true;
	}
}
