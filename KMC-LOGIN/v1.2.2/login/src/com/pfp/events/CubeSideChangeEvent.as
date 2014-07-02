package com.pfp.events
{
  import flash.events.Event;

  public class CubeSideChangeEvent extends Event
  {
    public var oldSide:String;
    public var newSide:String;
    
    public function CubeSideChangeEvent(type:String, oldSide:String, newSide:String, bubbles:Boolean = false)
    {
      super(type, bubbles);
      this.oldSide = oldSide;
      this.newSide = newSide;
    }
    
    public static const SIDE_CHANGE:String = "sideChange";
  }
}