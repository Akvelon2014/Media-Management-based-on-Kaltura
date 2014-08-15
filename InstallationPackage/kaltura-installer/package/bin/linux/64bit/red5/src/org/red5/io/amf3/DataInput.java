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

package org.red5.io.amf3;

import java.nio.ByteBuffer;
import java.nio.ByteOrder;
import java.nio.charset.Charset;

import org.apache.mina.core.buffer.IoBuffer;
import org.red5.io.amf.AMF;
import org.red5.io.object.Deserializer;

/**
 * Implementation of the IDataInput interface. Can be used to load an
 * IExternalizable object.
 *  
 * @author The Red5 Project (red5@osflash.org)
 * @author Joachim Bauch (jojo@struktur.de)
 * 
 */
public class DataInput implements IDataInput {

	/** The input stream. */
	private Input input;
	
	/** The deserializer to use. */
	private Deserializer deserializer;
	
	/** Raw data of input source. */
	private IoBuffer buffer;
	
	/**
	 * Create a new DataInput.
	 * 
	 * @param input			input to use
	 * @param deserializer	the deserializer to use
	 */
	protected DataInput(Input input, Deserializer deserializer) {
		this.input = input;
		this.deserializer = deserializer;
		buffer = input.getBuffer();
	}

	/** {@inheritDoc} */
	public ByteOrder getEndian() {
		return buffer.order();
	}

	/** {@inheritDoc} */
	public void setEndian(ByteOrder endian) {
		buffer.order(endian);
	}
	
	/** {@inheritDoc} */
	public boolean readBoolean() {
		return (buffer.get() != 0);
	}

	/** {@inheritDoc} */
	public byte readByte() {
		return buffer.get();
	}

	/** {@inheritDoc} */
	public void readBytes(byte[] bytes) {
		buffer.get(bytes);
	}

	/** {@inheritDoc} */
	public void readBytes(byte[] bytes, int offset) {
		buffer.get(bytes, offset, bytes.length - offset);
	}

	/** {@inheritDoc} */
	public void readBytes(byte[] bytes, int offset, int length) {
		buffer.get(bytes, offset, length);
	}

	/** {@inheritDoc} */
	public double readDouble() {
		return buffer.getDouble();
	}

	/** {@inheritDoc} */
	public float readFloat() {
		return buffer.getFloat();
	}

	/** {@inheritDoc} */
	public int readInt() {
		return buffer.getInt();
	}

	/** {@inheritDoc} */
	public String readMultiByte(int length, String charSet) {
		final Charset cs = Charset.forName(charSet);
		int limit = buffer.limit();
		final ByteBuffer strBuf = buffer.buf();
		strBuf.limit(strBuf.position() + length);
		final String string = cs.decode(strBuf).toString();
		buffer.limit(limit); // Reset the limit
		return string;
	}

	/** {@inheritDoc} */
	public Object readObject() {
		return deserializer.deserialize(input, Object.class);
	}

	/** {@inheritDoc} */
	public short readShort() {
		return buffer.getShort();
	}

	/** {@inheritDoc} */
	public int readUnsignedByte() {
		return buffer.getUnsigned();
	}

	/** {@inheritDoc} */
	public long readUnsignedInt() {
		return buffer.getUnsignedInt();
	}

	/** {@inheritDoc} */
	public int readUnsignedShort() {
		return buffer.getShort() & 0xffff; //buffer.getUnsignedShort();
	}

	/** {@inheritDoc} */
	public String readUTF() {
		int length = buffer.getShort() & 0xffff; //buffer.getUnsignedShort();
		return readUTFBytes(length);
	}

	/** {@inheritDoc} */
	public String readUTFBytes(int length) {
		int limit = buffer.limit();
		final ByteBuffer strBuf = buffer.buf();
		strBuf.limit(strBuf.position() + length);
		final String string = AMF.CHARSET.decode(strBuf).toString();
		buffer.limit(limit); // Reset the limit
		return string;
	}

}
