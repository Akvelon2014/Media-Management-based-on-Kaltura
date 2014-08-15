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

package org.red5.compatibility.flex.messaging.io;

import java.util.Collection;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;

import org.red5.io.amf3.IDataInput;
import org.red5.io.amf3.IDataOutput;
import org.red5.io.amf3.IExternalizable;

/**
 * Flex <code>ObjectProxy</code> compatibility class.
 * 
 * @see <a href="http://livedocs.adobe.com/flex/2/langref/mx/utils/ObjectProxy.html">Adobe Livedocs (external)</a>
 * @author The Red5 Project (red5@osflash.org)
 * @author Joachim Bauch (jojo@struktur.de)
 * @param <T> type
 * @param <V> value
 */
public class ObjectProxy<T, V> implements Map<T, V>, IExternalizable {

	private String uid;
	
	private Object type;
	
	/** The proxied object. */
	private Map<T, V> item;
	
	/** Create new empty proxy. */
	public ObjectProxy() {
		this(new HashMap<T, V>());
	}
	
	/**
	 * Create proxy for given object.
	 * 
	 * @param item object to proxy
	 */
	public ObjectProxy(Map<T, V> item) {
		this.item = new HashMap<T, V>(item);
	}

	/** {@inheritDoc} */
	@SuppressWarnings("unchecked")
	public void readExternal(IDataInput input) {
		item = (Map<T, V>) input.readObject();
	}

	/** {@inheritDoc} */
	public void writeExternal(IDataOutput output) {
		output.writeObject(item);
	}

	/**
	 * Return string representation of the proxied object.
	 * 
	 * @return string
	 */
	public String toString() {
		return item.toString();
	}

	public void clear() {
		item.clear();
	}

	/**
	 * Check if proxied object has a given property.
	 * 
	 * @param name name
	 * @return boolean
	 */
	public boolean containsKey(Object name) {
		return item.containsKey(name);
	}

	public boolean containsValue(Object value) {
		return item.containsValue(value);
	}

	public Set<Entry<T, V>> entrySet() {
		return Collections.unmodifiableSet(item.entrySet());
	}

	/**
	 * Return the value of a property.
	 * 
	 * @param name name
	 * @return value
	 */
	public V get(Object name) {
		return item.get(name);
	}

	public boolean isEmpty() {
		return item.isEmpty();
	}

	public Set<T> keySet() {
		return item.keySet();
	}

	/**
	 * Change a property of the proxied object.
	 * 
	 * @param name name
	 * @param value value
	 * @return old value
	 */
	public V put(T name, V value) {
		return item.put(name, value);
	}

	@SuppressWarnings({ "unchecked", "rawtypes" })
	public void putAll(Map values) {
		item.putAll(values);
	}

	/**
	 * Remove a property from the proxied object.
	 * 
	 * @param name name
	 * @return old value
	 */
	public V remove(Object name) {
		return item.remove(name);
	}

	public int size() {
		return item.size();
	}

	public Collection<V> values() {
		return Collections.unmodifiableCollection(item.values());
	}

	/**
	 * @return the uid
	 */
	public String getUid() {
		return uid;
	}

	/**
	 * @param uid the uid to set
	 */
	public void setUid(String uid) {
		this.uid = uid;
	}

	/**
	 * @return the type
	 */
	public Object getType() {
		return type;
	}

	/**
	 * @param type the type to set
	 */
	public void setType(Object type) {
		this.type = type;
	}
	
	// TODO: implement other ObjectProxy methods
	
}
