package com.kaltura.kmc.modules.content.commands
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.kaltura.commands.media.MediaAdd;
	import com.kaltura.edw.control.events.MediaEvent;
	import com.kaltura.edw.model.datapacks.EntryDataPack;
	import com.kaltura.edw.model.types.WindowsStates;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmc.modules.content.events.EntriesEvent;
	import com.kaltura.kmc.modules.content.events.WindowEvent;
	import com.kaltura.net.KalturaCall;
	import com.kaltura.vo.KalturaMediaEntry;
	
	import mx.resources.ResourceManager;

	public class AddMediaEntryCommand extends KalturaCommand {
		//whether to open drilldown after media is created
//		private var _openDrilldown:Boolean;
		
		override public function execute(event:CairngormEvent):void 
		{
			_model.increaseLoadCounter();
			var mediaEvent:EntriesEvent = event as EntriesEvent;
//			_openDrilldown = mediaEvent.openDrilldown;
			var addMedia:MediaAdd = new MediaAdd(mediaEvent.data);

			addMedia.addEventListener(KalturaEvent.COMPLETE, result);
			addMedia.addEventListener(KalturaEvent.FAILED, fault);
			_model.context.kc.post(addMedia);
		}
		
		override public function result(data:Object):void {
			super.result(data);
			
			if (data.data && (data.data is KalturaMediaEntry)) {
				(_model.entryDetailsModel.getDataPack(EntryDataPack) as EntryDataPack).selectedEntry = data.data as KalturaMediaEntry;
//				if (_openDrilldown) {	
					var cgEvent:WindowEvent = new WindowEvent(WindowEvent.OPEN, WindowsStates.ENTRY_DETAILS_WINDOW_NEW_ENTRY);
					cgEvent.dispatch();
//				}
			}
			else {
				trace ("error in add media");
			}
			
			_model.decreaseLoadCounter();
		}
		
	}
}