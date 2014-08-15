/*
 * RED5 Open Source Flash Server - http://code.google.com/p/red5/
 * 
 * Copyright 2006-2012 by respective authors (see below). All rights reserved.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package org.red5.server.api.scope;

import org.red5.server.api.IClient;
import org.red5.server.api.IConnection;
import org.red5.server.api.event.IEventHandler;
import org.red5.server.api.service.IServiceCall;

/**
 * The scope handler controls actions performed against a scope object, and also
 * is notified of all events.
 * 
 * Gives fine grained control over what actions can be performed with the can*
 * methods. Allows for detailed reporting on what is happening within the scope
 * with the on* methods. This is the core interface users implement to create
 * applications.
 * 
 * The thread local connection is always available via the Red5 object within
 * these methods
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Luke Hubbard (luke@codegent.com)
 */
public interface IScopeHandler extends IEventHandler {

	/**
	 * Called when a scope is created for the first time.
	 * 
	 * @param scope
	 * 			the new scope object
	 * @return <code>true</code> to allow, <code>false</code> to deny
	 */
	boolean start(IScope scope);

	/**
	 * Called just before a scope is disposed.
     * @param scope         Scope that id disposed
     */
	void stop(IScope scope);

	/**
	 * Called just before every connection to a scope. You can pass additional
	 * params from client using <code>NetConnection.connect</code> method (see
	 * below).
	 * 
	 * @param conn
	 * 			Connection object
	 * @param params
	 *            List of params passed from client via
	 *            <code>NetConnection.connect</code> method. All parameters
	 *            but the first one passed to <code>NetConnection.connect</code>
	 *            method are available as params array.
	 * 
	 * 
	 * @return <code>true</code> to allow, <code>false</code> to deny
     * @param scope           Scope object
	 */
	boolean connect(IConnection conn, IScope scope, Object[] params);

	/**
	 * Called just after the a connection is disconnected.
	 * 
	 * @param conn
	 * 			Connection object
	 * @param scope
	 * 			Scope object
	 */
	void disconnect(IConnection conn, IScope scope);

	/**
	 * Called just before a child scope is added.
	 * 
	 * @param scope
	 * 			Scope that will be added
	 * @return <code>true</code> to allow, <code>false</code> to deny
	 */
	boolean addChildScope(IBasicScope scope);

	/**
	 * Called just after a child scope has been removed.
	 * 
	 * @param scope
	 * 			Scope that has been removed
	 */
	void removeChildScope(IBasicScope scope);

	/**
	 * Called just before a client enters the scope.
	 * 
	 * @param client
	 * 			Client object
	 * @return <code>true</code> to allow, <code>false</code> to deny
	 *         connection
     * @param scope      Scope that is joined by client
	 */
	boolean join(IClient client, IScope scope);

	/**
	 * Called just after the client leaves the scope.
	 * 
	 * @param client
	 * 			Client object
	 * @param scope
	 * 			Scope object
	 */
	void leave(IClient client, IScope scope);

	/**
	 * Called when a service is called.
	 * 
	 * @param conn
	 * 			The connection object
	 * @param call
	 * 			The call object.
	 * 
	 * @return <code>true</code> to allow, <code>false</code> to deny
	 */
	boolean serviceCall(IConnection conn, IServiceCall call);

}
