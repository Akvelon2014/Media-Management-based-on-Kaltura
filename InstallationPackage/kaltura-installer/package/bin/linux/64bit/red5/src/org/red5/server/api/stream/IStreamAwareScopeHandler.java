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

import org.red5.server.api.scope.IScopeHandler;

/**
 * A scope handler that is stream aware.
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Steven Gong (steven.gong@gmail.com)
 */
public interface IStreamAwareScopeHandler extends IScopeHandler {
	/**
	 * A broadcast stream starts being published. This will be called
	 * when the first video packet has been received.
	 * 
	 * @param stream stream
	 */
	public void streamPublishStart(IBroadcastStream stream);

	/**
	 * A broadcast stream starts being recorded. This will be called
	 * when the first video packet has been received.
	 * 
	 * @param stream stream 
	 */
	public void streamRecordStart(IBroadcastStream stream);

	/**
	 * Notified when a broadcaster starts.
	 * 
	 * @param stream stream
	 */
	public void streamBroadcastStart(IBroadcastStream stream);

	/**
	 * Notified when a broadcaster closes.
	 * 
	 * @param stream stream
	 */
	public void streamBroadcastClose(IBroadcastStream stream);

	/**
	 * Notified when a subscriber starts.
	 * 
	 * @param stream stream
	 */
	public void streamSubscriberStart(ISubscriberStream stream);

	/**
	 * Notified when a subscriber closes.
	 * 
	 * @param stream stream
	 */
	public void streamSubscriberClose(ISubscriberStream stream);

	/**
	 * Notified when a play item plays.
	 * 
	 * @param stream stream
	 * @param item item
	 * @param isLive true if live
	 */
	public void streamPlayItemPlay(ISubscriberStream stream, IPlayItem item, boolean isLive);

	/**
	 * Notified when a play item stops.
	 * 
	 * @param stream stream
	 * @param item item
	 */
	public void streamPlayItemStop(ISubscriberStream stream, IPlayItem item);

	/**
	 * Notified when a play item pauses.
	 * 
	 * @param stream stream
	 * @param item item
	 * @param position position
	 */
	public void streamPlayItemPause(ISubscriberStream stream, IPlayItem item, int position);

	/**
	 * Notified when a play item resumes.
	 * 
	 * @param stream stream
	 * @param item item
	 * @param position position
	 */
	public void streamPlayItemResume(ISubscriberStream stream, IPlayItem item, int position);

	/**
	 * Notified when a play item seeks.
	 * 
	 * @param stream stream
	 * @param item item
	 * @param position position
	 */
	public void streamPlayItemSeek(ISubscriberStream stream, IPlayItem item, int position);

}
