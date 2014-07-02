package com.kaltura.kmc.modules.admin.control.events
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.kaltura.vo.KalturaUserRole;
	
	public class RoleEvent extends CairngormEvent {
		
		public static const ADD_ROLE_FROM_USERS:String = "admin_addRoleFromUsers";
		public static const ADD_ROLE:String = "admin_addRole";
		public static const UPDATE_ROLE:String = "admin_updateRole";
		public static const DUPLICATE_ROLE:String = "admin_duplicateRole";
		public static const DELETE_ROLE:String = "admin_deleteRole";
		public static const SELECT_ROLE:String = "admin_selectRole";
		
		
		private var _role:KalturaUserRole;
		
		
		public function RoleEvent(type:String, role:KalturaUserRole = null, bubbles:Boolean=false, cancelable:Boolean=false)
		{
			super(type, bubbles, cancelable);
			_role = role;
		}
		
		public function get role():KalturaUserRole
		{
			return _role;
		}
	}
}