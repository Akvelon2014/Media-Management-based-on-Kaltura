package org.kaltura.salesforceoutput;

/*************************************************************************************** 
 * Copyright (C) 2007 Samatar.  All rights reserved. 
 * This software was developed by Samatar and is provided under the terms 
 * of the GNU Lesser General Public License, Version 2.1. You may not use 
 * this file except in compliance with the license. A copy of the license, 
 * is included with the binaries and source code. The Original Code is Samatar.  
 * The Initial Developer is Samatar.
 *
 * Software distributed under the GNU Lesser Public License is distributed on an 
 * "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. 
 * Please refer to the license for the specific language governing your rights 
 * and limitations.
 ***************************************************************************************/
 

import com.sforce.soap.partner.DescribeSObjectResult;
import com.sforce.soap.partner.SoapBindingStub;
import com.sforce.soap.partner.LoginResult;
import com.sforce.soap.partner.GetUserInfoResult;
import com.sforce.soap.partner.SessionHeader;
import com.sforce.soap.partner.SforceServiceLocator;
import com.sforce.soap.partner.sobject.SObject;
import com.sforce.soap.partner.UpsertResult;
import com.sforce.soap.partner.fault.ApiFault;

import org.apache.axis.message.MessageElement;
import org.w3c.dom.Element;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowDataUtil;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStep;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;



/**
 * Read data from Salesforce module, convert them to rows and writes these to one or more output streams.
 * 
 * @author Samatar
 * @since 10-06-2007
 */
public class SalesforceOutput extends BaseStep implements StepInterface
{
	private SalesforceOutputMeta meta;
	private SalesforceOutputData data;
	
	private SoapBindingStub binding;
	private SObject[] sfBuffer;
	private Object[][] outputBuffer; 
	private int iBufferPos;
	
	private MessageElement newMessageElement(String name, Object value)
	throws Exception {

			MessageElement me = new MessageElement("", name); // , value);
			me.setObjectValue(value);
			Element e = me.getAsDOM();
			e.removeAttribute("xsi:type");
			e.removeAttribute("xmlns:ns1");
			e.removeAttribute("xmlns:xsd");
			e.removeAttribute("xmlns:xsi");

			me = new MessageElement(e);
			return me;
	}

	public SalesforceOutput(StepMeta stepMeta, StepDataInterface stepDataInterface, int copyNr, TransMeta transMeta, Trans trans)
	{
		super(stepMeta, stepDataInterface, copyNr, transMeta, trans);
	}
	
