/* Copyright (c) 2007 Pentaho Corporation.  All rights reserved. 
 * This software was developed by Pentaho Corporation and is provided under the terms 
 * of the GNU Lesser General Public License, Version 2.1. You may not use 
 * this file except in compliance with the license. If you need a copy of the license, 
 * please go to http://www.gnu.org/licenses/lgpl-2.1.txt. The Original Code is Pentaho 
 * Data Integration.  The Initial Developer is Pentaho Corporation.
 *
 * Software distributed under the GNU Lesser Public License is distributed on an "AS IS" 
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or  implied. Please refer to 
 * the license for the specific language governing your rights and limitations.*/

/* 
 * 
 * Created on 4-apr-2003
 * 
 */

package org.kaltura.fetchftpfile;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import org.pentaho.di.core.CheckResultInterface;
import org.pentaho.di.core.Counter;
import org.pentaho.di.core.database.DatabaseMeta;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.exception.KettleXMLException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.xml.XMLHandler;
import org.pentaho.di.repository.ObjectId;
import org.pentaho.di.repository.Repository;
import org.pentaho.di.resource.ResourceReference;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;
import org.w3c.dom.Node;

public class FetchFTPFileMeta extends BaseStepMeta implements StepMetaInterface
{
	private String dynamicFilenameField;
	private String destinationDir;
	
	private String host;
	private String port;
	private String username;
	private String password;
	
	private boolean binaryMode;
	private String timeout;
	private boolean activeFtpConnectionMode;
	private String encoding;

	public FetchFTPFileMeta()
	{
		super(); // allocate BaseStepMeta
	}

	public void loadXML(Node stepnode, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleXMLException
	{
		readData(stepnode);
	}

	public void setDefault()
	{
		host = "";
		port = "21";
		username = "";
		password = "";
		encoding = "UTF-8";
	}


	public String getXML()
	{
		StringBuffer retval = new StringBuffer(300);

		retval.append("    ").append(XMLHandler.addTagValue("dynamicFilenameField", getDynamicFilenameField()));
		retval.append("    ").append(XMLHandler.addTagValue("destinationDir", getDestinationDir()));
		retval.append("    ").append(XMLHandler.addTagValue("host", getHost()));
		retval.append("    ").append(XMLHandler.addTagValue("port", getPort()));
		retval.append("    ").append(XMLHandler.addTagValue("username", getUsername()));
		retval.append("    ").append(XMLHandler.addTagValue("password", getPassword()));
		retval.append("    ").append(XMLHandler.addTagValue("binaryMode", isBinaryMode()));
		retval.append("    ").append(XMLHandler.addTagValue("timeout", getTimeout()));
		retval.append("    ").append(XMLHandler.addTagValue("activeFtpConnectionMode", isActiveFtpConnectionMode()));
		retval.append("    ").append(XMLHandler.addTagValue("encoding", getEncoding()));
		
		return retval.toString();
	}

	private void readData(Node stepnode) throws KettleXMLException
	{
		try
		{
			setDynamicFilenameField(XMLHandler.getTagValue(stepnode, "dynamicFilenameField"));
			setDestinationDir(XMLHandler.getTagValue(stepnode, "destinationDir"));
			setHost(XMLHandler.getTagValue(stepnode, "host"));
			setPort(XMLHandler.getTagValue(stepnode, "port"));
			setUsername(XMLHandler.getTagValue(stepnode, "username"));
			setPassword(XMLHandler.getTagValue(stepnode, "password"));
			setBinaryMode("Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "binaryMode")));
			setTimeout(XMLHandler.getTagValue(stepnode, "timeout"));
			setActiveFtpConnectionMode("Y"
					.equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "activeFtpConnectionMode")));
			setEncoding(XMLHandler.getTagValue(stepnode, "encoding"));
		} catch (Exception e)
		{
			throw new KettleXMLException("Unable to load step info from XML", e);
		}
	}

