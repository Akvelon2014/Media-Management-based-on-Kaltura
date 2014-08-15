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

package org.red5.io.mp3.impl;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.nio.ByteBuffer;
import java.nio.ByteOrder;
import java.nio.channels.FileChannel;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import java.util.concurrent.Semaphore;

import org.apache.mina.core.buffer.IoBuffer;
import org.jaudiotagger.audio.AudioFileIO;
import org.jaudiotagger.audio.mp3.MP3AudioHeader;
import org.jaudiotagger.audio.mp3.MP3File;
import org.jaudiotagger.tag.FieldKey;
import org.jaudiotagger.tag.TagException;
import org.jaudiotagger.tag.datatype.Artwork;
import org.jaudiotagger.tag.datatype.DataTypes;
import org.jaudiotagger.tag.id3.ID3v24Tag;
import org.jaudiotagger.tag.id3.framebody.FrameBodyAPIC;
import org.red5.io.IKeyFrameMetaCache;
import org.red5.io.IStreamableFile;
import org.red5.io.ITag;
import org.red5.io.ITagReader;
import org.red5.io.IoConstants;
import org.red5.io.amf.Output;
import org.red5.io.flv.IKeyFrameDataAnalyzer;
import org.red5.io.flv.impl.Tag;
import org.red5.io.object.Serializer;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * Read MP3 files
 */
public class MP3Reader implements ITagReader, IKeyFrameDataAnalyzer {

	/**
	 * Logger
	 */
	protected static Logger log = LoggerFactory.getLogger(MP3Reader.class);

	/**
	 * File
	 */
	private File file;

	/**
	 * File input stream
	 */
	private FileInputStream fis;

	/**
	 * File channel
	 */
	private FileChannel channel;

	/**
	 * Byte buffer
	 */
	private ByteBuffer buf = ByteBuffer.allocate(4).order(ByteOrder.BIG_ENDIAN);

	/**
	 * Last read tag object
	 */
	private ITag tag;

	/**
	 * Previous tag size
	 */
	private int prevSize;

	/**
	 * Current time
	 */
	private double currentTime;

	/**
	 * Frame metadata
	 */
	private KeyFrameMeta frameMeta;

	/**
	 * Positions and time map
	 */
	private HashMap<Long, Double> posTimeMap;

	private int dataRate;

	/**
	 * File duration
	 */
	private long duration;

	/**
	 * Frame cache
	 */
	static private IKeyFrameMetaCache frameCache;

	/**
	 * Holder for ID3 meta data
	 */
	private MetaData metaData;

	/**
	 * Container for metadata and any other tags that should
	 * be sent prior to media data.
	 */
	private LinkedList<ITag> firstTags = new LinkedList<ITag>();

	private long fileSize;

	private final Semaphore lock = new Semaphore(1, true);

	MP3Reader() {
		// Only used by the bean startup code to initialize the frame cache
	}

