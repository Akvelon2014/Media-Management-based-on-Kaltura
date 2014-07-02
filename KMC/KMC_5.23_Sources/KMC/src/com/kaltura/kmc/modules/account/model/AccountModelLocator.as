package com.kaltura.kmc.modules.account.model {
	import com.adobe.cairngorm.model.IModelLocator;
	import com.kaltura.kmc.modules.account.model.states.WindowsStates;
	import com.kaltura.kmc.modules.account.vo.AdminVO;
	import com.kaltura.kmc.modules.account.vo.FlavorVO;
	import com.kaltura.kmc.modules.account.vo.PackagesVO;
	import com.kaltura.kmc.modules.account.vo.PartnerVO;
	import com.kaltura.types.KalturaAccessControlOrderBy;
	import com.kaltura.types.KalturaConversionProfileOrderBy;
	import com.kaltura.vo.KMCMetadataProfileVO;
	import com.kaltura.vo.KalturaAccessControlFilter;
	import com.kaltura.vo.KalturaBaseEntry;
	import com.kaltura.vo.KalturaCategory;
	import com.kaltura.vo.KalturaConversionProfileFilter;
	import com.kaltura.vo.KalturaFilterPager;
	import com.kaltura.vo.KalturaStorageProfile;
	import com.kaltura.vo.KalturaUser;
	
	import flash.events.EventDispatcher;
	
	import mx.collections.ArrayCollection;

	[Bindable]
	public class AccountModelLocator extends EventDispatcher implements IModelLocator {

		/**
		 * application context data 
		 */
		public var context:Context = null;
		
		/**
		 * partner info 
		 */		
		public var partnerData:PartnerVO;

		/* ****************************************************
		* account info
		**************************************************** */
		
		[ArrayElementType("KalturaUser")]
		/**
		 * a list of users with administrator role of the current partner
		 */		
		public var usersList:ArrayCollection;
		
		public var adminData:AdminVO = new AdminVO();
		
		/* ****************************************************
		 * integration
		 **************************************************** */
		
		[ArrayElementType("KalturaCategory")]
		/**
		 * list of categorys with a defined privacy context 
		 */		
		public var categoriesWithPrivacyContext:Array;
		
		
		
		/* ****************************************************
		 * metadata
		 **************************************************** */
		public var metadataProfilesArray:ArrayCollection = new ArrayCollection();
		public var selectedMetadataProfile:KMCMetadataProfileVO;
		public var metadataFilterPager:KalturaFilterPager;
		public var metadataProfilesTotalCount:int = 10;

		
		/* ****************************************************
		 * access control
		 **************************************************** */
		public var accessControls:ArrayCollection = new ArrayCollection();
		public var accessControlProfilesTotalCount:int = 10;
		public var filterPager:KalturaFilterPager;
		public var acpFilter:KalturaAccessControlFilter;

		/* ****************************************************
		 * conversion
		 **************************************************** */
		[ArrayElementType("KalturaStorageProfile")]
		/**
		 * a list of remote storages configured for the partner.
		 * <code>KalturaStorageProfile</code> objects 
		 */
		public var storageProfiles:ArrayCollection;
		
		[ArrayElementType("ConversionProfileVO")]
		/**
		 * list of conversion profiles <br>
		 * <code>ConversionProfileVO</code> objects
		 */
		public var conversionData:ArrayCollection = new ArrayCollection();
		
		/**
		 * total number of conversion profiles 
		 */		
		public var totalConversionProfiles:int;
		
		/**
		 * list of optional flavors <br>
		 * <code>FlavorVO</code> objects
		 * */
		public var flavorsData:ArrayCollection = new ArrayCollection();
		
		/**
		 * list of thumbnail flavors 
		 * */
		public var thumbsData:ArrayCollection = new ArrayCollection();
		
		/**
		 * default filter for conversion profiles in KMC
		 * */
		public var cpFilter:KalturaConversionProfileFilter;
		
		/**
		 * default pager for conversion profiles in KMC
		 * */
		public var cpPager:KalturaFilterPager;
		
		/**
		 * the default entry for the current conversion profile
		 * (only loaded during save for validation) 
		 */
		public var defaultEntry:KalturaBaseEntry;
		
		
		/* ****************************************************
		* upgrade
		**************************************************** */
		
		public var partnerPackage:PackagesVO = null;
		
		public var listPackages:ArrayCollection;
		
		
		//---------------------------------------------------------
		// states
		public var windowState:String = WindowsStates.NONE;

		//---------------------------------------------------------
		// Flags 
		public var devFlag:Boolean = false;
		
		/**
		 * any pending server requests
		 */
		public var loadingFlag:Boolean = false;
		
		/**
		 * the custom metadata tab is disabled
		 * */
		public var customDataDisabled:Boolean = false;
		
		public var partnerInfoLoaded:Boolean = false;
		public var saveAndExitFlag:Boolean = false;

		//---------------------------------------------------------
		// singleton methods
		private static var _modelLocator:AccountModelLocator;


		public static function getInstance():AccountModelLocator {
			if (_modelLocator == null) {
				_modelLocator = new AccountModelLocator(new Enforcer());
			}

			return _modelLocator;
		}


		public function AccountModelLocator(enforcer:Enforcer) {
			context = new Context();
			acpFilter = new KalturaAccessControlFilter();
			acpFilter.orderBy = KalturaAccessControlOrderBy.CREATED_AT_DESC;

			cpFilter = new KalturaConversionProfileFilter();
			cpFilter.orderBy = KalturaConversionProfileOrderBy.CREATED_AT_DESC;
				
		}


		/**
		 * clone all flavours and return them 
		 */
		public function getClonedFlavorsData():ArrayCollection {
			var arr:ArrayCollection = new ArrayCollection();
			for each (var flavor:FlavorVO in flavorsData) {
				arr.addItem(flavor.clone());
			}

			return arr;
		}


		/**
		 * clone all flavours, mark them all as unselected and return them 
		 */
		public function getUnselectedClonedFlavorsData():ArrayCollection {
			var arr:ArrayCollection = new ArrayCollection();
			for each (var flavor:FlavorVO in flavorsData) {
				var cloned:FlavorVO = flavor.clone();
				cloned.selected = false;
				arr.addItem(cloned);
			}

			return arr;
		}
	}
}

class Enforcer {}