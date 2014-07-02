package com.kaltura.kdpfl.view.containers
{
	import com.kaltura.kdpfl.component.IComponent;
	import com.kaltura.kdpfl.util.KColorUtil;
	import com.yahoo.astra.fl.containers.VBoxPane;

	public dynamic class KVBox extends VBoxPane implements IComponent
	{
		private var _bgColor:Number = -1;
		private var _bgAlpha:Number = 1;
		
		[Bindable]		
		public var maxWidth:Number = -1;
		[Bindable]
		public var maxHeight:Number = -1;
		
		public function KVBox(configuration:Array=null)
		{
			super(configuration);
			mouseEnabled = false;
		}

		public function initialize():void
		{
			this.verticalScrollPolicy = "off";
			this.horizontalScrollPolicy = "off";
		}

		public function setSkin(styleName:String, setSkinSize:Boolean=false):void
		{
			if (styleName != null && styleName != '')
				setStyle("skin", styleName);
			mouseEnabled = false;
		}

		override public function setStyle(type:String, name:Object):void
		{
			try{
				super.setStyle(type, name);
			}catch(ex:Error){}
		}
		
		
		//These two functions are a TEMPORARY workaround for a problem that exists in the ktextparser: the parser does not accept the ! operator.
		// Once the issue is fixed, this workaround will be removed.
		[Bindable]
		public function set notVisible(value:Boolean):void
		{
			visible = !value;
		}
		
		public function get notVisible () :Boolean
		{
			return !visible;
		}
		[Bindable]
		public function set bgColor(color:Number):void
		{
			_bgColor = color;
			drawBackground();
			
		}
		/**
		 * This parameter will flat color the container skin
		 */
		public function get bgColor():Number
		{
			return _bgColor;
		}
		public function set bgAlpha(str:String):void
		{
			_bgAlpha = Number(str);
		}
		/**
		 * This parameter will set the alpha of the skins container
		 */
		public function get bgAlpha():String
		{
			return _bgAlpha.toString();
		}
		/**
		 * To color the background
		 */
		override protected function drawBackground():void
		{
			super.drawBackground();
			if (this.background && _bgColor !=-1)
			{
				KColorUtil.colorDisplayObject(this.background,_bgColor);
				this.background.alpha = _bgAlpha;
			}
		}	
		
		override public function set width(value:Number):void
		{
			if (maxWidth != -1)
			{
				super.width = Math.min(value, maxWidth);
			}
			else
			{
				super.width = value;
			}
		}
		
		override public function set height(value:Number):void
		{
			if (maxHeight != -1)
			{
				super.height = Math.min(value, maxHeight);
			}
			else
			{
				super.height = value;
			}
		}
		
		
	}
}