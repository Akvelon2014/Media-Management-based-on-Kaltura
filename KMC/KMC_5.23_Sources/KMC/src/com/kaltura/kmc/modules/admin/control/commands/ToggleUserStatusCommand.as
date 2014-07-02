package com.kaltura.kmc.modules.admin.control.commands
{
	import com.adobe.cairngorm.control.CairngormEvent;
	import com.kaltura.analytics.GoogleAnalyticsConsts;
	import com.kaltura.analytics.GoogleAnalyticsTracker;
	import com.kaltura.commands.MultiRequest;
	import com.kaltura.commands.user.UserList;
	import com.kaltura.commands.user.UserUpdate;
	import com.kaltura.events.KalturaEvent;
	import com.kaltura.kmc.modules.admin.control.events.UserEvent;
	import com.kaltura.net.KalturaCall;
	import com.kaltura.types.KalturaUserStatus;
	import com.kaltura.vo.KalturaUser;
	import com.kaltura.vo.KalturaUserListResponse;
	
	import mx.collections.ArrayCollection;

	public class ToggleUserStatusCommand extends BaseCommand {
		
		override public function execute(event:CairngormEvent):void {
			var mr:MultiRequest = new MultiRequest();
			// toggle
			var gaEvent:String;
			var usr:KalturaUser = (event as UserEvent).user;
			usr.setUpdatedFieldsOnly(true);
			if (usr.status == KalturaUserStatus.ACTIVE) {
				usr.status = KalturaUserStatus.BLOCKED;
				gaEvent = GoogleAnalyticsConsts.ADMIN_USER_BLOCK;
			}
			else if(usr.status == KalturaUserStatus.BLOCKED) {
				usr.status = KalturaUserStatus.ACTIVE;
				gaEvent = GoogleAnalyticsConsts.ADMIN_USER_UNBLOCK;
			}
			var call:KalturaCall = new UserUpdate(usr.id, usr);
			mr.addAction(call);
			// list
			call = new UserList(_model.usersModel.usersFilter);
			mr.addAction(call);
			// post
			mr.addEventListener(KalturaEvent.COMPLETE, result);
			mr.addEventListener(KalturaEvent.FAILED, fault);
			_model.increaseLoadCounter();
			_model.kc.post(mr);
			GoogleAnalyticsTracker.getInstance().sendToGA(GoogleAnalyticsConsts.PAGE_VIEW + gaEvent);
		}
		
		override protected function result(data:Object):void {
			super.result(data);
			var response:KalturaUserListResponse = data.data[1] as KalturaUserListResponse;
			_model.usersModel.users = new ArrayCollection(response.objects);
			_model.decreaseLoadCounter();
		}
	}
}