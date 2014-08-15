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

package org.red5.server.stream;

import junit.framework.Assert;

import org.junit.Test;
import org.red5.server.stream.codec.AudioCodec;
import org.red5.server.stream.codec.VideoCodec;

public class CodecEnumTest {

	@Test
	public void testAudio() {
		Assert.assertTrue(AudioCodec.MP3.getId() == 2);
		Assert.assertTrue(AudioCodec.SPEEX.getId() == 11);
		Assert.assertTrue(AudioCodec.MP3_8K.getId() == 14);
	}

	@Test
	public void testVideo() {
		Assert.assertTrue(VideoCodec.AVC.getId() == 7);
	}
	
}
