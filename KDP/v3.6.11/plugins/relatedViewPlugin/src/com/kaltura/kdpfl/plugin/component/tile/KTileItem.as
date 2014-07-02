package com.kaltura.kdpfl.plugin.component.tile
{

import com.kaltura.kdpfl.view.controls.KTrace;

import fl.controls.listClasses.CellRenderer;
import fl.controls.listClasses.ListData;
import fl.core.InvalidationType;
import fl.core.UIComponent;

import flash.events.MouseEvent;

import mx.utils.ObjectProxy;


public class KTileItem extends CellRenderer
{
	protected var _content:UIComponent;
	private var _kData:ObjectProxy = null;

	
	public function KTileItem()
	{		
	}

    /**
     * @private
     *
     * @langversion 3.0
     * @playerversion Flash 9.0.28.0
     */
	private static var defaultStyles:Object =
	{
		upSkin: "Tile_itemUp_default",
		overSkin:"Tile_itemOver_default",
		downSkin:  "Tile_itemDown_default",
		disabledSkin: "Tile_itemDisabled_default",
		selectedUpSkin:"Tile_itemSelectedUp_default",
		selectedOverSkin:"Tile_itemSelectedOver_default",
		selectedDownSkin:"Tile_itemSelectedDown_default",
		selectedDisabledSkin: "Tile_itemSelectedDisabled_default",
		textFormat: null,
		disabledTextFormat: null,
		embedFonts: null,
		textPadding: 5
	};

	public static function getStyleDefinition():Object { return defaultStyles; }
	
	
	override public function set data(value:Object):void
	{
		super.data = ObjectProxy( value );
		kData = ObjectProxy(data);
		invalidate( InvalidationType.DATA );
		this.addEventListener(MouseEvent.ROLL_OVER, onOver);
		this.addEventListener(MouseEvent.ROLL_OUT, onOut);
	}
	
	private function onOver(event:MouseEvent):void 
	{
		super.data["isOver"] = true;
	}

	private function onOut(event:MouseEvent):void 
	{
		super.data["isOver"] = false;
	}
	
	public function get kData():ObjectProxy
	{
		return _kData;
	}
	
	[Bindable]
	public function set kData(kData:ObjectProxy):void
	{
		_kData = kData;
	}
	
	override protected function configUI():void
	{
		super.configUI();
		textField.visible = false;
	}

	/**
     * @private (protected)
     *
     * @langversion 3.0
     * @playerversion Flash 9.0.28.0
	 */		
	override protected function draw():void
	{
		if( isInvalid(InvalidationType.STYLES,InvalidationType.STATE) )
		{	
			drawBackground();	
			invalidate(InvalidationType.SIZE,false);
		}
		if ( isInvalid(InvalidationType.DATA) )
		{
			drawContent();
		}
		if (isInvalid(InvalidationType.SIZE))
		{
			drawLayout();
		}
		if (isInvalid(InvalidationType.SIZE,InvalidationType.STYLES))
		{
			if (isFocused && focusManager.showFocusIndicator) { drawFocus(true); }
		}
		invalidate(InvalidationType.ALL,true);
		validate(); // because we're not calling super.draw
	}	
	
	private function drawContent():void
	{
 		var contentFactory:Function = getStyleValue( "contentFactory" ) as Function;
		var contentLayout:XML = getStyleValue( "contentLayout" ) as XML;
		
		if( _content && this.contains( _content ) ) 
			this.removeChild( _content );
		
		_content = contentFactory( contentLayout, kData );
		_content.mouseEnabled = true;
		this.mouseChildren = true;
		addChild( _content ); 
	}
	
	
	override protected function drawLayout():void
	{
		super.drawLayout();
		
		if( _content )
		{
			_content.width = width;
			_content.height = height;
		}
	}
	
	override public function toString():String
	{
		return( "ListItem " + listData.index );
	}
	override public function set enabled(value:Boolean):void
	{
		this._enabled = value;
	}
	
	override public function set listData(arg0:ListData):void
	{
		super.listData = arg0;
	}
	
	override public function setSize(arg0:Number, arg1:Number):void
	{
		//add padding between cells
		super.setSize(arg0*0.9, arg1*0.8);
	//	super.setSize(arg0, arg1);
	}
	

}
}