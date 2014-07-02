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
 

package org.kaltura.salesforceoutput;


import java.util.ArrayList;
import java.util.List;

import org.eclipse.swt.SWT;
import org.eclipse.swt.custom.CCombo;
import org.eclipse.swt.custom.CTabFolder;
import org.eclipse.swt.custom.CTabItem;
import org.eclipse.swt.events.FocusListener;
import org.eclipse.swt.events.ModifyEvent;
import org.eclipse.swt.events.ModifyListener;
import org.eclipse.swt.events.SelectionAdapter;
import org.eclipse.swt.events.SelectionEvent;
import org.eclipse.swt.events.ShellAdapter;
import org.eclipse.swt.events.ShellEvent;
import org.eclipse.swt.graphics.Cursor;
import org.eclipse.swt.layout.FormAttachment;
import org.eclipse.swt.layout.FormData;
import org.eclipse.swt.layout.FormLayout;
import org.eclipse.swt.widgets.Button;
import org.eclipse.swt.widgets.Composite;
import org.eclipse.swt.widgets.Display;
import org.eclipse.swt.widgets.Event;
import org.eclipse.swt.widgets.Label;
import org.eclipse.swt.widgets.Listener;
import org.eclipse.swt.widgets.MessageBox;
import org.eclipse.swt.widgets.Shell;
import org.eclipse.swt.widgets.TableItem;
import org.eclipse.swt.widgets.Text;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.Props;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.ValueMeta;
import org.pentaho.di.core.util.StringUtil;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDialogInterface;
import org.pentaho.di.ui.core.dialog.ErrorDialog;
import org.pentaho.di.ui.core.widget.ColumnInfo;
import org.pentaho.di.ui.core.widget.LabelTextVar;
import org.pentaho.di.ui.core.widget.TableView;
import org.pentaho.di.ui.core.widget.TextVar;
import org.pentaho.di.ui.trans.step.BaseStepDialog;

import com.sforce.soap.partner.DescribeGlobalResult;
import com.sforce.soap.partner.DescribeSObjectResult;
import com.sforce.soap.partner.Field;
import com.sforce.soap.partner.LoginResult;
import com.sforce.soap.partner.SessionHeader;
import com.sforce.soap.partner.SforceServiceLocator;
import com.sforce.soap.partner.SoapBindingStub;

public class SalesforceOutputDialog extends BaseStepDialog implements StepDialogInterface {
	
	private String DEFAULT_DATE_FORMAT="yyyy-MM-dd";
	
	private CTabFolder wTabFolder;
	
    private CTabItem wFileTab, wFieldsTab;

	private Composite wFileComp, wFieldsComp;

	private FormData fdTabFolder,fdFileComp, fdFieldsComp;
	
	private FormData fdlModule, fdModule;

	private FormData fdlUpsertField, fdUpsertField;
	
	private FormData fdlBatchSize, fdBatchSize, fdlTimeOut,fdTimeOut;
	
	private FormData fdFields,fdUserName,fdURL,fdPassword; // fdCondition;
	
	// private Button wInclURL,wInclModule,wInclRownum;
	
	// private Label wlInclURLField,wlInclRownumField;
	
	// private Label wlInclModuleField,wlModule,wlInclSQLField,wlBatchSize; //wlCondition,
	private Label wlModule,wlBatchSize;
	
	private Label wlUpsertField, wlTimeOut;
	
	// private Label wlInclTimestampField;
	
	// private Button wInclSQL;
	
	// private TextVar wInclURLField,wInclModuleField,wInclRownumField,wInclSQLField;
	
	// private Button wInclTimestamp;
	
	// private TextVar wInclTimestampField;

	private TableView wFields;

	private SalesforceOutputMeta input;

    private LabelTextVar wUserName,wURL,wPassword;
    
 //   private Text  wCondition;
    
    private TextVar wTimeOut, wBatchSize;

    private CCombo  wModule;

    private CCombo wUpsertField;
    
    private boolean  gotModule = false; 
	
	private Button wTest;
	
	private FormData fdTest;
    private Listener lsTest;
    
	public SalesforceOutputDialog(Shell parent, Object in, TransMeta transMeta,
			String sname) {
		super(parent, (BaseStepMeta) in, transMeta, sname);
		input = (SalesforceOutputMeta) in;
	}

	public String open() {
		Shell parent = getParent();
		Display display = parent.getDisplay();

		shell = new Shell(parent, SWT.DIALOG_TRIM | SWT.RESIZE | SWT.MAX
				| SWT.MIN);
		props.setLook(shell);
		setShellImage(shell, input);

		ModifyListener lsMod = new ModifyListener() {
			public void modifyText(ModifyEvent e) {
				input.setChanged();
			}
		};
		changed = input.hasChanged();

		FormLayout formLayout = new FormLayout();
		formLayout.marginWidth = Const.FORM_MARGIN;
		formLayout.marginHeight = Const.FORM_MARGIN;

		shell.setLayout(formLayout);
		shell.setText(Messages.getString("SalesforceOutputDialog.DialogTitle"));

		int middle = props.getMiddlePct();
		int margin = Const.MARGIN;

		// Stepname line
		wlStepname = new Label(shell, SWT.RIGHT);
		wlStepname.setText(Messages.getString("System.Label.StepName"));
		props.setLook(wlStepname);
		fdlStepname = new FormData();
		fdlStepname.left = new FormAttachment(0, 0);
		fdlStepname.top = new FormAttachment(0, margin);
		fdlStepname.right = new FormAttachment(middle, -margin);
		wlStepname.setLayoutData(fdlStepname);
		wStepname = new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		wStepname.setText(stepname);
		props.setLook(wStepname);
		wStepname.addModifyListener(lsMod);
		fdStepname = new FormData();
		fdStepname.left = new FormAttachment(middle, 0);
		fdStepname.top = new FormAttachment(0, margin);
		fdStepname.right = new FormAttachment(100, 0);
		wStepname.setLayoutData(fdStepname);

		wTabFolder = new CTabFolder(shell, SWT.BORDER);
		props.setLook(wTabFolder, Props.WIDGET_STYLE_TAB);

		// ////////////////////////
		// START OF FILE TAB ///
		// ////////////////////////
		wFileTab = new CTabItem(wTabFolder, SWT.NONE);
		wFileTab.setText(Messages.getString("SalesforceOutputDialog.File.Tab"));

		wFileComp = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wFileComp);

