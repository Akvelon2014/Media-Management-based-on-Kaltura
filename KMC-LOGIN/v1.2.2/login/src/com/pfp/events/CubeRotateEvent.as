package com.pfp.events
{
  import flash.events.Event;

  public class CubeRotateEvent extends Event
  {
    public function CubeRotateEvent(type:String, bubbles:Boolean = false)
    {
      super(type, bubbles);
    }
    
    public static const ROTATE_START:String = "rotateStart";
    public static const ROTATE_END:String = "rotateEnd";
  }
}