	public SoapBindingStub getBinding(String Url,String username, String password, String module, 
			String timeout) throws KettleException
	{
		SoapBindingStub binding2=null;
		LoginResult loginResult = null;
		GetUserInfoResult userInfo = null;
		
		
		try{
			binding2 = (SoapBindingStub) new SforceServiceLocator().getSoap();
			if (log.isDetailed()) logDetailed(Messages.getString("SalesforceOutput.Log.LoginURL") + " : " + binding2._getProperty(SoapBindingStub.ENDPOINT_ADDRESS_PROPERTY));
		      
	        //  Set timeout
			int timeOut=Const.toInt(timeout, 0);
	      	if(timeOut>0) binding2.setTimeout(timeOut);
	        
	      	// Set URL
	        binding2._setProperty(SoapBindingStub.ENDPOINT_ADDRESS_PROPERTY, Url);
	        
	        // Attempt the login giving the user feedback
		      
	        if (log.isDetailed())
	        {
	        	logDetailed(Messages.getString("SalesforceOutput.Log.LoginNow"));
	        	logDetailed("----------------------------------------->");
	        	logDetailed(Messages.getString("SalesforceOutput.Log.LoginURL",Url));
	        	logDetailed(Messages.getString("SalesforceOutput.Log.LoginUsername",username));
	        	logDetailed(Messages.getString("SalesforceOutput.Log.LoginModule",module));
//	        	if(!Const.isEmpty(condition)) logDetailed(Messages.getString("SalesforceOutput.Log.LoginCondition",condition));
	        	logDetailed("<-----------------------------------------");
	        }
	        
	        // Login
	        loginResult = binding2.login(username, password);
	        
	        if (log.isDebug())
	        {
	        	logDebug(Messages.getString("SalesforceOutput.Log.SessionId") + " : " + loginResult.getSessionId());
	        	logDebug(Messages.getString("SalesforceOutput.Log.NewServerURL") + " : " + loginResult.getServerUrl());
	        }
	        
	        // set the session header for subsequent call authentication
	        binding2._setProperty(SoapBindingStub.ENDPOINT_ADDRESS_PROPERTY,loginResult.getServerUrl());
	
	        // Create a new session header object and set the session id to that
	        // returned by the login
	        SessionHeader sh = new SessionHeader();
	        sh.setSessionId(loginResult.getSessionId());
	        binding2.setHeader(new SforceServiceLocator().getServiceName().getNamespaceURI(), "SessionHeader", sh);
	        
	        // Return the user Infos
	        userInfo = binding2.getUserInfo();
	        if (log.isDebug()) 
	        {
	        	logDebug(Messages.getString("SalesforceOutput.Log.UserInfos") + " : " + userInfo.getUserFullName());
	        	logDebug("----------------------------------------->");
	        	logDebug(Messages.getString("SalesforceOutput.Log.UserName") + " : " + userInfo.getUserFullName());
	        	logDebug(Messages.getString("SalesforceOutput.Log.UserEmail") + " : " + userInfo.getUserEmail());
	        	logDebug(Messages.getString("SalesforceOutput.Log.UserLanguage") + " : " + userInfo.getUserLanguage());
	        	logDebug(Messages.getString("SalesforceOutput.Log.UserOrganization") + " : " + userInfo.getOrganizationName());    
			    logDebug("<-----------------------------------------");
	        }	
		}catch(Exception e)
		{
			throw new KettleException(e);
		}
		return binding2;
	}

	 public void connectSalesforce() throws KettleException {
		 
		String username=environmentSubstitute(meta.getUserName());
		String password = environmentSubstitute(meta.getPassword());
		String module = environmentSubstitute(meta.getModule());
//		String condition = environmentSubstitute(meta.getCondition()); 
		String timeout= environmentSubstitute(meta.getTimeOut()); 
		
		// connect and return binding
		binding=getBinding(data.URL,username, password, module, timeout);
		
		if(binding==null)  throw new KettleException(Messages.getString("SalesforceOutput.Exception.CanNotGetBiding"));
		

	    try{
	    	
			// check if Object is queryable			
		    DescribeSObjectResult describeSObjectResult = binding.describeSObject(module);
		        
		    if (describeSObjectResult == null) throw new KettleException(Messages.getString("SalesforceOutput.ErrorGettingObject"));
		        
		    if(!describeSObjectResult.isQueryable()) throw new KettleException(Messages.getString("SalesforceOutputDialog.ObjectNotQueryable",module));
  
		}catch(Exception e)
		{
			throw new KettleException(e);
		}
	 }
	
