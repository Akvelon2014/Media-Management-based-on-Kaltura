package com.kaltura.kmc.modules.analytics.commands {
	import com.adobe.cairngorm.commands.ICommand;
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.kaltura.commands.report.ReportGetUrlForReportAsCsv;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmc.modules.analytics.model.AnalyticsModelLocator;
	import com.kaltura.kmc.modules.analytics.model.types.ScreenTypes;
	import com.kaltura.kmc.modules.analytics.view.core.FileManager;
	import com.kaltura.vo.KalturaEndUserReportInputFilter;
	import com.kaltura.vo.KalturaReportInputFilter;

	import mx.controls.Alert;
	import mx.events.CloseEvent;
	import mx.resources.ResourceManager;
	import mx.rpc.IResponder;

	public class ExportToExcelCommand implements ICommand, IResponder {
		private var _model:AnalyticsModelLocator = AnalyticsModelLocator.getInstance();
		private var fm:FileManager = new FileManager();
		private var _fileUrl:String = "";


		public function execute(event:CairngormEvent):void {
			_model.loadingFlag = true;
			var headers:String = "";

			for (var j:int = 0; j < _model.selectedReportData.originalTotalHeaders.length; j++)
				if (_model.selectedReportData.originalTotalHeaders[j] != "object_id")
					headers += ResourceManager.getInstance().getString('analytics', _model.selectedReportData.originalTotalHeaders[j]) + ",";

			headers = headers.substr(0, headers.length - 1);
			headers += ";"; //";Object Id,"; (see mantis ticket 13090 requesting to remove this header)

			if (_model.selectedReportData.wasObjectIdDropped && (_model.selectedReportData.objectIds == "" || _model.selectedReportData.objectIds == null)) {
				headers += "Object Id,";
			}

			if (_model.selectedReportData.originalTableHeaders) {
				for (var i:int = 0; i < _model.selectedReportData.originalTableHeaders.length; i++) {
					if (_model.selectedReportData.originalTableHeaders[i] != "object_id") {
						headers += ResourceManager.getInstance().getString('analytics', _model.selectedReportData.originalTableHeaders[i]) + ",";
					}
				}
				// remove last ","
				headers = headers.substr(0, headers.length - 1);
			}
			else {
				headers += ResourceManager.getInstance().getString('analytics', 'no_table');
			}



			// default texts (not supposed to be used)
			var message2Send:String = _model.selectedReportData.message;
			if (_model.selectedReportData.message == "" || _model.selectedReportData.message == null)
				message2Send = ResourceManager.getInstance().getString('analytics', 'no_msg');
			message2Send = message2Send.replace(/<.*?>/g, ""); // remove HTML tags if any
			
			if (_model.selectedReportData.title == "" || _model.selectedReportData.title == null)
				_model.selectedReportData.title = ResourceManager.getInstance().getString('analytics', 'no_ttl');

			
			var krif:KalturaReportInputFilter;
			
			switch (_model.currentScreenState) {
				case ScreenTypes.END_USER_ENGAGEMENT:
				case ScreenTypes.END_USER_ENGAGEMENT_DRILL_DOWN:
				case ScreenTypes.END_USER_STORAGE:
				case ScreenTypes.END_USER_STORAGE_DRILL_DOWN:
					krif = ExecuteReportHelper.createEndUserFilterFromCurrentReport();
					break;
					
				case ScreenTypes.VIDEO_DRILL_DOWN_DEFAULT:
				case ScreenTypes.VIDEO_DRILL_DOWN_DROP_OFF:
				case ScreenTypes.VIDEO_DRILL_DOWN_INTERACTIONS:
					if (_model.entitlementEnabled) {
						krif = ExecuteReportHelper.createEndUserFilterFromCurrentReport();
					}
					else {
						krif = ExecuteReportHelper.createFilterFromCurrentReport();
					}
					break;
				default:
					krif = ExecuteReportHelper.createFilterFromCurrentReport();
					break;
			}
			
			var export2Csv:ReportGetUrlForReportAsCsv = new ReportGetUrlForReportAsCsv(_model.selectedReportData.title,
					message2Send, headers, _model.selectedReportData.type, krif, _model.selectedReportData.selectedDim,
					_model.selectedReportData.pager, _model.selectedReportData.orderBy, _model.selectedReportData.objectIds);

			export2Csv.addEventListener(KalturaEvent.COMPLETE, result);
			export2Csv.addEventListener(KalturaEvent.FAILED, fault);
			_model.kc.post(export2Csv);
		}


		public function result(result:Object):void {
			_model.loadingFlag = false;
			_fileUrl = result.data;
			Alert.show(ResourceManager.getInstance().getString('analytics', 'csvReady'),
				ResourceManager.getInstance().getString('analytics', 'csvReadyTitle'), Alert.OK, null, onClose);
		}


		private function onClose(event:CloseEvent):void {
			fm.downloadFile(_fileUrl,
				ResourceManager.getInstance().getString('analytics', 'downloadCSVTitle'),
				_model.selectedReportData.title + ".csv");
		}


		public function fault(info:Object):void {
			//_model.loadingFlag = false;
		}
	}
}
