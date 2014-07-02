<?php
/**
 * @package plugins.crossKalturaDistribution
 * @subpackage lib.batch
 */
class CrossKalturaEntryObjectsContainer
{
    /**
     * @var KalturaBaseEntry
     */
    public $entry;
        
    /**
     * @var array<KalturaMetadata>
     */
    public $metadataObjects;
    
    /**
     * @var array<KalturaFlavorAsset>
     */
    public $flavorAssets;
    
    /**
     * @var array<KalturaContentResource>
     */
    public $flavorAssetsContent;
    
    /**
     * @var array<KalturaThumbAsset>
     */
    public $thumbAssets;
    
    /**
     * @var array<KalturaContentResource>
     */
    public $thumbAssetsContent;
    
    /**
     * @var array<KalturaCaptionAsset>
     */
    public $captionAssets;
    
    /**
     * @var array<KalturaContentResource>
     */
    public $captionAssetsContent;
    
    /**
     * @var array<KalturaCuePoint>
     */
    public $cuePoints;
    
    /**
     * Initialize all member variables
     */
    public function __construct()
    {
        $this->entry = null;
        $this->metadataObjects = array();
        $this->flavorAssets = array();
        $this->flavorAssetsContent = array();
        $this->thumbAssets = array();
        $this->thumbAssetsContent = array();
        $this->captionAssets = array();
        $this->captionAssetsContent = array();
        $this->cuePoints = array();
    }
}