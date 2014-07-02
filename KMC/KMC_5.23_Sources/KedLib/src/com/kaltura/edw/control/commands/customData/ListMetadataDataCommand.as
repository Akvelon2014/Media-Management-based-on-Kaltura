package com.kaltura.edw.control.commands.customData {
	import com.kaltura.commands.metadata.MetadataList;
	import com.kaltura.edw.business.EntryFormBuilder;
	import com.kaltura.edw.control.commands.KedCommand;
	import com.kaltura.edw.control.events.MetadataDataEvent;
	import com.kaltura.edw.model.FilterModel;
	import com.kaltura.edw.model.datapacks.CustomDataDataPack;
	import com.kaltura.edw.model.datapacks.EntryDataPack;
	import com.kaltura.edw.model.datapacks.FilterDataPack;
	import com.kaltura.edw.vo.CustomMetadataDataVO;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmvc.control.KMvCEvent;
	import com.kaltura.vo.KMCMetadataProfileVO;
	import com.kaltura.vo.KalturaFilterPager;
	import com.kaltura.vo.KalturaMetadata;
	import com.kaltura.vo.KalturaMetadataFilter;
	import com.kaltura.vo.KalturaMetadataListResponse;
	
	import mx.collections.ArrayCollection;

	/**
	 * This class sends a metadata data list request to the server and handles the response
	 * @author Michal
	 *
	 */
	public class ListMetadataDataCommand extends KedCommand {

		private var _filterModel:FilterModel;


		/**
		 * This command requests the server for the latest valid metadata data, that suits
		 * the current profile id and current profile version
		 * @param event the event that triggered this command
		 *
		 */
		override public function execute(event:KMvCEvent):void {
			_filterModel = (_model.getDataPack(FilterDataPack) as FilterDataPack).filterModel;
			
			if (event.type == MetadataDataEvent.RESET) {
				var metadataEmptyData:Array = new Array;
				var kMetadata:KalturaMetadata;
				var prof:KMCMetadataProfileVO;
				for (var i:int = 0; i < _filterModel.metadataProfiles.length; i++) {
					prof = _filterModel.metadataProfiles[i] as KMCMetadataProfileVO;
					kMetadata = new KalturaMetadata();
					kMetadata.metadataProfileId = prof.profile.id;
					metadataEmptyData.push(kMetadata);
				}
				setDataToModel(metadataEmptyData);
			}
			else { // list
				
				var edp:EntryDataPack = _model.getDataPack(EntryDataPack) as EntryDataPack;
				if (!_filterModel.metadataProfiles || !edp.selectedEntry.id)
					return;

				var filter:KalturaMetadataFilter = new KalturaMetadataFilter();
				filter.objectIdEqual = edp.selectedEntry.id;

				var listMetadataData:MetadataList = new MetadataList(filter);
				listMetadataData.addEventListener(KalturaEvent.COMPLETE, result);
				listMetadataData.addEventListener(KalturaEvent.FAILED, fault);

				_client.post(listMetadataData);
			}
		}


		/**
		 * This function handles the response returned from the server
		 * @param data the data returned from the server
		 *
		 */
		override public function result(data:Object):void {
			super.result(data);
			var metadataResponse:KalturaMetadataListResponse = data.data as KalturaMetadataListResponse;
			setDataToModel(metadataResponse.objects);
		}


		/**
		 * match given data to profiles and form builders
		 * @param metadataArray entry metadata data
		 */
		private function setDataToModel(metadataArray:Array):void {
			var cddp:CustomDataDataPack = _model.getDataPack(CustomDataDataPack) as CustomDataDataPack;
			cddp.metadataInfoArray = new ArrayCollection();

			//go over all profiles and match to the metadata data
			for (var i:int = 0; i < _filterModel.metadataProfiles.length; i++) {
				var entryMetadata:CustomMetadataDataVO = new CustomMetadataDataVO();
				cddp.metadataInfoArray.addItem(entryMetadata);

				// get the form builder that matches this profile:
				var formBuilder:EntryFormBuilder = _filterModel.formBuilders[i] as EntryFormBuilder;
				formBuilder.metadataInfo = entryMetadata;

				// add the KalturaMetadata of this profile to the EntryMetadataDataVO
				var profileId:int = (_filterModel.metadataProfiles[i] as KMCMetadataProfileVO).profile.id;
				for each (var metadata:KalturaMetadata in metadataArray) {
					if (metadata.metadataProfileId == profileId) {
						entryMetadata.metadata = metadata;
						break;
					}
				}
				formBuilder.updateMultiTags();
			}
		}

	}
}
