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

package org.red5.compatibility.flex.messaging.messages;

/**
 * Flex compatibility message that is sent by the <code>mx:RemoteObject</code> mxml tag.
 * 
 * @see <a href="http://osflash.org/documentation/amf3">osflash documentation (external)</a>
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Joachim Bauch (jojo@struktur.de)
 */
public class RemotingMessage extends AsyncMessage {

	private static final long serialVersionUID = 1491092800943415719L;

	/** Method to execute. */
	public String operation;
	
	/** Value of the <code>source</code> attribute of mx:RemoteObject that sent the message. */
	public String source;

	/** {@inheritDoc} */
	protected void addParameters(StringBuilder result) {
		super.addParameters(result);
		result.append(",operation=");
		result.append(operation);
		result.append(",source=");
		result.append(source);
	}

}
