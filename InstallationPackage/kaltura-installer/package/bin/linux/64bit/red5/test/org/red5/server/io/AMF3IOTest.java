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

package org.red5.server.io;

import java.util.List;
import java.util.Vector;

import junit.framework.Assert;

import org.apache.mina.core.buffer.IoBuffer;
import org.junit.Test;
import org.red5.io.amf3.ByteArray;
import org.red5.io.amf3.Input;
import org.red5.io.amf3.Output;
import org.red5.io.utils.HexDump;
import org.red5.server.net.rtmp.message.StreamAction;

/*
 * @author The Red5 Project (red5@osflash.org)
 * @author Luke Hubbard, Codegent Ltd (luke@codegent.com)
 * @author Art Clarke, Vlideshow Inc (aclarke@vlideshow.com)
*/
public class AMF3IOTest extends AbstractIOTest {

	IoBuffer buf;

	/** {@inheritDoc} */
	@Override
	void dumpOutput() {
		buf.flip();
		System.err.println(HexDump.formatHexDump(buf.getHexDump()));
	}

	/** {@inheritDoc} */
	@Override
	void resetOutput() {
		setupIO();
	}

	/** {@inheritDoc} */
	@Override
	void setupIO() {
		buf = IoBuffer.allocate(0); // 1kb
		buf.setAutoExpand(true);
		buf.setAutoShrink(true);
		in = new Input(buf);
		out = new Output(buf);
	}

	@Test
	public void testEnum() {
		log.debug("Testing Enum");
		serializer.serialize(out, StreamAction.CONNECT);
		dumpOutput();
		Object object = deserializer.deserialize(in, StreamAction.class);
		log.debug("Enums - {} {}", object.getClass().getName(), StreamAction.CONNECT.getClass().getName());
		Assert.assertEquals(object.getClass().getName(), StreamAction.CONNECT.getClass().getName());
		resetOutput();
	}