	public void readRep(Repository rep, ObjectId id_step, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleException
	{
		try
		{
			host = rep.getStepAttributeString(id_step, "host");
			port = rep.getStepAttributeString(id_step, "port");
			username= rep.getStepAttributeString(id_step, "username");
			password = rep.getStepAttributeString(id_step, "password");
			
			activeFtpConnectionMode = rep.getStepAttributeBoolean(id_step, "activeFTPConnectionMode");
			binaryMode = rep.getStepAttributeBoolean(id_step, "binaryMode");
			encoding = rep.getStepAttributeString(id_step, "encoding");
			timeout = rep.getStepAttributeString(id_step, "timeout");
			
			dynamicFilenameField = rep.getStepAttributeString(id_step, "dynamicFilenameField");
			destinationDir = rep.getStepAttributeString(id_step, "destinationDir");
		} catch (Exception e)
		{
			throw new KettleException("Unexpected error reading step information from the repository", e);
		}
	}

	public void saveRep(Repository rep, ObjectId id_transformation, ObjectId id_step) throws KettleException
	{
		try
		{
			rep.saveStepAttribute(id_transformation, id_step, "host", host);
			rep.saveStepAttribute(id_transformation, id_step, "port", port);
			rep.saveStepAttribute(id_transformation, id_step, "username", username);
			rep.saveStepAttribute(id_transformation, id_step, "password", password);
			
			rep.saveStepAttribute(id_transformation, id_step, "activeFtpConnectionMode",activeFtpConnectionMode);
			rep.saveStepAttribute(id_transformation, id_step, "binaryMode", binaryMode);
			rep.saveStepAttribute(id_transformation, id_step, "encoding", encoding);
			rep.saveStepAttribute(id_transformation, id_step, "timeout", timeout);
			
			rep.saveStepAttribute(id_transformation, id_step, "dynamicFilenameField", dynamicFilenameField);
			rep.saveStepAttribute(id_transformation, id_step, "destinationDir", destinationDir);
			
		} catch (Exception e)
		{
			throw new KettleException("Unable to save step information to the repository for id_step=" + id_step, e);
		}
	}

	public void check(List<CheckResultInterface> remarks, TransMeta transMeta, StepMeta stepinfo, RowMetaInterface prev, String input[], String output[], RowMetaInterface info)
	{
		// CheckResult cr;
		// TODO: check FTP connection
	}

	@Override
	public List<ResourceReference> getResourceDependencies(TransMeta transMeta, StepMeta stepInfo)
	{
		List<ResourceReference> references = new ArrayList<ResourceReference>(5);
		ResourceReference reference = new ResourceReference(stepInfo);
		references.add(reference);

		return references;
	}

	public StepInterface getStep(StepMeta stepMeta, StepDataInterface stepDataInterface, int cnr, TransMeta transMeta, Trans trans)
	{
		return new FetchFTPFile(stepMeta, stepDataInterface, cnr, transMeta, trans);
	}

	public StepDataInterface getStepData()
	{
		return new FetchFTPFileData();
	}

	public void setHost(String host)
	{
		this.host = host;
	}

	public String getHost()
	{
		return host;
	}

	public void setPort(String port)
	{
		this.port = port;
	}

	public String getPort()
	{
		return port;
	}

	public void setUsername(String username)
	{
		this.username = username;
	}

	public String getUsername()
	{
		return username;
	}

	public void setPassword(String password)
	{
		this.password = password;
	}

	public String getPassword()
	{
		return password;
	}

	public void setBinaryMode(boolean binaryMode)
	{
		this.binaryMode = binaryMode;
	}

	public boolean isBinaryMode()
	{
		return binaryMode;
	}

	public void setTimeout(String timeout)
	{
		this.timeout = timeout;
	}

	public String getTimeout()
	{
		return timeout;
	}

	public void setActiveFtpConnectionMode(boolean activeFtpConnectionMode)
	{
		this.activeFtpConnectionMode = activeFtpConnectionMode;
	}

	public boolean isActiveFtpConnectionMode()
	{
		return activeFtpConnectionMode;
	}

	public void setEncoding(String encoding)
	{
		this.encoding = encoding;
	}

	public String getEncoding()
	{
		return encoding;
	}

	public void setDynamicFilenameField(String dynamicFilenameField) {
		this.dynamicFilenameField = dynamicFilenameField;
	}

	public String getDynamicFilenameField() {
		return dynamicFilenameField;
	}

	public void setDestinationDir(String destinationDir) {
		this.destinationDir = destinationDir;
	}

	public String getDestinationDir() {
		return destinationDir;
	}
}