	/**
	 * Creates reader from file input stream
	 * 
	 * @param file file input
	 * @throws IOException 
	 */
	public MP3Reader(File file) throws IOException {
		this.file = file;
		// parse the id3 info
		try {
			MP3File mp3file = (MP3File) AudioFileIO.read(file);
			MP3AudioHeader audioHeader = (MP3AudioHeader) mp3file.getAudioHeader();
			if (audioHeader != null) {
				log.debug("Track length: {}", audioHeader.getTrackLength());
				log.debug("Sample rate: {}", audioHeader.getSampleRateAsNumber());
				log.debug("Channels: {}", audioHeader.getChannels());
				log.debug("Variable bit rate: {}", audioHeader.isVariableBitRate());
				log.debug("Track length (2): {}", audioHeader.getTrackLengthAsString());
				log.debug("Mpeg version: {}", audioHeader.getMpegVersion());
				log.debug("Mpeg layer: {}", audioHeader.getMpegLayer());
				log.debug("Original: {}", audioHeader.isOriginal());
				log.debug("Copyrighted: {}", audioHeader.isCopyrighted());
				log.debug("Private: {}", audioHeader.isPrivate());
				log.debug("Protected: {}", audioHeader.isProtected());
				log.debug("Bitrate: {}", audioHeader.getBitRate());
				log.debug("Encoding type: {}", audioHeader.getEncodingType());
				log.debug("Encoder: {}", audioHeader.getEncoder());
			}
			ID3v24Tag idTag = mp3file.getID3v2TagAsv24();
			if (idTag != null) {
				// create meta data holder
				metaData = new MetaData();
				metaData.setAlbum(idTag.getFirst(FieldKey.ALBUM));
				metaData.setArtist(idTag.getFirst(FieldKey.ARTIST));
				metaData.setComment(idTag.getFirst(FieldKey.COMMENT));
				metaData.setGenre(idTag.getFirst(FieldKey.GENRE));
				metaData.setSongName(idTag.getFirst(FieldKey.TITLE));
				metaData.setTrack(idTag.getFirst(FieldKey.TRACK));
				metaData.setYear(idTag.getFirst(FieldKey.YEAR));
				//send album image if included
				List<Artwork> tagFieldList = idTag.getArtworkList();
				if (tagFieldList == null || tagFieldList.isEmpty()) {
					log.debug("No cover art was found");
				} else {
					Artwork imageField = tagFieldList.get(0);
					log.debug("Picture type: {}", imageField.getPictureType());
					FrameBodyAPIC imageFrameBody = new FrameBodyAPIC();
					imageFrameBody.setImageData(imageField.getBinaryData());
					if (!imageFrameBody.isImageUrl()) {
						byte[] imageBuffer = (byte[]) imageFrameBody.getObjectValue(DataTypes.OBJ_PICTURE_DATA);
						//set the cover image on the metadata
						metaData.setCovr(imageBuffer);
						// Create tag for onImageData event
						IoBuffer buf = IoBuffer.allocate(imageBuffer.length);
						buf.setAutoExpand(true);
						Output out = new Output(buf);
						out.writeString("onImageData");
						Map<Object, Object> props = new HashMap<Object, Object>();
						props.put("trackid", 1);
						props.put("data", imageBuffer);
						out.writeMap(props, new Serializer());
						buf.flip();
						//Ugh i hate flash sometimes!!
						//Error #2095: flash.net.NetStream was unable to invoke callback onImageData.
						ITag result = new Tag(IoConstants.TYPE_METADATA, 0, buf.limit(), null, 0);
						result.setBody(buf);
						//add to first frames
						firstTags.add(result);
					}
				}
			} else {
				log.info("File did not contain ID3v2 data: {}", file.getName());
			}
			mp3file = null;
		} catch (TagException e) {
			log.error("MP3Reader (tag error) {}", e);
		} catch (Exception e) {
			log.error("MP3Reader {}", e);
		}
		fis = new FileInputStream(file);
		// grab file channel and map it to memory-mapped byte buffer in read-only mode
		channel = fis.getChannel();
		// get the total bytes / file size
		fileSize = channel.size();
		log.debug("File size: {}", fileSize);
		// analyze keyframes data
		analyzeKeyFrames();
		// create file metadata object
		firstTags.addFirst(createFileMeta());
		// MP3 header is length of 32 bits, that is, 4 bytes
		// read further if there's still data
		if ((fileSize - channel.position()) > 4) {
			// look for next frame
			searchNextFrame();
			// get current position
			long pos = channel.position();
			// Data in MP3 file goes header-data-header-data...header-data
			// Read header...
			MP3Header header = null;
			try {
				header = readHeader();
			} catch (Exception e) {
				log.warn("Exception reading initial header", e);
			}
			// set position
			channel.position(pos);
			// Check header
			if (header != null) {
				checkValidHeader(header);
			} else {
				throw new RuntimeException("No initial header found");
			}
		}
	}

	/**
	 * A MP3 stream never has video.
	 * 
	 * @return always returns <code>false</code>
	 */
	public boolean hasVideo() {
		return false;
	}