		FormLayout fileLayout = new FormLayout();
		fileLayout.marginWidth = 3;
		fileLayout.marginHeight = 3;
		wFileComp.setLayout(fileLayout);
		
	      // Webservice URL
        wURL = new LabelTextVar(transMeta,wFileComp, Messages.getString("SalesforceOutputDialog.URL.Label"), Messages
            .getString("SalesforceOutputDialog.URL.Tooltip"));
        props.setLook(wURL);
        wURL.addModifyListener(lsMod);
        fdURL = new FormData();
        fdURL.left = new FormAttachment(0, 0);
        fdURL.top = new FormAttachment(0, margin);
        fdURL.right = new FormAttachment(100, 0);
        wURL.setLayoutData(fdURL);
        

	      // UserName line
        wUserName = new LabelTextVar(transMeta,wFileComp, Messages.getString("SalesforceOutputDialog.User.Label"), Messages
            .getString("SalesforceOutputDialog.User.Tooltip"));
        props.setLook(wUserName);
        wUserName.addModifyListener(lsMod);
        fdUserName = new FormData();
        fdUserName.left = new FormAttachment(0, 0);
        fdUserName.top = new FormAttachment(wURL, margin);
        fdUserName.right = new FormAttachment(100, 0);
        wUserName.setLayoutData(fdUserName);
		
        // Password line
        wPassword = new LabelTextVar(transMeta,wFileComp, Messages.getString("SalesforceOutputDialog.Password.Label"), Messages
            .getString("SalesforceOutputDialog.Password.Tooltip"));
        props.setLook(wPassword);
        wPassword.setEchoChar('*');
        wPassword.addModifyListener(lsMod);
        fdPassword = new FormData();
        fdPassword.left = new FormAttachment(0, 0);
        fdPassword.top = new FormAttachment(wUserName, margin);
        fdPassword.right = new FormAttachment(100, 0);
        wPassword.setLayoutData(fdPassword);

        // OK, if the password contains a variable, we don't want to have the password hidden...
        wPassword.getTextWidget().addModifyListener(new ModifyListener()
        {
            public void modifyText(ModifyEvent e)
            {
                checkPasswordVisible();
            }
        });

		// Test Salesforce connection button
		wTest=new Button(wFileComp,SWT.PUSH);
		wTest.setText(Messages.getString("SalesforceOutputDialog.TestConnection.Label"));
 		props.setLook(wTest);
		fdTest=new FormData();
		wTest.setToolTipText(Messages.getString("SalesforceOutputDialog.TestConnection.Tooltip"));
		//fdTest.left = new FormAttachment(middle, 0);
		fdTest.top  = new FormAttachment(wPassword, margin);
		fdTest.right= new FormAttachment(100, 0);
		wTest.setLayoutData(fdTest);
		
		// BatchSize value
		wlBatchSize = new Label(wFileComp, SWT.RIGHT);
		wlBatchSize.setText(Messages.getString("SalesforceOutputDialog.Limit.Label"));
		props.setLook(wlBatchSize);
		fdlBatchSize = new FormData();
		fdlBatchSize.left = new FormAttachment(0, 0);
		fdlBatchSize.top = new FormAttachment(wTest, margin);
		fdlBatchSize.right = new FormAttachment(middle, -margin);
		wlBatchSize.setLayoutData(fdlBatchSize);
		wBatchSize = new TextVar(transMeta,wFileComp, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wBatchSize);
		wBatchSize.addModifyListener(lsMod);
		fdBatchSize = new FormData();
		fdBatchSize.left = new FormAttachment(middle, 0);
		fdBatchSize.top = new FormAttachment(wTest, margin);
		fdBatchSize.right = new FormAttachment(100, 0);
		wBatchSize.setLayoutData(fdBatchSize);
	        
 		// Module
		wlModule=new Label(wFileComp, SWT.RIGHT);
        wlModule.setText(Messages.getString("SalesforceOutputDialog.Module.Label"));
        props.setLook(wlModule);
        fdlModule=new FormData();
        fdlModule.left = new FormAttachment(0, 0);
        fdlModule.top  = new FormAttachment(wBatchSize, 2*margin);
        fdlModule.right= new FormAttachment(middle, -margin);
        wlModule.setLayoutData(fdlModule);
        wModule=new CCombo(wFileComp, SWT.SINGLE | SWT.READ_ONLY | SWT.BORDER);
        wModule.setEditable(true);
        props.setLook(wModule);
        wModule.addModifyListener(lsMod);
        fdModule=new FormData();
        fdModule.left = new FormAttachment(middle, margin);
        fdModule.top  = new FormAttachment(wBatchSize, 2*margin);
        fdModule.right= new FormAttachment(100, -margin);
        wModule.setLayoutData(fdModule);
        wModule.addFocusListener(new FocusListener()
            {
                public void focusLost(org.eclipse.swt.events.FocusEvent e)
                {
                }
            
                public void focusGained(org.eclipse.swt.events.FocusEvent e)
                {
                    Cursor busy = new Cursor(shell.getDisplay(), SWT.CURSOR_WAIT);
                    shell.setCursor(busy);
                    getModulesList();
                    shell.setCursor(null);
                    busy.dispose();
                }
            }
        );

        
    	// Upsert Field
		wlUpsertField=new Label(wFileComp, SWT.RIGHT);
        wlUpsertField.setText(Messages.getString("SalesforceOutputDialog.Upsert.Label"));
        props.setLook(wlUpsertField);
        fdlUpsertField=new FormData();
        fdlUpsertField.left = new FormAttachment(0, 0);
        fdlUpsertField.top  = new FormAttachment(wModule, 2*margin);
        fdlUpsertField.right= new FormAttachment(middle, -margin);
        wlUpsertField.setLayoutData(fdlUpsertField);
        wUpsertField=new CCombo(wFileComp, SWT.SINGLE | SWT.READ_ONLY | SWT.BORDER);
        wUpsertField.setEditable(true);
        props.setLook(wUpsertField);
        wUpsertField.addModifyListener(lsMod);
        fdUpsertField=new FormData();
        fdUpsertField.left = new FormAttachment(middle, margin);
        fdUpsertField.top  = new FormAttachment(wModule, 2*margin);
        fdUpsertField.right= new FormAttachment(100, -margin);
        wUpsertField.setLayoutData(fdUpsertField);
        wUpsertField.addFocusListener(new FocusListener()
            {
                public void focusLost(org.eclipse.swt.events.FocusEvent e)
                {
                }
            
                public void focusGained(org.eclipse.swt.events.FocusEvent e)
                {
                    Cursor busy = new Cursor(shell.getDisplay(), SWT.CURSOR_WAIT);
                    shell.setCursor(busy);
                    getFieldsList();
                    shell.setCursor(null);
                    busy.dispose();
                }
            }
        );
        
