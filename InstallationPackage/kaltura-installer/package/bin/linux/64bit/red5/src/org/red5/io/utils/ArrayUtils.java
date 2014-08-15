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

package org.red5.io.utils;

import java.lang.reflect.Array;
import java.util.Collection;

public final class ArrayUtils {

	@SuppressWarnings({ "rawtypes" })
	public static Object toArray(Class<?> type, Collection collection) {
		if (byte.class.isAssignableFrom(type)) {
			return toByteArray(collection);
		} else if (short.class.isAssignableFrom(type)) {
			return toShortArray(collection);
		} else if (int.class.isAssignableFrom(type)) {
			return toIntegerArray(collection);
		} else if (long.class.isAssignableFrom(type)) {
			return toLongArray(collection);
		} else if (float.class.isAssignableFrom(type)) {
			return toFloatArray(collection);
		} else if (double.class.isAssignableFrom(type)) {
			return toDoubleArray(collection);
		} else if (boolean.class.isAssignableFrom(type)) {
			return toBooleanArray(collection);
		} else if (char.class.isAssignableFrom(type)) {
			return toCharacterArray(collection);
		} else {
			return toObjectArray(type, collection);
		}
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toByteArray(Collection collection) {
		byte[] ba = new byte[collection.size()];

		int i = 0;
		for (Object o : collection) {
			byte b = ((Byte) o).byteValue();
			ba[i++] = b;
		}

		return ba;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toShortArray(Collection collection) {
		short[] sa = new short[collection.size()];

		int i = 0;
		for (Object o : collection) {
			short s = ((Short) o).shortValue();
			sa[i++] = s;
		}

		return sa;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toIntegerArray(Collection collection) {
		int[] ia = new int[collection.size()];

		int i = 0;
		for (Object o : collection) {
			int j = ((Integer) o).intValue();
			ia[i++] = j;
		}

		return ia;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toLongArray(Collection collection) {
		long[] la = new long[collection.size()];

		int i = 0;
		for (Object o : collection) {
			long l = ((Long) o).longValue();
			la[i++] = l;
		}

		return la;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toFloatArray(Collection collection) {
		float[] fa = new float[collection.size()];

		int i = 0;
		for (Object o : collection) {
			float f = ((Float) o).floatValue();
			fa[i++] = f;
		}

		return fa;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toDoubleArray(Collection collection) {
		double[] da = new double[collection.size()];

		int i = 0;
		for (Object o : collection) {
			double d;
			if (o instanceof Integer) {
				d = (Integer) o;
			} else {
				d = ((Double) o).doubleValue();
			}
			da[i++] = d;
		}

		return da;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toBooleanArray(Collection collection) {
		boolean[] ba = new boolean[collection.size()];

		int i = 0;
		for (Object o : collection) {
			boolean b = ((Boolean) o).booleanValue();
			ba[i++] = b;
		}

		return ba;
	}

	@SuppressWarnings({ "rawtypes" })
	private static Object toCharacterArray(Collection collection) {
		char[] ca = new char[collection.size()];

		int i = 0;
		for (Object o : collection) {
			char c = ((Character) o).charValue();
			ca[i++] = c;
		}

		return ca;
	}

	@SuppressWarnings({ "unchecked", "rawtypes" })
	private static Object toObjectArray(Class<?> type, Collection collection) {
		return collection.toArray((Object[]) Array.newInstance(type, collection.size()));
	}

	public static Class<?> getGenericType(Class<?> nested) {
		if (nested == byte.class) {
			nested = Byte.class;
		} else if (nested == short.class) {
			nested = Short.class;
		} else if (nested == int.class) {
			nested = Integer.class;
		} else if (nested == long.class) {
			nested = Long.class;
		} else if (nested == float.class) {
			nested = Float.class;
		} else if (nested == Double.class) {
			nested = Double.class;
		} else if (nested == boolean.class) {
			nested = Boolean.class;
		} else if (nested == Character.class) {
			nested = Character.class;
		}
		return nested;
	}
}