	public void setFrameCache(IKeyFrameMetaCache frameCache) {
		MP3Reader.frameCache = frameCache;
	}

	/**
	 * Check if the file can be played back with Flash. Supported sample rates
	 * are 44KHz, 22KHz, 11KHz and 5.5KHz
	 * 
	 * @param header
	 *            Header to check
	 */
	private void checkValidHeader(MP3Header header) {
		switch (header.getSampleRate()) {
			case 48000:
			case 44100:
			case 22050:
			case 11025:
			case 5513:
				break;
			default:
				throw new RuntimeException("Unsupported sample rate: " + header.getSampleRate());
		}
	}

	/**
	 * Creates file metadata object
	 * 
	 * @return Tag
	 */
	private ITag createFileMeta() {
		log.debug("createFileMeta");
		// create tag for onMetaData event
		IoBuffer in = IoBuffer.allocate(1024);
		in.setAutoExpand(true);
		Output out = new Output(in);
		out.writeString("onMetaData");
		Map<Object, Object> props = new HashMap<Object, Object>();
		props.put("duration", frameMeta.timestamps[frameMeta.timestamps.length - 1] / 1000.0);
		props.put("audiocodecid", IoConstants.FLAG_FORMAT_MP3);
		if (dataRate > 0) {
			props.put("audiodatarate", dataRate);
		}
		props.put("canSeekToEnd", true);
		//set id3 meta data if it exists
		if (metaData != null) {
			props.put("artist", metaData.getArtist());
			props.put("album", metaData.getAlbum());
			props.put("songName", metaData.getSongName());
			props.put("genre", metaData.getGenre());
			props.put("year", metaData.getYear());
			props.put("track", metaData.getTrack());
			props.put("comment", metaData.getComment());
			if (metaData.hasCoverImage()) {
				Map<Object, Object> covr = new HashMap<Object, Object>(1);
				covr.put("covr", new Object[] { metaData.getCovr() });
				props.put("tags", covr);
			}
			//clear meta for gc
			metaData = null;
		}
		out.writeMap(props, new Serializer());
		in.flip();

		ITag result = new Tag(IoConstants.TYPE_METADATA, 0, in.limit(), null, prevSize);
		result.setBody(in);
		return result;
	}

	/** 
	 * Search for next frame sync word. Sync word identifies valid frame.
	 */
	public void searchNextFrame() {
		log.debug("searchNextFrame");
		ByteBuffer in = ByteBuffer.allocate(1).order(ByteOrder.BIG_ENDIAN);
		try {
			while ((fileSize - channel.position()) > 1) {
				// read byte
				in.clear();
				channel.read(in);
				in.flip();
				// check byte
				int ch = in.get() & 0xff;
				if (ch != 0xff) {
					continue;
				}
				// read next byte
				in.clear();
				channel.read(in);
				in.flip();
				// check next byte
				if ((in.get() & 0xe0) == 0xe0) {
					// found it
					log.debug("Found frame");
					channel.position(channel.position() - 2);
					break;
				}
			}
		} catch (IOException e) {
			log.warn("Exception getting next frame", e);
		} finally {
			in.clear();
			in = null;
		}
	}

	/** {@inheritDoc} */
	public IStreamableFile getFile() {
		return null;
	}

	/** {@inheritDoc} */
	public int getOffset() {
		return 0;
	}

	/** {@inheritDoc} */
	public long getBytesRead() {
		try {
			return channel.position();
		} catch (IOException e) {
			log.warn("Exception getting bytes read", e);
		}
		return 0;
	}

	/** {@inheritDoc} */
	public long getDuration() {
		return duration;
	}

	/**
	 * Get the total readable bytes in a file or ByteBuffer.
	 * 
	 * @return Total readable bytes
	 */
	public long getTotalBytes() {
		return fileSize;
	}

