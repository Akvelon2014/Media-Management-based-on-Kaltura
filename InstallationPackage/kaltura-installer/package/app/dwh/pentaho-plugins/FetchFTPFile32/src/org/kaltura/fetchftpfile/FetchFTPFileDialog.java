/*
 * Copyright (c) 2007 Pentaho Corporation.  All rights reserved. 
 * This software was developed by Pentaho Corporation and is provided under the terms 
 * of the GNU Lesser General Public License, Version 2.1. You may not use 
 * this file except in compliance with the license. If you need a copy of the license, 
 * please go to http://www.gnu.org/licenses/lgpl-2.1.txt. The Original Code is Pentaho 
 * Data Integration.  The Initial Developer is Samatarn.
 *
 * Software distributed under the GNU Lesser Public License is distributed on an "AS IS" 
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or  implied. Please refer to 
 * the license for the specific language governing your rights and limitations.
 */

/*
 * Created on 18-mei-2003
 *
 */

package org.kaltura.fetchftpfile;

import java.util.ArrayList;
import java.util.List;

import org.eclipse.swt.SWT;
import org.eclipse.swt.custom.CCombo;
import org.eclipse.swt.custom.CTabFolder;
import org.eclipse.swt.events.ModifyEvent;
import org.eclipse.swt.events.ModifyListener;
import org.eclipse.swt.events.SelectionAdapter;
import org.eclipse.swt.events.SelectionEvent;
import org.eclipse.swt.events.ShellAdapter;
import org.eclipse.swt.events.ShellEvent;
import org.eclipse.swt.layout.FormAttachment;
import org.eclipse.swt.layout.FormData;
import org.eclipse.swt.layout.FormLayout;
import org.eclipse.swt.widgets.Button;
import org.eclipse.swt.widgets.Combo;
import org.eclipse.swt.widgets.Display;
import org.eclipse.swt.widgets.Event;
import org.eclipse.swt.widgets.Group;
import org.eclipse.swt.widgets.Label;
import org.eclipse.swt.widgets.Listener;
import org.eclipse.swt.widgets.MessageBox;
import org.eclipse.swt.widgets.Shell;
import org.eclipse.swt.widgets.Text;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.util.StringUtil;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDialogInterface;
import org.pentaho.di.ui.core.dialog.ErrorDialog;
import org.pentaho.di.ui.core.widget.LabelTextVar;
import org.pentaho.di.ui.trans.step.BaseStepDialog;

import com.enterprisedt.net.ftp.FTPClient;

