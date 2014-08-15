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

package org.red5.io;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;

import javax.xml.parsers.DocumentBuilder;
import javax.xml.parsers.DocumentBuilderFactory;
import javax.xml.parsers.ParserConfigurationException;
import javax.xml.xpath.XPath;
import javax.xml.xpath.XPathConstants;
import javax.xml.xpath.XPathExpression;
import javax.xml.xpath.XPathExpressionException;
import javax.xml.xpath.XPathFactory;

import org.apache.xml.serialize.OutputFormat;
import org.apache.xml.serialize.XMLSerializer;
import org.red5.io.flv.IKeyFrameDataAnalyzer.KeyFrameMeta;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NamedNodeMap;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;
import org.xml.sax.SAXException;

/**
 * File-based keyframe metadata cache.
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Joachim Bauch (jojo@struktur.de)
 */
@SuppressWarnings("deprecation")
public class FileKeyFrameMetaCache implements IKeyFrameMetaCache {

    /**
     * Logger
     */
    private static Logger log = LoggerFactory.getLogger(FileKeyFrameMetaCache.class);

    /** {@inheritDoc} */
	public KeyFrameMeta loadKeyFrameMeta(File file) {
		String filename = file.getAbsolutePath() + ".meta";
		File metadataFile = new File(filename);
		if (!metadataFile.exists()) {
			// No such metadata
			return null;
		}
		
		Document dom;
		DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
		try {
			// Using factory get an instance of document builder
			DocumentBuilder db = dbf.newDocumentBuilder();
			
			// parse using builder to get DOM representation of the XML file
			dom = db.parse(filename);
			
			db.reset();
		} catch (ParserConfigurationException pce) {
			log.error("Could not parse XML file.", pce);
			return null;
		} catch (SAXException se) {
			log.error("Could not parse XML file.", se);
			return null;
		} catch (IOException ioe) {
			log.error("Could not parse XML file.", ioe);
			return null;
		}
		
		Element root = dom.getDocumentElement();
		// Check if .xml file is valid and for this .flv file
		if (!"FrameMetadata".equals(root.getNodeName())) {
			// Invalid XML
			return null;
		}
		String modified = root.getAttribute("modified");
		if (modified == null || !modified.equals(String.valueOf(file.lastModified()))) {
			// File has changed in the meantime
			return null;
		}
		if (!root.hasAttribute("duration")) {
			// Old file without duration informations
			return null;
		}
		if (!root.hasAttribute("audioOnly")) {
			// Old file without audio/video informations
			return null;
		}
		XPathFactory factory = XPathFactory.newInstance();
        XPath xpath = factory.newXPath();
        NodeList keyFrames;
        try {
            XPathExpression xexpr = xpath.compile("/FrameMetadata/KeyFrame");
            keyFrames = (NodeList) xexpr.evaluate(dom, XPathConstants.NODESET);
        } catch (XPathExpressionException err) {
        	log.error("could not compile xpath expression", err);
        	return null;
        }

        int length = keyFrames.getLength();
        if (keyFrames == null || length == 0) {
        	// File doesn't contain informations about keyframes
        	return null;
        }
        
        KeyFrameMeta result = new KeyFrameMeta();
        result.duration = Long.parseLong(root.getAttribute("duration"));
        result.positions = new long[length];
        result.timestamps = new int[length];
		for (int i=0; i<length; i++) {
			Node node = keyFrames.item(i);
			NamedNodeMap attrs = node.getAttributes();
			result.positions[i] = Long.parseLong(attrs.getNamedItem("position").getNodeValue());
			result.timestamps[i] = Integer.parseInt(attrs.getNamedItem("timestamp").getNodeValue());
		}
		result.audioOnly = "true".equals(root.getAttribute("audioOnly"));
        
		return result;
	}

    /** {@inheritDoc} */
	public void saveKeyFrameMeta(File file, KeyFrameMeta meta) {
		if (meta.positions.length == 0) {
			// Don't store empty meta informations
			return;
		}
	
		Document dom;
		DocumentBuilderFactory dbf = DocumentBuilderFactory.newInstance();
		try {
			//get an instance of builder
			DocumentBuilder db = dbf.newDocumentBuilder();	
			//create an instance of DOM
			dom = db.newDocument();
		} catch (ParserConfigurationException pce) {
			log.error("Error while creating document.", pce);
			return;
		}
		
		// Create file and add keyframe informations
		Element root = dom.createElement("FrameMetadata");
		root.setAttribute("modified", String.valueOf(file.lastModified()));
		root.setAttribute("duration", String.valueOf(meta.duration));
		root.setAttribute("audioOnly", meta.audioOnly ? "true" : "false");
		dom.appendChild(root);
		
		for (int i=0; i<meta.positions.length; i++) {
			Element node = dom.createElement("KeyFrame");
			node.setAttribute("position", String.valueOf(meta.positions[i]));
			node.setAttribute("timestamp", String.valueOf(meta.timestamps[i]));
			root.appendChild(node);
		}
		
		String filename = file.getAbsolutePath() + ".meta";
		
		OutputFormat format = new OutputFormat(dom);
		format.setIndenting(true);
		
		try {
			XMLSerializer serializer = new XMLSerializer(
				new FileOutputStream(new File(filename)), format);
			serializer.serialize(dom);
		} catch (IOException err) {
			log.error("could not save keyframe data", err);
		}
	}

}
