package com.kaltura.edw.business.permissions {
	import com.kaltura.edw.events.KedErrorEvent;
	import com.kaltura.edw.vo.PermissionVo;
	import com.kaltura.utils.CastUtil;
	import com.kaltura.vo.KalturaPermission;
	import com.kaltura.vo.KalturaPermissionListResponse;
	
	import flash.events.EventDispatcher;
	import flash.utils.describeType;

	/**
	 * This class will apply all permission to the UI by receiving a target screen to work with,
	 * and a list of PermissionVos.
	 * @author Eitan
	 *
	 */
	public class PermissionManager extends EventDispatcher {

		/**
		 * role permissions (original XML from <code>init()</code> transformed).
		 * it holds the effects on UI of permissions this role doesn't have.
		 */
		private var _deniedPermissions:XML;

		/**
		 * nodes from the uiconf of permissions a user has.
		 * (not groups, only permissions which may have ui elements)
		 */
		private var _grantedPermissions:XML = <permissions/>;

		/**
		 * @copy #partnerPermissions
		 */
		private var _partnerUIDefinitions:XML;

		/**
		 * list of permission ids that are denied
		 */
		private var _deniedPermissionsIds:Array = new Array();

		/**
		 * list of permissions VOs
		 */
		private var _instructionVos:Array;

		/**
		 * list of tabs and subtabs to hide
		 */
		private var _hideTabs:Array;


		/**
		 * remove any role permissions that depend on permissions the partner doesn't have,
		 * i.e if partner doesn't have custom metadata feature, make sure all metadata-related permissions are denied
		 * @param uiDefinitions		all partner's permissions ui defs (permissions uiconf)
		 * @param allRolePermissions	a list of the role's permission names
		 * @param partnerPermissions	partners permissions as returned from the server
		 * @return array of permission names the role has after filtering
		 */
		protected function removeRestrictedPermissions(uiDefinitions:XML, allRolePermissions:Array, partnerPermissions:KalturaPermissionListResponse):Array {
			var partnerPermissionsList:Array = parsePartnerPermissions(partnerPermissions);
			var depended:XMLList = uiDefinitions.descendants().(hasOwnProperty( "@dependsOnFeature" ));
			// scan the ui definitions and for each permission that depends on another, see if the partner has that permission.
			for each (var pXml:XML in depended) {
				var bPartnerHasPermission:Boolean = false;
				for each (var partnerPermission:KalturaPermission in partnerPermissions.objects) {
					if (pXml.@dependsOnFeature == partnerPermission.name) {
						bPartnerHasPermission = true;
						break;
					}
				}
				// if the partner doesn't have the permission, see if the role has it, and if so - remove it.
				if (!bPartnerHasPermission) {
					var i:int = stringIndex(pXml.@id, allRolePermissions);
					if (i > -1) {
						allRolePermissions.splice(i, 1);
					}
				}
			}
			return allRolePermissions;
		}
		
		
		/**
		 * Get the partner permissions XML and the users permissions list (comma-seperated list),
		 * parse them and keep relevant data in this class.
		 * @param uiDefinitions		all partner's permissions ui defs (permissions uiconf)
		 * @param rolePermission	a comma-separated-string of ids of the role's permissions
		 * @param partnerPermissions	partners permissions as returned from the server
		 */
		public function init(uiDefinitions:XML, rolePermissions:String = "", partnerPermissions:KalturaPermissionListResponse = null):void {
			_partnerUIDefinitions = uiDefinitions.copy();
			_deniedPermissions = uiDefinitions.copy();
			var allRolePermissions:Array = rolePermissions.split(",");
			allRolePermissions = removeRestrictedPermissions(uiDefinitions, allRolePermissions, partnerPermissions);
			var partnerPermissionsList:Array = parsePartnerPermissions (partnerPermissions); 
			// remove from permissions list the granted permissions and leave the ones that are forbidden.
			// first remove only sub-permissions (not groups)
			if (allRolePermissions.length > 0 && allRolePermissions[0] != "") {
				for each (var permissionId:String in allRolePermissions) {
					var permissionData:XML = _deniedPermissions.permissions..descendants().(attribute("id") == permissionId)[0];
					// if such permission exists (permission or permissionGroup)
					if (permissionData) {
						if (permissionData.localName() == "permissionGroup") {
							// if we remove groups now we will actually remove permissions we may need to ban.
							continue;
						}
						delete _deniedPermissions.permissions..descendants().(attribute("id") == permissionId)[0];
						_grantedPermissions.appendChild(permissionData);
					}
				}
			}

			// banned "BASE" permissions:
			var permissionGroupsToDeny:XMLList = _deniedPermissions.permissions.permissionGroup.(rolePermissions.indexOf(@id) == -1);
			
			// for these groups the base is granted but the contents are denied. 
			// we need to remove all ui elements from them before adding them to the main list.
			var groupsInDoubt:XMLList = _deniedPermissions.permissions.permissionGroup.(child("permission").length() > 0 && rolePermissions.indexOf(@id) > -1);
			var nodeList:XMLList = groupsInDoubt.child("ui");
			for(var i:int = nodeList.length() -1; i >= 0; i--) {
				delete nodeList[i];
			}

			// replace the original permissions node with the "clean" one
			delete _deniedPermissions.permissions[0];
			_deniedPermissions.appendChild(XML(<permissions/>).appendChild(permissionGroupsToDeny));
			_deniedPermissions.permissions.appendChild(groupsInDoubt);

			// remove colliding attributes between granted and denied permissions
			removeCollisions(_grantedPermissions, _deniedPermissions.permissions[0]);

			var permissionParser:PermissionsParser = new PermissionsParser();
			_instructionVos = permissionParser.parseAllPermissions(_deniedPermissions.permissions.permissionGroup);

			var partnerPermissionsUi:XMLList = getPartnerPermissionsUi(_deniedPermissions.partnerPermissions[0], partnerPermissions);
			_instructionVos = _instructionVos.concat(permissionParser.parseAllPermissions(partnerPermissionsUi));
			
			var permissionIdList:XMLList = _deniedPermissions.permissions.descendants().attribute("id");
			for each (var xml:XML in permissionIdList) {
				if (rolePermissions.indexOf(xml.toString()) == -1) { // this is for the granted groups with denied children
					_deniedPermissionsIds.push(xml.toString());
				}
			}
			
			
			var roleAndPartnerPermissionNames:Array = allRolePermissions.concat(partnerPermissionsList);
			
			_hideTabs = permissionParser.getTabsToHide(_deniedPermissions..uimapping[0], roleAndPartnerPermissionNames); 
		
		}
		
		/**
		 * get the partner level permissions the current partner doesn't have 
		 * @param uidefs	ui definitions concerning partner-level permissions
		 * @param partnerPermissions	server response for partner permissions list
		 * @return 		denied permissions in partner level
		 * 
		 */
		protected function getPartnerPermissionsUi(uidefs:XML, partnerPermissions:KalturaPermissionListResponse):XMLList {
			// remove nodes from uidefs whose id is the same as anything in partnerPermissions
			for each (var feature:KalturaPermission in partnerPermissions.objects) {
				var fname:String = feature.name;
				var xml:XML = uidefs.permissionGroup.(@id == fname)[0];
				if (xml) {
					delete uidefs.permissionGroup.(@id == fname)[0];
				}
			}
			return uidefs.permissionGroup;
		}
		
		/**
		 * parse the permissions list response
		 * @param klr	the permissions list response
		 * @return an array of partner permission ids.
		 * */
		protected function parsePartnerPermissions(klr:KalturaPermissionListResponse):Array {
			if (!klr) {
				return null;
			}
			var result:String = '';
			for each (var kperm:KalturaPermission in klr.objects) {
				result += kperm.name + ",";
			}
			// remove last ","
			result = result.substring(0, result.length - 1);
			return result.split(",");
		}


		/**
		 * remove the attributes on ui nodes that have values on both the granted and denied lists.
		 * this method alters the denied list.
		 * @param granted	permissions that the user has
		 * @param denied	permissions that the user doesn't have
		 */
		protected function removeCollisions(granted:XML, denied:XML):void {
			var grantedui:XMLList = granted..ui;
			var gl:int = grantedui.length();
			var deniedui:XMLList = denied..ui;
			var dl:int = deniedui.length();
			var uiid:String;
			var atts:XMLList;
			for (var i:int = 0; i < gl; i++) {
				uiid = grantedui[i].@id;
				for each (var uixml:XML in deniedui) {
					if (uixml.@id == uiid) {
						// remove the matching attributes from the denied permission
						atts = grantedui[i].attributes();
						for (var j:int = 0; j < atts.length(); j++) {
							if (atts[j].localName() != "id" && uixml.attribute(atts[j].localName())) {
								delete uixml.@[atts[j].localName()];
							}
						}
					}
				}
			}
		}



		/**
		 * Get a list of permission VOs that is relevant to the component specified by componentPath.
		 * @param componentPath 	path to component
		 * @return	list of permissionVo-s
		 */
		protected function getRelevantPermissions(componentPath:String):Array {
			var arr:Array = new Array();
			for each (var vo:PermissionVo in _instructionVos) {
				if (vo.path == componentPath) {
					// i.e. wizard > hideTabs="advertising"
					arr.push(vo);
				}
				else if (vo.path.indexOf(componentPath + ".") == 0) {
					// i.e. entryDrilldown.entryThumbnails > editable="false"
					arr.push(vo);
				} 
				else if(vo.path.indexOf("." + componentPath + ".") > -1) {
					// i.e. content.upload.submitBtn > enabled="false"
					arr.push(vo);
				}
				else if(vo.path.indexOf("." + componentPath) > -1 && vo.path.indexOf("." + componentPath) == (vo.path.length - componentPath.length - 1)) {
					// i.e. content.manage > showEmbed="false"
					arr.push(vo);
				}
			}
			return arr;
		}


		/**
		 * Search for relevant attributes for this component according to path,
		 * iterate on them, and try to change their value
		 * @param startComponent
		 * @param path
		 */
		public function applyAllAttributes(startComponent:Object, path:String):void {
			var relevantPermissions:Array = getRelevantPermissions(path);
			for each (var o:PermissionVo in relevantPermissions) {
				for (var attribute:Object in o.attributes) {
					apply(startComponent, o.path, attribute.toString(), o.attributes[attribute]);
				}
			}
		}


		/**
		 * This function recieves a path to a component IE myCompo1.myCompo2.myButton
		 * a starting target (instance of a uiComponent),a propery on the target to change
		 * and a new value.
		 * @param startComponent	the component from which to calculate path
		 * @param componentPath		path to the component to act on
		 * @param componentProperty	the property of the target component to be changed
		 * @param newValue			new value for <code>componentProperty</code>
		 */
		public function apply(startComponent:Object, componentPath:String, componentProperty:String, newValue:*):void {
			var o:Object = getWorkComponent(startComponent, componentPath);

			if (o) {
				var dt:XML = describeType(o);
				if (dt.@isDynamic.toString() == "true") {
					// dynamic type, always assign.
					assignProperty(o, componentProperty, newValue);
				}
				else if (o.hasOwnProperty(componentProperty)) {
					// statics type, only assign if attribute exists
					assignProperty(o, componentProperty, newValue);
				}
				else {
					dispatchError("cannot push attribute " + componentProperty + " to component of type " + dt.@name.toString());
				}
			}
		}


		/**
		 * Select the component to act on.
		 * @param startComponent	the component from which to calculate path
		 * @param componentPath		path to the component to act on
		 * @return 		the component to which componentPath directs.
		 */
		protected function getWorkComponent(startComponent:Object, componentPath:String):Object {
			var o:Object = startComponent;
			var chain:Array = componentPath.split(".");
			var ind:int = stringIndex(startComponent.id, chain);
			if (ind > -1) {
				// remove everything before, including.
				/*chain = */
				chain.splice(0, ind + 1);
			}
			else {
				// in this case we assume this is one of the popup windows, so we 
				// need to remove the meaningless first item
				chain.shift();
			}

			// find the current component position in chain
			// iterate from the next position 
			for (var i:uint = 0; i < chain.length; i++) {
				// next in chain
				if (chain[i]) {
					if (o.hasOwnProperty(chain[i])) {
						o = o[chain[i]];
					}
					else {
						dispatchError("component " + o.id + " doesn't have property " + chain[i]);
						return null;
					}
				}
			}
			return o;
		}



		/**
		 * dispatch an error event
		 * @param errorString	error text to present to the user
		 */
		protected function dispatchError(errorString:String):void {
			var kee:KedErrorEvent = new KedErrorEvent(KedErrorEvent.ERROR, errorString);
			dispatchEvent(kee);
		}


		/**
		 * Assign a new value to a property. This function cast if needed (depend on the
		 * target type) to Boolean and to int.
		 *
		 * @param target - the target object
		 * @param prop - the property name
		 * @param value - the new value
		 *
		 */
		protected function assignProperty(target:*, prop:String, value:*):void {
			if (target[prop] is Boolean) {
				target[prop] = CastUtil.castToBoolean(value);
				return;
			}
			//default behavior
			target[prop] = value;
		}


		/**
		 * get tabs-to-hide whose names include the given module name
		 * @param module	name of the module for which we want to hide tabs
		 * @return relevant sub-tabs to hide
		 */
		public function getRelevantSubTabsToHide(module:String = null):Array {
			var arr:Array = new Array();
			var tabName:String;
			if (module) {
				for each (tabName in _hideTabs) {
					if (tabName.indexOf(module) == 0 && tabName.indexOf(".") > -1) {
						arr.push(tabName.split(".").pop().toString()); //isolate the main tab name
					}
				}
			}
			else {
				// this is the KMC module dropping thingy
				for each (tabName in _hideTabs) {
					if (tabName.indexOf(".") == -1) {
						arr.push(tabName);
					}
				}
			}
			return arr;
		}


		/**
		 * If there is a permission vo associated with the component, and that vo
		 * has a definition for the given attribute, return the value.
		 * @param componentPath path to component
		 * @param attribute		the attribute whose value we want
		 * @return the value for the desired attribute
		 */
		public function getValue(componentPath:String, attribute:String):* {
			var result:* = null;
			var relevantPermissions:Array = getRelevantPermissions(componentPath);
			for each (var o:PermissionVo in relevantPermissions) {
				for (var att:Object in o.attributes) {
					if (att.toString() == attribute) {
						return o.attributes[att];
					}
				}
			}
			return null;
		}


		/**
		 * See if the given string is in the given array
		 * @param str
		 * @param array
		 * @return the index of the string in the array
		 */
		protected function stringIndex(str:String, array:Array):int {
			var l:int = array.length;
			for (var i:int = 0; i < l; i++) {
				if (array[i] == str) {
					return i;
				}
			}
			return -1;
		}


		////////////////// getters ///////////////////////////

		/**
		 * @copy #_permissionXml
		 */
		public function get permissionXml():XML {
			return _deniedPermissions;
		}


		/**
		 * @copy #_deniedPermissionsIds
		 */
		public function get deniedPermissions():Array {
			return _deniedPermissionsIds;
		}


		/**
		 * @copy #_instructionVos
		 */
		public function get instructionVos():Array {
			return _instructionVos;
		}


		/**
		 * @copy #_hideTabs
		 */
		public function get hideTabs():Array {
			return _hideTabs;
		}



		/**
		 * original permissions uiconf, unchanged.
		 */
		public function get partnerUIDefinitions():XML {
			return _partnerUIDefinitions;
		}


		////////////////////////////////////////////////singleton code
		/**
		 * Singleton instance
		 */
		private static var _instance:PermissionManager;

		CONFIG::realBuild {
			/**
			 * @param enforcer	singleton garantee
			 */
			public function PermissionManager(enforcer:Enforcer) {

			}


			/**
			 * Singleton means of retreiving an instance of the
			 * <code>PermissionManager</code> class.
			 */
			public static function getInstance():PermissionManager {
				if (_instance == null) {
					_instance = new PermissionManager(new Enforcer());
				}
				return _instance;
			}
		}

		CONFIG::unitTestingBuild {
			/**
			 * Constructor
			 */
			public function PermissionManager() {

			}


			/**
			 * Singleton means of retreiving an instance of the
			 * <code>PermissionManager</code> class.
			 */
			public static function getInstance():PermissionManager {
				if (_instance == null) {
					_instance = new PermissionManager();
				}
				return _instance;
			}
		}

	}
}

class Enforcer {

}