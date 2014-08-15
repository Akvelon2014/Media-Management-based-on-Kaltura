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

package org.red5.server.stream.codec;

import org.apache.mina.core.buffer.IoBuffer;
import org.red5.logging.Red5LoggerFactory;
import org.red5.server.api.stream.IAudioStreamCodec;
import org.slf4j.Logger;

/**
 * Red5 audio codec for the AAC audio format.
 *
 * Stores the decoder configuration
 * 
 * @author Paul Gregoire (mondain@gmail.com) 
 * @author Wittawas Nakkasem (vittee@hotmail.com)
 * @author Vladimir Hmelyoff (vlhm@splitmedialabs.com)
 */
public class AACAudio implements IAudioStreamCodec {

	private static Logger log = Red5LoggerFactory.getLogger(AACAudio.class);

	public static final int[] AAC_SAMPLERATES = { 96000, 88200, 64000, 48000, 44100, 32000, 24000, 22050, 16000, 12000, 11025, 8000, 7350 };

	/**
	 * AAC audio codec constant
	 */
	static final String CODEC_NAME = "AAC";

	/**
	 * Block of data (AAC DecoderConfigurationRecord)
	 */
	private byte[] blockDataAACDCR;

	/** Constructs a new AACAudio */
	public AACAudio() {
		this.reset();
	}

	/** {@inheritDoc} */
	public String getName() {
		return CODEC_NAME;
	}

	/** {@inheritDoc} */
	public void reset() {
		blockDataAACDCR = null;
	}

	/** {@inheritDoc} */
	public boolean canHandleData(IoBuffer data) {
		if (data.limit() == 0) {
			// Empty buffer
			return false;
		}
		byte first = data.get();
		boolean result = (((first & 0xf0) >> 4) == AudioCodec.AAC.getId());
		data.rewind();
		return result;
	}

	/** {@inheritDoc} */
	public boolean addData(IoBuffer data) {
		int dataLength = data.limit();
		if (dataLength > 1) {
			//ensure we are at the beginning
			data.rewind();
			byte frameType = data.get();
			log.trace("Frame type: {}", frameType);
			byte header = data.get();
			//go back to beginning
			data.rewind();
			//If we don't have the AACDecoderConfigurationRecord stored...
			if (blockDataAACDCR == null) {
				if ((((frameType & 0xF0) >> 4) == AudioCodec.AAC.getId()) && (header == 0)) {
					//go back to beginning
					data.rewind();
					blockDataAACDCR = new byte[dataLength];
					data.get(blockDataAACDCR);
					//go back to beginning
					data.rewind();
				}
			}
		}
		return true;
	}

	/** {@inheritDoc} */
	public IoBuffer getDecoderConfiguration() {
		if (blockDataAACDCR == null) {
			return null;
		}
		IoBuffer result = IoBuffer.allocate(4);
		result.setAutoExpand(true);
		result.put(blockDataAACDCR);
		result.rewind();
		return result;
	}

	@SuppressWarnings("unused")
	private long sample2TC(long time, int sampleRate) {
		return (time * 1000L / sampleRate);
	}

	//private final byte[] getAACSpecificConfig() {		
	//	byte[] b = new byte[] { 
	//			(byte) (0x10 | /*((profile > 2) ? 2 : profile << 3) | */((sampleRateIndex >> 1) & 0x03)),
	//			(byte) (((sampleRateIndex & 0x01) << 7) | ((channels & 0x0F) << 3))
	//		};
	//	log.debug("SpecificAudioConfig {}", HexDump.toHexString(b));
	//	return b;	
	//}    
}
