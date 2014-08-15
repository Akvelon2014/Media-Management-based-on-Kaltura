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

package org.red5.server.net.rtmp.codec;

import org.apache.mina.core.session.IoSession;
import org.apache.mina.filter.codec.ProtocolCodecFactory;
import org.apache.mina.filter.codec.ProtocolDecoder;
import org.apache.mina.filter.codec.ProtocolEncoder;
import org.red5.io.object.Deserializer;
import org.red5.io.object.Serializer;

/**
 * RTMP codec factory creates RTMP encoders/decoders.
 */
public class RTMPCodecFactory implements ProtocolCodecFactory {

    /**
     * Deserializer.
     */
    protected Deserializer deserializer;

    /**
     * Serializer.
     */
    protected Serializer serializer;

    /**
     * Mina protocol decoder for RTMP.
     */
    private RTMPMinaProtocolDecoder decoder;

    /**
     * Mina protocol encoder for RTMP.
     */
    private RTMPMinaProtocolEncoder encoder;

    /**
     * Initialization
     */
    public void init() {
		decoder = new RTMPMinaProtocolDecoder();
		decoder.setDeserializer(deserializer);
		encoder = new RTMPMinaProtocolEncoder();
		encoder.setSerializer(serializer);
	}

	/**
     * Setter for deserializer.
     *
     * @param deserializer  Deserializer
     */
    public void setDeserializer(Deserializer deserializer) {
		this.deserializer = deserializer;
	}

	/**
     * Setter for serializer.
     *
     * @param serializer  Serializer
     */
    public void setSerializer(Serializer serializer) {
		this.serializer = serializer;
	}

	/** {@inheritDoc} */
    public ProtocolDecoder getDecoder(IoSession session) {
		return decoder;
	}

	/** {@inheritDoc} */
    public ProtocolEncoder getEncoder(IoSession session) {
		return encoder;
	}

    /**
     * Returns the RTMP decoder.
     * 
     * @return decoder
     */
    public RTMPProtocolDecoder getRTMPDecoder() {
		return decoder.getDecoder();
	}

	/**
	 * Returns the RTMP encoder.
	 * 
	 * @return encoder
	 */
    public RTMPProtocolEncoder getRTMPEncoder() {
		return encoder.getEncoder();
	}    
    
}
