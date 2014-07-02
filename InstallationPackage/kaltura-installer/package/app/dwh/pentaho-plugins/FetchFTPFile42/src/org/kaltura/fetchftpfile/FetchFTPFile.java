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

package org.kaltura.fetchftpfile;

import org.pentaho.di.core.Const;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowMeta;
import org.pentaho.di.i18n.BaseMessages;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStep;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;

import com.enterprisedt.net.ftp.FTPClient;

/**
 * Read all sorts of text files, convert them to rows and writes these to one or more output streams.
 * 
 * @author Matt
 * @since 4-apr-2003
 */
public class FetchFTPFile extends BaseStep implements StepInterface
{
	private static Class<?> PKG = FetchFTPFile.class;
    private FetchFTPFileMeta meta;
    private FetchFTPFileData data;
    
    private FTPClient ftpClient;

    public FetchFTPFile(StepMeta stepMeta, StepDataInterface stepDataInterface, int copyNr, TransMeta transMeta, Trans trans)
    {
        super(stepMeta, stepDataInterface, copyNr, transMeta, trans);
    }
	
    public boolean processRow(StepMetaInterface smi, StepDataInterface sdi) throws KettleException
    {
    	data.readrow=getRow();
		if (data.readrow==null)  // no more input to be expected...
		{
			setOutputDone();
			return false;
		}
		
		data.inputRowMeta = getInputRowMeta();
		data.outputRowMeta = data.inputRowMeta.clone();
        meta.getFields(data.outputRowMeta, getStepname(), null, null, this);

        // Get total previous fields
        data.totalpreviousfields=data.inputRowMeta.size();
        
    	// Check is filename field is provided
		if (Const.isEmpty(meta.getDynamicFilenameField()))
		{
			logError(BaseMessages.getString(PKG, "FetchFTPFile.Log.NoField"));
			throw new KettleException(BaseMessages.getString(PKG, "FetchFTPFile.Log.NoField"));
		}
		
        
		// cache the position of the field			
		if (data.indexOfFilenameField<0)
		{	
			data.indexOfFilenameField =data.inputRowMeta.indexOfValue(meta.getDynamicFilenameField());
			if (data.indexOfFilenameField<0)
			{
				// The field is unreachable !
				logError(BaseMessages.getString(PKG, "GetFTPFileNames.Log.ErrorFindingField",meta.getDynamicFilenameField())); //$NON-NLS-1$ //$NON-NLS-2$
				throw new KettleException(BaseMessages.getString(PKG, "GetFTPFileNames.Exception.CouldnotFindField",meta.getDynamicFilenameField())); //$NON-NLS-1$ //$NON-NLS-2$
			}
		}   

		String filename=getInputRowMeta().getString(data.readrow,data.indexOfFilenameField);
		String destinationDir = environmentSubstitute(meta.getDestinationDir());
		String[] words = filename.split("/");
		String shortFileName = words[words.length -1];
		logBasic("Fetching " + filename + " into " + destinationDir + " as " + shortFileName);
		if (FTPHelper.GetFTPFile(ftpClient, filename, destinationDir, shortFileName))
		{
			logBasic("Successfully fetched " + filename + " into " + destinationDir + " as " + shortFileName);
		}
		else
		{
			logBasic("Successfully fetched " + filename + " into " + destinationDir + " as " + shortFileName);
		}
		putRow(getInputRowMeta(), data.readrow);
        return true;
    }

    public boolean init(StepMetaInterface smi, StepDataInterface sdi)
    {
        meta = (FetchFTPFileMeta) smi;
        data = (FetchFTPFileData) sdi;

        if (super.init(smi, sdi))
        {
        	
			try
			{
				// TODO: Validate FTP input
				
				if(ftpClient==null || !ftpClient.connected())
				{
					String host = environmentSubstitute(meta.getHost());
					int port = Const.toInt(environmentSubstitute(meta.getPort()),21);
					String username = environmentSubstitute(meta.getUsername());
					String pw = environmentSubstitute(meta.getPassword());
					
					boolean activeMode = meta.isActiveFtpConnectionMode();
					boolean binaryMode = meta.isBinaryMode();
					int timeout = Const.toInt(environmentSubstitute(meta.getTimeout()),3600000);
					String encoding = meta.getEncoding();
					ftpClient = FTPHelper.connectToFTP(host, port, username, pw, activeMode, binaryMode, timeout, encoding);
				}
				
				 // Create the output row meta-data
	            data.outputRowMeta = new RowMeta();
	            meta.getFields(data.outputRowMeta, getStepname(), null, null, this); // get the metadata populated
	            data.nrStepFields=  data.outputRowMeta.size();
	            
			}
			catch(Exception e)
			{
				logError("Error initializing step: "+e.toString());
				logError(Const.getStackTracker(e));
				return false;
			}
		
            data.rownr = 1L;
			data.filenr = 0;
			data.totalpreviousfields=0;
            
            return true;
          
        }
        return false;
    }

    public void dispose(StepMetaInterface smi, StepDataInterface sdi)
    {
        meta = (FetchFTPFileMeta) smi;
        data = (FetchFTPFileData) sdi;
        if(data.file!=null)
        {
        	data.file=null;        	
        }
        super.dispose(smi, sdi);
    }

    @Override
    public void setOutputDone() 
    {
       	super.setOutputDone();
       	if(ftpClient!=null && ftpClient.connected())
		{
       		try
			{
				ftpClient.quit();
			} catch (Exception e)
			{
				log.logBasic("Error","Failed to cleanly disconnect from ftp");
			}
		}	 
    }
}