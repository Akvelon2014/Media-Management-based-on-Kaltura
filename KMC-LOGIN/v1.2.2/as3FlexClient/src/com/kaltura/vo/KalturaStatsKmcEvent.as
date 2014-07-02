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
package com.kaltura.vo
{
	import com.kaltura.vo.BaseFlexVo;
	[Bindable]
	public dynamic class KalturaStatsKmcEvent extends BaseFlexVo
	{
		/** 
		* 		* */ 
		public var clientVer : String = null;

		/** 
		* 		* */ 
		public var kmcEventActionPath : String = null;

		/** 
		* 		* */ 
		public var kmcEventType : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var eventTimestamp : Number = Number.NEGATIVE_INFINITY;

		/** 
		* 		* */ 
		public var sessionId : String = null;

		/** 
		* 		* */ 
		public var partnerId : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var entryId : String = null;

		/** 
		* 		* */ 
		public var widgetId : String = null;

		/** 
		* 		* */ 
		public var uiconfId : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var userId : String = null;

		/** 
		* 		* */ 
		public var userIp : String = null;

		/** 
		* a list of attributes which may be updated on this object 
		* */ 
		public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = new Array();
			arr.push('clientVer');
			arr.push('kmcEventActionPath');
			arr.push('kmcEventType');
			arr.push('eventTimestamp');
			arr.push('sessionId');
			arr.push('partnerId');
			arr.push('entryId');
			arr.push('widgetId');
			arr.push('uiconfId');
			arr.push('userId');
			return arr;
		}

		/** 
		* a list of attributes which may only be inserted when initializing this object 
		* */ 
		public function getInsertableParamKeys():Array
		{
			var arr : Array;
			arr = new Array();
			return arr;
		}

	}
}
