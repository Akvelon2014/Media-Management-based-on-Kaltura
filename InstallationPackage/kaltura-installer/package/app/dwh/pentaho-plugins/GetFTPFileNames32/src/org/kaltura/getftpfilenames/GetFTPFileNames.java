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

package org.kaltura.getftpfilenames;

import org.pentaho.di.core.Const;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.exception.KettleStepException;
import org.pentaho.di.core.row.RowDataUtil;
import org.pentaho.di.core.row.RowMeta;
import org.pentaho.di.job.entries.ftp.Messages;
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
public class GetFTPFileNames extends BaseStep implements StepInterface
{
    private GetFTPFileNamesMeta meta;
    private GetFTPFileNamesData data;
    
    private FTPClient ftpClient;

    public GetFTPFileNames(StepMeta stepMeta, StepDataInterface stepDataInterface, int copyNr, TransMeta transMeta, Trans trans)
    {
        super(stepMeta, stepDataInterface, copyNr, transMeta, trans);
    }
	
	/**
	 * Build an empty row based on the meta-data...
	 * 
	 * @return
	 */

	private Object[] buildEmptyRow()
	{
        Object[] rowData = RowDataUtil.allocateRowData(data.outputRowMeta.size());
 
		 return rowData;
	}
	
    public boolean processRow(StepMetaInterface smi, StepDataInterface sdi) throws KettleException
    {
    	if(!meta.isFileField())
		{
    		if (data.filenr >= data.files.size())
  	        {
  	            setOutputDone();
  	            return false;
  	        }
		}else
		{
			if (data.filenr >= data.files.size())
  	        {
				// Grab one row from previous step ...
				data.readrow=getRow();
  	        }

			if (data.readrow==null)
  	        {
  	            setOutputDone();
  	            return false;
  	        }
			
	        if (first)
	        {	        	
	            first = false;

				data.inputRowMeta = getInputRowMeta();
				data.outputRowMeta = data.inputRowMeta.clone();
		        meta.getFields(data.outputRowMeta, getStepname(), null, null, this);

	            // Get total previous fields
	            data.totalpreviousfields=data.inputRowMeta.size();
	            
	        	// Check is filename field is provided
				if (Const.isEmpty(meta.getDynamicFilenameField()))
				{
					logError(Messages.getString("GetFTPFileNames.Log.NoField"));
					throw new KettleException(Messages.getString("GetFTPFileNames.Log.NoField"));
				}
				
	            
				// cache the position of the field			
				if (data.indexOfFilenameField<0)
				{	
					data.indexOfFilenameField =data.inputRowMeta.indexOfValue(meta.getDynamicFilenameField());
					if (data.indexOfFilenameField<0)
					{
						// The field is unreachable !
						logError(Messages.getString("GetFTPFileNames.Log.ErrorFindingField",meta.getDynamicFilenameField())); //$NON-NLS-1$ //$NON-NLS-2$
						throw new KettleException(Messages.getString("GetFTPFileNames.Exception.CouldnotFindField",meta.getDynamicFilenameField())); //$NON-NLS-1$ //$NON-NLS-2$
					}
				}  
				
	        	// If wildcard field is specified, Check if field exists
				if (!Const.isEmpty(meta.getDynamicWildcardField()))
				{
					if (data.indexOfWildcardField<0)
					{
						data.indexOfWildcardField =data.inputRowMeta.indexOfValue(meta.getDynamicWildcardField());
						if (data.indexOfWildcardField<0)
						{
							// The field is unreachable !
							logError(Messages.getString("GetFTPFileNames.Log.ErrorFindingField")+ "[" + meta.getDynamicWildcardField()+"]"); //$NON-NLS-1$ //$NON-NLS-2$
							throw new KettleException(Messages.getString("GetFTPFileNames.Exception.CouldnotFindField",meta.getDynamicWildcardField())); //$NON-NLS-1$ //$NON-NLS-2$
						}
					}
				}
	        }
		}// end if first
    	
        try
        {
        	Object[] outputRow = buildEmptyRow();
        	Object extraData[] = new Object[data.nrStepFields];
        	if(meta.isFileField())
        	{
    			if (data.filenr >= data.files.size())
    		    {
    				// Get value of dynamic filename field ...
    	    		String filename=getInputRowMeta().getString(data.readrow,data.indexOfFilenameField);
    	    		String wildcard="";
    	    		if(data.indexOfWildcardField>=0)
    	    			wildcard=getInputRowMeta().getString(data.readrow,data.indexOfWildcardField);
    	    		
    	    		String[] filesname={filename};
    		      	String[] filesmask={wildcard};
    		      	// Get files list
    		      	data.files = meta.getDynamicFileList(ftpClient, getTransMeta(), filesname, filesmask);
    		      	data.filenr=0;
    		     }
        		
        		// Clone current input row
    			outputRow = data.readrow.clone();
        	}
        	if(data.files.size()>0)
        	{
	        	data.file = data.files.get(data.filenr);
	
	        	int outputIndex = 0;
				
                //// filename        		
	        	extraData[outputIndex++]=data.file.getPath() + "/" + data.file.getName();

                //// short_filename
        		extraData[outputIndex++]=data.file.getName();

				 // Path
            	extraData[outputIndex++]=data.file.getPath();

                // isDir
    			extraData[outputIndex++]=data.file.isDir();

                // lastmodifiedtime
				extraData[outputIndex++]=data.file.lastModified();

	            // size
	   		 	extraData[outputIndex++]=data.file.size();

				// See if we need to add the row number to the row...  
				if (meta.includeRowNumber() && !Const.isEmpty(meta.getRowNumberField()))
				{
				  extraData[outputIndex++]= new Long(data.rownr);
				}
		
		         data.rownr++;
		        // Add row data
		        outputRow = RowDataUtil.addRowData(outputRow,data.totalpreviousfields, extraData);
                // Send row
		        putRow(data.outputRowMeta, outputRow);
		        
	      		if (meta.getRowLimit()>0 && data.rownr>=meta.getRowLimit())  // limit has been reached: stop now.
	      		{
	   	           setOutputDone();
	   	           return false;
	      		}
	      		
            }
        }
        catch (Exception e)
        {
            throw new KettleStepException(e);
        }

        data.filenr++;

        if (checkFeedback(getLinesInput())) 	
        {
        	if(log.isBasic()) logBasic(Messages.getString("GetFTPFileNames.Log.NrLine",""+getLinesInput()));
        }

        return true;
    }

    public boolean init(StepMetaInterface smi, StepDataInterface sdi)
    {
        meta = (GetFTPFileNamesMeta) smi;
        data = (GetFTPFileNamesData) sdi;

        if (super.init(smi, sdi))
        {
        	
			try
			{
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
	            
				if(!meta.isFileField())
				{
	                data.files = meta.getFileList(ftpClient, getTransMeta());
				}
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
        meta = (GetFTPFileNamesMeta) smi;
        data = (GetFTPFileNamesData) sdi;
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
    
    //
    // Run is were the action happens!
    public void run()
    {
    	BaseStep.runStepThread(this, meta, data);
    }
}