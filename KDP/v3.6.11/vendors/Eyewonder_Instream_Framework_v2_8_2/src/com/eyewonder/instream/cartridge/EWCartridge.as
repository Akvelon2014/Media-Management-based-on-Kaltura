﻿/*
EWCartridge.as

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



Description
-----------
instream vendor-specific cartridge base class. This class is used inside of InstreamFrameworkBase.
A vendor can override with a subclass. For the publisher to interact with the cartridge methods
the vendor provides, the vendor needs to provide the cartridge to the publisher to build into
InstreamFramework.

EyeWonder's vendor cartridge implementation

Example usage
-------------
EyeWonder ad:
var _vendorCartridge:EWCartridge = new EWCartridge();
parent.uif_instreamObject.insertVendorCartridge(_vendorCartridge);



Publisher:
if (_vendorCartridge._vendorName == "EyeWonder")
{
	EWCartridge(_vendorCartridge).__ew_closead();
}
*/
package com.eyewonder.instream.cartridge
{
	public dynamic class EWCartridge implements IVendorCartridge
	{
	
		public var _qaReportUUID:String;
		
		public function EWCartridge()
		{

		}
		
		public function getVendorName():String
		{
			return "EyeWonder";	
		}
		
		public function closeAd():void
		{
			EW._shutdown();	
		}
	
	}

}