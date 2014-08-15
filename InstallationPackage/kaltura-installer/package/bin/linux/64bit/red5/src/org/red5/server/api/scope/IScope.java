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

import java.util.Collection;
import java.util.Map;
import java.util.Set;

import org.red5.server.api.IClient;
import org.red5.server.api.IConnection;
import org.red5.server.api.IContext;
import org.red5.server.api.service.IServiceHandlerProvider;
import org.red5.server.api.statistics.IScopeStatistics;
import org.springframework.core.io.support.ResourcePatternResolver;

/**
 * The scope object.
 * 
 * A stateful object shared between a group of clients connected to the same
 * <tt>context path</tt>. Scopes are arranged in hierarchical way, so its possible for
 * a scope to have a parent and children scopes. If a client connects to a scope then they are
 * also connected to its parent scope. The scope object is used to access
 * resources, shared object, streams, etc. That is, scope are general option for grouping things
 * in application.
 * 
 * The following are all names for scopes: application, room, place, lobby.
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Luke Hubbard (luke@codegent.com)
 */
public interface IScope extends IBasicScope, ResourcePatternResolver, IServiceHandlerProvider {

	/**
	 * Scope separator
	 */
	public static final String SEPARATOR = ":";

	/**
	 * Check to see if this scope has a child scope matching a given name.
	 * 
	 * @param name the name of the child scope
	 * @return <code>true</code> if a child scope exists, otherwise
	 *         <code>false</code>
	 */
	public boolean hasChildScope(String name);

	/**
	 * Checks whether scope has a child scope with given name and type
	 * 
	 * @param type Child scope type
	 * @param name Child scope name
	 * @return <code>true</code> if a child scope exists, otherwise
	 *         <code>false</code>
	 */
	public boolean hasChildScope(ScopeType type, String name);

	/**
	 * Creates child scope with name given and returns success value. Returns
	 * <code>true</code> on success, <code>false</code> if given scope
	 * already exists among children.
	 * 
	 * @param name New child scope name
	 * @return <code>true</code> if child scope was successfully creates,
	 *         <code>false</code> otherwise
	 */
	public boolean createChildScope(String name);

	/**
	 * Adds scope as a child scope. Returns <code>true</code> on success,
	 * <code>false</code> if given scope is already a child of current.
	 * 
	 * @param scope Scope given
	 * @return <code>true</code> if child scope was successfully added,
	 *         <code>false</code> otherwise
	 */
	public boolean addChildScope(IBasicScope scope);

	/**
	 * Removes scope from the children scope list.
	 * 
	 * @param scope Scope given
	 */
	public void removeChildScope(IBasicScope scope);

	/**
	 * Removes all the child scopes
	 */
	public void removeChildren();
	
	/**
	 * Get a set of the child scope names.
	 * 
	 * @return set containing child scope names
	 */
	public Set<String> getScopeNames();

	public Set<String> getBasicScopeNames(ScopeType type);

	/**
	 * Return the broadcast scope for a given name
	 * 
	 * @param name
	 * @return broadcast scope or null if not found
	 */
	public IBroadcastScope getBroadcastScope(String name);
	
	/**
	 * Get a child scope by type and name.
	 * 
	 * @param type     Child scope type
	 * @param name Name of the child scope
	 * @return the child scope, or null if no scope is found
	 */
	public IBasicScope getBasicScope(ScopeType type, String name);

	/**
	 * Return scope by name
	 * 
	 * @param name     Scope name
	 * @return         Scope with given name
	 */
	public IScope getScope(String name);

	/**
	 * Get a set of connected clients. You can get the connections by passing
	 * the scope to the clients {@link IClient#getConnections()} method.
	 * 
	 * @return Set containing all connected clients
	 * @see org.red5.server.api.IClient#getConnections(IScope)
	 */
	public Set<IClient> getClients();

	/**
	 * Get a connection iterator. You can call remove, and the connection will
	 * be closed.
	 * 
	 * @return Iterator holding all connections
	 */
	public Collection<Set<IConnection>> getConnections();

	/**
	 * Lookup connections.
	 * 
	 * @param client object
	 * @return Set of connection objects (readonly)
	 */
	public Set<IConnection> lookupConnections(IClient client);

	/**
	 * Returns scope context
	 * 
	 * @return	Scope context
	 */
	public IContext getContext();

	/**
	 * Checks whether scope has handler or not. 
	 * 
	 * @return <code>true</code> if scope has a handler, <code>false</code>
	 *         otherwise
	 */
	public boolean hasHandler();

	/**
	 * Return handler of the scope
	 * 
	 * @return	Scope handler
	 */
	public IScopeHandler getHandler();

	/**
	 * Return context path.
	 * 
	 * @return	Context path
	 */
	public String getContextPath();

	/**
	 * Adds given connection to the scope
	 * 
	 * @param conn Given connection
	 * @return <code>true</code> on success, <code>false</code> if given
	 *         connection already belongs to this scope
	 */
	public boolean connect(IConnection conn);

	/**
	 * Add given connection to the scope, overloaded for parameters pass case.
	 * @param conn             Given connection
	 * @param params           Parameters passed
	 * @return                 <code>true</code> on success, <code>false</code> if given
	 *                         connection already belongs to this scope
	 */
	public boolean connect(IConnection conn, Object[] params);

	/**
	 * Removes given connection from list of scope connections. This disconnects
	 * all clients of given connection from the scope.
	 * 
	 * @param conn Connection given
	 */
	public void disconnect(IConnection conn);

	/**
	 * Return statistics informations about the scope.
	 * 
	 * @return statistics
	 */
	public IScopeStatistics getStatistics();

	/**
	 * Set attribute by name
	 * 
	 * @param name
	 * @param value
	 * @return true if added, false if not added
	 */
	public boolean setAttribute(String name, Object value);
	
	/**
	 * Get attribute by name
	 * 
	 * @param name
	 * @return value for the given name in the attributes or null if not found
	 */
	public Object getAttribute(String name);
	
	/**
	 * Whether or not an attribute exists, keyed by the given name
	 * 
	 * @param name
	 * @return true if it exists, false otherwise
	 */
	public boolean hasAttribute(String name);

	/**
	 * Remove attribute by name
	 * 
	 * @param name
	 * @return true if removed, false otherwise
	 */
	public boolean removeAttribute(String name);
	
	/**
	 * Return attribute names
	 * 
	 * @return attribute names
	 */
	public Set<String> getAttributeNames();	
	
	/**
	 * Return scope attributes
	 * 
	 * @return attributes
	 */
	public Map<String, Object> getAttributes();

}
