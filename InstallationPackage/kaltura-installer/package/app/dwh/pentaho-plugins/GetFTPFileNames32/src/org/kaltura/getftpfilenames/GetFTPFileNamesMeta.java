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

package org.kaltura.getftpfilenames;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import org.pentaho.di.core.CheckResult;
import org.pentaho.di.core.CheckResultInterface;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.Counter;
import org.pentaho.di.core.database.DatabaseMeta;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.exception.KettleStepException;
import org.pentaho.di.core.exception.KettleXMLException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.row.ValueMeta;
import org.pentaho.di.core.row.ValueMetaInterface;
import org.pentaho.di.core.variables.VariableSpace;
import org.pentaho.di.core.xml.XMLHandler;
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

import com.enterprisedt.net.ftp.FTPClient;

public class GetFTPFileNamesMeta extends BaseStepMeta implements StepMetaInterface
{
	private static final String NO = "N";

	private static final String YES = "Y";

	private String host;
	private String port;
	private String username;
	private String password;
	
	private boolean binaryMode;
	private String timeout;
	private boolean activeFtpConnectionMode;
	private String encoding;

	/** Array of filenames */
	private String fileNames[];

	/** Wildcard or filemask (regular expression) */
	private String fileMask[];

	/** Array of boolean values as string, indicating if a file is required. */

	private String fileRequired[];

	/** The name of the field in the output containing the filename */
	private String filenameField;

	/** Flag indicating that a row number field should be included in the output */
	private boolean includeRowNumber;

	/** The name of the field in the output containing the row number */
	private String rowNumberField;

	private String dynamicFilenameField;

	private String dynamicWildcardField;

	/** file name from previous fields **/
	private boolean filefield;
	
	private boolean searchRecursively;

	private boolean isaddresult;

	/** The maximum number or lines to read */
	private long rowLimit;

	public GetFTPFileNamesMeta()
	{
		super(); // allocate BaseStepMeta
	}

