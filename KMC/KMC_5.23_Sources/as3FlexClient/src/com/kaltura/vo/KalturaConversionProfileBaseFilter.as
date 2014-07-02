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
	import com.kaltura.vo.KalturaFilter;

	[Bindable]
	public dynamic class KalturaConversionProfileBaseFilter extends KalturaFilter
	{
		/**
		 **/
		public var idEqual : int = int.MIN_VALUE;

		/**
		 **/
		public var idIn : String = null;

		/**
		 * @see com.kaltura.types.KalturaConversionProfileStatus
		 **/
		public var statusEqual : String = null;

		/**
		 **/
		public var statusIn : String = null;

		/**
		 **/
		public var nameEqual : String = null;

		/**
		 **/
		public var systemNameEqual : String = null;

		/**
		 **/
		public var systemNameIn : String = null;

		/**
		 **/
		public var tagsMultiLikeOr : String = null;

		/**
		 **/
		public var tagsMultiLikeAnd : String = null;

		/**
		 **/
		public var defaultEntryIdEqual : String = null;

		/**
		 **/
		public var defaultEntryIdIn : String = null;

		override public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = super.getUpdateableParamKeys();
			arr.push('idEqual');
			arr.push('idIn');
			arr.push('statusEqual');
			arr.push('statusIn');
			arr.push('nameEqual');
			arr.push('systemNameEqual');
			arr.push('systemNameIn');
			arr.push('tagsMultiLikeOr');
			arr.push('tagsMultiLikeAnd');
			arr.push('defaultEntryIdEqual');
			arr.push('defaultEntryIdIn');
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