	@Test
	public void testByteArray() {
		log.debug("Testing ByteArray");
		// just some ones and such
		ByteArray baIn = new ByteArray();
		baIn.writeBytes(new byte[] { (byte) 0, (byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42,
				(byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42, (byte) 0x1, (byte) 0x42, (byte) 0x99 });
		serializer.serialize(out, baIn);
		dumpOutput();
		ByteArray baOut = deserializer.deserialize(in, ByteArray.class);
		Assert.assertNotNull(baOut);
		Assert.assertEquals(baIn.length(), baOut.length());
		for (int i = 0; i < baOut.length(); i++) {
			System.err.println("Byte: " + baOut.readByte());
		}
		resetOutput();
	}

	@Test
	public void testVectorRoundTrip() {
		log.debug("Testing Vector on a round trip");
		Vector<String> vIn = new Vector<String>();
		vIn.add("This is my vector and her name is Sally");
		serializer.serialize(out, vIn);
		dumpOutput();
		// test fails without enforcing amf3 here
		((org.red5.io.amf3.Input) in).enforceAMF3();
		Vector<String> vOut = deserializer.deserialize(in, Vector.class);
		Assert.assertNotNull(vOut);
		Assert.assertEquals(vIn.size(), vOut.size());
		for (int i = 0; i < vOut.size(); i++) {
			System.err.println("Element: " + vOut.elementAt(i));
		}
		resetOutput();
	}	
	
	@Test
	public void testVectorIntInput() {
		log.debug("Testing Vector<int>");
		//0D090000000002000007D07FFFFFFF80000000
		byte[] v = new byte[] { (byte) 0x0D, (byte) 0x09, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x02, (byte) 0x00, (byte) 0x00, (byte) 0x07, (byte) 0xD0,
				(byte) 0x7F, (byte) 0xFF, (byte) 0xFF, (byte) 0xFF, (byte) 0x80, (byte) 0x00, (byte) 0x00, (byte) 0x00 };

		in = new Input(IoBuffer.wrap(v));

		List<Object> vectorOut = deserializer.deserialize(in, null);
		//[2, 2000, 2147483647, -2147483648]
		Assert.assertNotNull(vectorOut);
		Assert.assertEquals(vectorOut.size(), 4);
		for (int i = 0; i < vectorOut.size(); i++) {
			System.err.println("Vector: " + vectorOut.get(i));
		}
		resetOutput();
	}

	@Test
	public void testVectorUIntInput() {
		log.debug("Testing Vector<uint>");
		//0E090000000002000007D0FFFFFFFF00000000
		byte[] v = new byte[] { (byte) 0x0E, (byte) 0x09, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x02, (byte) 0x00, (byte) 0x00, (byte) 0x07, (byte) 0xD0,
				(byte) 0xFF, (byte) 0xFF, (byte) 0xFF, (byte) 0xFF, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00 };

		in = new Input(IoBuffer.wrap(v));

		List<Object> vectorOut = deserializer.deserialize(in, null);
		//[2, 2000, 4294967295, 0]
		Assert.assertNotNull(vectorOut);
		Assert.assertEquals(vectorOut.size(), 4);
		for (int i = 0; i < vectorOut.size(); i++) {
			System.err.println("Vector: " + vectorOut.get(i));
		}
		resetOutput();
	}

	@Test
	public void testVectorNumberInput() {
		log.debug("Testing Vector<Number>");
		//0F0F003FF199999999999ABFF199999999999A7FEFFFFFFFFFFFFF0000000000000001FFF8000000000000FFF00000000000007FF0000000000000
		byte[] v = new byte[] { (byte) 0x0F, (byte) 0x0F, (byte) 0x00, (byte) 0x3F, (byte) 0xF1, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x9A,
				(byte) 0xBF, (byte) 0xF1, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x99, (byte) 0x9A, (byte) 0x7F, (byte) 0xEF, (byte) 0xFF, (byte) 0xFF,
				(byte) 0xFF, (byte) 0xFF, (byte) 0xFF, (byte) 0xFF, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x01,
				(byte) 0xFF, (byte) 0xF8, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0xFF, (byte) 0xF0, (byte) 0x00, (byte) 0x00,
				(byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x7F, (byte) 0xF0, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00 };

		in = new Input(IoBuffer.wrap(v));
		((org.red5.io.amf3.Input) in).enforceAMF3();
		List<Double> vectorOut = deserializer.deserialize(in, null);
		//[1.1, -1.1, 1.7976931348623157E308, 4.9E-324, NaN, -Infinity, Infinity]
		Assert.assertNotNull(vectorOut);
		Assert.assertEquals(vectorOut.size(), 7);
		for (int i = 0; i < vectorOut.size(); i++) {
			System.err.println("Vector: " + vectorOut.get(i));
		}
		resetOutput();
	}

	@Test
	public void testVectorMixedInput() {
		log.debug("Testing Vector<Object>");
		//100700010607666f6f010a13256f72672e726564352e74673742e466f6f33000403
		//[foo, null, org.red5.test.Foo3[foo=0]] // Foo3 is a class instance
		byte[] v2 = new byte[] { (byte) 0x10, (byte) 0x07, (byte) 0x00, (byte) 0x01, (byte) 0x06, (byte) 0x07, (byte) 0x66, (byte) 0x6f, (byte) 0x6f, (byte) 0x01, (byte) 0x0a,
				(byte) 0x13, (byte) 0x25, (byte) 0x6f, (byte) 0x72, (byte) 0x67, (byte) 0x2e, (byte) 0x72, (byte) 0x65, (byte) 0x64, (byte) 0x35, (byte) 0x2e, (byte) 0x74,
				(byte) 0x65, (byte) 0x73, (byte) 0x74, (byte) 0x2e, (byte) 0x46, (byte) 0x6f, (byte) 0x6f, (byte) 0x33, (byte) 0x00, (byte) 0x04, (byte) 0x03 };

		in = new Input(IoBuffer.wrap(v2));
		((org.red5.io.amf3.Input) in).enforceAMF3();
		List<Object> vectorOut = deserializer.deserialize(in, null);
		Assert.assertNotNull(vectorOut);
		Assert.assertEquals(vectorOut.size(), 3);
		for (int i = 0; i < vectorOut.size(); i++) {
			System.err.println("Vector: " + vectorOut.get(i));
		}
		resetOutput();
	}

	@SuppressWarnings("unused")
	@Test
	public void testVectorStringInput() {
		log.debug("Testing Vector<String>");
		//[Paul, ]
		byte[] v = new byte[] { (byte) 0x10, (byte) 0x05, (byte) 0x00, (byte) 0x01, (byte) 0x06, (byte) 0x09, (byte) 0x50, (byte) 0x61, (byte) 0x75, (byte) 0x6c, (byte) 0x06,
				(byte) 0x01 };
		//[Paul, Paul]
		byte[] v1 = new byte[] { (byte) 0x10, (byte) 0x05, (byte) 0x00, (byte) 0x01, (byte) 0x06, (byte) 0x09, (byte) 0x50, (byte) 0x61, (byte) 0x75, (byte) 0x6c, (byte) 0x06,
				(byte) 0x00 };
		//[Paul, Paul, Paul]
		byte[] v2 = new byte[] { (byte) 0x10, (byte) 0x07, (byte) 0x00, (byte) 0x01, (byte) 0x06, (byte) 0x09, (byte) 0x50, (byte) 0x61, (byte) 0x75, (byte) 0x6c, (byte) 0x06,
				(byte) 0x00, (byte) 0x06, (byte) 0x00 };
		//[Paul, Tawnya]
		byte[] v3 = new byte[] { (byte) 0x10, (byte) 0x05, (byte) 0x00, (byte) 0x01, (byte) 0x06, (byte) 0x09, (byte) 0x50, (byte) 0x61, (byte) 0x75, (byte) 0x6c, (byte) 0x06,
				(byte) 0x0d, (byte) 0x54, (byte) 0x61, (byte) 0x77, (byte) 0x6e, (byte) 0x79, (byte) 0x61 };

		//[1.0, 3.0, aaa, 5.0, aaa, aaa, 5.0, bb, bb]
		byte[] v4 = new byte[] { (byte) 0x10, (byte) 0x13, (byte) 0x00, (byte) 0x01, (byte) 0x04, (byte) 0x01, (byte) 0x04, (byte) 0x03, (byte) 0x06, (byte) 0x07, (byte) 0x61,
				(byte) 0x61, (byte) 0x61, (byte) 0x04, (byte) 0x05, (byte) 0x06, (byte) 0x00, (byte) 0x06, (byte) 0x00, (byte) 0x04, (byte) 0x05, (byte) 0x06, (byte) 0x05,
				(byte) 0x62, (byte) 0x62, (byte) 0x06, (byte) 0x02 };
		//[1.0, 3.0, aaa, [1, 2]]
		byte[] v5 = new byte[] { (byte) 0x10, (byte) 0x09, (byte) 0x00, (byte) 0x01, (byte) 0x04, (byte) 0x01, (byte) 0x04, (byte) 0x03, (byte) 0x06, (byte) 0x07, (byte) 0x61,
				(byte) 0x61, (byte) 0x61, (byte) 0x0d, (byte) 0x05, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x00, (byte) 0x01, (byte) 0x00, (byte) 0x00, (byte) 0x00,
				(byte) 0x02 };

		in = new Input(IoBuffer.wrap(v5));
		((org.red5.io.amf3.Input) in).enforceAMF3();
		List<Object> vectorOut = deserializer.deserialize(in, null);
		//[Paul, ]
		Assert.assertNotNull(vectorOut);
		//Assert.assertEquals(vectorOut.size(), 4);
		for (int i = 0; i < vectorOut.size(); i++) {
			System.err.println("Vector: " + vectorOut.get(i));
		}
		resetOutput();
	}

}
