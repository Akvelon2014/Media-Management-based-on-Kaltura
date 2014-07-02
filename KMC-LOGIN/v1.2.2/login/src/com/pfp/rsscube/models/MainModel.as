package com.pfp.rsscube.models
{
  [Bindable]
  public class MainModel
  {
    private static const model:MainModel =
      new MainModel(ModelLock);
   
    public static function GetInstance():MainModel
    {
      return MainModel.model;
    }
   
    public function MainModel(lock:Class)
    {
      if(lock != ModelLock)
      {
        throw new Error("Invalid MainModel access. " +
            "Use MainModel.getInstance()");
      }
      
      cubeSide = "Front";
    }
    
    public var cubeSide:String;
  }
}

class ModelLock {}