		// Timeout
		wlTimeOut = new Label(wFileComp, SWT.RIGHT);
		wlTimeOut.setText(Messages.getString("SalesforceOutputDialog.TimeOut.Label"));
		props.setLook(wlTimeOut);
		fdlTimeOut = new FormData();
		fdlTimeOut.left = new FormAttachment(0, 0);
		fdlTimeOut.top = new FormAttachment(wUpsertField, 2*margin);
		fdlTimeOut.right = new FormAttachment(middle, -margin);
		wlTimeOut.setLayoutData(fdlTimeOut);
		wTimeOut = new TextVar(transMeta,wFileComp, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wTimeOut);
		wTimeOut.addModifyListener(lsMod);
		fdTimeOut = new FormData();
		fdTimeOut.left = new FormAttachment(middle, 0);
		fdTimeOut.top = new FormAttachment(wUpsertField, 2*margin);
		fdTimeOut.right = new FormAttachment(100, 0);
		wTimeOut.setLayoutData(fdTimeOut);

		fdFileComp = new FormData();
		fdFileComp.left = new FormAttachment(0, 0);
		fdFileComp.top = new FormAttachment(0, 0);
		fdFileComp.right = new FormAttachment(100, 0);
		fdFileComp.bottom = new FormAttachment(100, 0);
		wFileComp.setLayoutData(fdFileComp);

		wFileComp.layout();
		wFileTab.setControl(wFileComp);

