// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Kaltura Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Kaltura Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================
package com.kaltura.commands.session
{
	import com.kaltura.delegates.session.SessionImpersonateByKsDelegate;
	import com.kaltura.net.KalturaCall;

	/**
	 * Start an impersonated session with Kaltura's server.
	 * The result KS info contains the session key that you should pass to all services that requires a ticket.
	 * Type, expiry and privileges won't be changed if they're not set
	 * 
	 **/
	public class SessionImpersonateByKs extends KalturaCall
	{
		public var filterFields : String;
		
		/**
		 * @param session String
		 * @param type int
		 * @param expiry int
		 * @param privileges String
		 **/
		public function SessionImpersonateByKs( session : String,type : int=int.MIN_VALUE,expiry : int=int.MIN_VALUE,privileges : String = null )
		{
			service= 'session';
			action= 'impersonateByKs';

			var keyArr : Array = new Array();
			var valueArr : Array = new Array();
			var keyValArr : Array = new Array();
			keyArr.push('session');
			valueArr.push(session);
			keyArr.push('type');
			valueArr.push(type);
			keyArr.push('expiry');
			valueArr.push(expiry);
			keyArr.push('privileges');
			valueArr.push(privileges);
			applySchema(keyArr, valueArr);
		}

		override public function execute() : void
		{
			setRequestArgument('filterFields', filterFields);
			delegate = new SessionImpersonateByKsDelegate( this , config );
		}
	}
}
