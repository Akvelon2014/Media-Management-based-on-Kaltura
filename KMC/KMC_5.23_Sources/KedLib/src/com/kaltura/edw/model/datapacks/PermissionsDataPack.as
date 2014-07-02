package com.kaltura.edw.model.datapacks
{
	import com.kaltura.kmvc.model.IDataPack;
	
	[Bindable]
	/**
	 * information about different user / partner permissions 
	 * @author Atar
	 */	
	public class PermissionsDataPack implements IDataPack {
		
		public var shared:Boolean = true;
		
		/**
		 * R&P: whether to enable custom data update
		 */		
		public var enableUpdateMetadata:Boolean = true;
		
		/**
		 * R&P: whether partner has remote storage feature
		 */		
		public var remoteStorageEnabled:Boolean = true;
		
		/**
		 * R&P: for image entries, add size params when requesting image
		 * (for accounts who use remote storage, then images don't use kaltura service)
		 * */
		public var enableThumbResize:Boolean = false;
		
		/**
		 * RnP: show clipping button in EDW
		 * */
		public var allowClipping:Boolean = true;
		
		/**
		 * RnP: show trimming button in EDW
		 * */
		public var allowTrimming:Boolean = true;
		
	}
}