		// ///////////////////////////////////////////////////////////
		// / END OF FILE TAB
		// ///////////////////////////////////////////////////////////
/*
		// ////////////////////////
		// START OF CONTENT TAB///
		// /
		wContentTab = new CTabItem(wTabFolder, SWT.NONE);
		wContentTab.setText(Messages.getString("SalesforceOutputDialog.Content.Tab"));

		FormLayout contentLayout = new FormLayout();
		contentLayout.marginWidth = 3;
		contentLayout.marginHeight = 3;

		wContentComp = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wContentComp);
		wContentComp.setLayout(contentLayout);

		// ///////////////////////////////
		// START OF Additional Fields GROUP  //
		///////////////////////////////// 

		wAdditionalFields = new Group(wContentComp, SWT.SHADOW_NONE);
		props.setLook(wAdditionalFields);
		wAdditionalFields.setText(Messages.getString("SalesforceOutputDialog.wAdditionalFields.Label"));
		
		FormLayout AdditionalFieldsgroupLayout = new FormLayout();
		AdditionalFieldsgroupLayout.marginWidth = 10;
		AdditionalFieldsgroupLayout.marginHeight = 10;
		wAdditionalFields.setLayout(AdditionalFieldsgroupLayout);
		
		// Add Salesforce URL in the output stream ?
		wlInclURL = new Label(wAdditionalFields, SWT.RIGHT);
		wlInclURL.setText(Messages
				.getString("SalesforceOutputDialog.InclURL.Label"));
		props.setLook(wlInclURL);
		fdlInclURL = new FormData();
		fdlInclURL.left = new FormAttachment(0, 0);
		fdlInclURL.top = new FormAttachment(0, 3*margin);
		fdlInclURL.right = new FormAttachment(middle, -margin);
		wlInclURL.setLayoutData(fdlInclURL);
		wInclURL = new Button(wAdditionalFields, SWT.CHECK);
		props.setLook(wInclURL);
		wInclURL.setToolTipText(Messages
				.getString("SalesforceOutputDialog.InclURL.Tooltip"));
		fdInclURL = new FormData();
		fdInclURL.left = new FormAttachment(middle, 0);
		fdInclURL.top = new FormAttachment(0, 3*margin);
		wInclURL.setLayoutData(fdInclURL);
		wInclURL.addSelectionListener(new SelectionAdapter() 
		{
			public void widgetSelected(SelectionEvent e) 
			{
				setEnableInclTargetURL();
			}
		}
	);

		wlInclURLField = new Label(wAdditionalFields, SWT.LEFT);
		wlInclURLField.setText(Messages
				.getString("SalesforceOutputDialog.InclURLField.Label"));
		props.setLook(wlInclURLField);
		fdlInclURLField = new FormData();
		fdlInclURLField.left = new FormAttachment(wInclURL, margin);
		fdlInclURLField.top = new FormAttachment(0, 3*margin);
		wlInclURLField.setLayoutData(fdlInclURLField);
		wInclURLField = new TextVar(transMeta,wAdditionalFields, SWT.SINGLE | SWT.LEFT	| SWT.BORDER);
		props.setLook(wlInclURLField);
		wInclURLField.addModifyListener(lsMod);
		fdInclURLField = new FormData();
		fdInclURLField.left = new FormAttachment(wlInclURLField,margin);
		fdInclURLField.top = new FormAttachment(0,  3*margin);
		fdInclURLField.right = new FormAttachment(100, 0);
		wInclURLField.setLayoutData(fdInclURLField);
		
		
		//	Add module in the output stream ?
		wlInclModule = new Label(wAdditionalFields, SWT.RIGHT);
		wlInclModule.setText(Messages.getString("SalesforceOutputDialog.InclModule.Label"));
		props.setLook(wlInclModule);
		fdlInclModule = new FormData();
		fdlInclModule.left = new FormAttachment(0, 0);
		fdlInclModule.top = new FormAttachment(wInclURLField, margin);
		fdlInclModule.right = new FormAttachment(middle, -margin);
		wlInclModule.setLayoutData(fdlInclModule);
		wInclModule = new Button(wAdditionalFields, SWT.CHECK);
		props.setLook(wInclModule);
		wInclModule.setToolTipText(Messages.getString("SalesforceOutputDialog.InclModule.Tooltip"));
		fdModule = new FormData();
		fdModule.left = new FormAttachment(middle, 0);
		fdModule.top = new FormAttachment(wInclURLField, margin);
		wInclModule.setLayoutData(fdModule);

		wInclModule.addSelectionListener(new SelectionAdapter() 
		{
			public void widgetSelected(SelectionEvent e) 
			{
				setEnableInclModule();
			}
		}
	);
		
		wlInclModuleField = new Label(wAdditionalFields, SWT.RIGHT);
		wlInclModuleField.setText(Messages
				.getString("SalesforceOutputDialog.InclModuleField.Label"));
		props.setLook(wlInclModuleField);
		fdlInclModuleField = new FormData();
		fdlInclModuleField.left = new FormAttachment(wInclModule, margin);
		fdlInclModuleField.top = new FormAttachment(wInclURLField, margin);
		wlInclModuleField.setLayoutData(fdlInclModuleField);
		wInclModuleField = new TextVar(transMeta,wAdditionalFields, SWT.SINGLE | SWT.LEFT
				| SWT.BORDER);
		props.setLook(wInclModuleField);
		wInclModuleField.addModifyListener(lsMod);
		fdInclModuleField = new FormData();
		fdInclModuleField.left = new FormAttachment(wlInclModuleField, margin);
		fdInclModuleField.top = new FormAttachment(wInclURLField, margin);
		fdInclModuleField.right = new FormAttachment(100, 0);
		wInclModuleField.setLayoutData(fdInclModuleField);


		// Add SQL in the output stream ?
		wlInclSQL = new Label(wAdditionalFields, SWT.RIGHT);
		wlInclSQL.setText(Messages.getString("SalesforceOutputDialog.InclSQL.Label"));
		props.setLook(wlInclSQL);
		fdlInclSQL = new FormData();
		fdlInclSQL.left = new FormAttachment(0, 0);
		fdlInclSQL.top = new FormAttachment(wInclModuleField, margin);
		fdlInclSQL.right = new FormAttachment(middle, -margin);
		wlInclSQL.setLayoutData(fdlInclSQL);
		wInclSQL = new Button(wAdditionalFields, SWT.CHECK);
		props.setLook(wInclSQL);
		wInclSQL.setToolTipText(Messages.getString("SalesforceOutputDialog.InclSQL.Tooltip"));
		fdInclSQL = new FormData();
		fdInclSQL.left = new FormAttachment(middle, 0);
		fdInclSQL.top = new FormAttachment(wInclModuleField, margin);
		wInclSQL.setLayoutData(fdInclSQL);
		wInclSQL.addSelectionListener(new SelectionAdapter() 
		{
			public void widgetSelected(SelectionEvent e) 
			{
				setEnableInclSQL();
			}
		}
	);

		wlInclSQLField = new Label(wAdditionalFields, SWT.LEFT);
		wlInclSQLField.setText(Messages.getString("SalesforceOutputDialog.InclSQLField.Label"));
		props.setLook(wlInclSQLField);
		fdlInclSQLField = new FormData();
		fdlInclSQLField.left = new FormAttachment(wInclSQL, margin);
		fdlInclSQLField.top = new FormAttachment(wInclModuleField, margin);
		wlInclSQLField.setLayoutData(fdlInclSQLField);
		wInclSQLField = new TextVar(transMeta,wAdditionalFields, SWT.SINGLE | SWT.LEFT	| SWT.BORDER);
		props.setLook(wlInclSQLField);
		wInclSQLField.addModifyListener(lsMod);
		fdInclSQLField = new FormData();
		fdInclSQLField.left = new FormAttachment(wlInclSQLField,margin);
		fdInclSQLField.top = new FormAttachment(wInclModuleField,  margin);
		fdInclSQLField.right = new FormAttachment(100, 0);
		wInclSQLField.setLayoutData(fdInclSQLField);
		
		// Add Timestamp in the output stream ?
		wlInclTimestamp = new Label(wAdditionalFields, SWT.RIGHT);
		wlInclTimestamp.setText(Messages.getString("SalesforceOutputDialog.InclTimestamp.Label"));
		props.setLook(wlInclTimestamp);
		fdlInclTimestamp = new FormData();
		fdlInclTimestamp.left = new FormAttachment(0, 0);
		fdlInclTimestamp.top = new FormAttachment(wInclSQLField, margin);
		fdlInclTimestamp.right = new FormAttachment(middle, -margin);
		wlInclTimestamp.setLayoutData(fdlInclTimestamp);
		wInclTimestamp = new Button(wAdditionalFields, SWT.CHECK);
		props.setLook(wInclTimestamp);
		wInclTimestamp.setToolTipText(Messages.getString("SalesforceOutputDialog.InclTimestamp.Tooltip"));
		fdInclTimestamp = new FormData();
		fdInclTimestamp.left = new FormAttachment(middle, 0);
		fdInclTimestamp.top = new FormAttachment(wInclSQLField, margin);
		wInclTimestamp.setLayoutData(fdInclTimestamp);
		wInclTimestamp.addSelectionListener(new SelectionAdapter() 
		{
			public void widgetSelected(SelectionEvent e) 
			{
				setEnableInclTimestamp();
			}
		}
	);

		wlInclTimestampField = new Label(wAdditionalFields, SWT.LEFT);
		wlInclTimestampField.setText(Messages.getString("SalesforceOutputDialog.InclTimestampField.Label"));
		props.setLook(wlInclTimestampField);
		fdlInclTimestampField = new FormData();
		fdlInclTimestampField.left = new FormAttachment(wInclTimestamp, margin);
		fdlInclTimestampField.top = new FormAttachment(wInclSQLField, margin);
		wlInclTimestampField.setLayoutData(fdlInclTimestampField);
		wInclTimestampField = new TextVar(transMeta,wAdditionalFields, SWT.SINGLE | SWT.LEFT	| SWT.BORDER);
		props.setLook(wlInclTimestampField);
		wInclTimestampField.addModifyListener(lsMod);
		fdInclTimestampField = new FormData();
		fdInclTimestampField.left = new FormAttachment(wlInclTimestampField,margin);
		fdInclTimestampField.top = new FormAttachment(wInclSQLField,  margin);
		fdInclTimestampField.right = new FormAttachment(100, 0);
		wInclTimestampField.setLayoutData(fdInclTimestampField);
		
		
		// Include Rownum in output stream?
		wlInclRownum=new Label(wAdditionalFields, SWT.RIGHT);
		wlInclRownum.setText(Messages.getString("SalesforceOutputDialog.InclRownum.Label"));
 		props.setLook(wlInclRownum);
		fdlInclRownum=new FormData();
		fdlInclRownum.left = new FormAttachment(0, 0);
		fdlInclRownum.top  = new FormAttachment(wInclTimestampField, margin);
		fdlInclRownum.right= new FormAttachment(middle, -margin);
		wlInclRownum.setLayoutData(fdlInclRownum);
		wInclRownum=new Button(wAdditionalFields, SWT.CHECK );
 		props.setLook(wInclRownum);
		wInclRownum.setToolTipText(Messages.getString("SalesforceOutputDialog.InclRownum.Tooltip"));
		fdRownum=new FormData();
		fdRownum.left = new FormAttachment(middle, 0);
		fdRownum.top  = new FormAttachment(wInclTimestampField, margin);
		wInclRownum.setLayoutData(fdRownum);

		wInclRownum.addSelectionListener(new SelectionAdapter() 
		{
			public void widgetSelected(SelectionEvent e) 
			{
				setEnableInclRownum();
			}
		}
	);
		
		wlInclRownumField=new Label(wAdditionalFields, SWT.RIGHT);
		wlInclRownumField.setText(Messages.getString("SalesforceOutputDialog.InclRownumField.Label"));
 		props.setLook(wlInclRownumField);
		fdlInclRownumField=new FormData();
		fdlInclRownumField.left = new FormAttachment(wInclRownum, margin);
		fdlInclRownumField.top  = new FormAttachment(wInclTimestampField, margin);
		wlInclRownumField.setLayoutData(fdlInclRownumField);
		wInclRownumField=new TextVar(transMeta,wAdditionalFields, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wInclRownumField);
		wInclRownumField.addModifyListener(lsMod);
		fdInclRownumField=new FormData();
		fdInclRownumField.left = new FormAttachment(wlInclRownumField, margin);
		fdInclRownumField.top  = new FormAttachment(wInclTimestampField, margin);
		fdInclRownumField.right= new FormAttachment(100, 0);
		wInclRownumField.setLayoutData(fdInclRownumField);

		
		fdAdditionalFields = new FormData();
		fdAdditionalFields.left = new FormAttachment(0, margin);
		fdAdditionalFields.top = new FormAttachment(0, 3*margin);
		fdAdditionalFields.right = new FormAttachment(100, -margin);
		wAdditionalFields.setLayoutData(fdAdditionalFields);
		
		// ///////////////////////////////////////////////////////////
		// / END OF Additional Fields GROUP
		// ///////////////////////////////////////////////////////////	
*/	

/*		
		// Limit rows
		wlLimit = new Label(wContentComp, SWT.RIGHT);
		wlLimit.setText(Messages.getString("SalesforceOutputDialog.Limit.Label"));
		props.setLook(wlLimit);
		fdlLimit = new FormData();
		fdlLimit.left = new FormAttachment(0, 0);
		fdlLimit.top = new FormAttachment(wTimeOut, margin);
		fdlLimit.right = new FormAttachment(middle, -margin);
		wlLimit.setLayoutData(fdlLimit);
		wLimit = new TextVar(transMeta,wContentComp, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wLimit);
		wLimit.addModifyListener(lsMod);
		fdLimit = new FormData();
		fdLimit.left = new FormAttachment(middle, 0);
		fdLimit.top = new FormAttachment(wTimeOut, margin);
		fdLimit.right = new FormAttachment(100, 0);
		wLimit.setLayoutData(fdLimit);

		fdContentComp = new FormData();
		fdContentComp.left = new FormAttachment(0, 0);
		fdContentComp.top = new FormAttachment(0, 0);
		fdContentComp.right = new FormAttachment(100, 0);
		fdContentComp.bottom = new FormAttachment(100, 0);
		wContentComp.setLayoutData(fdContentComp);

		wContentComp.layout();
		wContentTab.setControl(wContentComp);

		// ///////////////////////////////////////////////////////////
		// / END OF CONTENT TAB
		// ///////////////////////////////////////////////////////////
*/
		// Fields tab...
		//
		wFieldsTab = new CTabItem(wTabFolder, SWT.NONE);
		wFieldsTab.setText(Messages.getString("SalesforceOutputDialog.Fields.Tab"));

