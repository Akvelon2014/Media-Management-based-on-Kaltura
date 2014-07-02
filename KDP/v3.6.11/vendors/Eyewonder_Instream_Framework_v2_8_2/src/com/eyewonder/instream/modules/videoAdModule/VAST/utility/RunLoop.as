﻿/*
RunLoop.as

Universal Instream Framework
Copyright (c) 2006-2009, Eyewonder, Inc
All Rights Reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright
  notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
  notice, this list of conditions and the following disclaimer in the
  documentation and/or other materials provided with the distribution.
 * Neither the name of Eyewonder, Inc nor the
 names of contributors may be used to endorse or promote products
 derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY Eyewonder, Inc ''AS IS'' AND ANY
EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Eyewonder, Inc BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

This file should be accompanied with supporting documentation and source code.
If you believe you are missing files or information, please 
contact Eyewonder, Inc (http://www.eyewonder.com)

*/
package com.eyewonder.instream.modules.videoAdModule.VAST.utility
{
	import flash.utils.setInterval;
	import flash.utils.clearInterval;
	import flash.events.Event;
	
	/** @private */
	public dynamic class RunLoop
	{
		public var functions:Array;
		
		private static var runloop:RunLoop = null;
		public var interval:uint;
		
		/** @private */
		public function RunLoop()
		{
			functions = new Array();
			interval = setInterval( run, 100 );
		}
		
		public static function initialize():void
		{
			if(runloop == null) runloop = new RunLoop();
		}
		
		public static function getInstance():RunLoop
		{
			if(runloop == null) runloop = new RunLoop();
			return runloop;
		}
		
		public function run():void
		{
			
			for( var i:int = 0; i < functions.length; i++)
			{
				var func:Object = functions[i];
				
				func.timeleft -= 100;
				if( func.timeleft <= 0 )
				{
					func.funcref();
					func.timeleft = func.interval;
				}
			}
			
		}
		
		public static function addFunction( funcref:Function, interval:int = 250):int
		{
			var func:Object = new Object();
			func.interval = interval;
			func.funcref = funcref;
			func.timeleft = interval;
			
			getInstance().functions.push( func );
			
			return getInstance().functions.length-1;
		}
		
		public static function remove( id:int ):void
		{
			delete getInstance().functions[id];
		}
		
		public function OnShutdown( event:Event ):void
		{
			clearInterval(interval);
			runloop = null;
		}
	}
}