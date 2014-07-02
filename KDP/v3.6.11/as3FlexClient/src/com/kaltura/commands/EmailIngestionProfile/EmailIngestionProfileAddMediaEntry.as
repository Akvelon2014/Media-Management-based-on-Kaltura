// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Kaltura Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Kaltura Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================
package com.kaltura.commands.EmailIngestionProfile
{
	import com.kaltura.vo.KalturaMediaEntry;
	import com.kaltura.delegates.EmailIngestionProfile.EmailIngestionProfileAddMediaEntryDelegate;
	import com.kaltura.net.KalturaCall;

	/**
	 * add KalturaMediaEntry from email ingestion
	 * 
	 **/
	public class EmailIngestionProfileAddMediaEntry extends KalturaCall
	{
		public var filterFields : String;
		
		/**
		 * @param mediaEntry KalturaMediaEntry
		 * @param uploadTokenId String
		 * @param emailProfId int
		 * @param fromAddress String
		 * @param emailMsgId String
		 **/
		public function EmailIngestionProfileAddMediaEntry( mediaEntry : KalturaMediaEntry,uploadTokenId : String,emailProfId : int,fromAddress : String,emailMsgId : String )
		{
			service= 'emailingestionprofile';
			action= 'addMediaEntry';

			var keyArr : Array = new Array();
			var valueArr : Array = new Array();
			var keyValArr : Array = new Array();
 			keyValArr = kalturaObject2Arrays(mediaEntry, 'mediaEntry');
			keyArr = keyArr.concat(keyValArr[0]);
			valueArr = valueArr.concat(keyValArr[1]);
			keyArr.push('uploadTokenId');
			valueArr.push(uploadTokenId);
			keyArr.push('emailProfId');
			valueArr.push(emailProfId);
			keyArr.push('fromAddress');
			valueArr.push(fromAddress);
			keyArr.push('emailMsgId');
			valueArr.push(emailMsgId);
			applySchema(keyArr, valueArr);
		}

		override public function execute() : void
		{
			setRequestArgument('filterFields', filterFields);
			delegate = new EmailIngestionProfileAddMediaEntryDelegate( this , config );
		}
	}
}
