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

import java.io.IOException;
import java.util.HashMap;
import java.util.Map;
import java.util.Set;

import org.apache.commons.lang3.StringUtils;
import org.red5.io.utils.ObjectMap;
import org.red5.server.BaseConnection;
import org.red5.server.api.IConnection;
import org.red5.server.api.IContext;
import org.red5.server.api.Red5;
import org.red5.server.api.scope.IBroadcastScope;
import org.red5.server.api.scope.IScope;
import org.red5.server.api.stream.IBroadcastStream;
import org.red5.server.api.stream.IClientBroadcastStream;
import org.red5.server.api.stream.IClientStream;
import org.red5.server.api.stream.IPlayItem;
import org.red5.server.api.stream.IPlaylistSubscriberStream;
import org.red5.server.api.stream.ISingleItemSubscriberStream;
import org.red5.server.api.stream.IStreamCapableConnection;
import org.red5.server.api.stream.IStreamPlaybackSecurity;
import org.red5.server.api.stream.IStreamPublishSecurity;
import org.red5.server.api.stream.IStreamSecurityService;
import org.red5.server.api.stream.IStreamService;
import org.red5.server.api.stream.ISubscriberStream;
import org.red5.server.api.stream.OperationNotSupportedException;
import org.red5.server.api.stream.support.DynamicPlayItem;
import org.red5.server.api.stream.support.SimplePlayItem;
import org.red5.server.net.rtmp.BaseRTMPHandler;
import org.red5.server.net.rtmp.Channel;
import org.red5.server.net.rtmp.RTMPConnection;
import org.red5.server.net.rtmp.status.Status;
import org.red5.server.net.rtmp.status.StatusCodes;
import org.red5.server.util.ScopeUtils;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

/**
 * Stream service
 */
public class StreamService implements IStreamService {

	private static Logger log = LoggerFactory.getLogger(StreamService.class);

	/**
	 * Use to determine playback type.
	 */
	private ThreadLocal<Boolean> simplePlayback = new ThreadLocal<Boolean>() {
		@Override
		protected Boolean initialValue() {
			return Boolean.TRUE;
		}
	};