		FormLayout fieldsLayout = new FormLayout();
		fieldsLayout.marginWidth = Const.FORM_MARGIN;
		fieldsLayout.marginHeight = Const.FORM_MARGIN;

		wFieldsComp = new Composite(wTabFolder, SWT.NONE);
		wFieldsComp.setLayout(fieldsLayout);
		props.setLook(wFieldsComp);

		wGet = new Button(wFieldsComp, SWT.PUSH);
		wGet.setText(Messages.getString("SalesforceOutputDialog.GetFields.Button"));
		fdGet = new FormData();
		fdGet.left = new FormAttachment(50, 0);
		fdGet.bottom = new FormAttachment(100, 0);
		wGet.setLayoutData(fdGet);


		final int FieldsRows = input.getInputFields().length;

		ColumnInfo[] colinf = new ColumnInfo[] {
				new ColumnInfo(Messages
						.getString("SalesforceOutputDialog.FieldsTable.Name.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Field.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(Messages
						.getString("SalesforceOutputDialog.FieldsTable.Type.Column"),
						ColumnInfo.COLUMN_TYPE_CCOMBO, ValueMeta.getTypes(), true),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Format.Column"),
						ColumnInfo.COLUMN_TYPE_CCOMBO, Const.getConversionFormats()),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Length.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Precision.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Currency.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Decimal.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(Messages
						.getString("SalesforceOutputDialog.FieldsTable.Group.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.TrimType.Column"),
						ColumnInfo.COLUMN_TYPE_CCOMBO,
						SalesforceOutputField.trimTypeDesc, true),
				new ColumnInfo(
						Messages
								.getString("SalesforceOutputDialog.FieldsTable.Repeat.Column"),
						ColumnInfo.COLUMN_TYPE_CCOMBO, new String[] {
								Messages.getString("System.Combo.Yes"),
								Messages.getString("System.Combo.No") }, true),

		};

		colinf[0].setUsingVariables(true);
		colinf[0].setToolTip(Messages
				.getString("SalesforceOutputDialog.FieldsTable.Name.Column.Tooltip"));
		colinf[1].setUsingVariables(true);
		colinf[1]
				.setToolTip(Messages
						.getString("SalesforceOutputDialog.FieldsTable.Field.Column.Tooltip"));

		wFields = new TableView(transMeta,wFieldsComp, SWT.FULL_SELECTION | SWT.MULTI,
				colinf, FieldsRows, lsMod, props);

