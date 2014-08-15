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

package org.red5.io.object;

import java.util.Collection;
import java.util.Date;
import java.util.Map;
import java.util.Vector;

import org.red5.io.amf3.ByteArray;
import org.w3c.dom.Document;

/**
 * Output interface which defines contract methods to be implemented
 *
 * @author The Red5 Project (red5@osflash.org)
 * @author Luke Hubbard, Codegent Ltd (luke@codegent.com)
 */
public interface Output {

	boolean supportsDataType(byte type);

	void putString(String string);

	// Basic Data Types
	/**
	 * Write number
	 * @param num       Number
	 */
	void writeNumber(Number num);

	/**
	 * Write boolean
	 * @param bol       Boolean
	 */
	void writeBoolean(Boolean bol);

	/**
	 * Write string
	 * @param string    String
	 */
	void writeString(String string);

	/**
	 * Write date
	 * @param date      Date
	 */
	void writeDate(Date date);

	void writeNull();

	/**
	 * Write array.
	 * @param array     	Array to write.
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeArray(Collection<?> array, Serializer serializer);

	/**
	 * Write array.
	 * @param array     	Array to write.
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeArray(Object[] array, Serializer serializer);

	/**
	 * Write primitive array.
	 * @param array     	Array to write.
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeArray(Object array, Serializer serializer);

	/**
	 * Write map.
	 *
	 * @param map			Map to write
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeMap(Map<Object, Object> map, Serializer serializer);

	/**
	 * Write array as map.
	 *
	 * @param array			Array to write
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeMap(Collection<?> array, Serializer serializer);

	/**
	 * Write object.
	 *
	 * @param object		Object to write
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeObject(Object object, Serializer serializer);

	/**
	 * Write map as object.
	 *
	 * @param map			Map to write
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeObject(Map<Object, Object> map, Serializer serializer);

	/**
	 * Write recordset.
	 *
	 * @param recordset		Recordset to write.
	 * @param serializer	Serializer to use for subobjects.
	 */
	void writeRecordSet(RecordSet recordset, Serializer serializer);

	/**
	 * Write XML object
	 * @param xml      XML document
	 */
	void writeXML(Document xml);

	/**
	 * Write ByteArray object (AMF3 only).
	 * 
	 * @param array		object to write
	 */
	void writeByteArray(ByteArray array);

	/**
	 * Write a Vector<int>.
	 * 
	 * @param vector
	 */
	void writeVectorInt(Vector<Integer> vector);

	/**
	 * Write a Vector<uint>.
	 * 
	 * @param vector
	 */
	void writeVectorUInt(Vector<Long> vector);

	/**
	 * Write a Vector<Number>.
	 * 
	 * @param vector
	 */
	void writeVectorNumber(Vector<Double> vector);

	/**
	 * Write a Vector<Object>.
	 * 
	 * @param vector
	 */
	void writeVectorObject(Vector<Object> vector);

	/**
	 * Write reference to complex data type
	 * @param obj   Referenced object
	 */
	void writeReference(Object obj);

	/**
	 * Whether object is custom
	 *
	 * @param custom           Object
	 * @return                 true if object is of user type, false otherwise
	 */
	boolean isCustom(Object custom);

	/**
	 * Write custom (user) object
	 * @param custom     Custom data type object
	 */
	void writeCustom(Object custom);

	/**
	 * Clear references
	 */
	void clearReferences();
}