	/** {@inheritDoc} */
	public boolean hasMoreTags() {
		log.debug("hasMoreTags");
		MP3Header header = null;
		try {
			header = readHeader();
		} catch (IOException e) {
			log.warn("Exception reading header", e);
		} catch (Exception e) {
			searchNextFrame();
		}
		//log.debug("Header: {}", header);
		if (header == null || header.frameSize() == 0) {
			// TODO find better solution how to deal with broken files
			return false;
		}
		try {
			if (channel.position() + header.frameSize() - 4 > fileSize) {
				// last frame is incomplete
				channel.position(fileSize);
				return false;
			}
			channel.position(channel.position() - 4);
		} catch (IOException e) {
			log.warn("Exception checking for more tags", e);
		}
		return true;
	}

	private MP3Header readHeader() throws Exception {
		log.debug("readHeader at {}", channel.position());
		buf.clear();
		channel.read(buf);
		buf.flip();
		return new MP3Header(buf.getInt());
	}

	/** {@inheritDoc} */
	public ITag readTag() {
		log.debug("readTag");
		try {
			lock.acquire();
			if (!firstTags.isEmpty()) {
				// Return first tags before media data
				return firstTags.removeFirst();
			}
			MP3Header header = readHeader();
			int frameSize = header.frameSize();
			if (frameSize == 0) {
				// TODO find better solution how to deal with broken files
				return null;
			}
			if (channel.position() + frameSize - 4 > fileSize) {
				// last frame is incomplete
				channel.position(fileSize);
				return null;
			}
			tag = new Tag(IoConstants.TYPE_AUDIO, (int) currentTime, frameSize + 1, null, prevSize);
			prevSize = frameSize + 1;
			currentTime += header.frameDuration();
			IoBuffer body = IoBuffer.allocate(tag.getBodySize());
			body.setAutoExpand(true);
			byte tagType = (IoConstants.FLAG_FORMAT_MP3 << 4) | (IoConstants.FLAG_SIZE_16_BIT << 1);
			switch (header.getSampleRate()) {
				case 48000:
					tagType |= IoConstants.FLAG_RATE_48_KHZ << 2;
					break;
				case 44100:
					tagType |= IoConstants.FLAG_RATE_44_KHZ << 2;
					break;
				case 22050:
					tagType |= IoConstants.FLAG_RATE_22_KHZ << 2;
					break;
				case 11025:
					tagType |= IoConstants.FLAG_RATE_11_KHZ << 2;
					break;
				default:
					tagType |= IoConstants.FLAG_RATE_5_5_KHZ << 2;
			}
			tagType |= (header.isStereo() ? IoConstants.FLAG_TYPE_STEREO : IoConstants.FLAG_TYPE_MONO);
			body.put(tagType);
			body.putInt(header.getData());

			ByteBuffer in = ByteBuffer.allocate(frameSize - 4).order(ByteOrder.BIG_ENDIAN);
			channel.read(in);
			in.flip();
			body.put(in);
			body.flip();

			tag.setBody(body);
		} catch (InterruptedException e) {
			log.warn("Exception acquiring lock", e);
		} catch (Exception e) {
			log.warn("Exception reading tag", e);
		} finally {
			lock.release();
		}
		return tag;
	}

	/** {@inheritDoc} */
	public void close() {
		if (posTimeMap != null) {
			posTimeMap.clear();
		}
		if (buf != null) {
			buf.clear();
			buf = null;
		}
		try {
			fis.close();
			channel.close();
		} catch (IOException e) {
			log.error("Exception on close", e);
		}
	}

	/** {@inheritDoc} */
	public void decodeHeader() {
	}

	/** {@inheritDoc} */
	public void position(long pos) {
		try {
			if (pos == Long.MAX_VALUE) {
				// seek at EOF
				channel.position(fileSize);
				currentTime = duration;
				return;
			}
			channel.position(pos);
			// advance to next frame
			searchNextFrame();
			Double time = posTimeMap.get(channel.position());
			if (time != null) {
				currentTime = time;
			} else {
				// Unknown frame position - this should never happen
				currentTime = 0;
			}
		} catch (IOException e) {
			log.warn("Exception setting position", e);
		}
	}