		fdFields = new FormData();
		fdFields.left = new FormAttachment(0, 0);
		fdFields.top = new FormAttachment(0, 0);
		fdFields.right = new FormAttachment(100, 0);
		fdFields.bottom = new FormAttachment(wGet, -margin);
		wFields.setLayoutData(fdFields);

		fdFieldsComp = new FormData();
		fdFieldsComp.left = new FormAttachment(0, 0);
		fdFieldsComp.top = new FormAttachment(0, 0);
		fdFieldsComp.right = new FormAttachment(100, 0);
		fdFieldsComp.bottom = new FormAttachment(100, 0);
		wFieldsComp.setLayoutData(fdFieldsComp);

		wFieldsComp.layout();
		wFieldsTab.setControl(wFieldsComp);

		fdTabFolder = new FormData();
		fdTabFolder.left = new FormAttachment(0, 0);
		fdTabFolder.top = new FormAttachment(wStepname, margin);
		fdTabFolder.right = new FormAttachment(100, 0);
		fdTabFolder.bottom = new FormAttachment(100, -50);
		wTabFolder.setLayoutData(fdTabFolder);

		wOK = new Button(shell, SWT.PUSH);
		wOK.setText(Messages.getString("System.Button.OK"));

		wPreview = new Button(shell, SWT.PUSH);
		wPreview.setText(Messages
				.getString("SalesforceOutputDialog.Button.PreviewRows"));

		wCancel = new Button(shell, SWT.PUSH);
		wCancel.setText(Messages.getString("System.Button.Cancel"));

		setButtonPositions(new Button[] { wOK, wPreview, wCancel }, margin,
				wTabFolder);

		// Add listeners
		lsOK = new Listener() {
			public void handleEvent(Event e) {
				ok();
			}
		};
		lsTest     = new Listener() { public void handleEvent(Event e) { test(); } };
		lsGet = new Listener() {
			public void handleEvent(Event e) {
		        Cursor busy = new Cursor(shell.getDisplay(), SWT.CURSOR_WAIT);
		        shell.setCursor(busy);
				get();
		        shell.setCursor(null);
		        busy.dispose();
			}
		};
		lsCancel = new Listener() {
			public void handleEvent(Event e) {
				cancel();
			}
		};

		wOK.addListener(SWT.Selection, lsOK);
		wGet.addListener(SWT.Selection, lsGet);
		wTest.addListener    (SWT.Selection, lsTest    );	
		wCancel.addListener(SWT.Selection, lsCancel);

		lsDef = new SelectionAdapter() {
			public void widgetDefaultSelected(SelectionEvent e) {
				ok();
			}
		};

		wStepname.addSelectionListener(lsDef);
//		wLimit.addSelectionListener(lsDef);
//		wInclModuleField.addSelectionListener(lsDef);
//		wInclURLField.addSelectionListener(lsDef);


		// Detect X or ALT-F4 or something that kills this window...
		shell.addShellListener(new ShellAdapter() {
			public void shellClosed(ShellEvent e) {
				cancel();
			}
		});

		wTabFolder.setSelection(0);

		// Set the shell size, based upon previous time...
		setSize();
		getData(input);
/*		setEnableInclTargetURL();
		setEnableInclSQL();
		setEnableInclTimestamp();
		setEnableInclModule();
		setEnableInclRownum(); */
		input.setChanged(changed);