	public void loadXML(Node stepnode, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleXMLException
	{
		readData(stepnode);
	}

	public Object clone()
	{
		GetFTPFileNamesMeta retval = (GetFTPFileNamesMeta) super.clone();

		int nrfiles = fileNames.length;

		retval.allocate(nrfiles);

		for (int i = 0; i < nrfiles; i++)
		{
			retval.fileNames[i] = fileNames[i];
			retval.fileMask[i] = fileMask[i];
			retval.fileRequired[i] = fileRequired[i];
		}

		return retval;
	}

	public void allocate(int nrfiles)
	{
		fileNames = new String[nrfiles];
		fileMask = new String[nrfiles];
		fileRequired = new String[nrfiles];
	}

	public void setDefault()
	{
		int nrfiles = 0;
		isaddresult = true;
		filefield = false;
		includeRowNumber = false;
		rowNumberField = "";
		dynamicFilenameField = "";
		dynamicWildcardField = "";
		host = "";
		port = "21";
		username = "";
		password = "";
		encoding = "UTF-8";
		allocate(nrfiles);

		for (int i = 0; i < nrfiles; i++)
		{
			fileNames[i] = "filename" + (i + 1);
			fileMask[i] = "";
			fileRequired[i] = NO;
		}
	}

	public void getFields(RowMetaInterface row, String name, RowMetaInterface[] info, StepMeta nextStep, VariableSpace space) throws KettleStepException
	{

		// the filename
		ValueMetaInterface filename = new ValueMeta("filename", ValueMeta.TYPE_STRING);
		filename.setLength(500);
		filename.setPrecision(-1);
		filename.setOrigin(name);
		row.addValueMeta(filename);

		// the short filename
		ValueMetaInterface short_filename = new ValueMeta("short_filename", ValueMeta.TYPE_STRING);
		short_filename.setLength(500);
		short_filename.setPrecision(-1);
		short_filename.setOrigin(name);
		row.addValueMeta(short_filename);

		// the path
		ValueMetaInterface path = new ValueMeta("path", ValueMeta.TYPE_STRING);
		path.setLength(500);
		path.setPrecision(-1);
		path.setOrigin(name);
		row.addValueMeta(path);

		// the isDir
		ValueMetaInterface type = new ValueMeta("is_directory", ValueMeta.TYPE_BOOLEAN);
		type.setOrigin(name);
		row.addValueMeta(type);

		// the lastmodifiedtime
		ValueMetaInterface lastmodifiedtime = new ValueMeta("lastmodifiedtime", ValueMeta.TYPE_DATE);
		lastmodifiedtime.setOrigin(name);
		row.addValueMeta(lastmodifiedtime);

		// the size
		ValueMetaInterface size = new ValueMeta("size", ValueMeta.TYPE_INTEGER);
		size.setOrigin(name);
		row.addValueMeta(size);

    	if (includeRowNumber)
		{
			ValueMetaInterface v = new ValueMeta(space.environmentSubstitute(rowNumberField), ValueMeta.TYPE_INTEGER);
			v.setLength(ValueMetaInterface.DEFAULT_INTEGER_LENGTH, 0);
			v.setOrigin(name);
			row.addValueMeta(v);
		}

	}

	public String getXML()
	{
		StringBuffer retval = new StringBuffer(300);

		retval.append("    ").append(XMLHandler.addTagValue("host", getHost()));
		retval.append("    ").append(XMLHandler.addTagValue("port", getPort()));
		retval.append("    ").append(XMLHandler.addTagValue("username", getUsername()));
		retval.append("    ").append(XMLHandler.addTagValue("password", getPassword()));
		retval.append("    ").append(XMLHandler.addTagValue("binaryMode", isBinaryMode()));
		retval.append("    ").append(XMLHandler.addTagValue("timeout", getTimeout()));
		retval.append("    ").append(XMLHandler.addTagValue("activeFtpConnectionMode", isActiveFtpConnectionMode()));
		retval.append("    ").append(XMLHandler.addTagValue("encoding", getEncoding()));

		retval.append("    ").append(XMLHandler.addTagValue("rownum", includeRowNumber));
		retval.append("    ").append(XMLHandler.addTagValue("isaddresult", isaddresult));
		retval.append("    ").append(XMLHandler.addTagValue("filefield", filefield));
		retval.append("    ").append(XMLHandler.addTagValue("rownum_field", rowNumberField));
		retval.append("    ").append(XMLHandler.addTagValue("filename_Field", dynamicFilenameField));
		retval.append("    ").append(XMLHandler.addTagValue("wildcard_Field", dynamicWildcardField));

		retval.append("    ").append(XMLHandler.addTagValue("limit", rowLimit));
		retval.append("    ").append(XMLHandler.addTagValue("recursive", searchRecursively));
		retval.append("    <file>").append(Const.CR);

		for (int i = 0; i < fileNames.length; i++)
		{
			retval.append("      ").append(XMLHandler.addTagValue("name", fileNames[i]));
			retval.append("      ").append(XMLHandler.addTagValue("filemask", fileMask[i]));
			retval.append("      ").append(XMLHandler.addTagValue("file_required", fileRequired[i]));
		}
		retval.append("    </file>").append(Const.CR);

		return retval.toString();
	}

	private void readData(Node stepnode) throws KettleXMLException
	{
		try
		{
			setHost(XMLHandler.getTagValue(stepnode, "host"));
			setPort(XMLHandler.getTagValue(stepnode, "port"));
			setUsername(XMLHandler.getTagValue(stepnode, "username"));
			setPassword(XMLHandler.getTagValue(stepnode, "password"));
			setBinaryMode("Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "binaryMode")));
			setTimeout(XMLHandler.getTagValue(stepnode, "timeout"));
			setActiveFtpConnectionMode("Y"
					.equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "activeFtpConnectionMode")));
			setEncoding(XMLHandler.getTagValue(stepnode, "encoding"));

			includeRowNumber = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "rownum"));
			isaddresult = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "isaddresult"));
			filefield = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "filefield"));
			rowNumberField = XMLHandler.getTagValue(stepnode, "rownum_field");
			dynamicFilenameField = XMLHandler.getTagValue(stepnode, "filename_Field");
			dynamicWildcardField = XMLHandler.getTagValue(stepnode, "wildcard_Field");

			// Is there a limit on the number of rows we process?
			rowLimit = Const.toLong(XMLHandler.getTagValue(stepnode, "limit"), 0L);
			setSearchRecursively("Y".equals(XMLHandler.getTagValue(stepnode, "recursive")));

			Node filenode = XMLHandler.getSubNode(stepnode, "file");
			int nrfiles = XMLHandler.countNodes(filenode, "name");

			allocate(nrfiles);

			for (int i = 0; i < nrfiles; i++)
			{
				Node filenamenode = XMLHandler.getSubNodeByNr(filenode, "name", i);
				Node filemasknode = XMLHandler.getSubNodeByNr(filenode, "filemask", i);
				Node fileRequirednode = XMLHandler.getSubNodeByNr(filenode, "file_required", i);
				fileNames[i] = XMLHandler.getNodeValue(filenamenode);
				fileMask[i] = XMLHandler.getNodeValue(filemasknode);
				fileRequired[i] = XMLHandler.getNodeValue(fileRequirednode);
			}
		} catch (Exception e)
		{
			throw new KettleXMLException("Unable to load step info from XML", e);
		}
	}

	public void readRep(Repository rep, long id_step, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleException
	{
		try
		{
			int nrfiles = rep.countNrStepAttributes(id_step, "file_name");

			dynamicFilenameField = rep.getStepAttributeString(id_step, "filename_Field");
			dynamicWildcardField = rep.getStepAttributeString(id_step, "wildcard_Field");

			includeRowNumber = rep.getStepAttributeBoolean(id_step, "rownum");
			isaddresult = rep.getStepAttributeBoolean(id_step, rep.getStepAttributeString(id_step, "isaddresult"));
			filefield = rep.getStepAttributeBoolean(id_step, "filefield");
			rowNumberField = rep.getStepAttributeString(id_step, "rownum_field");
			rowLimit = rep.getStepAttributeInteger(id_step, "limit");
			searchRecursively = rep.getStepAttributeBoolean(id_step, "recursive");
			host = rep.getStepAttributeString(id_step, "host");
			port = rep.getStepAttributeString(id_step, "port");
			username= rep.getStepAttributeString(id_step, "username");
			password = rep.getStepAttributeString(id_step, "password");
			// TODO: add FTP advanced
			allocate(nrfiles);

			for (int i = 0; i < nrfiles; i++)
			{
				fileNames[i] = rep.getStepAttributeString(id_step, i, "file_name");
				fileMask[i] = rep.getStepAttributeString(id_step, i, "file_mask");
				fileRequired[i] = rep.getStepAttributeString(id_step, i, "file_required");
				if (!YES.equalsIgnoreCase(fileRequired[i]))
					fileRequired[i] = NO;
			}
		} catch (Exception e)
		{
			throw new KettleException("Unexpected error reading step information from the repository", e);
		}
	}

	public void saveRep(Repository rep, long id_transformation, long id_step) throws KettleException
	{
		try
		{
			rep.saveStepAttribute(id_transformation, id_step, "rownum", includeRowNumber);
			rep.saveStepAttribute(id_transformation, id_step, "isaddresult", isaddresult);
			rep.saveStepAttribute(id_transformation, id_step, "filefield", filefield);
			rep.saveStepAttribute(id_transformation, id_step, "filename_Field", dynamicFilenameField);
			rep.saveStepAttribute(id_transformation, id_step, "wildcard_Field", dynamicWildcardField);

			rep.saveStepAttribute(id_transformation, id_step, "rownum_field", rowNumberField);
			rep.saveStepAttribute(id_transformation, id_step, "limit", rowLimit);
			rep.saveStepAttribute(id_transformation, id_step, "revursive", searchRecursively);
			rep.saveStepAttribute(id_transformation, id_step, "host", host);
			rep.saveStepAttribute(id_transformation, id_step, "port", port);
			rep.saveStepAttribute(id_transformation, id_step, "username", username);
			rep.saveStepAttribute(id_transformation, id_step, "password", password);
			// TODO: add FTP advanced
			for (int i = 0; i < fileNames.length; i++)
			{
				rep.saveStepAttribute(id_transformation, id_step, i, "file_name", fileNames[i]);
				rep.saveStepAttribute(id_transformation, id_step, i, "file_mask", fileMask[i]);
				rep.saveStepAttribute(id_transformation, id_step, i, "file_required", fileRequired[i]);
			}
		} catch (Exception e)
		{
			throw new KettleException("Unable to save step information to the repository for id_step=" + id_step, e);
		}
	}

	public FTPFileInputList getFileList(FTPClient ftpClient, VariableSpace space) throws KettleException
	{
		return FTPFileInputList.createFileList(ftpClient, space, fileNames, fileMask, searchRecursively);
	}

	public FTPFileInputList getDynamicFileList(FTPClient ftpClient, VariableSpace space, String[] filename, String[] filemask) throws KettleException
	{
		return FTPFileInputList.createFileList(ftpClient, space, filename, filemask, searchRecursively);
	}

	public void check(List<CheckResultInterface> remarks, TransMeta transMeta, StepMeta stepinfo, RowMetaInterface prev, String input[], String output[], RowMetaInterface info)
	{
		CheckResult cr;

		// See if we get input...
		if (filefield)
		{
			if (input.length > 0)
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.InputOk"), stepinfo);
			else
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.InputErrorKo"), stepinfo);
			remarks.add(cr);

			if (Const.isEmpty(dynamicFilenameField))
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.FolderFieldnameMissing"), stepinfo);
			else
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.FolderFieldnameOk"), stepinfo);
			remarks.add(cr);

		} else
		{

			if (input.length > 0)
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.NoInputError"), stepinfo);
			else
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK,
						Messages.getString("GetFTPFileNamesMeta.CheckResult.NoInputOk"), stepinfo);

			remarks.add(cr);

			// check specified file names

			// TODO: Check FTP connection is opened

			// TODO: Check files
			// FTPFileInputList fileList = getFileList(transMeta);
			// if (fileList.nrOfFiles() == 0)
			// cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
			// Messages.getString("GetFTPFileNamesMeta.CheckResult.ExpectedFilesError"),
			// stepinfo);
			// else
			// cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK,
			// Messages.getString("GetFTPFileNamesMeta.CheckResult.ExpectedFilesOk",
			// ""+fileList.nrOfFiles()), stepinfo);
			// remarks.add(cr);
		}
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
		return new GetFTPFileNames(stepMeta, stepDataInterface, cnr, transMeta, trans);
	}

	public StepDataInterface getStepData()
	{
		return new GetFTPFileNamesData();
	}

	/**
	 * @return Returns the filenameField.
	 */
	public String getFilenameField()
	{
		return filenameField;
	}

	/**
	 * @return Returns the rowNumberField.
	 */
	public String getRowNumberField()
	{
		return rowNumberField;
	}

	/**
	 * @param dynamicFilenameField
	 *            The dynamic filename field to set.
	 */
	public void setDynamicFilenameField(String dynamicFilenameField)
	{
		this.dynamicFilenameField = dynamicFilenameField;
	}

	/**
	 * @param dynamicWildcardField
	 *            The dynamic wildcard field to set.
	 */
	public void setDynamicWildcardField(String dynamicWildcardField)
	{
		this.dynamicWildcardField = dynamicWildcardField;
	}

	/**
	 * @param rowNumberField
	 *            The rowNumberField to set.
	 */
	public void setRowNumberField(String rowNumberField)
	{
		this.rowNumberField = rowNumberField;
	}

	/**
	 * @return Returns the dynamic filename field (from previous steps)
	 */
	public String getDynamicFilenameField()
	{
		return dynamicFilenameField;
	}

	/**
	 * @return Returns the dynamic wildcard field (from previous steps)
	 */
	public String getDynamicWildcardField()
	{
		return dynamicWildcardField;
	}

	/**
	 * @return Returns the includeRowNumber.
	 */
	public boolean includeRowNumber()
	{
		return includeRowNumber;
	}

	/**
	 * @return Returns the File field.
	 */
	public boolean isFileField()
	{
		return filefield;
	}

	/**
	 * @param filefield
	 *            The filefield to set.
	 */
	public void setFileField(boolean filefield)
	{
		this.filefield = filefield;
	}

	/**
	 * @param includeRowNumber
	 *            The includeRowNumber to set.
	 */
	public void setIncludeRowNumber(boolean includeRowNumber)
	{
		this.includeRowNumber = includeRowNumber;
	}

	/**
	 * @param isaddresult
	 *            The isaddresult to set.
	 */
	public void setAddResultFile(boolean isaddresult)
	{
		this.isaddresult = isaddresult;
	}

	/**
	 * @return Returns isaddresult.
	 */
	public boolean isAddResultFile()
	{
		return isaddresult;
	}

	/**
	 * @return Returns the fileMask.
	 */
	public String[] getFileMask()
	{
		return fileMask;
	}

	/**
	 * @return Returns the fileRequired.
	 */
	public String[] getFileRequired()
	{
		return fileRequired;
	}

	/**
	 * @param fileMask
	 *            The fileMask to set.
	 */
	public void setFileMask(String[] fileMask)
	{
		this.fileMask = fileMask;
	}

	/**
	 * @param fileRequired
	 *            The fileRequired to set.
	 */
	public void setFileRequired(String[] fileRequired)
	{
		this.fileRequired = fileRequired;
	}

	/**
	 * @return Returns the fileName.
	 */
	public String[] getFileName()
	{
		return fileNames;
	}

	/**
	 * @param fileName
	 *            The fileName to set.
	 */
	public void setFileName(String[] fileName)
	{
		this.fileNames = fileName;
	}

	/**
	 * @return Returns the rowLimit.
	 */
	public long getRowLimit()
	{
		return rowLimit;
	}

	/**
	 * @param rowLimit
	 *            The rowLimit to set.
	 */
	public void setRowLimit(long rowLimit)
	{
		this.rowLimit = rowLimit;
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

	public void setSearchRecursively(boolean searchRecursively) {
		this.searchRecursively = searchRecursively;
	}

	public boolean isSearchRecursively() {
		return searchRecursively;
	}

}