	/** {@inheritDoc} */
	public KeyFrameMeta analyzeKeyFrames() {
		log.debug("analyzeKeyFrames");
		if (frameMeta != null) {
			return frameMeta;
		}
		try {
			lock.acquire();
			// check for cached frame informations
			if (frameCache != null) {
				frameMeta = frameCache.loadKeyFrameMeta(file);
				if (frameMeta != null && frameMeta.duration > 0) {
					// Frame data loaded, create other mappings
					duration = frameMeta.duration;
					frameMeta.audioOnly = true;
					posTimeMap = new HashMap<Long, Double>();
					for (int i = 0; i < frameMeta.positions.length; i++) {
						posTimeMap.put(frameMeta.positions[i], (double) frameMeta.timestamps[i]);
					}
					return frameMeta;
				}
			}
			List<Long> positionList = new ArrayList<Long>();
			List<Double> timestampList = new ArrayList<Double>();
			dataRate = 0;
			long rate = 0;
			int count = 0;
			long origPos = channel.position();
			double time = 0;
			channel.position(0);
			searchNextFrame();
			while (hasMoreTags()) {
				MP3Header header = readHeader();
				if (header == null || header.frameSize() == 0) {
					// TODO find better solution how to deal with broken files...
					// See APPSERVER-62 for details
					break;
				}
				long pos = channel.position() - 4;
				if (pos + header.frameSize() > fileSize) {
					// last frame is incomplete
					break;
				}
				positionList.add(pos);
				timestampList.add(time);
				rate += header.getBitRate() / 1000;
				time += header.frameDuration();
				channel.position(pos + header.frameSize());
				count++;
			}
			// restore the pos
			channel.position(origPos);
			duration = (long) time;
			dataRate = (int) (rate / count);
			posTimeMap = new HashMap<Long, Double>();
			frameMeta = new KeyFrameMeta();
			frameMeta.duration = duration;
			frameMeta.positions = new long[positionList.size()];
			frameMeta.timestamps = new int[timestampList.size()];
			frameMeta.audioOnly = true;
			for (int i = 0; i < frameMeta.positions.length; i++) {
				frameMeta.positions[i] = positionList.get(i);
				frameMeta.timestamps[i] = timestampList.get(i).intValue();
				posTimeMap.put(positionList.get(i), timestampList.get(i));
			}
			if (frameCache != null) {
				frameCache.saveKeyFrameMeta(file, frameMeta);
			}
		} catch (InterruptedException e) {
			log.warn("Exception acquiring lock", e);
		} catch (Exception e) {
			log.warn("Exception analyzing frames", e);
		} finally {
			lock.release();
		}
		log.debug("Analysis complete");
		return frameMeta;
	}

	/**
	 * Simple holder for id3 meta data
	 */
	static class MetaData {
		String album = "";

		String artist = "";

		String genre = "";

		String songName = "";

		String track = "";

		String year = "";

		String comment = "";

		byte[] covr = null;

		public String getAlbum() {
			return album;
		}

		public void setAlbum(String album) {
			this.album = album;
		}

		public String getArtist() {
			return artist;
		}

		public void setArtist(String artist) {
			this.artist = artist;
		}

		public String getGenre() {
			return genre;
		}

		public void setGenre(String genre) {
			this.genre = genre;
		}

		public String getSongName() {
			return songName;
		}

		public void setSongName(String songName) {
			this.songName = songName;
		}

		public String getTrack() {
			return track;
		}

		public void setTrack(String track) {
			this.track = track;
		}

		public String getYear() {
			return year;
		}

		public void setYear(String year) {
			this.year = year;
		}

		public String getComment() {
			return comment;
		}

		public void setComment(String comment) {
			this.comment = comment;
		}

		public byte[] getCovr() {
			return covr;
		}

		public void setCovr(byte[] covr) {
			this.covr = covr;
			log.debug("Cover image array size: {}", covr.length);
		}

		public boolean hasCoverImage() {
			return covr != null;
		}

	}

}