	public boolean processRow(StepMetaInterface smi, StepDataInterface sdi) throws KettleException
	{
		Object[] outputRowData = null;

		
	 	// get one row ... This does some basic initialization of the objects, including loading the info coming in
		outputRowData = getRow(); 
		
		if(outputRowData==null)
		{
			if ( iBufferPos > 0 ) {
				flushBuffers();
			}
			setOutputDone();
			return false;
		}
		
		// If we haven't looked at a row before then do some basic setup.
		if(first)
		{
			first=false;
			// Check if module is specified 
			 if (Const.isEmpty(meta.getModule()))
			 {    
				 throw new KettleException(Messages.getString("SalesforceOutputDialog.ModuleMissing.DialogMessage"));
			 }
			 
			  // Check if username is specified 
			 if (Const.isEmpty(meta.getUserName()))
			 {
				 throw new KettleException(Messages.getString("SalesforceOutputDialog.UsernameMissing.DialogMessage"));
			 }

			 // initialize variables
			 data.recordcount=0;
			 data.rownr = 0;	
			 data.limit=Const.toLong(environmentSubstitute(meta.getRowLimit()),0);
			 data.URL=environmentSubstitute(meta.getTargetURL());
			 data.Module=environmentSubstitute(meta.getModule());
				
			 sfBuffer = new SObject[meta.getBatchSizeInt()];
			 outputBuffer = new Object[meta.getBatchSizeInt()][];
			 iBufferPos = 0;
			 
			 // get total fields in the grid
			 data.nrfields = meta.getInputFields().length;
				
			 // Check if field list is filled 
			 if (data.nrfields==0)
			 {
				 throw new KettleException(Messages.getString("SalesforceOutputDialog.FieldsMissing.DialogMessage"));
			 }
 
			// Create the output row meta-data
	        data.outputRowMeta = getInputRowMeta().clone();
			meta.getFields(data.outputRowMeta, getStepname(), null, null, this);
			
			// Set the formatRowMeta as the meta data for the input rows
			if ( getInputRowMeta() == null ) {
				logDebug("Why is this empty?");
			} else {
					data.formatRowMeta = getInputRowMeta().clone();
			}
			
			 // Build the mapping of input position to field name
			 data.fieldnrs = new int[meta.getInputFields().length];
				for (int i = 0; i < meta.getInputFields().length; i++)
				{
					data.fieldnrs[i] = data.formatRowMeta.indexOfValue(meta.getInputFields()[i].getField());
					if (data.fieldnrs[i] < 0)
					{
						throw new KettleException("Field [" + meta.getInputFields()[i].getField()+ "] couldn't be found in the input stream!");
					}
				}
			
			// connect to Salesforce
			connectSalesforce();
		}
		
		if(log.isDebug()) logDebug(Messages.getString("SalesforceOutput.Log.Connected"));	
		
		try 
		{	
			writeToSalesForce(outputRowData);
		    return true; 
		} 
		catch(Exception e)
		{
				logError(Messages.getString("SalesforceOutput.log.Exception", e.getMessage()));
				setErrors(1);
				stopAll();
				setOutputDone();  // signal end to receiver(s)
				return false;				
		} // Catch 
	}		
	
	private void writeToSalesForce(Object[] rowData) throws KettleException
	{
		try {			

			if (log.isDetailed()) logDetailed("Called writeToSalesForce with " + iBufferPos + " out of " + meta.getBatchSizeInt());
			// if there is room in the buffer
			if ( iBufferPos < meta.getBatchSizeInt()) {
				// build the XML node
				MessageElement[] arNode = new MessageElement[data.nrfields];
				int index=0;
				for ( int i = 0; i < data.nrfields; i++) {
					SalesforceOutputField outputField = meta.getInputFields()[i];
					arNode[index++] = newMessageElement( outputField.getField(), rowData[data.fieldnrs[i]]);
				}				
				
				//build the SObject
				SObject	sobjPass = new SObject();
				sobjPass.set_any(arNode);
				sobjPass.setType(meta.getModule());
				
				//Load the buffer array
				sfBuffer[iBufferPos] = sobjPass;
				outputBuffer[iBufferPos] = rowData;
				iBufferPos++;
			} // if for space in buffer
			
			if ( iBufferPos >= meta.getBatchSizeInt()) {
				if (log.isDetailed()) logDetailed("Calling flush buffer from writeToSalesForce");
				flushBuffers();
			}
		} catch (Exception e) {
			throw new KettleException("\nFailed in writeToSalesForce: \n"
					+ e.getMessage());	
		}
	}	// writeToSalesForce
	
