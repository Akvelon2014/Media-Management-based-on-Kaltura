package com.kaltura.edw.vo
{
	public class EntryDetailsValidationError {
		
		public static const ENTRY_NAME_MISSING:String = "entryNameMissingError";
		public static const CATEGORIES_LIMIT:String = "categoriesLimitError";
		public static const SCHEDULING_START_DATE:String = "schedualingStartDateError";
		public static const SCHEDULING_END_DATE:String = "scedualingEndDateError";
		public static const BITRATE:String = "bitrateError";
		public static const CAPTIONS_URL:String = "captionsUrl";
		public static const CAPTIONS_LANGUAGE:String = "captionsLanguage";
		public static const RELATED_FILES_NOT_UPLOADED:String = "relatedFilesNotUploaded";
		public static const CUEPOINTS_DATA:String = "cuepointsData";
		
		
		public var error:String;
		
		public function EntryDetailsValidationError() {}

	}
}