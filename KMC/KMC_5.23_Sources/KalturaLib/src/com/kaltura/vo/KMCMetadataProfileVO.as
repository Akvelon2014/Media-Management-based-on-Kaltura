/*
This file is part of the Kaltura Collaborative Media Suite which allows users
to do with audio, video, and animation what Wiki platfroms allow them to do with
text.

Copyright (C) 2006-2008  Kaltura Inc.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

@ignore
*/
package com.kaltura.vo {
	import com.adobe.cairngorm.vo.IValueObject;
	
	import mx.collections.ArrayCollection;

	[Bindable]
	/**
	 *	This class represents a profile of metadata custom fields
	 * @author Michal
	 *
	 */
	public class KMCMetadataProfileVO implements IValueObject {
		
		public static var serveURL:String = "/api_v3/index.php/service/metadata_metadataprofile/action/serve";

		/**
		 * used to mark selection in table 
		 */		
		public var tableSelected:Boolean;
		
		/**
		 * id of the wrapped profile 
		 */		
		public var id:int;
		
		public var profile:KalturaMetadataProfile = new KalturaMetadataProfile();
		public var metadataFieldVOArray:ArrayCollection = new ArrayCollection();
		public var metadataProfileChanged:Boolean = false;

		public var metadataProfileReordered:Boolean = false;
		public var isNewProfile:Boolean = false;
		public var isCurrentlyEdited:Boolean = false;

		/**
		 * represents the xsd from the profile, in an XML representation
		 * */
		public var xsd:XML;

		public var viewXML:XML;
		public var profileDisabled:Boolean = false;
		public var downloadUrl:String;


		/**
		 *  Constructs a new KMCMetadataProfileVO class
		 *
		 */
		public function KMCMetadataProfileVO():void {
		}


	}
}
