package com.kaltura.edw.control.commands.customData {
	import com.kaltura.commands.metadataProfile.MetadataProfileList;
	import com.kaltura.edw.business.EntryFormBuilder;
	import com.kaltura.edw.control.commands.KedCommand;
	import com.kaltura.edw.model.FilterModel;
	import com.kaltura.edw.model.datapacks.CustomDataDataPack;
	import com.kaltura.edw.model.datapacks.FilterDataPack;
	import com.kaltura.edw.model.types.APIErrorCode;
	import com.kaltura.errors.KalturaError;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmvc.control.KMvCEvent;
	import com.kaltura.types.KalturaMetadataObjectType;
	import com.kaltura.types.KalturaMetadataOrderBy;
	import com.kaltura.types.KalturaMetadataProfileCreateMode;
	import com.kaltura.utils.parsers.MetadataProfileParser;
	import com.kaltura.vo.KMCMetadataProfileVO;
	import com.kaltura.vo.KalturaFilterPager;
	import com.kaltura.vo.KalturaMetadataProfile;
	import com.kaltura.vo.KalturaMetadataProfileFilter;
	import com.kaltura.vo.KalturaMetadataProfileListResponse;
	import com.kaltura.vo.MetadataFieldVO;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.resources.ResourceManager;

	/**
	 * This command is being executed when the event MetadataProfileEvent.LIST is dispatched.
	 * @author Michal
	 *
	 */
	public class ListMetadataProfileCommand extends KedCommand {

		/**
		 * only if a metadata profile view contains layout with this name it will be used
		 */
		public static const KMC_LAYOUT_NAME:String = "KMC";


		/**
		 * This command requests the server for the last created metadata profile
		 * @param event the event that triggered this command
		 *
		 */
		override public function execute(event:KMvCEvent):void {
			_model.increaseLoadCounter();
			var filter:KalturaMetadataProfileFilter = new KalturaMetadataProfileFilter();
			filter.orderBy = KalturaMetadataOrderBy.CREATED_AT_DESC;
			filter.createModeNotEqual = KalturaMetadataProfileCreateMode.APP;
			filter.metadataObjectTypeEqual = KalturaMetadataObjectType.ENTRY;
			var listMetadataProfile:MetadataProfileList = new MetadataProfileList(filter);
			listMetadataProfile.addEventListener(KalturaEvent.COMPLETE, result);
			listMetadataProfile.addEventListener(KalturaEvent.FAILED, fault);

			_client.post(listMetadataProfile);
		}


		/**
		 * This function handles the response from the server. if a profile returned from the server then it will be
		 * saved into the model.
		 * @param data the data returned from the server
		 *
		 */
		override public function result(data:Object):void {
			_model.decreaseLoadCounter();

			if (data.error) {
				var er:KalturaError = data.error as KalturaError;
				if (er) {
					// ignore service forbidden
					if (er.errorCode != APIErrorCode.SERVICE_FORBIDDEN) {
						Alert.show(er.errorMsg, "Error");
					}
				}
			}
			else {
				var response:KalturaMetadataProfileListResponse = data.data as KalturaMetadataProfileListResponse;
				var metadataProfiles:Array = new Array();
				var formBuilders:Array = new Array();
				if (response.objects) {
					for (var i:int = 0; i < response.objects.length; i++) {
						var recievedProfile:KalturaMetadataProfile = response.objects[i];
						if (recievedProfile) {
							var metadataProfile:KMCMetadataProfileVO = new KMCMetadataProfileVO();
							metadataProfile.profile = recievedProfile;
							metadataProfile.xsd = new XML(recievedProfile.xsd);
							metadataProfile.metadataFieldVOArray = MetadataProfileParser.fromXSDtoArray(metadataProfile.xsd);
	
							//set the displayed label of each label
							for each (var field:MetadataFieldVO in metadataProfile.metadataFieldVOArray) {
								var label:String = ResourceManager.getInstance().getString('customFields', field.defaultLabel);
								if (label) {
									field.displayedLabel = label;
								}
								else {
									field.displayedLabel = field.defaultLabel;
								}
							}
	
							//adds the profile to metadataProfiles, and its matching formBuilder to formBuilders
							metadataProfiles.push(metadataProfile);
							var fb:EntryFormBuilder = new EntryFormBuilder(metadataProfile);
							formBuilders.push(fb);
							var isViewExist:Boolean = false;
	
							if (recievedProfile.views) {
								var recievedView:XML;
								try {
									recievedView = new XML(recievedProfile.views);
								}
								catch (e:Error) {
									//invalid view xmls
									continue;
								}
								for each (var layout:XML in recievedView.children()) {
									if (layout.@id == ListMetadataProfileCommand.KMC_LAYOUT_NAME) {
										metadataProfile.viewXML = layout;
										isViewExist = true;
										continue;
									}
								}
							}
							if (!isViewExist) {
								var cddp:CustomDataDataPack = _model.getDataPack(CustomDataDataPack) as CustomDataDataPack;
								//if no view was retruned, or no view with "KMC" name, we will set the default metadata view uiconf XML
								if (cddp.metadataDefaultUiconfXML){
									metadataProfile.viewXML = cddp.metadataDefaultUiconfXML.copy();
								}
								// create the actual view:
								fb.buildInitialMxml();
							}
						}
					}
				}
				var filterModel:FilterModel = (_model.getDataPack(FilterDataPack) as FilterDataPack).filterModel;
				filterModel.metadataProfiles = new ArrayCollection(metadataProfiles);
				filterModel.formBuilders = new ArrayCollection(formBuilders);
			}

		}


		/**
		 * This function will be called if the request failed
		 * @param info the info returned from the server
		 *
		 */
		override public function fault(info:Object):void {
			if (info && info.error && info.error.errorMsg && info.error.errorCode != APIErrorCode.SERVICE_FORBIDDEN) {
				Alert.show(info.error.errorMsg, ResourceManager.getInstance().getString('cms', 'error'));
			}
			_model.decreaseLoadCounter();
		}
	}
}