		shell.open();
		while (!shell.isDisposed()) {
			if (!display.readAndDispatch())
				display.sleep();
		}
		return stepname;
	}
 public void checkPasswordVisible()
    {
        String password = wPassword.getText();
        List<String> list = new ArrayList<String>();
        StringUtil.getUsedVariables(password, list, true);
        if (list.size() == 0)
            wPassword.setEchoChar('*');
        else
            wPassword.setEchoChar('\0'); // Show it all...
    }
	  
 /*private void setEnableInclTargetURL()
 {
	wInclURLField.setEnabled(wInclURL.getSelection());
	wlInclURLField.setEnabled(wInclURL.getSelection());
 }
 private void setEnableInclSQL()
 {
	wInclSQLField.setEnabled(wInclSQL.getSelection());
	wlInclSQLField.setEnabled(wInclSQL.getSelection());
 }
 private void setEnableInclTimestamp()
 {
	wInclTimestampField.setEnabled(wInclTimestamp.getSelection());
	wlInclTimestampField.setEnabled(wInclTimestamp.getSelection());
 }
 
 private void setEnableInclModule()
 {
	wInclModuleField.setEnabled(wInclModule.getSelection());
	wlInclModuleField.setEnabled(wInclModule.getSelection());
 }
 private void setEnableInclRownum()
 {
	wInclRownumField.setEnabled(wInclRownum.getSelection());
	wlInclRownumField.setEnabled(wInclRownum.getSelection());
 }
*/ 
 
 private void test()
	{
		try
     {
			SalesforceOutputMeta meta = new SalesforceOutputMeta();
			getInfo(meta);
			
			// check if the user is given
			if (!checkUser()) return;
			
			getBinding();
			
			MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_INFORMATION );
			mb.setMessage(Messages.getString("SalesforceOutputDialog.Connected.OK",wUserName.getText()) +Const.CR);
			mb.setText(Messages.getString("SalesforceOutputDialog.Connected.Title.Ok")); 
			mb.open();
	
		}
		catch(Exception e)
		{
			MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
			mb.setMessage(Messages.getString("SalesforceOutputDialog.Connected.NOK",wUserName.getText(),e.getMessage()));
			mb.setText(Messages.getString("SalesforceOutputDialog.Connected.Title.Error")); 
			mb.open(); 
		} 
	}
 private SoapBindingStub getBinding() throws KettleException
 {
	SoapBindingStub binding;
	LoginResult loginResult = null;
	
	try {
	// get real values
	String realURL=transMeta.environmentSubstitute(wURL.getText());
	String realUsername=transMeta.environmentSubstitute(wUserName.getText());
	String realPassword=transMeta.environmentSubstitute(wPassword.getText());
	int realTimeOut=Const.toInt(transMeta.environmentSubstitute(wTimeOut.getText()),0);

	 //binding = SalesforceOutput.get
	  binding = (SoapBindingStub) new SforceServiceLocator().getSoap();
     //  Set timeout
	if(realTimeOut>0)  binding.setTimeout(realTimeOut);
      
     if (!Const.isEmpty(realURL))
     	binding._setProperty(SoapBindingStub.ENDPOINT_ADDRESS_PROPERTY, realURL);

     // Login
     loginResult = binding.login(realUsername, realPassword);
      
     // set the session header for subsequent call authentication
     binding._setProperty(SoapBindingStub.ENDPOINT_ADDRESS_PROPERTY,loginResult.getServerUrl());

     // Create a new session header object and set the session id to that
     // returned by the login
     SessionHeader sh = new SessionHeader();
     sh.setSessionId(loginResult.getSessionId());
     binding.setHeader(new SforceServiceLocator().getServiceName().getNamespaceURI(), "SessionHeader", sh);
	}catch(Exception e)
	{
		throw new KettleException(e);
	}
     return binding;
 }
  private void getModulesList()
  {
	  if (!gotModule){

		  try{
			  String selectedField=wModule.getText();
			  wModule.removeAll();
			  SoapBindingStub binding=getBinding();
			  DescribeGlobalResult describeGlobalResult = binding.describeGlobal();
			  // let's get all objects
			  // please do not fetch (too long)!
			  String[] types = describeGlobalResult.getTypes();
			  wModule.setItems(types);
			  if(!Const.isEmpty(selectedField)) wModule.setText(selectedField);
			  
		      gotModule = true;
			  
		  }catch(Exception e)
		  {
				new ErrorDialog(shell,Messages.getString("SalesforceOutputDialog.ErrorRetrieveModules.DialogTitle"),
						Messages.getString("SalesforceOutputDialog.ErrorRetrieveData.ErrorRetrieveModules"),e);
		  }
	   }
  }
 
  private void getFieldsList()
  {
	  try{
			  String selectedModule=wModule.getText();
			  String selectedField=wUpsertField.getText();
			  wUpsertField.removeAll();
			  SoapBindingStub binding=getBinding();
			  // Get object definition
		      DescribeSObjectResult describeSObjectResult = binding.describeSObject(selectedModule);
			  
		      if (describeSObjectResult != null) 
		        {
		    	  	
			      	// loop through the objects and find build the list of fields
			        Field[] fields = describeSObjectResult.getFields();
			        String[] fieldList = new String[fields.length];    
			            for (int i = 0; i < fields.length; i++) 
			            {
			            	fieldList[i] = fields[i].getName();
			            } //for
			         wUpsertField.setItems(fieldList);
		        } // if
			  
			  if(!Const.isEmpty(selectedField)) wUpsertField.setText(selectedField);
			  

			  
		  }catch(Exception e)
		  {
				new ErrorDialog(shell,Messages.getString("SalesforceOutputDialog.ErrorRetrieveModules.DialogTitle"),
						Messages.getString("SalesforceOutputDialog.ErrorRetrieveData.ErrorRetrieveModules"),e);
		  }

  }
 private void get() 
 { 
	try {
		
		SalesforceOutputMeta meta = new SalesforceOutputMeta();
		getInfo(meta);
		
		// Check if a module, username is specified 
		if (!checkInput()) return;
		
		// Clear Fields Grid
		wFields.removeAll();
	     
	   String realModule=transMeta.environmentSubstitute(wModule.getText());
	   // get binding
	   SoapBindingStub binding=getBinding();
	   // Get object
       DescribeSObjectResult describeSObjectResult = binding.describeSObject(realModule);
        
       if (describeSObjectResult != null) 
        {
		   if(!describeSObjectResult.isQueryable())
		   {
				throw new KettleException(Messages.getString("SalesforceOutputDialog.ObjectNotQueryable",realModule));
		   }else{
		        // Object is queryable
	            Field[] fields = describeSObjectResult.getFields();
	            
	            for (int i = 0; i < fields.length; i++) 
	            {
	            	String FieldLabel= fields[i].getLabel();	
	            	String FieldName= fields[i].getName();	
	            	String FieldType=fields[i].getType().getValue();
	            	String FieldLengh =  fields[i].getLength() + "";
	            	
	            	TableItem item = new TableItem(wFields.table,SWT.NONE);
					item.setText(1, FieldLabel);
					item.setText(2, FieldName);
				
					// Try to get the Type
					if (FieldType.equals("boolean")) {
						item.setText(3, "Boolean");
					} else if (FieldType.equals("datetime") || FieldType.equals("date")) {
						item.setText(3, "Date");
						item.setText(4, DEFAULT_DATE_FORMAT);
					} else if (FieldType.equals("double")) {
						item.setText(3, "Number");
	                } else if (FieldType.equals("int")) {
						item.setText(3, "Integer");
					}
				      else {
				        item.setText(3, "String");
				      }
					
					// Get length
					if (!FieldType.equals("boolean") && !FieldType.equals("datetime") && !FieldType.equals("date"))
					{
						item.setText(5, FieldLengh);
					}					
	            }
		   }
        }
		wFields.removeEmptyRows();
		wFields.setRowNums();
		wFields.optWidth(true);
	} catch (KettleException e) {
		new ErrorDialog(shell,Messages.getString("SalesforceOutputMeta.ErrorRetrieveData.DialogTitle"),
				Messages.getString("SalesforceOutputMeta.ErrorRetrieveData.DialogMessage"),	e);
	} catch (Exception e) {
		new ErrorDialog(shell,Messages.getString("SalesforceOutputMeta.ErrorRetrieveData.DialogTitle"),
				Messages.getString("SalesforceOutputMeta.ErrorRetrieveData.DialogMessage"),e);

	}
 }


	/**
	 * Read the data from the TextFileInputMeta object and show it in this
	 * dialog.
	 * 
	 * @param in
	 *            The SalesforceOutputMeta object to obtain the data from.
	 */
	public void getData(SalesforceOutputMeta in) 
	{
		wURL.setText(Const.NVL(in.getTargetURL(),""));
		wUserName.setText(Const.NVL(in.getUserName(),""));
		wPassword.setText(Const.NVL(in.getPassword(),""));
		wBatchSize.setText("10");
		wModule.setText(Const.NVL(in.getModule(), "Account"));
		wUpsertField.setText(Const.NVL(in.getUpsertField(), "Id"));
		
	//	wCondition.setText(Const.NVL(in.getCondition(),""));
		
/*		wInclURLField.setText(Const.NVL(in.getTargetURLField(),""));
		wInclURL.setSelection(in.includeTargetURL());
		
		wInclSQLField.setText(Const.NVL(in.getSQLField(),""));
		wInclSQL.setSelection(in.includeSQL());
		
		wInclTimestampField.setText(Const.NVL(in.getTimestampField(),""));
		wInclTimestamp.setSelection(in.includeTimestamp());
		
		
		wInclModuleField.setText(Const.NVL(in.getModuleField(),""));
		wInclModule.setSelection(in.includeModule());
		
		wInclRownumField.setText(Const.NVL(in.getRowNumberField(),""));
		wInclRownum.setSelection(in.includeRowNumber());
*/		
		wTimeOut.setText("" + in.getTimeOut());
			
		// wLimit.setText("" + in.getRowLimit());
		wBatchSize.setText("" + in.getBatchSize());
		
		if(log.isDebug()) log.logDebug(toString(), Messages.getString("SalesforceOutputDialog.Log.GettingFieldsInfo"));
		for (int i = 0; i < in.getInputFields().length; i++) 
		{
			SalesforceOutputField field = in.getInputFields()[i];

			if (field != null) {
				TableItem item = wFields.table.getItem(i);
				String name = field.getName();
				String xpath = field.getField();
				String type = field.getTypeDesc();
				String format = field.getFormat();
				String length = "" + field.getLength();
				String prec = "" + field.getPrecision();
				String curr = field.getCurrencySymbol();
				String group = field.getGroupSymbol();
				String decim = field.getDecimalSymbol();
				String trim = field.getTrimTypeDesc();
				String rep = field.isRepeated() ? Messages
						.getString("System.Combo.Yes") : Messages
						.getString("System.Combo.No");

				if (name != null)
					item.setText(1, name);
				if (xpath != null)
					item.setText(2, xpath);
				if (type != null)
					item.setText(3, type);
				if (format != null)
					item.setText(4, format);
				if (length != null && !"-1".equals(length))
					item.setText(5, length);
				if (prec != null && !"-1".equals(prec))
					item.setText(6, prec);
				if (curr != null)
					item.setText(7, curr);
				if (decim != null)
					item.setText(8, decim);
				if (group != null)
					item.setText(9, group);
				if (trim != null)
					item.setText(10, trim);
				if (rep != null)
					item.setText(11, rep);
			}
		}

		wFields.removeEmptyRows();
		wFields.setRowNums();
		wFields.optWidth(true);

		wStepname.selectAll();
	}

	private void cancel() {
		stepname = null;
		input.setChanged(changed);
		dispose();
	}

	private void ok() {
		try {
			getInfo(input);
		} catch (KettleException e) {
			new ErrorDialog(
					shell,Messages.getString("SalesforceOutputDialog.ErrorValidateData.DialogTitle"),
					Messages.getString("SalesforceOutputDialog.ErrorValidateData.DialogMessage"),	e);
		}
		dispose();
	}

	private void getInfo(SalesforceOutputMeta in) throws KettleException {
		stepname = wStepname.getText(); // return value

		// copy info to SalesforceOutputMeta class (input)
		in.setTargetURL(Const.NVL(wURL.getText(),in.TargetDefaultURL));
		in.setUserName(Const.NVL(wUserName.getText(),""));
		in.setPassword(Const.NVL(wPassword.getText(),""));
		in.setModule(Const.NVL(wModule.getText(),"Account"));
	//	in.setCondition(Const.NVL(wCondition.getText(),""));
		in.setUpsertField(Const.NVL(wUpsertField.getText(), "Id"));
		in.setTimeOut(Const.NVL(wTimeOut.getText(),"0"));
//		in.setRowLimit(Const.NVL(wLimit.getText(),"0"));
//		in.setTargetURLField(Const.NVL(wInclURLField.getText(),""));
//		in.setSQLField(Const.NVL(wInclSQLField.getText(),""));
//		in.setTimestampField(Const.NVL(wInclTimestampField.getText(),""));
//		in.setModuleField(Const.NVL(wInclModuleField.getText(),""));
//		in.setRowNumberField(Const.NVL(wInclRownumField.getText(),""));
		in.setBatchSize(Const.NVL(wBatchSize.getText(),"0"));
		
		// in.setIncludeTargetURL(wInclURL.getSelection());
//		in.setIncludeSQL(wInclSQL.getSelection());
//		in.setIncludeTimestamp(wInclTimestamp.getSelection());
		// in.setIncludeModule(wInclModule.getSelection());
		// in.setIncludeRowNumber(wInclRownum.getSelection());

		int nrFields = wFields.nrNonEmpty();

		in.allocate(nrFields);

		for (int i = 0; i < nrFields; i++) {
			SalesforceOutputField field = new SalesforceOutputField();

			TableItem item = wFields.getNonEmpty(i);

			field.setName(item.getText(1));
			field.setField(item.getText(2));
			field.setType(ValueMeta.getType(item.getText(3)));
			field.setFormat(item.getText(4));
			field.setLength(Const.toInt(item.getText(5), -1));
			field.setPrecision(Const.toInt(item.getText(6), -1));
			field.setCurrencySymbol(item.getText(7));
			field.setDecimalSymbol(item.getText(8));
			field.setGroupSymbol(item.getText(9));
			field.setTrimType(SalesforceOutputField
					.getTrimTypeByDesc(item.getText(10)));
			field.setRepeated(Messages.getString("System.Combo.Yes")
					.equalsIgnoreCase(item.getText(11)));

			in.getInputFields()[i] = field;
		}
	}

	// check if module, username is given
	private boolean checkInput(){
        if (Const.isEmpty(wModule.getText()))
        {
            MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
            mb.setMessage(Messages.getString("SalesforceOutputDialog.ModuleMissing.DialogMessage"));
            mb.setText(Messages.getString("System.Dialog.Error.Title"));
            mb.open(); 
            return false;
        }
        if (Const.isEmpty(wUserName.getText()))
        {
            MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
            mb.setMessage(Messages.getString("SalesforceOutputDialog.UsernameMissing.DialogMessage"));
            mb.setText(Messages.getString("System.Dialog.Error.Title"));
            mb.open(); 
            return false;
        }
        
        return true;
	}
	// check if module, username is given
	private boolean checkUser(){

        if (Const.isEmpty(wUserName.getText()))
        {
            MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
            mb.setMessage(Messages.getString("SalesforceOutputDialog.UsernameMissing.DialogMessage"));
            mb.setText(Messages.getString("System.Dialog.Error.Title"));
            mb.open(); 
            return false;
        }
        
        return true;
	}
	public String toString() {
		return this.getClass().getName();
	}
}