package com.kaltura.kmc.modules.content.business
{
	import com.kaltura.dataStructures.HashMap;
	import com.kaltura.edw.business.base.FormBuilderBase;
	import com.kaltura.edw.model.MetadataDataObject;
	import com.kaltura.edw.model.datapacks.ContextDataPack;
	import com.kaltura.edw.model.datapacks.DistributionDataPack;
	import com.kaltura.edw.model.datapacks.EntryDataPack;
	import com.kaltura.edw.model.datapacks.FilterDataPack;
	import com.kaltura.edw.model.types.CustomMetadataConstantTypes;
	import com.kaltura.kmc.modules.content.model.CmsModelLocator;
	import com.kaltura.kmvc.model.KMvCModel;
	import com.kaltura.vo.KMCMetadataProfileVO;
	import com.kaltura.vo.KalturaMetadata;
	
	import mx.collections.ArrayCollection;
	import mx.core.UIComponent;
	
	public class CategoryFormBuilder extends FormBuilderBase
	{
		private var _model:CmsModelLocator = CmsModelLocator.getInstance();
		
		public function CategoryFormBuilder(metadataProfile:KMCMetadataProfileVO)
		{
			super(metadataProfile);
		}
		
		override protected function handleNonVBoxFieldDataHook(field:XML, valuesHashMap:HashMap):Boolean{
			var metadataDataAttribute:String = field.@metadataData;
			//in linked entry we want all the values array
			if (field.@id == CustomMetadataConstantTypes.ENTRY_LINK_TABLE){
				field.@[metadataDataAttribute] = valuesHashMap.getValue(field.@name);
				return true;
			}
			
			return false;
		}
		
		
		override protected function buildObjectFieldNodeHook(componentMap:HashMap, multi:Boolean):XML{
			var fieldNode:XML = XML(componentMap.getValue(CustomMetadataConstantTypes.ENTRY_LINK_TABLE)).copy();
			if (!multi)
				fieldNode.@maxAllowedValues = "1";
			
			return fieldNode;
		}
		
		
		override protected function buildComponentCheckHook(componentNode:XML, compInstance:UIComponent):void{
			if (componentNode.@id == CustomMetadataConstantTypes.ENTRY_LINK_TABLE) {
				compInstance["context"] = _model.context;
				compInstance["profileName"] = _metadataProfile.profile.name;
			}
		}
		
		override protected function handleComponentTypePropertiesHook(component:XML, compInstance:UIComponent, boundModel:MetadataDataObject):Boolean{
			if (component.@id == CustomMetadataConstantTypes.ENTRY_LINK_TABLE) {
				compInstance.id = component.@name;
				compInstance["metadataObject"] = boundModel;
				// pass relevant model parts:
				compInstance["filterModel"] = _model.filterModel;
				compInstance["distributionProfilesArr"] = (_model.entryDetailsModel.getDataPack(DistributionDataPack) as DistributionDataPack).distributionInfo.distributionProfiles;
				compInstance["editedItem"] = _model.categoriesModel.selectedCategory; 
				return true;
			}
			return false;
		}

	}
}