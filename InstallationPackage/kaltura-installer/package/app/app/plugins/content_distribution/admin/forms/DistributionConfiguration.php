<?php 
/**
 * @package plugins.contentDistribution 
 * @subpackage admin
 */
class Form_DistributionConfiguration extends Infra_Form
{
	public function getObject($objectType, array $properties, $add_underscore = true, $include_empty_fields = false)
	{
		$object = parent::getObject($objectType, $properties, $add_underscore, $include_empty_fields);

		$requiredFlavorParamsIds = array();
		$optionalFlavorParamsIds = array();
		$object->requiredThumbDimensions = array();
		$object->optionalThumbDimensions = array();
		
		foreach($properties as $property => $value)
		{
			$matches = null;
			if(preg_match('/flavorParamsId_(\d+)$/', $property, $matches))
			{
				$flavorId = $matches[1];
				if($value == 'required')
					$requiredFlavorParamsIds[] = $flavorId;
				if($value == 'optional')
					$optionalFlavorParamsIds[] = $flavorId;
			}
			
			if(preg_match('/dimensionsWidth_(\d+)$/', $property, $matches))
			{
				$thumbIndex = $matches[1];
				
				$dimensions = new Kaltura_Client_ContentDistribution_Type_DistributionThumbDimensions();
				$dimensions->width = $value;
				$dimensions->height = $properties["dimensionsHeight_{$thumbIndex}"];
				
				if($properties["dimensionsRequired_{$thumbIndex}"])
					$object->requiredThumbDimensions[] = $dimensions;
				else
					$object->optionalThumbDimensions[] = $dimensions;
			}
		}
		$object->requiredFlavorParamsIds = implode(',', $requiredFlavorParamsIds);
		$object->optionalFlavorParamsIds = implode(',', $optionalFlavorParamsIds);
		
		if(isset($properties['dimensionsWidth']) && is_array($properties['dimensionsWidth']))
		{
			foreach($properties['dimensionsWidth'] as $index => $dimensionsWidth)
			{
				$dimensionsHeight = $properties['dimensionsHeight'][$index];
				$dimensionsRequired = $properties['dimensionsRequired'][$index];
				
				$dimensions = new Kaltura_Client_ContentDistribution_Type_DistributionThumbDimensions();
				$dimensions->width = $dimensionsWidth;
				$dimensions->height = $dimensionsHeight;
				
				if($dimensionsRequired)
					$object->requiredThumbDimensions[] = $dimensions;
				else
					$object->optionalThumbDimensions[] = $dimensions;
			}
		}

		$requiredAssetDistributionRules = array();
		$optionalAssetDistributionRules = array();
		foreach($properties as $key => $value)
		{
			if (strpos($key, 'asset_distribution_rule_') === 0)
			{
				$assetDistributionRule = new Kaltura_Client_ContentDistribution_Type_AssetDistributionRule();
				$assetDistributionRule->validationError = $value['validation_error'];
				$assetDistributionRule->assetDistributionConditions = array();
				foreach($value as $subKey => $subValue)
				{
					if (strpos($subKey, 'asset_distribution_property_condition_') === 0)
					{
						$assetDistributionPropertyCondition = new Kaltura_Client_ContentDistribution_Type_AssetDistributionPropertyCondition();
						$assetDistributionPropertyCondition->propertyName = $subValue['property_name'];
						$assetDistributionPropertyCondition->propertyValue = $subValue['property_value'];
						$assetDistributionRule->assetDistributionConditions[] = $assetDistributionPropertyCondition;
					}
				}

				if (isset($value['required']) && $value['required'])
					$requiredAssetDistributionRules[] = $assetDistributionRule;
				else
					$optionalAssetDistributionRules[] = $assetDistributionRule;
			}
		}

		$object->requiredAssetDistributionRules = $requiredAssetDistributionRules;
		$object->optionalAssetDistributionRules = $optionalAssetDistributionRules;
		
		return $object;
	}
	
