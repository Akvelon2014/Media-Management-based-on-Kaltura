package com.kaltura.edw.control.commands {
	import com.kaltura.commands.flavorAsset.FlavorAssetGetFlavorAssetsWithParams;
	import com.kaltura.edw.control.events.KedEntryEvent;
	import com.kaltura.edw.model.datapacks.DistributionDataPack;
	import com.kaltura.edw.model.datapacks.EntryDataPack;
	import com.kaltura.edw.model.types.APIErrorCode;
	import com.kaltura.edw.vo.FlavorAssetWithParamsVO;
	import com.kaltura.errors.KalturaError;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmvc.control.KMvCEvent;
	import com.kaltura.types.KalturaFlavorAssetStatus;
	import com.kaltura.vo.KalturaBaseEntry;
	import com.kaltura.vo.KalturaFlavorAssetWithParams;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.resources.ResourceManager;

	public class ListFlavorAssetsByEntryIdCommand extends KedCommand {
		override public function execute(event:KMvCEvent):void {
			_model.increaseLoadCounter();
			(_model.getDataPack(DistributionDataPack) as DistributionDataPack).flavorsLoaded = false;
			var entryId:String = (event as KedEntryEvent).entryVo.id;
			var getAssetsAndFlavorsByEntryId:FlavorAssetGetFlavorAssetsWithParams = new FlavorAssetGetFlavorAssetsWithParams(entryId);
			getAssetsAndFlavorsByEntryId.addEventListener(KalturaEvent.COMPLETE, result);
			getAssetsAndFlavorsByEntryId.addEventListener(KalturaEvent.FAILED, fault);
			_client.post(getAssetsAndFlavorsByEntryId);
		}


		override public function fault(info:Object):void {
			_model.decreaseLoadCounter();
			var entry:KalturaBaseEntry = (_model.getDataPack(EntryDataPack) as EntryDataPack).selectedEntry;
			// if this is a replacement entry
			if (entry.replacedEntryId) {
				var er:KalturaError = (info as KalturaEvent).error;
				if (er.errorCode == APIErrorCode.ENTRY_ID_NOT_FOUND) {
					Alert.show(ResourceManager.getInstance().getString('cms','replacementNotExistMsg'),ResourceManager.getInstance().getString('cms','replacementNotExistTitle'));
				}		
				else {
					Alert.show(ResourceManager.getInstance().getString('cms', 'flavorAssetsErrorMsg') + ":\n" + er.errorMsg, ResourceManager.getInstance().getString('cms', 'error'));
				}
			}
			else {
				Alert.show(ResourceManager.getInstance().getString('cms', 'flavorAssetsErrorMsg') + ":\n" + info.error.errorMsg, ResourceManager.getInstance().getString('cms', 'error'));
			}
		}


		override public function result(event:Object):void {
			super.result(event);
			setDataInModel((event as KalturaEvent).data as Array);
			(_model.getDataPack(DistributionDataPack) as DistributionDataPack).flavorsLoaded = true;
			_model.decreaseLoadCounter();
		}


		private function setDataInModel(arrCol:Array):void {
			var flavorParamsAndAssetsByEntryId:ArrayCollection = new ArrayCollection();
//			flavorParamsAndAssetsByEntryId.removeAll();
			var tempAc:ArrayCollection = new ArrayCollection();
			var foundIsOriginal:Boolean = false;
			for each (var assetWithParam:KalturaFlavorAssetWithParams in arrCol) {
				if (assetWithParam.flavorAsset && assetWithParam.flavorAsset.status == KalturaFlavorAssetStatus.TEMP) {
					// flavor assets will have status temp if it's source of conversion 
					// profile that has no source, during transcoding. we don't want to 
					// show these.
					continue;
				}
				if ((assetWithParam.flavorAsset != null) && (assetWithParam.flavorAsset.isOriginal)) {
					foundIsOriginal = true;
				}
				var kawp:FlavorAssetWithParamsVO = new FlavorAssetWithParamsVO();
				kawp.kalturaFlavorAssetWithParams = assetWithParam;
				if (assetWithParam.flavorAsset != null) {

					flavorParamsAndAssetsByEntryId.addItem(kawp);
				}
				else {
					tempAc.addItem(kawp);
				}
			}


			for each (var tmpObj:FlavorAssetWithParamsVO in tempAc) {
				flavorParamsAndAssetsByEntryId.addItem(tmpObj);
			}

			if (foundIsOriginal) {
				for each (var afwps:FlavorAssetWithParamsVO in flavorParamsAndAssetsByEntryId) {
					afwps.hasOriginal = true;
				}
			}
			(_model.getDataPack(DistributionDataPack) as DistributionDataPack).flavorParamsAndAssetsByEntryId = flavorParamsAndAssetsByEntryId;
		
		}
	}
}