	/** {@inheritDoc} */
	public int createStream() {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			return ((IStreamCapableConnection) conn).reserveStreamId();
		} else {
			return -1;
		}
	}

	/** {@inheritDoc} */
	public void initStream(int streamId) {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			((IStreamCapableConnection) conn).reserveStreamId(streamId);
			IClientStream stream = ((IStreamCapableConnection) conn).getStreamById(getCurrentStreamId());
			if (stream != null) {
				if (stream instanceof IClientBroadcastStream) {
					IClientBroadcastStream bs = (IClientBroadcastStream) stream;
					IBroadcastScope bsScope = getBroadcastScope(conn.getScope(), bs.getPublishedName());
					if (bsScope != null && conn instanceof BaseConnection) {
						((BaseConnection) conn).unregisterBasicScope(bsScope);
					}
				}
				stream.close();
			}
			((IStreamCapableConnection) conn).deleteStreamById(getCurrentStreamId());
		} else {
			log.warn("ERROR in intiStream, connection is not stream capable");
		}
	}

	/**
	 * Close stream
	 */
	public void closeStream() {
		closeStream(Red5.getConnectionLocal(), getCurrentStreamId());
	}

	/**
	 * Close stream.
	 * This method can close both IClientBroadcastStream (coming from Flash Player to Red5)
	 * and ISubscriberStream (from Red5 to Flash Player).
	 * Corresponding application handlers (streamSubscriberClose, etc.) are called as if close was initiated by Flash Player.
	 * 
	 * It is recommended to remember stream id in application handlers, ex.:
	 * <pre>
	 * public void streamBroadcastStart(IBroadcastStream stream) {
	 * 	super.streamBroadcastStart(stream);
	 * 	if (stream instanceof IClientBroadcastStream) {
	 * 		int publishedStreamId = ((ClientBroadcastStream)stream).getStreamId();
	 * 		Red5.getConnectionLocal().setAttribute(PUBLISHED_STREAM_ID_ATTRIBUTE, publishedStreamId);
	 * 	}
	 * }
	 * </pre>
	 * <pre>
	 * public void streamPlaylistItemPlay(IPlaylistSubscriberStream stream, IPlayItem item, boolean isLive) {
	 * 	super.streamPlaylistItemPlay(stream, item, isLive);
	 * 	Red5.getConnectionLocal().setAttribute(WATCHED_STREAM_ID_ATTRIBUTE, stream.getStreamId());
	 * }
	 * </pre>
	 * When stream is closed, corresponding NetStream status will be sent to stream provider / consumers.
	 * Implementation is based on Red5's StreamService.close()
	 * 
	 * @param connection client connection
	 * @param streamId stream ID (number: 1,2,...)
	 */
	public void closeStream(IConnection connection, int streamId) {
		if (connection instanceof IStreamCapableConnection) {
			IStreamCapableConnection scConnection = (IStreamCapableConnection) connection;
			IClientStream stream = scConnection.getStreamById(streamId);
			if (stream != null) {
				if (stream instanceof IClientBroadcastStream) {
					// this is a broadcasting stream (from Flash Player to Red5)
					IClientBroadcastStream bs = (IClientBroadcastStream) stream;
					IBroadcastScope bsScope = connection.getScope().getBroadcastScope(bs.getPublishedName());
					if (bsScope != null && connection instanceof BaseConnection) {
						((BaseConnection) connection).unregisterBasicScope(bsScope);
					}
				}
				stream.close();
				scConnection.deleteStreamById(streamId);
				// in case of broadcasting stream, status is sent automatically by Red5
				if (!(stream instanceof IClientBroadcastStream)) {
					StreamService.sendNetStreamStatus(connection, StatusCodes.NS_PLAY_STOP, "Stream closed by server", stream.getName(), Status.STATUS, streamId);
				}
			} else {
				log.info("Stream not found: streamId={}, connection={}", streamId, connection);
			}
		} else {
			log.warn("Connection is not instance of IStreamCapableConnection: {}", connection);
		}
	}

	/** {@inheritDoc} */
	public void releaseStream(String streamName) {
		// XXX: what to do here?
	}

	/** {@inheritDoc} */
	public void deleteStream(int streamId) {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			deleteStream(streamConn, streamId);
		}
	}

	/** {@inheritDoc} */
	public void deleteStream(IStreamCapableConnection conn, int streamId) {
		IClientStream stream = conn.getStreamById(streamId);
		if (stream != null) {
			if (stream instanceof IClientBroadcastStream) {
				IClientBroadcastStream bs = (IClientBroadcastStream) stream;
				IBroadcastScope bsScope = getBroadcastScope(conn.getScope(), bs.getPublishedName());
				if (bsScope != null && conn instanceof BaseConnection) {
					((BaseConnection) conn).unregisterBasicScope(bsScope);
				}
			}
			stream.close();
		}
		conn.unreserveStreamId(streamId);
	}

	/** {@inheritDoc} */
	public void pauseRaw(Boolean pausePlayback, int position) {
		log.trace("pauseRaw - pausePlayback:{} position:{}", pausePlayback, position);
		pause(pausePlayback, position);
	}

	/**
	 * Pause at given position. Required as "pausePlayback" can be "null" if no flag is passed by the
	 * client
	 * @param pausePlayback         Pause playback or not
	 * @param position              Pause position
	 */
	public void pause(Boolean pausePlayback, int position) {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			IClientStream stream = streamConn.getStreamById(streamId);
			if (stream != null && stream instanceof ISubscriberStream) {
				ISubscriberStream subscriberStream = (ISubscriberStream) stream;
				// pausePlayback can be "null" if "pause" is called without any parameters from flash
				if (pausePlayback == null) {
					pausePlayback = !subscriberStream.isPaused();
				}
				if (pausePlayback) {
					subscriberStream.pause(position);
				} else {
					subscriberStream.resume(position);
				}
			}
		}
	}

	/**
	 * Plays back a stream based on the supplied name, from the specified position for the given length of time.
	 * 
	 * @param name - The name of a recorded file, or the identifier for live data. If 
	 * @param start - The start time, in seconds. Allowed values are -2, -1, 0, or a positive number. 
	 * The default value is -2, which looks for a live stream, then a recorded stream, and if it finds neither, 
	 * opens a live stream. If -1, plays only a live stream. If 0 or a positive number, plays a recorded stream, 
	 * beginning start seconds in.
	 * @param length - The duration of the playback, in seconds. Allowed values are -1, 0, or a positive number. 
	 * The default value is -1, which plays a live or recorded stream until it ends. If 0, plays a single frame 
	 * that is start seconds from the beginning of a recorded stream. If a positive number, plays a live or recorded 
	 * stream for length seconds.
	 * @param reset - Whether to clear a playlist. The default value is 1 or true, which clears any previous play 
	 * calls and plays name immediately. If 0 or false, adds the stream to a playlist. If 2, maintains the playlist 
	 * and returns all stream messages at once, rather than at intervals. If 3, clears the playlist and returns all 
	 * stream messages at once.
	 */
	public void play(String name, int start, int length, Object reset) {
		if (reset instanceof Boolean) {
			play(name, start, length, ((Boolean) reset).booleanValue());
		} else {
			if (reset instanceof Integer) {
				int value = (Integer) reset;
				switch (value) {
					case 0:
						//adds the stream to a playlist
						IStreamCapableConnection streamConn = (IStreamCapableConnection) Red5.getConnectionLocal();
						IPlaylistSubscriberStream playlistStream = (IPlaylistSubscriberStream) streamConn.getStreamById(getCurrentStreamId());
						IPlayItem item = SimplePlayItem.build(name);
						playlistStream.addItem(item);
						play(name, start, length, false);
						break;
					case 2:
						//maintains the playlist and returns all stream messages at once, rather than at intervals

						break;
					case 3:
						//clears the playlist and returns all stream messages at once

						break;
					default:
						//clears any previous play calls and plays name immediately
						play(name, start, length, true);
				}
			} else {
				play(name, start, length);
			}
		}
	}

	/** {@inheritDoc} */
	public void play(String name, int start, int length, boolean flushPlaylist) {
		log.debug("Play called - name: {} start: {} length: {} flush playlist: {}", new Object[] { name, start, length, flushPlaylist });
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IScope scope = conn.getScope();
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			if (StringUtils.isEmpty(name)) {
				sendNSFailed(streamConn, StatusCodes.NS_FAILED, "The stream name may not be empty.", name, streamId);
				return;
			}
			IStreamSecurityService security = (IStreamSecurityService) ScopeUtils.getScopeService(scope, IStreamSecurityService.class);
			if (security != null) {
				Set<IStreamPlaybackSecurity> handlers = security.getStreamPlaybackSecurity();
				for (IStreamPlaybackSecurity handler : handlers) {
					if (!handler.isPlaybackAllowed(scope, name, start, length, flushPlaylist)) {
						sendNSFailed(streamConn, StatusCodes.NS_FAILED, "You are not allowed to play the stream.", name, streamId);
						return;
					}
				}
			}
			IClientStream stream = streamConn.getStreamById(streamId);
			boolean created = false;
			if (stream == null) {
				if (streamId <= 0) {
					streamId = streamConn.reserveStreamId();
				}
				stream = streamConn.newPlaylistSubscriberStream(streamId);
				stream.start();
				created = true;
			}
			if (stream != null && stream instanceof ISubscriberStream) {
				ISubscriberStream subscriberStream = (ISubscriberStream) stream;
				IPlayItem item = simplePlayback.get() ? SimplePlayItem.build(name, start, length) : DynamicPlayItem.build(name, start, length);
				if (subscriberStream instanceof IPlaylistSubscriberStream) {
					IPlaylistSubscriberStream playlistStream = (IPlaylistSubscriberStream) subscriberStream;
					if (flushPlaylist) {
						playlistStream.removeAllItems();
					}
					playlistStream.addItem(item);
				} else if (subscriberStream instanceof ISingleItemSubscriberStream) {
					ISingleItemSubscriberStream singleStream = (ISingleItemSubscriberStream) subscriberStream;
					singleStream.setPlayItem(item);
				} else {
					// not supported by this stream service
					log.warn("Stream instance type: {} is not supported", subscriberStream.getClass().getName());
					return;
				}
				try {
					subscriberStream.play();
				} catch (IOException err) {
					if (created) {
						stream.close();
						streamConn.deleteStreamById(streamId);
					}
					sendNSFailed(streamConn, StatusCodes.NS_FAILED, err.getMessage(), name, streamId);
				}
			}
		} else {
			log.debug("Connection was not stream capable");
		}
	}

	/** {@inheritDoc} */
	public void play(String name, int start, int length) {
		play(name, start, length, true);
	}

	/** {@inheritDoc} */
	public void play(String name, int start) {
		play(name, start, -1000, true);
	}

	/** {@inheritDoc} */
	public void play(String name) {
		play(name, -2000, -1000, true);
	}

	/** {@inheritDoc} */
	public void play(Boolean dontStop) {
		log.debug("Play called. Dont stop param: {}", dontStop);
		if (!dontStop) {
			IConnection conn = Red5.getConnectionLocal();
			if (conn instanceof IStreamCapableConnection) {
				IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
				int streamId = getCurrentStreamId();
				IClientStream stream = streamConn.getStreamById(streamId);
				if (stream != null) {
					stream.stop();
				}
			}
		}
	}

	/**
	 * Dynamic streaming play method. This is a convenience method.
	 * 
	 * @param oldStreamName
	 * @param start
	 * @param transition
	 * @param length
	 * @param offset
	 * @param streamName
	 */
	public void play2(String oldStreamName, int start, String transition, int length, double offset, String streamName) {
		Map<String, Object> playOptions = new HashMap<String, Object>();
		playOptions.put("oldStreamName", oldStreamName);
		playOptions.put("streamName", streamName);
		playOptions.put("start", start);
		playOptions.put("len", length);
		playOptions.put("offset", offset);
		play2(playOptions);
	}

	/**
	 * Dynamic streaming play method. This is a convenience method.
	 * 
	 * @param params
	 */
	@SuppressWarnings("rawtypes")
	public void play2(ObjectMap params) {
		log.debug("play2 options: {}", params);
		Map<String, Object> playOptions = new HashMap<String, Object>();
		for (Object key : params.keySet()) {
			String k = key.toString();
			log.trace("Parameter: {}", k);
			playOptions.put(k, params.get(k));
		}
		play2(playOptions);
	}

	/**
	 * Dynamic streaming play method.
	 * 
	 * The following properties are supported on the play options:
	 * <pre>
		streamName: String. The name of the stream to play or the new stream to switch to.
		oldStreamName: String. The name of the initial stream that needs to be switched out. This is not needed and ignored 
		               when play2 is used for just playing the stream and not switching to a new stream.
		start: Number. The start time of the new stream to play, just as supported by the existing play API. and it has the 
		               same defaults. This is ignored when the method is called for switching (in other words, the transition 
		               is either NetStreamPlayTransition.SWITCH or NetStreamPlayTransitions.SWAP)
		len: Number. The duration of the playback, just as supported by the existing play API and has the same defaults.
		transition: String. The transition mode for the playback command. It could be one of the following:
							NetStreamPlayTransitions.RESET
							NetStreamPlayTransitions.APPEND
							NetStreamPlayTransitions.SWITCH
							NetStreamPlayTransitions.SWAP
		</pre>
		NetStreamPlayTransitions:
		<pre>					
			APPEND : String = "append" - Adds the stream to a playlist and begins playback with the first stream.
	 		APPEND_AND_WAIT : String = "appendAndWait" - Builds a playlist without starting to play it from the first stream.
	 		RESET : String = "reset" - Clears any previous play calls and plays the specified stream immediately.
	 		RESUME : String = "resume" - Requests data from the new connection starting from the point at which the previous connection ended.
	 		STOP : String = "stop" - Stops playing the streams in a playlist.
	 		SWAP : String = "swap" - Replaces a content stream with a different content stream and maintains the rest of the playlist.
	 		SWITCH : String = "switch" - Switches from playing one stream to another stream, typically with streams of the same content.			
	   </pre>
	   @see <a href="http://www.adobe.com/devnet/flashmediaserver/articles/dynstream_actionscript.html">ActionScript guide to dynamic streaming</a>
	   @see <a href="http://www.adobe.com/devnet/flashmediaserver/articles/dynstream_advanced_pt1.html">Dynamic streaming in Flash Media Server - Part 1: Overview of the new capabilities</a>
	   @see <a href="http://help.adobe.com/en_US/FlashPlatform/reference/actionscript/3/flash/net/NetStreamPlayTransitions.html">NetStreamPlayTransitions</a>
	 */
	public void play2(Map<String, ?> playOptions) {
		log.debug("play2 options: {}", playOptions.toString());
		/* { streamName=streams/new.flv, oldStreamName=streams/old.flv, 
			start=0, len=-1, offset=12.195, transition=switch } */
		// get the transition type
		String transition = (String) playOptions.get("transition");
		String streamName = (String) playOptions.get("streamName");
		String oldStreamName = (String) playOptions.get("oldStreamName");
		// now initiate new playback
		int start = (Integer) playOptions.get("start");
		int length = (Integer) playOptions.get("len");
		// get the stream id
		int streamId = getCurrentStreamId();
		// get the clients connection
		IConnection conn = Red5.getConnectionLocal();
		if (conn != null && conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			if ("stop".equals(transition)) {
				play(Boolean.FALSE);
			} else if ("reset".equals(transition)) {
				// just reset the currently playing stream
				play(streamName);
			} else if ("switch".equals(transition)) {
				try {
					// set the playback type
					simplePlayback.set(Boolean.FALSE);
					// send the "start" of transition
					sendNSStatus(conn, StatusCodes.NS_PLAY_TRANSITION, String.format("Transitioning from %s to %s.", oldStreamName, streamName), streamName, streamId);
					// support offset?
					//playOptions.get("offset")
					play(streamName, start, length);
				} finally {
					// clean up
					simplePlayback.remove();
				}
			} else if ("append".equals(transition) || "appendAndWait".equals(transition)) {
				IPlaylistSubscriberStream playlistStream = (IPlaylistSubscriberStream) streamConn.getStreamById(streamId);
				IPlayItem item = SimplePlayItem.build(streamName);
				playlistStream.addItem(item);
				if ("append".equals(transition)) {
					play(streamName, start, length, false);
				}
			} else if ("swap".equals(transition)) {
				IPlaylistSubscriberStream playlistStream = (IPlaylistSubscriberStream) streamConn.getStreamById(streamId);
				IPlayItem item = SimplePlayItem.build(streamName);
				int itemCount = playlistStream.getItemSize();
				for (int i = 0; i < itemCount; i++) {
					IPlayItem tmpItem = playlistStream.getItem(i);
					if (tmpItem.getName().equals(oldStreamName)) {
						if (!playlistStream.replace(tmpItem, item)) {
							log.warn("Playlist item replacement failed");
							sendNSFailed(streamConn, StatusCodes.NS_PLAY_FAILED, "Playlist swap failed.", streamName, streamId);
						}
						break;
					}
				}
			} else {
				log.warn("Unhandled transition: {}", transition);
				sendNSFailed(conn, StatusCodes.NS_FAILED, "Transition type not supported", streamName, streamId);
			}
		} else {
			log.info("Connection was null ?");
		}
	}

	/** {@inheritDoc} */
	public void publish(Boolean dontStop) {
		if (!dontStop) {
			IConnection conn = Red5.getConnectionLocal();
			if (conn instanceof IStreamCapableConnection) {
				IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
				int streamId = getCurrentStreamId();
				IClientStream stream = streamConn.getStreamById(streamId);
				if (stream instanceof IBroadcastStream) {
					IBroadcastStream bs = (IBroadcastStream) stream;
					if (bs.getPublishedName() != null) {
						IBroadcastScope bsScope = getBroadcastScope(conn.getScope(), bs.getPublishedName());
						if (bsScope != null) {
							bsScope.unsubscribe(bs.getProvider());
							if (conn instanceof BaseConnection) {
								((BaseConnection) conn).unregisterBasicScope(bsScope);
							}
						}
						bs.close();
						streamConn.deleteStreamById(streamId);
					}
				}
			}
		}
	}

	/** {@inheritDoc} */
	public void publish(String name, String mode) {
		Map<String, String> params = null;
		if (name != null && name.contains("?")) {
			// read and utilize the query string values
			params = new HashMap<String, String>();
			String tmp = name;
			// check if we start with '?' or not
			if (name.charAt(0) != '?') {
				tmp = name.split("\\?")[1];
			} else if (name.charAt(0) == '?') {
				tmp = name.substring(1);
			}
			// now break up into key/value blocks
			String[] kvs = tmp.split("&");
			// take each key/value block and break into its key value parts
			for (String kv : kvs) {
				String[] split = kv.split("=");
				params.put(split[0], split[1]);
			}
			// grab the streams name
			name = name.substring(0, name.indexOf("?"));
		}
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IScope scope = conn.getScope();
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			if (StringUtils.isEmpty(name)) {
				sendNSFailed(streamConn, StatusCodes.NS_FAILED, "The stream name may not be empty.", name, streamId);
				return;
			}
			IStreamSecurityService security = (IStreamSecurityService) ScopeUtils.getScopeService(scope, IStreamSecurityService.class);
			if (security != null) {
				Set<IStreamPublishSecurity> handlers = security.getStreamPublishSecurity();
				for (IStreamPublishSecurity handler : handlers) {
					if (!handler.isPublishAllowed(scope, name, mode)) {
						sendNSFailed(streamConn, StatusCodes.NS_FAILED, "You are not allowed to publish the stream.", name, streamId);
						return;
					}
				}
			}
			IBroadcastScope bsScope = getBroadcastScope(scope, name);
			if (bsScope != null && !bsScope.getProviders().isEmpty()) {
				// another stream with that name is already published			
				sendNSFailed(streamConn, StatusCodes.NS_PUBLISH_BADNAME, name, name, streamId);
				return;
			}
			IClientStream stream = streamConn.getStreamById(streamId);
			if (stream != null && !(stream instanceof IClientBroadcastStream)) {
				return;
			}
			boolean created = false;
			if (stream == null) {
				stream = streamConn.newBroadcastStream(streamId);
				created = true;
			}
			IClientBroadcastStream bs = (IClientBroadcastStream) stream;
			try {
				// set publish name
				bs.setPublishedName(name);
				// set stream parameters if they exist
				if (params != null) {
					bs.setParameters(params);
				}
				IContext context = conn.getScope().getContext();
				IProviderService providerService = (IProviderService) context.getBean(IProviderService.BEAN_NAME);
				// TODO handle registration failure
				if (providerService.registerBroadcastStream(conn.getScope(), name, bs)) {
					bsScope = getBroadcastScope(conn.getScope(), name);
					bsScope.setClientBroadcastStream(bs);
					if (conn instanceof BaseConnection) {
						((BaseConnection) conn).registerBasicScope(bsScope);
					}
				}
				log.debug("Mode: {}", mode);
				if (IClientStream.MODE_RECORD.equals(mode)) {
					bs.start();
					bs.saveAs(name, false);
				} else if (IClientStream.MODE_APPEND.equals(mode)) {
					bs.start();
					bs.saveAs(name, true);
				} else {
					bs.start();
				}
				bs.startPublishing();
			} catch (IOException e) {
				log.warn("Stream I/O exception", e);
				sendNSFailed(streamConn, StatusCodes.NS_RECORD_NOACCESS, "The file could not be created/written to.", name, streamId);
				bs.close();
				if (created) {
					streamConn.deleteStreamById(streamId);
				}
				return;
			} catch (Exception e) {
				log.warn("Exception on publish", e);
			}
		}
	}

	/** {@inheritDoc} */
	public void publish(String name) {
		publish(name, IClientStream.MODE_LIVE);
	}

	/** {@inheritDoc} */
	public void seek(int position) {
		log.trace("seek - position:{}", position);
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			IClientStream stream = streamConn.getStreamById(streamId);
			if (stream != null && stream instanceof ISubscriberStream) {
				ISubscriberStream subscriberStream = (ISubscriberStream) stream;
				try {
					subscriberStream.seek(position);
				} catch (OperationNotSupportedException err) {
					sendNSFailed(streamConn, StatusCodes.NS_SEEK_FAILED, "The stream doesn't support seeking.", stream.getName(), streamId);
				}
			}
		}
	}

	/** {@inheritDoc} */
	public void receiveVideo(boolean receive) {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			IClientStream stream = streamConn.getStreamById(streamId);
			if (stream != null && stream instanceof ISubscriberStream) {
				ISubscriberStream subscriberStream = (ISubscriberStream) stream;
				subscriberStream.receiveVideo(receive);
			}
		}
	}

	/** {@inheritDoc} */
	public void receiveAudio(boolean receive) {
		IConnection conn = Red5.getConnectionLocal();
		if (conn instanceof IStreamCapableConnection) {
			IStreamCapableConnection streamConn = (IStreamCapableConnection) conn;
			int streamId = getCurrentStreamId();
			IClientStream stream = streamConn.getStreamById(streamId);
			if (stream != null && stream instanceof ISubscriberStream) {
				ISubscriberStream subscriberStream = (ISubscriberStream) stream;
				subscriberStream.receiveAudio(receive);
			}
		}
	}

	/**
	 * Getter for current stream id.
	 *
	 * @return  Current stream id
	 */
	private int getCurrentStreamId() {
		// TODO: this must come from the current connection!
		return BaseRTMPHandler.getStreamId();
	}

	/**
	 * Return broadcast scope object for given scope and child scope name
	 * @param scope          Scope object
	 * @param name           Child scope name
	 * @return               Broadcast scope
	 */
	public IBroadcastScope getBroadcastScope(IScope scope, String name) {
		return scope.getBroadcastScope(name);
	}

	/**
	 * Send a <code>NetStream.Play.Failed</code> message to the client.
	 * 
	 * @param conn
	 * @param errorCode
	 * @param description
	 * @param name
	 * @param streamId
	 */
	private void sendNSFailed(IConnection conn, String errorCode, String description, String name, int streamId) {
		StreamService.sendNetStreamStatus(conn, errorCode, description, name, Status.ERROR, streamId);
	}

	/**
	 * Send <code>NetStream.Status</code> to client (Flash Player)
	 * @param conn
	 * @param statusCode see StatusCodes class
	 * @param description
	 * @param name
	 * @param streamId
	 */
	private void sendNSStatus(IConnection conn, String statusCode, String description, String name, int streamId) {
		StreamService.sendNetStreamStatus(conn, statusCode, description, name, Status.STATUS, streamId);
	}

	/**
	 * Send <code>NetStream.Status</code> to client (Flash Player)
	 *  
	 * @param conn connection
	 * @param statusCode NetStream status code
	 * @param description description
	 * @param name name
	 * @param status The status - error, warning, or status
	 * @param streamId stream id
	 */
	public static void sendNetStreamStatus(IConnection conn, String statusCode, String description, String name, String status, int streamId) {
		if (conn instanceof RTMPConnection) {
			Status s = new Status(statusCode);
			s.setClientid(streamId);
			s.setDesciption(description);
			s.setDetails(name);
			s.setLevel(status);
			// get the channel
			Channel channel = ((RTMPConnection) conn).getChannel((byte) (4 + ((streamId - 1) * 5)));
			channel.sendStatus(s);
		} else {
			throw new RuntimeException("Connection is not RTMPConnection: " + conn);
		}
	}

}
