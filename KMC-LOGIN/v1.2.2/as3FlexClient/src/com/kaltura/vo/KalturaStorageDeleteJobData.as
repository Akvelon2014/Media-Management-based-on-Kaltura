package com.kaltura.vo
{
	import com.kaltura.vo.KalturaStorageJobData;

	[Bindable]
	public dynamic class KalturaStorageDeleteJobData extends KalturaStorageJobData
	{
override public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = super.getUpdateableParamKeys();
			return arr;
		}
	}
}
