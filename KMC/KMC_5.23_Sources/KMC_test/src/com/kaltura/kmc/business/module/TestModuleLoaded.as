package com.kaltura.kmc.business.module
{
	import com.kaltura.kmc.business.KmcModuleLoader;
	import com.kaltura.kmc.events.KmcModuleEvent;
	import com.kaltura.kmc.modules.KmcModule;KmcModule;
	
	
	import mx.modules.ModuleLoader;
	
	import org.flexunit.Assert;
	import org.flexunit.async.Async;
	import org.fluint.uiImpersonation.UIImpersonator;
	import mx.controls.ComboBox;ComboBox;

	public class TestModuleLoaded
	{		
		
		private var _kmcModuleLoader:KmcModuleLoader;
		private var _ml:ModuleLoader;
		
		[Before( async, ui )]
		public function setUp():void
		{
			_kmcModuleLoader = new KmcModuleLoader();
			_ml = _kmcModuleLoader.loadKmcModule("bin-debug/modules/Dashboard.swf", "dashboard");
			Async.proceedOnEvent( this, _kmcModuleLoader, KmcModuleEvent.MODULE_LOADED, 2000 );
			UIImpersonator.addChild( _ml );
		}
		
		[After(ui)]
		public function tearDown():void
		{
			UIImpersonator.removeChild( _ml );
			_ml = null;
			_kmcModuleLoader = null;
			
		}
		
		[Test(async, description="return the id a module was loaded with")]
		public function testGetModuleId():void
		{
			Assert.assertEquals(_kmcModuleLoader.getModuleLoadId(_ml), "dashboard");
		}
		
		
	}
}