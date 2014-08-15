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

package org.red5.server.net.servlet;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import javax.servlet.http.HttpServletRequest;

public class ServletUtils {

	/**
	 * Default value is 2048.
	 */
	public static final int DEFAULT_BUFFER_SIZE = 2048;

	/**
	 * Copies information from the input stream to the output stream using a
	 * default buffer size of 2048 bytes.
	 * @param input input
	 * @param output output
	 * 
	 * @throws java.io.IOException on error
	 */
	public static void copy(InputStream input, OutputStream output) throws IOException {
		copy(input, output, DEFAULT_BUFFER_SIZE);
	}

	/**
	 * Copies information from the input stream to the output stream using the
	 * specified buffer size
	 * 
	 * @param input input
	 * @param bufferSize buffer size
	 * @param output output 
	 * @throws java.io.IOException on error
	 */
	public static void copy(InputStream input, OutputStream output, int bufferSize) throws IOException {
		byte[] buf = new byte[bufferSize];
		int bytesRead = input.read(buf);
		while (bytesRead != -1) {
			output.write(buf, 0, bytesRead);
			bytesRead = input.read(buf);
		}
		output.flush();
	}

	/**
	 * Copies information between specified streams and then closes both of the
	 * streams.
	 * 
	 * @param output output
	 * @param input input
	 * @throws java.io.IOException on error
	 */
	public static void copyThenClose(InputStream input, OutputStream output) throws IOException {
		copy(input, output);
		input.close();
		output.close();
	}

	/**
	 * @param input input stream
	 * @return a byte[] containing the information contained in the specified
	 *          InputStream.
	 * @throws java.io.IOException on error
	 */
	public static byte[] getBytes(InputStream input) throws IOException {
		ByteArrayOutputStream result = new ByteArrayOutputStream();
		copy(input, result);
		result.close();
		return result.toByteArray();
	}

	/**
	 * Return all remote addresses that were involved in the passed request.
	 * 
	 * @param request request
	 * @return remote addresses
	 */
	public static List<String> getRemoteAddresses(HttpServletRequest request) {
		List<String> addresses = new ArrayList<String>();
		addresses.add(request.getRemoteHost());
		if (!request.getRemoteAddr().equals(request.getRemoteHost())) {
			// Store both remote host and remote address 
			addresses.add(request.getRemoteAddr());
		}
		final String forwardedFor = request.getHeader("X-Forwarded-For");
		if (forwardedFor != null) {
			// Also store address this request was forwarded for.
			final String[] parts = forwardedFor.split(",");
			for (String part : parts) {
				addresses.add(part);
			}
		}
		final String httpVia = request.getHeader("Via");
		if (httpVia != null) {
			// Also store address this request was forwarded for.
			final String[] parts = httpVia.split(",");
			for (String part : parts) {
				addresses.add(part);
			}
		}
		return Collections.unmodifiableList(addresses);
	}

}
