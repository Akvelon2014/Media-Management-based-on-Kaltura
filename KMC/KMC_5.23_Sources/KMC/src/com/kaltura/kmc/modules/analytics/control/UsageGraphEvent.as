package com.kaltura.kmc.modules.analytics.control
{
	import com.adobe.cairngorm.control.CairngormEvent;
	
	public class  UsageGraphEvent extends CairngormEvent
	{
		public static const USAGE_GRAPH : String = "analytics_usageGraph";
		
		public function UsageGraphEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
		}
	}
}