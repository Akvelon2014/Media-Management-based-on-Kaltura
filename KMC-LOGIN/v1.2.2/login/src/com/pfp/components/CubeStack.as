/*
 * taken from:
 * http://www.switchonthecode.com/tutorials/flex-rotating-cube-viewstack-component
 * */
package com.pfp.components
{
  import com.adobe.ac.mxeffects.CubeRotate;
  import com.pfp.events.CubeRotateEvent;
  import com.pfp.events.CubeSideChangeEvent;
  
  import flash.display.DisplayObject;
  import flash.utils.Timer;
  import flash.utils.getTimer;
  import flash.utils.setTimeout;
  
  import mx.containers.ViewStack;
  import mx.effects.Effect;
  import mx.events.EffectEvent;
  
  [Event(name="rotateStart", type="com.pfp.events.CubeRotateEvent")]
  [Event(name="rotateEnd", type="com.pfp.events.CubeRotateEvent")]
  [Event(name="sideChange", type="com.pfp.events.CubeStackEvent")]

  [Bindable]
  public class CubeStack extends ViewStack
  { 
    public static const Sides:Array = ["Front", "Left", "Back", "Right", "Top", "Bottom"];
    
    protected var flipDuration:Number = 750;
    
    protected var currentEffect:Effect;
    
    public function set FlipDuration(value:Number):void
    { this.flipDuration = value; }
    
    public function get FlipDuration():Number
    { return this.flipDuration; }
    
    override public function set selectedIndex(value:int):void
    {
      this.selectedSide = CubeStack.Sides[value];
    }
    
    public function set selectedSide(value:String):void
    {
      var index:int = CubeStack.Sides.indexOf(value);
      var oldIndex:int = super.selectedIndex;
      
      if(index == -1)
        return;
        
      if(this.selectedIndex == index)
        return;
        
      if(this.currentEffect && this.currentEffect.isPlaying)
        return;
       
      var distance:int = 0;
      var direction:String = "RIGHT";
        
      //TODO: Review this code to see if I can refactor
      if(this.selectedIndex < 4 && index < 4) //Going Left or Right
      { 
        while(index != ((this.selectedIndex + distance) % 4))
          distance++;
        
        if(distance > 2)
        {
          direction = "LEFT";
          distance = 0;
          while(this.selectedIndex != ((index + distance) % 4))
            distance++;
        }
      }
      else
      {
        if(this.selectedIndex < 4) //Currently not in top or bottom
        {
          distance = 1;
          direction = (index == 5) ? "TOP" : "BOTTOM";
        }
        else //Currently already at the top or bottom
        {
          distance = (index < 4) ? 1 : 2;
          direction = (this.selectedIndex == 5) ? "BOTTOM" : "TOP";
        }
      }
      this.rotate(direction, distance, index);
      
      super.selectedIndex = index;
      this.dispatchEvent(new CubeSideChangeEvent(CubeSideChangeEvent.SIDE_CHANGE, CubeStack.Sides[oldIndex], CubeStack.Sides[index] ,true));
    }
    
    public function get selectedSide():String
    {
      return CubeStack.Sides[this.selectedIndex];
    }
    
    protected function rotate(direction:String, numberOfSides:Number, newSelectedIndex:int):void
    {
      var hideEffect:CubeRotate = new CubeRotate(this.getChildAt(this.selectedIndex));
      if (numberOfSides == 2)
      {
        var midIndex:int;
        if(direction == "RIGHT" || direction == "LEFT")
          midIndex = (this.selectedIndex + 1) % 4;
        else
          midIndex = Math.round((Math.random() * 3));
        
        hideEffect.siblings = [this.getChildAt(midIndex), this.getChildAt(newSelectedIndex)];
      }
      else
      {
        hideEffect.siblings = [this.getChildAt(newSelectedIndex)];
      }
      
      hideEffect.direction = direction;
      hideEffect.duration = this.flipDuration;
      this.selectedChild.setStyle("hideEffect", hideEffect);
      
      this.currentEffect = hideEffect;
      this.currentEffect.addEventListener(EffectEvent.EFFECT_START, rotateEventStart);
      this.currentEffect.addEventListener(EffectEvent.EFFECT_END, rotateEventEnd);
    }
    
    protected function rotateEventStart(event:EffectEvent):void
    {
      this.dispatchEvent(new CubeRotateEvent(CubeRotateEvent.ROTATE_START, true));
    }
    
    protected function rotateEventEnd(event:EffectEvent):void
    {
      this.dispatchEvent(new CubeRotateEvent(CubeRotateEvent.ROTATE_END, true));
    }
  }
}