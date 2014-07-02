package com.kaltura.kdpfl.plugin.component
{
	import flash.text.TextFormat;

	public class CCLine
	{
		public var start:Number = 0;
		public var end:Number = 0;
		public var text:String = "";
		public var textFormat : TextFormat;
		public var backgroundColor : Number;
		public var showBGColor : Boolean = true;
		public var x:int;
		public var y:int;
		/**
		 * Array of InnerTextFormats objects
		 */		
		public var innerTextFormats:Array;

	}
}