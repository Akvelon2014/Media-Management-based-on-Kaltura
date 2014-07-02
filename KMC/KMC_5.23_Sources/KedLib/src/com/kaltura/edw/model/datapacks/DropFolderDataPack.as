package com.kaltura.edw.model.datapacks
{
	import com.kaltura.kmvc.model.IDataPack;
	import com.kaltura.vo.KalturaDropFolder;
	
	import mx.collections.ArrayCollection;
	
	[Bindable]
	public class DropFolderDataPack implements IDataPack {
		
		public var shared:Boolean = false;
		
		/**
		 * currently selected drop folder 
		 */		
		public var selectedDropFolder:KalturaDropFolder;
		
		/**
		 * list of DropFolders 
		 */
		public var dropFolders:ArrayCollection;
		
		/**
		 * list of files in the selected DropFolder
		 */
		public var dropFolderFiles:ArrayCollection;
	}
}