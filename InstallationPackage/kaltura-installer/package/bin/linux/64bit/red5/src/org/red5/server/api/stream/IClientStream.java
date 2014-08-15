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

package org.red5.server.api.stream;

/**
 * A stream that is bound to a client.
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Steven Gong (steven.gong@gmail.com)
 */
public interface IClientStream extends IStream {

	public static final String MODE_READ = "read";

	public static final String MODE_RECORD = "record";

	public static final String MODE_APPEND = "append";

	public static final String MODE_LIVE = "live";
	
	public static final String MODE_PUBLISH = "publish";

	/**
	 * Get stream id allocated in a connection.
	 * 
	 * @return the stream id
	 */
	int getStreamId();

	/**
	 * Get connection containing the stream.
	 * 
	 * @return the connection object or <code>null</code> if the connection is no longer active
	 */
	IStreamCapableConnection getConnection();

	/**
	 * Set the buffer duration for this stream as requested by the client.
	 * 
	 * @param bufferTime duration in ms the client wants to buffer
	 */
	void setClientBufferDuration(int bufferTime);
	
	/**
	 * Get the buffer duration for this stream as requested by the client.
	 * 
	 * @return bufferTime duration in ms the client wants to buffer
	 */
	int getClientBufferDuration();	

	/**
	 * Returns the published stream name that this client is consuming.
	 * 
	 * @return stream name of stream being consumed
	 */
	String getBroadcastStreamPublishName();
	
}
