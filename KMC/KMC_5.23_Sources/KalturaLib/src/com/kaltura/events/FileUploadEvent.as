package com.kaltura.events {
	import flash.events.Event;

	/**
	 * Events concerning file upload process 
	 * @author Atar
	 */
	public class FileUploadEvent extends Event {

		/**
		 * defines the value for the "type" property of the upload canceled event
		 */
		public static const UPLOAD_CANCELED:String = "upload_cacnceled";
		
		/**
		 * defines the value for the "type" property of the group upload started event
		 */
		public static const UPLOAD_STARTED:String = "upload_started";

		/**
		 * defines the value for the "type" property of the upload complete event
		 */
		public static const UPLOAD_COMPLETE:String = "upload_complete";
		
		/**
		 * defines the value for the "type" property of the group upload complete event
		 */
		public static const GROUP_UPLOAD_COMPLETE:String = "group_upload_complete";
		
		/**
		 * defines the value for the "type" property of the group upload complete event
		 */
		public static const GROUP_UPLOAD_STARTED:String = "group_upload_started";
				
		/**
		 * defines the value for the "type" property of the upload error event
		 */
		public static const UPLOAD_ERROR:String = "upload_error";
		
		/**
		 * request to cancel a current uploaded file by id
		 */
		public static const CANCEL_UPLOAD:String = "cancel_upload";
		
		/**
		 * request to retry to upload a current uploaded file that failed uploading
		 */
		public static const RETRY_UPLOAD:String = "retry_upload";
		
		/**
		 * request to move a queued file up in queue list by id
		 */
		public static const MOVE_UP_IN_QUEUE:String = "move_up_in_queue";
		
		/**
		 * request to move a queued file down in queue list by id
		 */
		public static const MOVE_DOWN_IN_QUEUE:String = "move_down_in_queue";
		
		public var error:String;

		private var _uploadid:String;


		public function FileUploadEvent(type:String, uploadid:String, bubbles:Boolean = true, cancelable:Boolean = false) {
			super(type, bubbles, cancelable);
			_uploadid = uploadid;
		}


		public function get uploadid():String {
			return _uploadid;
		}

	}
}