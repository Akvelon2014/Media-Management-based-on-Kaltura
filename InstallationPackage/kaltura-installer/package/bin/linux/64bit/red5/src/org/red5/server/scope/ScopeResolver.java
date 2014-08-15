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

package org.red5.server.scope;

import java.util.Arrays;

import org.apache.commons.lang3.StringUtils;
import org.red5.server.api.scope.IGlobalScope;
import org.red5.server.api.scope.IScope;
import org.red5.server.api.scope.IScopeResolver;
import org.red5.server.api.scope.ScopeType;
import org.red5.server.exception.ScopeNotFoundException;
import org.red5.server.exception.ScopeShuttingDownException;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * Resolves scopes from path
 */
public class ScopeResolver implements IScopeResolver {

	protected static Logger log = LoggerFactory.getLogger(ScopeResolver.class);

	/**
	 *  Global scope
	 */
	protected IGlobalScope globalScope;

	/**
	 * Getter for global scope
	 * @return      Global scope
	 */
	public IGlobalScope getGlobalScope() {
		return globalScope;
	}

	/**
	 * Setter for global scope
	 * @param root        Global scope
	 */
	public void setGlobalScope(IGlobalScope root) {
		this.globalScope = root;
	}

	/**
	 * Return scope associated with given path
	 *
	 * @param path        Scope path
	 * @return            Scope object
	 */
	public IScope resolveScope(String path) {
		// start from global scope
		return resolveScope(globalScope, path);
	}

	/**
	 * Return scope associated with given path from given root scope.
	 *
	 * @param root        Scope to start from
	 * @param path        Scope path
	 * @return            Scope object
	 */
	public IScope resolveScope(IScope root, String path) {
		log.debug("resolveScope - root: {} path: {}", root, path);
		// start from root scope
		IScope scope = root;
		// if there's no path return root scope (e.i. root path scope)
		if (StringUtils.isNotEmpty(path)) {
			// Split path to parts
			final String[] parts = path.split("/");
			log.debug("Split path: {}", Arrays.toString(parts));
			// Iterate thru them, skip empty parts
			for (String child : parts) {
				if (StringUtils.isEmpty(child)) {
					// skip empty path elements
					continue;
				}
				if (scope.hasChildScope(child)) {
					scope = scope.getScope(child);
				} else if (!scope.equals(root)) {
					// no need for sync here, scope.children is concurrent
					if (scope.createChildScope(child)) {
						scope = scope.getScope(child);
					}
				}
				// if the scope is still equal to root then the room was not found
				if (scope == root) {
					throw new ScopeNotFoundException(scope, child);
				}
				if (scope.getType().equals(ScopeType.APPLICATION) && ((WebScope) scope).isShuttingDown()) {
					throw new ScopeShuttingDownException(scope);
				}
			}
		}
		return scope;
	}

}
