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

import org.red5.server.api.stream.IAudioStreamCodec;
import org.red5.server.api.stream.IStreamCodecInfo;
import org.red5.server.api.stream.IVideoStreamCodec;

public class StreamCodecInfo implements IStreamCodecInfo {
	
	/**
	 * Audio support flag
	 */
	private boolean audio;

	/**
	 * Video support flag
	 */
	private boolean video;

	/**
	 * Audio codec
	 */
	private IAudioStreamCodec audioCodec;

	/**
	 * Video codec
	 */
	private IVideoStreamCodec videoCodec;	
	
	/** {@inheritDoc} */
	public boolean hasAudio() {
		return audio;
	}

	/**
	 * New value for audio support
	 *
	 * @param value Audio support
	 */
	public void setHasAudio(boolean value) {
		this.audio = value;
	}
	
	/** {@inheritDoc} */
	public String getAudioCodecName() {
		if (audioCodec == null) {
			return null;
		}
		return audioCodec.getName();
	}	
	
	/** {@inheritDoc} */
	public IAudioStreamCodec getAudioCodec() {
		return audioCodec;
	}

	/**
	 * Setter for audio codec
	 *
	 * @param codec Audio codec
	 */
	public void setAudioCodec(IAudioStreamCodec codec) {
		this.audioCodec = codec;
	}	

	/** {@inheritDoc} */
	public boolean hasVideo() {
		return video;
	}

	/**
	 * New value for video support
	 *
	 * @param value Video support
	 */
	public void setHasVideo(boolean value) {
		this.video = value;
	}

	/** {@inheritDoc} */
	public String getVideoCodecName() {
		if (videoCodec == null) {
			return null;
		}
		return videoCodec.getName();
	}

	/** {@inheritDoc} */
	public IVideoStreamCodec getVideoCodec() {
		return videoCodec;
	}

	/**
	 * Setter for video codec
	 *
	 * @param codec  Video codec
	 */
	public void setVideoCodec(IVideoStreamCodec codec) {
		this.videoCodec = codec;
	}
	
}
