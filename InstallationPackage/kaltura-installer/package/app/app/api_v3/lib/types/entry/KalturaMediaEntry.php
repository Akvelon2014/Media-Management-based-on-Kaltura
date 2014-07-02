<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaMediaEntry extends KalturaPlayableEntry
{
	/**
	 * The media type of the entry
	 * 
	 * @var KalturaMediaType
	 * @insertonly
	 * @filter eq,in,order
	 */
	public $mediaType;
	
	/**
	 * Override the default conversion quality  
	 * 
	 * @var string
	 * @insertonly
	 * @deprecated use conversionProfileId instead
	 */
	public $conversionQuality;

	/**
	 * The source type of the entry 
	 *
	 * @var KalturaSourceType
	 * @insertonly
	 */
	public $sourceType;
	
	/**
	 * The search provider type used to import this entry
	 *
	 * @var KalturaSearchProviderType
	 * @insertonly
	 */
	public $searchProviderType;

	/**
	 * The ID of the media in the importing site
	 *
	 * @var string
	 * @insertonly
	 */
	public $searchProviderId;

	/**
	 * The user name used for credits
	 *
	 * @var string
	 */
	public $creditUserName;

	/**
	 * The URL for credits
	 *
	 * @var string
	 */
	public $creditUrl;

	/**
	 * The media date extracted from EXIF data (For images) as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 * @filter gte,lte
	 */
	public $mediaDate;

	/**
	 * The URL used for playback. This is not the download URL.
	 *
	 * @var string
	 * @readonly
	 */
	public $dataUrl;
	
	/**
	 * Comma separated flavor params ids that exists for this media entry
	 * 
	 * @var string
	 * @readonly
	 * @filter matchor,matchand
	 */
	public $flavorParamsIds;
	
	private static $map_between_objects = array
	(
		"mediaType",
		"conversionQuality",
		//"sourceType", // see special logic for this field below
		//"searchProviderType", // see special logic for this field below
		"searchProviderId" => "sourceId",
		"creditUserName" => "credit",
		"creditUrl" => "siteUrl",
	 	"partnerId",
	 	"mediaDate",
	 	"dataUrl", 
		"flavorParamsIds",
	);

	public function __construct()
	{
		$this->type = KalturaEntryType::MEDIA_CLIP;
	}
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function fromObject($entry)
	{
		parent::fromObject($entry);

		$this->mediaDate = $entry->getMediaDate(null);
		
		$reflect = KalturaTypeReflectorCacher::get('KalturaSourceType');
		$constants = $reflect->getConstantsValues();
		$sourceApi = kPluginableEnumsManager::coreToApi('EntrySourceType', $entry->getSource());
		if(!in_array($sourceApi, $constants) || $sourceApi == EntrySourceType::SEARCH_PROVIDER)
		{
			$this->sourceType = KalturaSourceType::SEARCH_PROVIDER;
			$this->searchProviderType = $sourceApi;
		}
		else
		{
			$this->sourceType = $sourceApi;
			$this->searchProviderType = null;
		}
	}
	
	public function toObject($entry = null, $a = array())
	{
		if(is_null($entry))
		{
			KalturaLog::debug("Creating new entry");
			$entry = new entry();
		}
		
		KalturaLog::debug("type: {$this->mediaType} , duration: {$this->msDuration}");
		
		$entry = parent::toObject($entry);
		
		if (($entry->mediaType == KalturaMediaType::IMAGE && $entry->msDuration) ||
			($this->mediaType == KalturaMediaType::IMAGE && $this->msDuration))
		{
			throw new KalturaAPIException(KalturaErrors::PROPERTY_VALIDATION_NOT_UPDATABLE, "msDuration");
		}
		
		if ($this->sourceType === KalturaSourceType::SEARCH_PROVIDER)
		{
			$entry->setSource($this->searchProviderType);
		}
		else
		{
			$entry->setSource(kPluginableEnumsManager::apiToCore('EntrySourceType', $this->sourceType));
		}
		
		return $entry;
	}
}