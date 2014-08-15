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

import java.nio.ByteBuffer;
import java.nio.charset.Charset;

import org.apache.mina.core.buffer.IoBuffer;
import org.slf4j.Logger;

/**
 * Miscellaneous I/O utility methods
 * 
 * @see <a href="http://www.cs.utsa.edu/~wagner/laws/Abytes.html">Unsigned bytes in Java</a>
 */
public class IOUtils {

	/**
	 * UTF-8 is used
	 */
	public final static Charset CHARSET = Charset.forName("UTF-8");

	/**
	 * Writes integer in reverse order
	 * @param out         Data buffer to fill
	 * @param value       Integer
	 */
	public final static void writeReverseInt(IoBuffer out, int value) {
		byte[] bytes = new byte[4];
		IoBuffer rev = IoBuffer.allocate(4);
		rev.putInt(value);
		rev.flip();
		bytes[3] = rev.get();
		bytes[2] = rev.get();
		bytes[1] = rev.get();
		bytes[0] = rev.get();
		out.put(bytes);
		rev.free();
		rev = null;
	}

	/**
	 * Writes medium integer
	 * @param out           Output buffer
	 * @param value         Integer to write
	 */
	public final static void writeMediumInt(IoBuffer out, int value) {
		byte[] bytes = new byte[3];
		bytes[0] = (byte) ((value >>> 16) & 0xff);
		bytes[1] = (byte) ((value >>> 8) & 0xff);
		bytes[2] = (byte) (value & 0xff);
		out.put(bytes);
	}

	/**
	 * Writes medium integer
	 * @param out           Output buffer
	 * @param value         Integer to write
	 */
	public final static void writeMediumInt(ByteBuffer out, int value) {
		out.put((byte) ((value >>> 16) & 0xff));
		out.put((byte) ((value >>> 8) & 0xff));
		out.put((byte) (value & 0xff));
	}

	/**
	 * Writes extended medium integer (equivalent to a regular integer whose
	 * most significant byte has been moved to its end, past its least significant
	 * byte)
	 * @param out           Output buffer
	 * @param value         Integer to write
	 */
	public final static void writeExtendedMediumInt(IoBuffer out, int value) {
		value = ((value & 0xff000000) >> 24) | (value << 8);
		out.putInt(value);
	}

	/**
	 * Writes extended medium integer (equivalent to a regular integer whose
	 * most significant byte has been moved to its end, past its least significant
	 * byte)
	 * @param out           Output buffer
	 * @param value         Integer to write
	 */
	public final static void writeExtendedMediumInt(ByteBuffer out, int value) {
		value = ((value & 0xff000000) >> 24) | (value << 8);
		out.putInt(value);
	}

	/**
	 * Writes an unsigned byte value to the supplied buffer.
	 * 
	 * @param out           Output buffer
	 * @param value			Byte to write
	 */
	public final static void writeUnsignedByte(ByteBuffer out, byte value) {
		out.put((byte) (value & 0xff));
	}

	/**
	 * Reads unsigned medium integer
	 * @param in              Unsigned medium int source
	 * @return                int value
	 */
	public final static int readUnsignedMediumInt(IoBuffer in) {
		return ((in.get() & 0xff) << 16) + ((in.get() & 0xff) << 8) + ((in.get() & 0xff));
	}

	/**
	 * Reads medium int
	 * @param in       Source
	 * @return         int value
	 */
	public final static int readMediumInt(ByteBuffer in) {
		return ((in.get() & 0x000000ff) << 16) + ((in.get() & 0x000000ff) << 8) + ((in.get() & 0x000000ff));
	}

	/**
	 * Reads medium int
	 * @param in       Source
	 * @return         int value
	 */
	public final static int readMediumInt(IoBuffer in) {
		return ((in.get() & 0x000000ff) << 16) + ((in.get() & 0x000000ff) << 8) + ((in.get() & 0x000000ff));
	}

	/**
	 * Reads extended medium int
	 * @param in       Source
	 * @return         int value
	 */
	public final static int readExtendedMediumInt(IoBuffer in) {
		int result = in.getInt();
		result = (result >>> 8) | ((result & 0x000000ff) << 24);
		return result;
	}

	/**
	 * Reads extended medium int
	 * @param in       Source
	 * @return         int value
	 */
	public final static int readExtendedMediumInt(ByteBuffer in) {
		int result = in.getInt();
		result = (result >>> 8) | ((result & 0x000000ff) << 24);
		return result;
	}

	/**
	 * Reads reverse int
	 * @param in       Source
	 * @return         int
	 */
	public final static int readReverseInt(IoBuffer in) {
		byte[] bytes = new byte[4];
		in.get(bytes);
		int val = 0;
		val += bytes[3] * 256 * 256 * 256;
		val += bytes[2] * 256 * 256;
		val += bytes[1] * 256;
		val += bytes[0];
		return val;
	}

	/**
	 * Format debug message
	 * @param log          Logger
	 * @param msg          Message
	 * @param buf          Byte buffer to debug
	 */
	public final static void debug(Logger log, String msg, IoBuffer buf) {
		if (log.isDebugEnabled()) {
			log.debug(msg);
			log.debug("Size: {}", buf.remaining());
			log.debug("Data:\n{}", HexDump.formatHexDump(buf.getHexDump()));
			log.debug("\n{}\n", toString(buf));
		}
	}

	/**
	 * String representation of byte buffer
	 * @param buf           Byte buffer
	 * @return              String representation
	 */
	public final static String toString(IoBuffer buf) {
		int pos = buf.position();
		int limit = buf.limit();
		final java.nio.ByteBuffer strBuf = buf.buf();
		final String string = CHARSET.decode(strBuf).toString();
		buf.position(pos);
		buf.limit(limit);
		return string;
	}

	public static void main(String[] args) {
		ByteBuffer buf = ByteBuffer.allocate(4);
		IOUtils.writeExtendedMediumInt(buf, 1234567890);
		buf.flip();
		System.out.println("Result: " + IOUtils.readExtendedMediumInt(buf));
	}

}