public class FetchFTPFileDialog extends BaseStepDialog implements
		StepDialogInterface {
	private CTabFolder wTabFolder;

	private FetchFTPFileMeta input;

	private int middle, margin;

	private ModifyListener lsMod;

	
	
	private Label wlFilenameField;
	private CCombo wFilenameField;

	private FormData fdlFilenameField;

	private FormData fdFilenameField;
	
	private LabelTextVar wDestinationDir;

	private FormData fdDestinationDir;
	
	private Group wServerSettings;

	private LabelTextVar wServerName;

	private FormData fdServerName;

	private LabelTextVar wPort;

	private FormData fdPort;

	private LabelTextVar wUserName;

	private FormData fdUserName;

	private LabelTextVar wPassword;

	private FormData fdPassword;

	private Button wTest;

	private FormData fdTest;

	private FormData fdServerSettings;

	private Group wAdvancedSettings;

	private Label wlBinaryMode;

	private FormData fdlBinaryMode;

	private Button wBinaryMode;

	private FormData fdBinaryMode;

	private LabelTextVar wTimeout;

	private FormData fdTimeout;

	private Label wlActive;

	private FormData fdlActive;

	private Button wActive;

	private FormData fdActive;

	private Label wlControlEncoding;

	private FormData fdlControlEncoding;

	private Combo wControlEncoding;

	private FormData fdControlEncoding;

	private FormData fdAdvancedSettings;

	private boolean getpreviousFields;


    // These should not be translated, they are required to exist on all
    // platforms according to the documentation of "Charset".
    private static String[] encodings = { "US-ASCII",
    	                                  "ISO-8859-1",
    	                                  "UTF-8",
    	                                  "UTF-16BE",
    	                                  "UTF-16LE",
    	                                  "UTF-16" }; 


	public FetchFTPFileDialog(Shell parent, Object in, TransMeta transMeta,
			String sname) {
		super(parent, (BaseStepMeta) in, transMeta, sname);
		input = (FetchFTPFileMeta) in;
	}

	public String open() {
		Shell parent = getParent();
		Display display = parent.getDisplay();

		shell = new Shell(parent, SWT.DIALOG_TRIM | SWT.RESIZE | SWT.MAX
				| SWT.MIN);
		props.setLook(shell);
		setShellImage(shell, input);

		lsMod = new ModifyListener() {
			public void modifyText(ModifyEvent e) {
				input.setChanged();
			}
		};
		changed = input.hasChanged();

		FormLayout formLayout = new FormLayout();
		formLayout.marginWidth = Const.FORM_MARGIN;
		formLayout.marginHeight = Const.FORM_MARGIN;

		shell.setLayout(formLayout);
		shell.setText(Messages.getString("FetchFTPFileDialog.DialogTitle"));

		middle = props.getMiddlePct();
		margin = Const.MARGIN;

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

		// ////////////////////////
		// START OF SERVER SETTINGS GROUP///
		// /
		wServerSettings = new Group(shell, SWT.SHADOW_NONE);
		props.setLook(wServerSettings);
		wServerSettings.setText(Messages
				.getString("FetchFTPFileDialog.ServerSettings.Group.Label"));

		FormLayout ServerSettingsgroupLayout = new FormLayout();
		ServerSettingsgroupLayout.marginWidth = 10;
		ServerSettingsgroupLayout.marginHeight = 10;
				

		wServerSettings.setLayout(ServerSettingsgroupLayout);

		// ServerName line
		wServerName = new LabelTextVar(transMeta, wServerSettings,
				Messages.getString("FetchFTPFileDialog.Server.Label"),
				Messages.getString("FetchFTPFileDialog.Server.Tooltip"));
		props.setLook(wServerName);
		wServerName.addModifyListener(lsMod);
		fdServerName = new FormData();
		fdServerName.left = new FormAttachment(0, 0);
		fdServerName.top = new FormAttachment(0, margin);
		fdServerName.right = new FormAttachment(100, 0);
		wServerName.setLayoutData(fdServerName);

		// Server port line
		wPort = new LabelTextVar(transMeta, wServerSettings,
				Messages.getString("FetchFTPFileDialog.Port.Label"),
				Messages.getString("FetchFTPFileDialog.Port.Tooltip"));
		props.setLook(wPort);
		wPort.addModifyListener(lsMod);
		fdPort = new FormData();
		fdPort.left = new FormAttachment(0, 0);
		fdPort.top = new FormAttachment(wServerName, margin);
		fdPort.right = new FormAttachment(100, 0);
		wPort.setLayoutData(fdPort);

		// UserName line
		wUserName = new LabelTextVar(transMeta, wServerSettings,
				Messages.getString("FetchFTPFileDialog.User.Label"),
				Messages.getString("FetchFTPFileDialog.User.Tooltip"));
		props.setLook(wUserName);
		wUserName.addModifyListener(lsMod);
		fdUserName = new FormData();
		fdUserName.left = new FormAttachment(0, 0);
		fdUserName.top = new FormAttachment(wPort, margin);
		fdUserName.right = new FormAttachment(100, 0);
		wUserName.setLayoutData(fdUserName);

		// Password line
		wPassword = new LabelTextVar(transMeta, wServerSettings,
				Messages.getString("FetchFTPFileDialog.Password.Label"),
				Messages.getString("FetchFTPFileDialog.Password.Tooltip"));
		props.setLook(wPassword);
		wPassword.setEchoChar('*');
		wPassword.addModifyListener(lsMod);
		fdPassword = new FormData();
		fdPassword.left = new FormAttachment(0, 0);
		fdPassword.top = new FormAttachment(wUserName, margin);
		fdPassword.right = new FormAttachment(100, 0);
		wPassword.setLayoutData(fdPassword);

		// OK, if the password contains a variable, we don't want to have the
		// password hidden...
		wPassword.getTextWidget().addModifyListener(new ModifyListener() {
			public void modifyText(ModifyEvent e) {
				checkPasswordVisible();
			}
		});
		// Test connection button
		wTest = new Button(wServerSettings, SWT.PUSH);
		wTest.setText(Messages.getString("FetchFTPFileDialog.TestConnection.Label"));
		props.setLook(wTest);
		fdTest = new FormData();
		wTest.setToolTipText(Messages
				.getString("FetchFTPFileDialog.TestConnection.Tooltip"));
		// fdTest.left = new FormAttachment(middle, 0);
		fdTest.top = new FormAttachment(wPassword, margin);
		fdTest.right = new FormAttachment(100, 0);
		wTest.setLayoutData(fdTest);

		fdServerSettings = new FormData();
		fdServerSettings.left = new FormAttachment(0, margin);
		fdServerSettings.top = new FormAttachment(wStepname, margin);
		fdServerSettings.right = new FormAttachment(100, -margin);
		wServerSettings.setLayoutData(fdServerSettings);
		// ///////////////////////////////////////////////////////////
		// / END OF SERVER SETTINGS GROUP
		// ///////////////////////////////////////////////////////////

		// ////////////////////////
		// START OF Advanced SETTINGS GROUP///
		// /
		wAdvancedSettings = new Group(shell, SWT.SHADOW_NONE);
		props.setLook(wAdvancedSettings);
		wAdvancedSettings.setText(Messages
				.getString("FetchFTPFileDialog.AdvancedSettings.Group.Label"));
		FormLayout AdvancedSettingsgroupLayout = new FormLayout();
		AdvancedSettingsgroupLayout.marginWidth = 10;
		AdvancedSettingsgroupLayout.marginHeight = 10;
		wAdvancedSettings.setLayout(AdvancedSettingsgroupLayout);

		// Binary mode selection...
		wlBinaryMode = new Label(wAdvancedSettings, SWT.RIGHT);
		wlBinaryMode.setText(Messages.getString("FetchFTPFileDialog.BinaryMode.Label"));
		props.setLook(wlBinaryMode);
		fdlBinaryMode = new FormData();
		fdlBinaryMode.left = new FormAttachment(0, 0);
		fdlBinaryMode.top = new FormAttachment(wServerSettings, margin);
		fdlBinaryMode.right = new FormAttachment(middle, 0);
		wlBinaryMode.setLayoutData(fdlBinaryMode);
		wBinaryMode = new Button(wAdvancedSettings, SWT.CHECK);
		props.setLook(wBinaryMode);
		wBinaryMode.setToolTipText(Messages
				.getString("FetchFTPFileDialog.BinaryMode.Tooltip"));
		fdBinaryMode = new FormData();
		fdBinaryMode.left = new FormAttachment(middle, margin);
		fdBinaryMode.top = new FormAttachment(wServerSettings, margin);
		fdBinaryMode.right = new FormAttachment(100, 0);
		wBinaryMode.setLayoutData(fdBinaryMode);

		// Timeout line
		wTimeout = new LabelTextVar(transMeta, wAdvancedSettings,
				Messages.getString("FetchFTPFileDialog.Timeout.Label"),
				Messages.getString("FetchFTPFileDialog.Timeout.Tooltip"));
		props.setLook(wTimeout);
		wTimeout.addModifyListener(lsMod);
		fdTimeout = new FormData();
		fdTimeout.left = new FormAttachment(0, 0);
		fdTimeout.top = new FormAttachment(wlBinaryMode, margin);
		fdTimeout.right = new FormAttachment(100, 0);
		wTimeout.setLayoutData(fdTimeout);

		// active connection?
		wlActive = new Label(wAdvancedSettings, SWT.RIGHT);
		wlActive.setText(Messages.getString("FetchFTPFileDialog.ActiveConns.Label"));
		props.setLook(wlActive);
		fdlActive = new FormData();
		fdlActive.left = new FormAttachment(0, 0);
		fdlActive.top = new FormAttachment(wTimeout, margin);
		fdlActive.right = new FormAttachment(middle, 0);
		wlActive.setLayoutData(fdlActive);
		wActive = new Button(wAdvancedSettings, SWT.CHECK);
		wActive.setToolTipText(Messages.getString("FetchFTPFileDialog.ActiveConns.Tooltip"));
		props.setLook(wActive);
		fdActive = new FormData();
		fdActive.left = new FormAttachment(middle, margin);
		fdActive.top = new FormAttachment(wTimeout, margin);
		fdActive.right = new FormAttachment(100, 0);
		wActive.setLayoutData(fdActive);

		// Control encoding line
		//
		// The drop down is editable as it may happen an encoding may not be
		// present
		// on one machine, but you may want to use it on your execution server
		//
		wlControlEncoding = new Label(wAdvancedSettings, SWT.RIGHT);
		wlControlEncoding.setText(Messages
				.getString("FetchFTPFileDialog.ControlEncoding.Label"));
		props.setLook(wlControlEncoding);
		fdlControlEncoding = new FormData();
		fdlControlEncoding.left = new FormAttachment(0, 0);
		fdlControlEncoding.top = new FormAttachment(wActive, margin);
		fdlControlEncoding.right = new FormAttachment(middle, 0);
		wlControlEncoding.setLayoutData(fdlControlEncoding);
		wControlEncoding = new Combo(wAdvancedSettings, SWT.SINGLE | SWT.LEFT
				| SWT.BORDER);
		wControlEncoding.setToolTipText(Messages
				.getString("FetchFTPFileDialog.ControlEncoding.Tooltip"));
		wControlEncoding.setItems(encodings);
		props.setLook(wControlEncoding);
		fdControlEncoding = new FormData();
		fdControlEncoding.left = new FormAttachment(middle, margin);
		fdControlEncoding.top = new FormAttachment(wActive, margin);
		fdControlEncoding.right = new FormAttachment(100, 0);
		wControlEncoding.setLayoutData(fdControlEncoding);

		fdAdvancedSettings = new FormData();
		fdAdvancedSettings.left = new FormAttachment(0, margin);
		fdAdvancedSettings.top = new FormAttachment(wServerSettings, margin);
		fdAdvancedSettings.right = new FormAttachment(100, -margin);
		wAdvancedSettings.setLayoutData(fdAdvancedSettings);
		// ///////////////////////////////////////////////////////////
		// / END OF Advanced SETTINGS GROUP
		// ///////////////////////////////////////////////////////////

		// Filename field
		wlFilenameField = new Label(shell, SWT.RIGHT);
		wlFilenameField.setText(Messages
				.getString("FetchFTPFileDialog.wlFilenameField.Label"));
		props.setLook(wlFilenameField);
		fdlFilenameField = new FormData();
		fdlFilenameField.left = new FormAttachment(0, margin);
		fdlFilenameField.top = new FormAttachment(wAdvancedSettings, margin);
		fdlFilenameField.right = new FormAttachment(middle, -margin);
		wlFilenameField.setLayoutData(fdlFilenameField);

		wFilenameField = new CCombo(shell, SWT.BORDER | SWT.READ_ONLY);
		wFilenameField.setEditable(true);
		props.setLook(wFilenameField);
		wFilenameField.addModifyListener(lsMod);
		fdFilenameField = new FormData();
		fdFilenameField.left = new FormAttachment(middle, 0);
		fdFilenameField.top = new FormAttachment(wAdvancedSettings, margin);
		fdFilenameField.right = new FormAttachment(100, 0);
		wFilenameField.setLayoutData(fdFilenameField);

		// Destination dir
		wDestinationDir = new LabelTextVar(transMeta, shell,
				Messages.getString("FetchFTPFileDialog.DestinationDir.Label"),
				Messages.getString("FetchFTPFileDialog.DestinationDir.Tooltip"));
		props.setLook(wDestinationDir);
		wDestinationDir.addModifyListener(lsMod);
		fdDestinationDir = new FormData();
		fdDestinationDir.left = new FormAttachment(0, 0);
		fdDestinationDir.top = new FormAttachment(wFilenameField, margin);
		fdDestinationDir.right = new FormAttachment(100, 0);
		wDestinationDir.setLayoutData(fdDestinationDir);
	
		wOK = new Button(shell, SWT.PUSH);
		wOK.setText(Messages.getString("System.Button.OK"));

		wCancel = new Button(shell, SWT.PUSH);
		wCancel.setText(Messages.getString("System.Button.Cancel"));

		setButtonPositions(new Button[] { wOK, wCancel}, margin,
				wTabFolder);

		// Add listeners
		lsOK = new Listener() {
			public void handleEvent(Event e) {
				ok();
			}
		};

		lsCancel = new Listener() {
			public void handleEvent(Event e) {
				cancel();
			}
		};

		wOK.addListener(SWT.Selection, lsOK);
		wCancel.addListener(SWT.Selection, lsCancel);

		lsDef = new SelectionAdapter() {
			public void widgetDefaultSelected(SelectionEvent e) {
				ok();
			}
		};

		wStepname.addSelectionListener(lsDef);

		wTest.addSelectionListener(new SelectionAdapter()
		{
			@Override
			public void widgetSelected(SelectionEvent e)
			{
				try
				{									
					String host = transMeta.environmentSubstitute(wServerName.getText());
					int port = Const.toInt(transMeta.environmentSubstitute(wPort.getText()),21);
					String username = transMeta.environmentSubstitute(wUserName.getText());
					String pw = transMeta.environmentSubstitute(wPassword.getText());
					
					
					boolean activeMode = wActive.getSelection();
					boolean binaryMode = wBinaryMode.getSelection();
					int timeout = Const.toInt(transMeta.environmentSubstitute(wTimeout.getText()),3600000);
					String encoding = wControlEncoding.getText();
					
					
					FTPClient ftp = FTPHelper.connectToFTP(host, port, username, pw, activeMode, binaryMode, timeout, encoding);
										
			    	if(ftp.connected())
			    	{
						MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_INFORMATION );
						mb.setMessage(Messages.getString("FetchFTPFileDialog.Connected.OK",host) +Const.CR);
						mb.setText(Messages.getString("FetchFTPFileDialog.Connected.Title.Ok"));
						mb.open();
						ftp.quit();
					}else
					{
						MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
						mb.setMessage(Messages.getString("FetchFTPFileDialog.Connected.NOK.ConnectionBad",host) +Const.CR);
						mb.setText(Messages.getString("FetchFTPFileDialog.Connected.Title.Bad"));
						mb.open(); 
				    }
				} catch(Exception e1)
				{
					MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
					mb.setMessage(e1.getMessage());
					mb.setText(Messages.getString("FetchFTPFileDialog.Connected.Title.Bad"));
					mb.open(); 
				}
			}
		});
		
		// Detect X or ALT-F4 or something that kills this window...
		shell.addShellListener(new ShellAdapter() {
			public void shellClosed(ShellEvent e) {
				cancel();
			}
		});

		// Set the shell size, based upon previous time...
		setFileField();
		getData(input);
		setSize();

		shell.open();
		while (!shell.isDisposed()) {
			if (!display.readAndDispatch())
				display.sleep();
		}
		return stepname;
	}

	private void setFileField() {
		try {
			if (!getpreviousFields) {
				getpreviousFields = true;
				wFilenameField.removeAll();

				RowMetaInterface r = transMeta.getPrevStepFields(stepname);
				if (r != null) {
					r.getFieldNames();

					for (int i = 0; i < r.getFieldNames().length; i++) {
						wFilenameField.add(r.getFieldNames()[i]);
					}
				}
			}

		} catch (KettleException ke) {
			new ErrorDialog(
					shell,
					Messages.getString("FetchFTPFileDialog.FailedToGetFields.DialogTitle"), Messages.getString("FetchFTPFileDialog.FailedToGetFields.DialogMessage"), ke); //$NON-NLS-1$ //$NON-NLS-2$
		}
	}

	/**
	 * Read the data from the TextFileInputMeta object and show it in this
	 * dialog.
	 * 
	 * @param meta
	 *            The TextFileInputMeta object to obtain the data from.
	 */
	public void getData(FetchFTPFileMeta meta) {
		final FetchFTPFileMeta in = meta;

		wStepname.selectAll();
		
		if (in.getDynamicFilenameField() != null)
		{
			int i=0;
			for (String item : wFilenameField.getItems())
			{
				if (in.getDynamicFilenameField().equals(item))
				{
					wFilenameField.select(i);
					break;
				}
				i++;
			}
		}
		
		if(in.getDestinationDir()!=null)
		{
			wDestinationDir.setText(in.getDestinationDir());
		}
		
		if(in.getHost()!=null)
		{
			wServerName.setText(in.getHost());
		}
		wPort.setText(""+in.getPort());
		
		if(in.getUsername()!=null)
		{
			wUserName.setText(in.getUsername());
		}
		
		if(in.getPassword()!=null)
		{
			wPassword.setText(in.getPassword());
		}
		
		if (in.getEncoding() != null)
		{
			int i=0;
			for (String item : wControlEncoding.getItems())
			{
				if (in.getEncoding().equals(item))
				{
					wControlEncoding.select(i);
					break;
				}
				i++;
			}
		}
		
		wActive.setSelection(in.isActiveFtpConnectionMode());
		wBinaryMode.setSelection(in.isBinaryMode());
		if (in.getTimeout() != null)
		{
			wTimeout.setText(in.getTimeout());
		}
	}

	private void cancel() {
		stepname = null;
		input.setChanged(changed);
		dispose();
	}

	private void ok() {
		if (Const.isEmpty(wStepname.getText()))
			return;

		getInfo(input);
		dispose();
	}

	private void getInfo(FetchFTPFileMeta in) {
		stepname = wStepname.getText(); // return value

		in.setHost(wServerName.getText());
		in.setPort(wPort.getText());
		in.setUsername(wUserName.getText());
		in.setPassword(wPassword.getText());
		
		in.setActiveFtpConnectionMode(wActive.getSelection());
		in.setBinaryMode(wBinaryMode.getSelection());
		in.setEncoding(wControlEncoding.getText());
		in.setTimeout(wTimeout.getText());
		in.setDynamicFilenameField(wFilenameField.getText());
		in.setDestinationDir(wDestinationDir.getText());
	}

	public void checkPasswordVisible()
    {
        String password = wPassword.getText();
        List<String> list = new ArrayList<String>();
        StringUtil.getUsedVariables(password, list, true);
        if (list.size() == 0)
        {
            wPassword.setEchoChar('*');
        }
        else
        {
            wPassword.setEchoChar('\0'); // Show it all...
        }
    }
	
	public String toString() {
		return this.getClass().getName();
	}
}