	private void flushBuffers() throws KettleException
	{
        boolean sendToErrorRow=false;
		String errorMessage = null;
		
		if(binding==null)  throw new KettleException(Messages.getString("SalesforceOutput.Exception.CanNotGetBiding"));
		try {
			// create the object(s) by sending the array to the web service
			UpsertResult[] sr = binding.upsert(meta.getUpsertField(), sfBuffer);
			for (int j = 0; j < sr.length; j++) {
				if (sr[j].isSuccess()) {
					if (log.isDetailed()) logDetailed("An account was create with an id of: " + sr[j].getId());
					
					// write out the row with the SalesForce ID
					Object[] newRow = RowDataUtil.resizeArray(outputBuffer[j], data.outputRowMeta.size());
					int newIndex = getInputRowMeta().size();
					newRow[newIndex++] = sr[j].getId();
					if (log.isDetailed()) logDetailed("The new row has an id value of : " + newRow[0]);
					putRow(data.outputRowMeta, data.outputRowMeta.cloneRow(newRow));  // copy row to output rowset(s);
				    
				    if (checkFeedback(getLinesInput()))
				    {
				    	if(log.isDetailed()) logDetailed(Messages.getString("SalesforceOutput.log.LineRow",""+ getLinesInput()));
				    }

				} else {
					// there were errors during the create call, go through the
					// errors
					// array and write them to the screen
					if (getStepMeta().isDoingErrorHandling())
					{
				         sendToErrorRow = true;
				         errorMessage = null;
				         for (int i = 0; i < sr[j].getErrors().length; i++) {
								// get the next error
								com.sforce.soap.partner.Error err = sr[j].getErrors()[i];
								errorMessage = errorMessage + ": Errors were found on item "
										+ new Integer(j).toString()
										 + " Error code is: "
										+ err.getStatusCode().toString()
								  + " Error message: " + err.getMessage();
						}
					}
					else 
					{
						if(log.isDetailed()) logDetailed("Found error from SalesForce and raising the exception"); 
						for (int i = 0; i < sr[j].getErrors().length; i++) {
							// get the next error
							com.sforce.soap.partner.Error err = sr[j].getErrors()[i];
							throw new KettleException("Errors were found on item "
									+ new Integer(j).toString()
									 + " Error code is: "
									+ err.getStatusCode().toString()
							  + " Error message: " + err.getMessage());
						} // for error messages
					}  // end of determining if pass error messages on or not.
					
					if (sendToErrorRow) {
						   // Simply add this row to the error row
						if(log.isDetailed()) logDetailed("Passing row to error step");
						   putError(getInputRowMeta(), outputBuffer[j], 1, errorMessage, null, "SalesforceOutput001");
						}
				} // error handling
				
			} // for
			
			// reset the buffers
			sfBuffer = new SObject[meta.getBatchSizeInt()];
			outputBuffer = new Object[meta.getBatchSizeInt()][];
			iBufferPos = 0;
			
		}  catch (ApiFault af) {
			throw new KettleException("\nFailed to upsert object, API Fault: \n"
					+ af.getExceptionMessage());

		} catch (Exception e) {
	
			throw new KettleException("\nFailed to upsert object, error message was: \n"
					+ e.getMessage());
			
		} 

	} // flushBuffers
	
	
	public boolean init(StepMetaInterface smi, StepDataInterface sdi)
	{
		meta=(SalesforceOutputMeta)smi;
		data=(SalesforceOutputData)sdi;
		
		logDetailed("Calling INIT on SalesforceOutput");
		if (super.init(smi, sdi))
		{
			return true;
		}
		return false;
	}
	
	public void dispose(StepMetaInterface smi, StepDataInterface sdi)
	{
		meta=(SalesforceOutputMeta)smi;
		data=(SalesforceOutputData)sdi;
		super.dispose(smi, sdi);
	}
	
	//
	// Run is were the action happens!	
	public void run()
	{
    	BaseStep.runStepThread(this, meta, data);
	}
}