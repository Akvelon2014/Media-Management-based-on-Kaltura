package com.kaltura.kdpfl.style
{
	import com.kaltura.kdpfl.view.controls.KTrace;
	
	import flash.display.DisplayObjectContainer;
	import flash.display.MovieClip;
	import flash.text.TextField;
	import flash.text.TextFormat;
	import flash.utils.getDefinitionByName;
	
	public class TextFormatManager
	{
		protected static var instance : TextFormatManager;
		private var textFormats:Object = new Object; 

		public static function getInstance() : TextFormatManager
		{
			if (instance == null) instance = new TextFormatManager();
			return instance;
		}
		
		/**
		 * Retrieve the textformat from the given class. drill down to look for a single textfield
		 * in the 1st child index. this is protected in a try/catch. 
		 */ 
		public function getTextFormat(styleName:String):TextFormat
		{
			if (!textFormats.hasOwnProperty([styleName]))
			{
				try
				{
					var styleClass:Class = getDefinitionByName(styleName) as Class;
					var styleMovieClip:DisplayObjectContainer = new styleClass() as DisplayObjectContainer;
					textFormats[styleName] = styleMovieClip.getChildAt(0) as TextField;
				}
				catch(e:Error)
				{
					textFormats[styleName] = new TextField();
					KTrace.getInstance().log("getTextFormat", e);
					//trace("getTextFormat", e);
				}
			}
			
			return TextField(textFormats[styleName]).getTextFormat();
		} 
	}
}