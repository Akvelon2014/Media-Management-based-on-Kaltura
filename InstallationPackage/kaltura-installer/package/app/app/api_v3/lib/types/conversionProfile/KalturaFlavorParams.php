<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaFlavorParams extends KalturaAssetParams 
{
	/**
	 * The video codec of the Flavor Params
	 * 
	 * @var KalturaVideoCodec
	 */
	public $videoCodec;
	
	/**
	 * The video bitrate (in KBits) of the Flavor Params
	 * 
	 * @var int
	 */
	public $videoBitrate;
	
	/**
	 * The audio codec of the Flavor Params
	 * 
	 * @var KalturaAudioCodec
	 */
	public $audioCodec;
	
	/**
	 * The audio bitrate (in KBits) of the Flavor Params
	 * 
	 * @var int
	 */
	public $audioBitrate;
	
	/**
	 * The number of audio channels for "downmixing"
	 * 
	 * @var int
	 */
	public $audioChannels;
	
	/**
	 * The audio sample rate of the Flavor Params
	 * 
	 * @var int
	 */
	public $audioSampleRate;
	
	/**
	 * The desired width of the Flavor Params
	 * 
	 * @var int
	 */
	public $width;
	
	/**
	 * The desired height of the Flavor Params
	 * 
	 * @var int
	 */
	public $height;
	
	/**
	 * The frame rate of the Flavor Params
	 * 
	 * @var int
	 */
	public $frameRate;
	
	/**
	 * The gop size of the Flavor Params
	 * 
	 * @var int
	 */
	public $gopSize;
	
	/**
	 * The list of conversion engines (comma separated)
	 * 
	 * @var string
	 */
	public $conversionEngines;
	
	/**
	 * The list of conversion engines extra params (separated with "|")
	 * 
	 * @var string
	 */
	public $conversionEnginesExtraParams;
	
	/**
	 * @var bool
	 */
	public $twoPass;
	
	/**
	 * @var int
	 */
	public $deinterlice;
	
	/**
	 * @var int
	 */
	public $rotate;
	
	/**
	 * @var string
	 */
	public $operators;
	
	/**
	 * @var int
	 */
	public $engineVersion;

	/**
	 * The container format of the Flavor Params
	 *  
	 * @var KalturaContainerFormat
	 * @filter eq
	 */
	public $format;
	
	/**
	 * @var int
	 */
	public $aspectRatioProcessingMode;

	/**
	 * @var int
	 */
	public $forceFrameToMultiplication16;

	/**
	 * @var int
	 */
	public $isGopInSec;

	/**
	 * @var int
	 */
	public $isAvoidVideoShrinkFramesizeToSource;
	
	/**
	 * @var int
	 */
	public $isAvoidVideoShrinkBitrateToSource;
	
	/**
	 * @var int
	 */
	public $isVideoFrameRateForLowBrAppleHls;
	
	/**
	 * @var int
	 */
	public $videoConstantBitrate;

	/**
	 * @var int
	 */
	public $videoBitrateTolerance;

	/**
	 * @var int
	 */
	public $clipOffset;

	/**
	 * @var int
	 */
	public $clipDuration;
	
	private static $map_between_objects = array
	(
		"videoCodec",
		"videoBitrate",
		"audioCodec",
		"audioBitrate",
		"audioChannels",
		"audioSampleRate",
		"width",
		"height",
		"frameRate",
		"gopSize",
		"conversionEngines",
		"conversionEnginesExtraParams",
		"twoPass",
		"deinterlice",
		"rotate",
		"operators",
		"engineVersion",
		"format",
		"aspectRatioProcessingMode",
		"forceFrameToMultiplication16",
		"isGopInSec",
		"isAvoidVideoShrinkFramesizeToSource",
		"isAvoidVideoShrinkBitrateToSource",
		"isVideoFrameRateForLowBrAppleHls",
		"videoConstantBitrate",
		"videoBitrateTolerance",
		"clipOffset",
		"clipDuration",
	);
	
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function getExtraFilters()
	{
		return array();
	}
	
	public function getFilterDocs()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toObject()
	 */
	public function toObject($object = null, $skip = array())
	{
		if(is_null($object))
			$object = new flavorParams();
			
		return parent::toObject($object, $skip);
	}
}