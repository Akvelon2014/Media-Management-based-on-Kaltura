package com.kaltura.vo
{
	import com.kaltura.vo.KalturaJobData;

	[Bindable]
	public dynamic class KalturaFlattenJobData extends KalturaJobData
	{
override public function getUpdateableParamKeys():Array
		{
			var arr : Array;
			arr = super.getUpdateableParamKeys();
			return arr;
		}
	}
}
