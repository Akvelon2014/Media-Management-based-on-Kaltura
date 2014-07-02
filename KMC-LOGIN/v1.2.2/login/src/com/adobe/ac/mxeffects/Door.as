package com.adobe.ac.mxeffects
{

import flash.filters.BlurFilter;

import mx.effects.IEffectInstance;
import mx.effects.TweenEffect;
import com.adobe.ac.mxeffects.effectClasses.DoorInstance;

public class Door extends TweenEffect
{
	public function Door( target:Object = null )
	{
		super( target );
		instanceClass = DoorInstance;
	}
	
	[Inspectable(category="General", defaultValue="null")]
	public static var defaultDirection : String = "RIGHT";
	
	[Inspectable(defaultValue="null")]
	public static const OPEN : String = "OPEN";
	
	[Inspectable(defaultValue="null")]
	public static const CLOSE : String = "CLOSE";	
	
	[Inspectable(category="General", defaultValue="null")]
	public static var defaultMode : String = "OPEN";
	
	[Inspectable(category="General", defaultValue="null")]
	public var siblings : Array;

	[Inspectable(category="General", defaultValue="null", enumeration="RIGHT,LEFT,TOP,BOTTOM")]
	public var direction : String;
	
	[Inspectable(category="General", defaultValue="null", enumeration="OPEN,CLOSE")]
	public var mode : String;
	
	[Inspectable(category="General", defaultValue="null")]
	public static var defaultBuildMode : String = "POPUP";
	
	[Inspectable(category="General", defaultValue="null", enumeration="POPUP,REPLACE,ADD,OVERWRITE")]
	public var buildMode : String;
	
	[Inspectable(category="General", defaultValue="false")]
	public var smooth : Boolean;

	[Inspectable(category="General", defaultValue="NaN")]
	public var distortion : Number;
	
	[Inspectable(category="General", defaultValue="false")]
	public var liveUpdate : Boolean = false;
	
	[Inspectable(category="General", defaultValue="0")]
	public var liveUpdateInterval : int = 0;	
		
	[Inspectable(category="General", defaultValue="null")]
	public var blur : BlurFilter;
		
	override protected function initInstance( instance : IEffectInstance ) : void
	{
		super.initInstance( instance );		
		var effectInstance : DoorInstance = DoorInstance( instance );	
		effectInstance.siblings = siblings;
		effectInstance.direction = direction;
		effectInstance.buildMode = buildMode;
		effectInstance.smooth = smooth;
		effectInstance.mode = mode;
		effectInstance.distortion = distortion;
		effectInstance.liveUpdate = liveUpdate;
		effectInstance.liveUpdateInterval = liveUpdateInterval;
		effectInstance.blur = blur;
	}
}

}