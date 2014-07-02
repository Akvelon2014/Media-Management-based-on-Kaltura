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
	import com.kaltura.vo.KalturaCaptionAssetFilter;

	[Bindable]
	public dynamic class KalturaCaptionAssetItemFilter extends KalturaCaptionAssetFilter
	{
		/** 
		* 		* */ 
		public var contentLike : String = null;

		/** 
		* 		* */ 
		public var contentMultiLikeOr : String = null;

		/** 
		* 		* */ 
		public var contentMultiLikeAnd : String = null;

		/** 
		* 		* */ 
		public var partnerDescriptionLike : String = null;

		/** 
		* 		* */ 
		public var partnerDescriptionMultiLikeOr : String = null;

		/** 
		* 		* */ 
		public var partnerDescriptionMultiLikeAnd : String = null;

		/** 
		* 		* */ 
		public var languageEqual : String = null;

		/** 
		* 		* */ 
		public var languageIn : String = null;

		/** 
		* 		* */ 
		public var labelEqual : String = null;

		/** 
		* 		* */ 
		public var labelIn : String = null;

		/** 
		* 		* */ 
		public var startTimeGreaterThanOrEqual : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var startTimeLessThanOrEqual : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var endTimeGreaterThanOrEqual : int = int.MIN_VALUE;

		/** 
		* 		* */ 
		public var endTimeLessThanOrEqual : int = int.MIN_VALUE;

		override public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = super.getUpdateableParamKeys();
			arr.push('contentLike');
			arr.push('contentMultiLikeOr');
			arr.push('contentMultiLikeAnd');
			arr.push('partnerDescriptionLike');
			arr.push('partnerDescriptionMultiLikeOr');
			arr.push('partnerDescriptionMultiLikeAnd');
			arr.push('languageEqual');
			arr.push('languageIn');
			arr.push('labelEqual');
			arr.push('labelIn');
			arr.push('startTimeGreaterThanOrEqual');
			arr.push('startTimeLessThanOrEqual');
			arr.push('endTimeGreaterThanOrEqual');
			arr.push('endTimeLessThanOrEqual');
			return arr;
		}

		override public function getInsertableParamKeys():Array
		{
			var arr : Array;
			arr = super.getInsertableParamKeys();
			return arr;
		}

	}
}