	public function addFlavorParamsFields(Kaltura_Client_Type_FlavorParamsListResponse $flavorParams, array $optionalFlavorParamsIds = array(), array $requiredFlavorParamsIds = array())
	{
		$this->addElement('hidden', 'crossLine2', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
		$element = new Zend_Form_Element_Hidden('setFlavorParams');
		$element->setLabel('Flavor Params');
		$element->setDecorators(array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'b'))));
		$this->addElements(array($element));

		foreach($flavorParams->objects as $index => $flavorParamsItem)
		{
			$element = $this->createElement('radio', 'flavorParamsId_' . $flavorParamsItem->id);
			$element->setLabel($flavorParamsItem->name);
			$element->setDecorators(
				array(
					'ViewHelper', 
					array('Label', array('placement' => 'prepend', 'class' => 'flavor_name')),
					array('HtmlTag',  array('tag' => 'div', 'class' => 'flavor_param')),
				));
			$element->setSeparator('');
			
			$element->addMultiOption("", 'None');
			$element->addMultiOption("optional", 'Optional');
			$element->addMultiOption("required", 'Required');
			
			if(in_array($flavorParamsItem->id, $requiredFlavorParamsIds))
				$element->setValue('required');
			elseif(in_array($flavorParamsItem->id, $optionalFlavorParamsIds))
				$element->setValue('optional');
			else
				$element->setValue('');
			
			$this->addElement($element);
		}
		
		$this->addElement('hidden', 'crossLine3', array(
			'lable'			=> 'line',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'hr', 'class' => 'crossLine')))
		));
		
	}
	
	private $dimensionsCounter = 0;
	public function addThumbDimensions(Kaltura_Client_ContentDistribution_Type_DistributionThumbDimensions $dimensions, $isRequired)
	{
		if(!$this->dimensionsCounter)
		{
			$element = new Zend_Form_Element_Hidden('setThumbnailDimensions');
			$element->setLabel('Thumbnail Dimensions');
			$element->setDecorators(array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'b'))));
			$this->addElements(array($element));
		}
		
		$this->addElement('text', 'dimensionsWidth_' . $this->dimensionsCounter, array(
			'label'			=> 'Width:',
			'value'			=> $dimensions->width,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'dimensionsHeight_' . $this->dimensionsCounter, array(
			'label'			=> 'Height:',
			'value'			=> $dimensions->height,
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('checkbox', 'dimensionsRequired_' . $this->dimensionsCounter, array(
			'label'	  => 'Is Required',
			'value'	  => $isRequired,
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'dt')))
		));
			
		$this->addElement('button', 'removeThumbDimensions_' . $this->dimensionsCounter, array(
			'label'		=> 'Remove',
			'onclick'		=> "removeThumbDimensions({$this->dimensionsCounter})",
			'decorators'	=> array('ViewHelper'),
		));
		
		$this->addDisplayGroup(
			array('dimensionsWidth_' . $this->dimensionsCounter, 'dimensionsHeight_' . $this->dimensionsCounter, 'dimensionsRequired_' . $this->dimensionsCounter, 'removeThumbDimensions_' . $this->dimensionsCounter), 
			'thumbDimensions_' . $this->dimensionsCounter,
			array(
				'decorators' => array('FormElements', 'Fieldset', array('HtmlTag',array('tag'=>'div', 'class' => 'thumbDimensions_' . $this->dimensionsCounter))),
			)
		);
		
		$this->dimensionsCounter++;
	}
	
	public function addThumbDimensionsForm()
	{
		$this->addElement('button', 'newThumbDimensionsButton', array(
			'label'		=> 'Add Thumbnail',
			'onclick'		=> "newThumbDimensions()",
			'decorators'	=> array('ViewHelper'),
		));
		$this->addElement('hidden', 'thumbnailSettings', array(
			'label'		=> 'Thumbnail Settings',
			'decorators'	=> array('ViewHelper'),
		));
		
		$this->addDisplayGroup(
			array('thumbnailSettings','newThumbDimensionsButton'),
			'newThumbDimensionsButtonGroup',
			array(
				'decorators' => array('FormElements', 'Fieldset', array('HtmlTag',array('tag'=>'div'))),
			)
		);
		
		$this->addElement('text', 'dimensionsWidth', array(
			'label'			=> 'Width:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('text', 'dimensionsHeight', array(
			'label'			=> 'Height:',
			'filters'		=> array('StringTrim'),
		));
		
		$this->addElement('checkbox', 'dimensionsRequired', array(
			'label'	  => 'Is Required',
			'decorators' => array('ViewHelper', array('Label', array('placement' => 'append')), array('HtmlTag',  array('tag' => 'dt', 'id' => 'thumbnailRequired')))
		));
			
		$this->addDisplayGroup(
			array('dimensionsWidth', 'dimensionsHeight', 'dimensionsRequired'), 
			'newThumbDimensions',
			array(
				'legend' => 'New Thumbnail Dimensions',
				'decorators' => array('FormElements', 'Fieldset', array('HtmlTag',array('tag'=>'div','style'=>'display: none;', 'class' => 'newThumbDimensions'))),
			)
		);
	}

	public function addDistributionAssetRules($optionalDistributionAssetRules, $requiredDistributionAssetRules)
	{
		if (!is_array($optionalDistributionAssetRules))
			$optionalDistributionAssetRules = array();
		if (!is_array($requiredDistributionAssetRules))
			$requiredDistributionAssetRules = array();

		$assetDistributionRules = array_merge($requiredDistributionAssetRules, $optionalDistributionAssetRules);
		$assetDistributionRulesSubForm = new Zend_Form_SubForm(array('DisableLoadDefaultDecorators' => true));
		$assetDistributionRulesSubForm->addDecorator('ViewScript', array(
			'viewScript' => 'asset-distribution-rules-sub-form.phtml',
		));
		foreach($assetDistributionRules as $assetDistributionRule)
		{
			$assetDistributionRuleSubForm = new Form_AssetDistributionRuleSubForm();
			$assetDistributionRuleSubForm->populateFromObject($assetDistributionRule);
			if (in_array($assetDistributionRule, $requiredDistributionAssetRules))
				$assetDistributionRuleSubForm->setIsRequired(true);
			$assetDistributionRulesSubForm->addSubForm($assetDistributionRuleSubForm, 'asset_distribution_rule_'.spl_object_hash($assetDistributionRule));
		}
		$this->addSubForm($assetDistributionRulesSubForm, 'asset_distribution_rules');
	}
}