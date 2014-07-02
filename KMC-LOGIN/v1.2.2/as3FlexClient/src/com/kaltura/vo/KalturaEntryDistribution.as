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
	public dynamic class KalturaEntryDistribution extends BaseFlexVo
	{
		/** 
		* 		* */ 
		public var id : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var createdAt : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var updatedAt : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var submittedAt : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var entryId : String = null;

		/** 
		* 		* */ 
		public var partnerId : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var distributionProfileId : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var status : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var sunStatus : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var dirtyStatus : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var thumbAssetIds : String = null;

		/** 
		* 		* */ 
		public var flavorAssetIds : String = null;

		/** 
		* 		* */ 
		public var sunrise : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var sunset : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var remoteId : String = null;

		/** 
		* 		* */ 
		public var plays : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var views : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var validationErrors : Array = null;

		/** 
		* 		* */ 
		public var errorType : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var errorNumber : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var errorDescription : String = null;

		/** 
		* 		* */ 
		public var hasSubmitResultsLog : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var hasSubmitSentDataLog : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var hasUpdateResultsLog : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var hasUpdateSentDataLog : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var hasDeleteResultsLog : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var hasDeleteSentDataLog : int = int.MIN_VALUE;

		/** 
		* a list of attributes which may be updated on this object 
		* */ 
		public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = new Array();
			arr.push('thumbAssetIds');
			arr.push('flavorAssetIds');
			arr.push('sunrise');
			arr.push('sunset');
			arr.push('validationErrors');
			return arr;
		}

		/** 
		* a list of attributes which may only be inserted when initializing this object 
		* */ 
		public function getInsertableParamKeys():Array
		{
			var arr : Array;
			arr = new Array();
			arr.push('entryId');
			arr.push('distributionProfileId');
			return arr;
		}

	}
}
