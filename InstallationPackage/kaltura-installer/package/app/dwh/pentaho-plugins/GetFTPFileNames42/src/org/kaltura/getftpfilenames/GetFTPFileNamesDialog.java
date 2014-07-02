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

package org.kaltura.getftpfilenames;

import java.util.ArrayList;
import java.util.List;

import org.eclipse.swt.SWT;
import org.eclipse.swt.custom.CCombo;
import org.eclipse.swt.custom.CTabFolder;
import org.eclipse.swt.custom.CTabItem;
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
import org.eclipse.swt.widgets.Composite;
import org.eclipse.swt.widgets.DirectoryDialog;
import org.eclipse.swt.widgets.Display;
import org.eclipse.swt.widgets.Event;
import org.eclipse.swt.widgets.FileDialog;
import org.eclipse.swt.widgets.Group;
import org.eclipse.swt.widgets.Label;
import org.eclipse.swt.widgets.Listener;
import org.eclipse.swt.widgets.MessageBox;
import org.eclipse.swt.widgets.Shell;
import org.eclipse.swt.widgets.Text;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.Props;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.util.StringUtil;
import org.pentaho.di.i18n.BaseMessages;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.TransPreviewFactory;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDialogInterface;
import org.pentaho.di.ui.core.dialog.EnterNumberDialog;
import org.pentaho.di.ui.core.dialog.EnterTextDialog;
import org.pentaho.di.ui.core.dialog.ErrorDialog;
import org.pentaho.di.ui.core.dialog.PreviewRowsDialog;
import org.pentaho.di.ui.core.widget.ColumnInfo;
import org.pentaho.di.ui.core.widget.LabelTextVar;
import org.pentaho.di.ui.core.widget.TableView;
import org.pentaho.di.ui.core.widget.TextVar;
import org.pentaho.di.ui.trans.dialog.TransPreviewProgressDialog;
import org.pentaho.di.ui.trans.step.BaseStepDialog;

import com.enterprisedt.net.ftp.FTPClient;

public class GetFTPFileNamesDialog extends BaseStepDialog implements
		StepDialogInterface {
	
	private static Class<?> PKG = GetFTPFileNamesDialog.class;
	private CTabFolder wTabFolder;

	private FormData fdTabFolder;

	private CTabItem wFileTab, wFilterTab, wFTPTab;

	private Composite wFileComp, wFilterComp, wFTPComp;

	private FormData fdFileComp, fdFilterComp, fdFTPComp;

	private Label wlFilename;

	private Button wbbFilename; // Browse: add file or directory

	private Button wbdFilename; // Delete

	private Button wbeFilename; // Edit

	private Button wbaFilename; // Add or change

	private TextVar wFilename;

	private FormData fdlFilename, fdbFilename, fdbdFilename, fdbeFilename,
			fdbaFilename, fdFilename;

	private Label wlFilenameList;

	private TableView wFilenameList;

	private FormData fdlFilenameList, fdFilenameList;

	private Label wlFilemask;

	private TextVar wFilemask;

	private FormData fdlFilemask, fdFilemask;

	private Button wbShowFiles;

	private FormData fdbShowFiles;

	private Label wlRecursiveSearch;

	private Button wRecursiveSearch;

	private FormData fdlRecursiveSearch, fdRecursiveSearch;

	private GetFTPFileNamesMeta input;

	private int middle, margin;

	private ModifyListener lsMod;

	private Group wOriginFiles;

	private FormData fdOriginFiles, fdFilenameField, fdlFilenameField;
	private Button wFileField;

	private Label wlFileField, wlFilenameField;
	private CCombo wFilenameField;
	private FormData fdlFileField, fdFileField;

	private Label wlWildcardField;
	private CCombo wWildcardField;
	private FormData fdlWildcardField, fdWildcardField;

	private Group wAdditionalGroup;
	private FormData fdAdditionalGroup, fdlAddResult;
	private Group wAddFileResult;

	private FormData fdAddResult, fdAddFileResult;
	private Button wAddResult;

	private Label wlLimit;
	private Text wLimit;
	private FormData fdlLimit, fdLimit;

	private Label wlInclRownum;
	private Button wInclRownum;
	private FormData fdlInclRownum, fdRownum;

	private Label wlInclRownumField;
	private TextVar wInclRownumField;
	private FormData fdlInclRownumField, fdInclRownumField;

	private boolean getpreviousFields = false;

	private Label wlAddResult;

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
	
    // These should not be translated, they are required to exist on all
    // platforms according to the documentation of "Charset".
    private static String[] encodings = { "US-ASCII",
    	                                  "ISO-8859-1",
    	                                  "UTF-8",
    	                                  "UTF-16BE",
    	                                  "UTF-16LE",
    	                                  "UTF-16" }; 


	public GetFTPFileNamesDialog(Shell parent, Object in, TransMeta transMeta,
			String sname) {
		super(parent, (BaseStepMeta) in, transMeta, sname);
		input = (GetFTPFileNamesMeta) in;
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
		shell.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.DialogTitle"));

		middle = props.getMiddlePct();
		margin = Const.MARGIN;

		// Stepname line
		wlStepname = new Label(shell, SWT.RIGHT);
		wlStepname.setText(BaseMessages.getString(PKG, "System.Label.StepName"));
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
		// START OF FTP TAB ///
		// ////////////////////////
		wFTPTab = new CTabItem(wTabFolder, SWT.NONE);
		wFTPTab.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FTPTab.TabTitle"));

		wFTPComp = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wFTPComp);

		FormLayout ftpLayout = new FormLayout();
		ftpLayout.marginWidth = 3;
		ftpLayout.marginHeight = 3;
		wFTPComp.setLayout(ftpLayout);

		// ////////////////////////
		// START OF SERVER SETTINGS GROUP///
		// /
		wServerSettings = new Group(wFTPComp, SWT.SHADOW_NONE);
		props.setLook(wServerSettings);
		wServerSettings.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ServerSettings.Group.Label"));

		FormLayout ServerSettingsgroupLayout = new FormLayout();
		ServerSettingsgroupLayout.marginWidth = 10;
		ServerSettingsgroupLayout.marginHeight = 10;

		wServerSettings.setLayout(ServerSettingsgroupLayout);

		// ServerName line
		wServerName = new LabelTextVar(transMeta, wServerSettings,
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Server.Label"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Server.Tooltip"));
		props.setLook(wServerName);
		wServerName.addModifyListener(lsMod);
		fdServerName = new FormData();
		fdServerName.left = new FormAttachment(0, 0);
		fdServerName.top = new FormAttachment(wStepname, margin);
		fdServerName.right = new FormAttachment(100, 0);
		wServerName.setLayoutData(fdServerName);

		// Server port line
		wPort = new LabelTextVar(transMeta, wServerSettings,
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Port.Label"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Port.Tooltip"));
		props.setLook(wPort);
		wPort.addModifyListener(lsMod);
		fdPort = new FormData();
		fdPort.left = new FormAttachment(0, 0);
		fdPort.top = new FormAttachment(wServerName, margin);
		fdPort.right = new FormAttachment(100, 0);
		wPort.setLayoutData(fdPort);

		// UserName line
		wUserName = new LabelTextVar(transMeta, wServerSettings,
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.User.Label"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.User.Tooltip"));
		props.setLook(wUserName);
		wUserName.addModifyListener(lsMod);
		fdUserName = new FormData();
		fdUserName.left = new FormAttachment(0, 0);
		fdUserName.top = new FormAttachment(wPort, margin);
		fdUserName.right = new FormAttachment(100, 0);
		wUserName.setLayoutData(fdUserName);

		// Password line
		wPassword = new LabelTextVar(transMeta, wServerSettings,
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Password.Label"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Password.Tooltip"));
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
		wTest.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.TestConnection.Label"));
		props.setLook(wTest);
		fdTest = new FormData();
		wTest.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.TestConnection.Tooltip"));
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
		wAdvancedSettings = new Group(wFTPComp, SWT.SHADOW_NONE);
		props.setLook(wAdvancedSettings);
		wAdvancedSettings.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.AdvancedSettings.Group.Label"));
		FormLayout AdvancedSettingsgroupLayout = new FormLayout();
		AdvancedSettingsgroupLayout.marginWidth = 10;
		AdvancedSettingsgroupLayout.marginHeight = 10;
		wAdvancedSettings.setLayout(AdvancedSettingsgroupLayout);

		// Binary mode selection...
		wlBinaryMode = new Label(wAdvancedSettings, SWT.RIGHT);
		wlBinaryMode.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.BinaryMode.Label"));
		props.setLook(wlBinaryMode);
		fdlBinaryMode = new FormData();
		fdlBinaryMode.left = new FormAttachment(0, 0);
		fdlBinaryMode.top = new FormAttachment(wServerSettings, margin);
		fdlBinaryMode.right = new FormAttachment(middle, 0);
		wlBinaryMode.setLayoutData(fdlBinaryMode);
		wBinaryMode = new Button(wAdvancedSettings, SWT.CHECK);
		props.setLook(wBinaryMode);
		wBinaryMode.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.BinaryMode.Tooltip"));
		fdBinaryMode = new FormData();
		fdBinaryMode.left = new FormAttachment(middle, margin);
		fdBinaryMode.top = new FormAttachment(wServerSettings, margin);
		fdBinaryMode.right = new FormAttachment(100, 0);
		wBinaryMode.setLayoutData(fdBinaryMode);

		// Timeout line
		wTimeout = new LabelTextVar(transMeta, wAdvancedSettings,
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Timeout.Label"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Timeout.Tooltip"));
		props.setLook(wTimeout);
		wTimeout.addModifyListener(lsMod);
		fdTimeout = new FormData();
		fdTimeout.left = new FormAttachment(0, 0);
		fdTimeout.top = new FormAttachment(wlBinaryMode, margin);
		fdTimeout.right = new FormAttachment(100, 0);
		wTimeout.setLayoutData(fdTimeout);

		// active connection?
		wlActive = new Label(wAdvancedSettings, SWT.RIGHT);
		wlActive.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ActiveConns.Label"));
		props.setLook(wlActive);
		fdlActive = new FormData();
		fdlActive.left = new FormAttachment(0, 0);
		fdlActive.top = new FormAttachment(wTimeout, margin);
		fdlActive.right = new FormAttachment(middle, 0);
		wlActive.setLayoutData(fdlActive);
		wActive = new Button(wAdvancedSettings, SWT.CHECK);
		wActive.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ActiveConns.Tooltip"));
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
		wlControlEncoding.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ControlEncoding.Label"));
		props.setLook(wlControlEncoding);
		fdlControlEncoding = new FormData();
		fdlControlEncoding.left = new FormAttachment(0, 0);
		fdlControlEncoding.top = new FormAttachment(wActive, margin);
		fdlControlEncoding.right = new FormAttachment(middle, 0);
		wlControlEncoding.setLayoutData(fdlControlEncoding);
		wControlEncoding = new Combo(wAdvancedSettings, SWT.SINGLE | SWT.LEFT
				| SWT.BORDER);
		wControlEncoding.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ControlEncoding.Tooltip"));
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

		fdFTPComp = new FormData();
		fdFTPComp.left = new FormAttachment(0, 0);
		fdFTPComp.top = new FormAttachment(0, 0);
		fdFTPComp.right = new FormAttachment(100, 0);
		fdFTPComp.bottom = new FormAttachment(100, 0);
		wFTPComp.setLayoutData(fdFTPComp);

		wFTPComp.layout();
		wFTPTab.setControl(wFTPComp);

		// ///////////////////////////////////////////////////////////
		// / END OF FTP TAB
		// ///////////////////////////////////////////////////////////

		// ////////////////////////
		// START OF FILE TAB ///
		// ////////////////////////
		wFileTab = new CTabItem(wTabFolder, SWT.NONE);
		wFileTab.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FileTab.TabTitle"));

		wFileComp = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wFileComp);

		FormLayout fileLayout = new FormLayout();
		fileLayout.marginWidth = 3;
		fileLayout.marginHeight = 3;
		wFileComp.setLayout(fileLayout);

		// ///////////////////////////////
		// START OF Origin files GROUP //
		// ///////////////////////////////

		wOriginFiles = new Group(wFileComp, SWT.SHADOW_NONE);
		props.setLook(wOriginFiles);
		wOriginFiles.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.wOriginFiles.Label"));

		FormLayout OriginFilesgroupLayout = new FormLayout();
		OriginFilesgroupLayout.marginWidth = 10;
		OriginFilesgroupLayout.marginHeight = 10;
		wOriginFiles.setLayout(OriginFilesgroupLayout);

		// Is Filename defined in a Field
		wlFileField = new Label(wOriginFiles, SWT.RIGHT);
		wlFileField.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FileField.Label"));
		props.setLook(wlFileField);
		fdlFileField = new FormData();
		fdlFileField.left = new FormAttachment(0, -margin);
		fdlFileField.top = new FormAttachment(0, margin);
		fdlFileField.right = new FormAttachment(middle, -2 * margin);
		wlFileField.setLayoutData(fdlFileField);

		wFileField = new Button(wOriginFiles, SWT.CHECK);
		props.setLook(wFileField);
		wFileField.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FileField.Tooltip"));
		fdFileField = new FormData();
		fdFileField.left = new FormAttachment(middle, -margin);
		fdFileField.top = new FormAttachment(0, margin);
		wFileField.setLayoutData(fdFileField);
		SelectionAdapter lfilefield = new SelectionAdapter() {
			public void widgetSelected(SelectionEvent arg0) {
				ActiveFileField();
				setFileField();
				input.setChanged();
			}
		};
		wFileField.addSelectionListener(lfilefield);

		// Filename field
		wlFilenameField = new Label(wOriginFiles, SWT.RIGHT);
		wlFilenameField.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.wlFilenameField.Label"));
		props.setLook(wlFilenameField);
		fdlFilenameField = new FormData();
		fdlFilenameField.left = new FormAttachment(0, -margin);
		fdlFilenameField.top = new FormAttachment(wFileField, margin);
		fdlFilenameField.right = new FormAttachment(middle, -2 * margin);
		wlFilenameField.setLayoutData(fdlFilenameField);

		wFilenameField = new CCombo(wOriginFiles, SWT.BORDER | SWT.READ_ONLY);
		wFilenameField.setEditable(true);
		props.setLook(wFilenameField);
		wFilenameField.addModifyListener(lsMod);
		fdFilenameField = new FormData();
		fdFilenameField.left = new FormAttachment(middle, -margin);
		fdFilenameField.top = new FormAttachment(wFileField, margin);
		fdFilenameField.right = new FormAttachment(100, -margin);
		wFilenameField.setLayoutData(fdFilenameField);

		// Wildcard field
		wlWildcardField = new Label(wOriginFiles, SWT.RIGHT);
		wlWildcardField.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.wlWildcardField.Label"));
		props.setLook(wlWildcardField);
		fdlWildcardField = new FormData();
		fdlWildcardField.left = new FormAttachment(0, -margin);
		fdlWildcardField.top = new FormAttachment(wFilenameField, margin);
		fdlWildcardField.right = new FormAttachment(middle, -2 * margin);
		wlWildcardField.setLayoutData(fdlWildcardField);

		wWildcardField = new CCombo(wOriginFiles, SWT.BORDER | SWT.READ_ONLY);
		wWildcardField.setEditable(true);
		props.setLook(wWildcardField);
		wWildcardField.addModifyListener(lsMod);
		fdWildcardField = new FormData();
		fdWildcardField.left = new FormAttachment(middle, -margin);
		fdWildcardField.top = new FormAttachment(wFilenameField, margin);
		fdWildcardField.right = new FormAttachment(100, -margin);
		wWildcardField.setLayoutData(fdWildcardField);

		fdOriginFiles = new FormData();
		fdOriginFiles.left = new FormAttachment(0, margin);
		fdOriginFiles.top = new FormAttachment(wFilenameList, margin);
		fdOriginFiles.right = new FormAttachment(100, -margin);
		wOriginFiles.setLayoutData(fdOriginFiles);

		// ///////////////////////////////////////////////////////////
		// / END OF Origin files GROUP
		// ///////////////////////////////////////////////////////////

		// Filename line
		wlFilename = new Label(wFileComp, SWT.RIGHT);
		wlFilename.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Filename.Label"));
		props.setLook(wlFilename);
		fdlFilename = new FormData();
		fdlFilename.left = new FormAttachment(0, 0);
		fdlFilename.top = new FormAttachment(wOriginFiles, margin);
		fdlFilename.right = new FormAttachment(middle, -margin);
		wlFilename.setLayoutData(fdlFilename);

		wbbFilename = new Button(wFileComp, SWT.PUSH | SWT.CENTER);
		props.setLook(wbbFilename);
		wbbFilename.setText(BaseMessages.getString(PKG, "System.Button.Browse"));
		wbbFilename.setToolTipText(BaseMessages.getString(PKG, "System.Tooltip.BrowseForFileOrDirAndAdd"));
		fdbFilename = new FormData();
		fdbFilename.right = new FormAttachment(100, 0);
		fdbFilename.top = new FormAttachment(wOriginFiles, margin);
		wbbFilename.setLayoutData(fdbFilename);

		wbaFilename = new Button(wFileComp, SWT.PUSH | SWT.CENTER);
		props.setLook(wbaFilename);
		wbaFilename.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameAdd.Button"));
		wbaFilename.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameAdd.Tooltip"));
		fdbaFilename = new FormData();
		fdbaFilename.right = new FormAttachment(wbbFilename, -margin);
		fdbaFilename.top = new FormAttachment(wOriginFiles, margin);
		wbaFilename.setLayoutData(fdbaFilename);

		wFilename = new TextVar(transMeta, wFileComp, SWT.SINGLE | SWT.LEFT
				| SWT.BORDER);
		props.setLook(wFilename);
		wFilename.addModifyListener(lsMod);
		fdFilename = new FormData();
		fdFilename.left = new FormAttachment(middle, 0);
		fdFilename.right = new FormAttachment(wbaFilename, -margin);
		fdFilename.top = new FormAttachment(wOriginFiles, margin);
		wFilename.setLayoutData(fdFilename);

		wlFilemask = new Label(wFileComp, SWT.RIGHT);
		wlFilemask.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Filemask.Label"));
		props.setLook(wlFilemask);
		fdlFilemask = new FormData();
		fdlFilemask.left = new FormAttachment(0, 0);
		fdlFilemask.top = new FormAttachment(wFilename, margin);
		fdlFilemask.right = new FormAttachment(middle, -margin);
		wlFilemask.setLayoutData(fdlFilemask);
		wFilemask = new TextVar(transMeta, wFileComp, SWT.SINGLE | SWT.LEFT
				| SWT.BORDER);
		props.setLook(wFilemask);
		wFilemask.addModifyListener(lsMod);
		fdFilemask = new FormData();
		fdFilemask.left = new FormAttachment(middle, 0);
		fdFilemask.top = new FormAttachment(wFilename, margin);
		fdFilemask.right = new FormAttachment(wFilename, 0, SWT.RIGHT);
		wFilemask.setLayoutData(fdFilemask);

		// Filename list line
		wlFilenameList = new Label(wFileComp, SWT.RIGHT);
		wlFilenameList.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameList.Label"));
		props.setLook(wlFilenameList);
		fdlFilenameList = new FormData();
		fdlFilenameList.left = new FormAttachment(0, 0);
		fdlFilenameList.top = new FormAttachment(wFilemask, margin);
		fdlFilenameList.right = new FormAttachment(middle, -margin);
		wlFilenameList.setLayoutData(fdlFilenameList);

		// Buttons to the right of the screen...
		wbdFilename = new Button(wFileComp, SWT.PUSH | SWT.CENTER);
		props.setLook(wbdFilename);
		wbdFilename.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameDelete.Button"));
		wbdFilename.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameDelete.Tooltip"));
		fdbdFilename = new FormData();
		fdbdFilename.right = new FormAttachment(100, 0);
		fdbdFilename.top = new FormAttachment(wFilemask, 40);
		wbdFilename.setLayoutData(fdbdFilename);

		wbeFilename = new Button(wFileComp, SWT.PUSH | SWT.CENTER);
		props.setLook(wbeFilename);
		wbeFilename.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameEdit.Button"));
		wbeFilename.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilenameEdit.Tooltip"));
		fdbeFilename = new FormData();
		fdbeFilename.right = new FormAttachment(100, 0);
		fdbeFilename.left = new FormAttachment(wbdFilename, 0, SWT.LEFT);
		fdbeFilename.top = new FormAttachment(wbdFilename, margin);
		wbeFilename.setLayoutData(fdbeFilename);

		wbShowFiles = new Button(wFileComp, SWT.PUSH | SWT.CENTER);
		props.setLook(wbShowFiles);
		wbShowFiles.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ShowFiles.Button"));
		fdbShowFiles = new FormData();
		fdbShowFiles.left = new FormAttachment(middle, 0);
		fdbShowFiles.bottom = new FormAttachment(100, 0);
		wbShowFiles.setLayoutData(fdbShowFiles);

		ColumnInfo[] colinfo = new ColumnInfo[] {
				new ColumnInfo(
						BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FileDirColumn.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false),
				new ColumnInfo(
						BaseMessages.getString(PKG, "GetFTPFileNamesDialog.WildcardColumn.Column"),
						ColumnInfo.COLUMN_TYPE_TEXT, false), };

		colinfo[0].setUsingVariables(true);
		colinfo[1].setUsingVariables(true);
		colinfo[1].setToolTip(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.RegExpColumn.Column"));

		wFilenameList = new TableView(transMeta, wFileComp, SWT.FULL_SELECTION
				| SWT.SINGLE | SWT.BORDER, colinfo, colinfo.length, lsMod,
				props);
		props.setLook(wFilenameList);
		fdFilenameList = new FormData();
		fdFilenameList.left = new FormAttachment(middle, 0);
		fdFilenameList.right = new FormAttachment(wbdFilename, -margin);
		fdFilenameList.top = new FormAttachment(wFilemask, margin);
		fdFilenameList.bottom = new FormAttachment(wbShowFiles, -margin);
		wFilenameList.setLayoutData(fdFilenameList);

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

		fdTabFolder = new FormData();
		fdTabFolder.left = new FormAttachment(0, 0);
		fdTabFolder.top = new FormAttachment(wStepname, margin);
		fdTabFolder.right = new FormAttachment(100, 0);
		fdTabFolder.bottom = new FormAttachment(100, -50);
		wTabFolder.setLayoutData(fdTabFolder);

		// ////////////////////////
		// START OF Filter TAB ///
		// ////////////////////////
		wFilterTab = new CTabItem(wTabFolder, SWT.NONE);
		wFilterTab.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilterTab.TabTitle"));

		wFilterComp = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wFilterComp);

		FormLayout filesettingLayout = new FormLayout();
		filesettingLayout.marginWidth = 3;
		filesettingLayout.marginHeight = 3;
		wFilterComp.setLayout(fileLayout);

		// Filter File Type
		wlRecursiveSearch = new Label(wFilterComp, SWT.RIGHT);
		wlRecursiveSearch.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilterTab.RecursiveSearch.Label"));
		props.setLook(wlRecursiveSearch);
		fdlRecursiveSearch = new FormData();
		fdlRecursiveSearch.left = new FormAttachment(0, 0);
		fdlRecursiveSearch.right = new FormAttachment(middle, 0);
		fdlRecursiveSearch.top = new FormAttachment(0, 3 * margin);
		wlRecursiveSearch.setLayoutData(fdlRecursiveSearch);
		
		wRecursiveSearch = new Button(wFilterComp, SWT.CHECK);
		wRecursiveSearch.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FilterTab.RecursiveSearch.CheckBox"));
		// wFilterFileType.select(0); // +1: starts at -1
		props.setLook(wRecursiveSearch);
		fdRecursiveSearch = new FormData();
		fdRecursiveSearch.left = new FormAttachment(middle, margin);
		fdRecursiveSearch.top = new FormAttachment(0, 3 * margin);
		fdRecursiveSearch.right = new FormAttachment(100, 0);
		wRecursiveSearch.setLayoutData(fdRecursiveSearch);

		// /////////////////////////////////
		// START OF Additional Fields GROUP
		// /////////////////////////////////

		wAdditionalGroup = new Group(wFilterComp, SWT.SHADOW_NONE);
		props.setLook(wAdditionalGroup);
		wAdditionalGroup.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Group.AdditionalGroup.Label"));

		FormLayout additionalgroupLayout = new FormLayout();
		additionalgroupLayout.marginWidth = 10;
		additionalgroupLayout.marginHeight = 10;
		wAdditionalGroup.setLayout(additionalgroupLayout);

		wlInclRownum = new Label(wAdditionalGroup, SWT.RIGHT);
		wlInclRownum.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.InclRownum.Label"));
		props.setLook(wlInclRownum);
		fdlInclRownum = new FormData();
		fdlInclRownum.left = new FormAttachment(0, 0);
		fdlInclRownum.top = new FormAttachment(wRecursiveSearch, 2 * margin);
		fdlInclRownum.right = new FormAttachment(middle, -margin);
		wlInclRownum.setLayoutData(fdlInclRownum);
		wInclRownum = new Button(wAdditionalGroup, SWT.CHECK);
		props.setLook(wInclRownum);
		wInclRownum.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.InclRownum.Tooltip"));
		fdRownum = new FormData();
		fdRownum.left = new FormAttachment(middle, 0);
		fdRownum.top = new FormAttachment(wRecursiveSearch, 2 * margin);
		wInclRownum.setLayoutData(fdRownum);

		wlInclRownumField = new Label(wAdditionalGroup, SWT.RIGHT);
		wlInclRownumField.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.InclRownumField.Label"));
		props.setLook(wlInclRownumField);
		fdlInclRownumField = new FormData();
		fdlInclRownumField.left = new FormAttachment(wInclRownum, margin);
		fdlInclRownumField.top = new FormAttachment(wRecursiveSearch, 2 * margin);
		wlInclRownumField.setLayoutData(fdlInclRownumField);
		wInclRownumField = new TextVar(transMeta, wAdditionalGroup, SWT.SINGLE
				| SWT.LEFT | SWT.BORDER);
		props.setLook(wInclRownumField);
		wInclRownumField.addModifyListener(lsMod);
		fdInclRownumField = new FormData();
		fdInclRownumField.left = new FormAttachment(wlInclRownumField, margin);
		fdInclRownumField.top = new FormAttachment(wRecursiveSearch, 2 * margin);
		fdInclRownumField.right = new FormAttachment(100, 0);
		wInclRownumField.setLayoutData(fdInclRownumField);

		fdAdditionalGroup = new FormData();
		fdAdditionalGroup.left = new FormAttachment(0, margin);
		fdAdditionalGroup.top = new FormAttachment(wRecursiveSearch, margin);
		fdAdditionalGroup.right = new FormAttachment(100, -margin);
		wAdditionalGroup.setLayoutData(fdAdditionalGroup);

		// ///////////////////////////////////////////////////////////
		// / END OF DESTINATION ADDRESS GROUP
		// ///////////////////////////////////////////////////////////

		wlLimit = new Label(wFilterComp, SWT.RIGHT);
		wlLimit.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Limit.Label"));
		props.setLook(wlLimit);
		fdlLimit = new FormData();
		fdlLimit.left = new FormAttachment(0, 0);
		fdlLimit.top = new FormAttachment(wAdditionalGroup, 2 * margin);
		fdlLimit.right = new FormAttachment(middle, -margin);
		wlLimit.setLayoutData(fdlLimit);
		wLimit = new Text(wFilterComp, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wLimit);
		wLimit.addModifyListener(lsMod);
		fdLimit = new FormData();
		fdLimit.left = new FormAttachment(middle, 0);
		fdLimit.top = new FormAttachment(wAdditionalGroup, 2 * margin);
		fdLimit.right = new FormAttachment(100, 0);
		wLimit.setLayoutData(fdLimit);

		// ///////////////////////////////
		// START OF AddFileResult GROUP //
		// ///////////////////////////////

		wAddFileResult = new Group(wFilterComp, SWT.SHADOW_NONE);
		props.setLook(wAddFileResult);
		wAddFileResult.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.wAddFileResult.Label"));

		FormLayout AddFileResultgroupLayout = new FormLayout();
		AddFileResultgroupLayout.marginWidth = 10;
		AddFileResultgroupLayout.marginHeight = 10;
		wAddFileResult.setLayout(AddFileResultgroupLayout);

		wlAddResult = new Label(wAddFileResult, SWT.RIGHT);
		wlAddResult.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.AddResult.Label"));
		props.setLook(wlAddResult);
		fdlAddResult = new FormData();
		fdlAddResult.left = new FormAttachment(0, 0);
		fdlAddResult.top = new FormAttachment(wLimit, margin);
		fdlAddResult.right = new FormAttachment(middle, -margin);
		wlAddResult.setLayoutData(fdlAddResult);
		wAddResult = new Button(wAddFileResult, SWT.CHECK);
		props.setLook(wAddResult);
		wAddResult.setToolTipText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.AddResult.Tooltip"));
		fdAddResult = new FormData();
		fdAddResult.left = new FormAttachment(middle, 0);
		fdAddResult.top = new FormAttachment(wLimit, margin);
		wAddResult.setLayoutData(fdAddResult);

		fdAddFileResult = new FormData();
		fdAddFileResult.left = new FormAttachment(0, margin);
		fdAddFileResult.top = new FormAttachment(wLimit, margin);
		fdAddFileResult.right = new FormAttachment(100, -margin);
		wAddFileResult.setLayoutData(fdAddFileResult);

		// ///////////////////////////////////////////////////////////
		// / END OF AddFileResult GROUP
		// ///////////////////////////////////////////////////////////

		fdFilterComp = new FormData();
		fdFilterComp.left = new FormAttachment(0, 0);
		fdFilterComp.top = new FormAttachment(0, 0);
		fdFilterComp.right = new FormAttachment(100, 0);
		fdFilterComp.bottom = new FormAttachment(100, 0);
		wFilterComp.setLayoutData(fdFilterComp);

		wFilterComp.layout();
		wFilterTab.setControl(wFilterComp);

		// ///////////////////////////////////////////////////////////
		// / END OF FILE Filter TAB
		// ///////////////////////////////////////////////////////////

		wOK = new Button(shell, SWT.PUSH);
		wOK.setText(BaseMessages.getString(PKG, "System.Button.OK"));

		wPreview = new Button(shell, SWT.PUSH);
		wPreview.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Preview.Button"));

		wCancel = new Button(shell, SWT.PUSH);
		wCancel.setText(BaseMessages.getString(PKG, "System.Button.Cancel"));

		setButtonPositions(new Button[] { wOK, wCancel, wPreview }, margin,
				wTabFolder);

		// Add listeners
		lsOK = new Listener() {
			public void handleEvent(Event e) {
				ok();
			}
		};
		lsPreview = new Listener() {
			public void handleEvent(Event e) {
				preview();
			}
		};
		lsCancel = new Listener() {
			public void handleEvent(Event e) {
				cancel();
			}
		};

		wOK.addListener(SWT.Selection, lsOK);
		wPreview.addListener(SWT.Selection, lsPreview);
		wCancel.addListener(SWT.Selection, lsCancel);

		lsDef = new SelectionAdapter() {
			public void widgetDefaultSelected(SelectionEvent e) {
				ok();
			}
		};

		wStepname.addSelectionListener(lsDef);

		// Add the file to the list of files...
		SelectionAdapter selA = new SelectionAdapter() {
			public void widgetSelected(SelectionEvent arg0) {
				wFilenameList.add(new String[] { wFilename.getText(),
						wFilemask.getText() });
				wFilename.setText("");
				wFilemask.setText("");
				wFilenameList.removeEmptyRows();
				wFilenameList.setRowNums();
				wFilenameList.optWidth(true);
			}
		};
		wbaFilename.addSelectionListener(selA);
		wFilename.addSelectionListener(selA);

		// Delete files from the list of files...
		wbdFilename.addSelectionListener(new SelectionAdapter() {
			public void widgetSelected(SelectionEvent arg0) {
				int idx[] = wFilenameList.getSelectionIndices();
				wFilenameList.remove(idx);
				wFilenameList.removeEmptyRows();
				wFilenameList.setRowNums();
			}
		});

		// Edit the selected file & remove from the list...
		wbeFilename.addSelectionListener(new SelectionAdapter() {
			public void widgetSelected(SelectionEvent arg0) {
				int idx = wFilenameList.getSelectionIndex();
				if (idx >= 0) {
					String string[] = wFilenameList.getItem(idx);
					wFilename.setText(string[0]);
					wFilemask.setText(string[1]);
					wFilenameList.remove(idx);
				}
				wFilenameList.removeEmptyRows();
				wFilenameList.setRowNums();
			}
		});

		// Show the files that are selected at this time...
		/*wbShowFiles.addSelectionListener(new SelectionAdapter() {
			public void widgetSelected(SelectionEvent e) {
				GetFTPFileNamesMeta tfii = new GetFTPFileNamesMeta();
				getInfo(tfii);
				String files[] = tfii.getFilePaths(transMeta);
				if (files != null && files.length > 0) {
					EnterSelectionDialog esd = new EnterSelectionDialog(shell,
							files, "Files read", "Files read:");
					esd.setViewOnly();
					esd.open();
				} else {
					MessageBox mb = new MessageBox(shell, SWT.OK
							| SWT.ICON_ERROR);
					mb.setMessage(Messages
							.getString("GetFTPFileNamesDialog.NoFilesFound.DialogMessage"));
					mb.setText(BaseMessages.getString(PKG, "System.Dialog.Error.Title"));
					mb.open();
				}
			}
		});*/

		// Listen to the Browse... button
		wbbFilename.addSelectionListener(new SelectionAdapter() {
			public void widgetSelected(SelectionEvent e) {
				if (wFilemask.getText() != null
						&& wFilemask.getText().length() > 0) // A
																// mask:
																// a
																// directory!
				{
					DirectoryDialog dialog = new DirectoryDialog(shell,
							SWT.OPEN);
					if (wFilename.getText() != null) {
						String fpath = transMeta
								.environmentSubstitute(wFilename.getText());
						dialog.setFilterPath(fpath);
					}

					if (dialog.open() != null) {
						String str = dialog.getFilterPath();
						wFilename.setText(str);
					}
				} else {
					FileDialog dialog = new FileDialog(shell, SWT.OPEN);
					dialog.setFilterExtensions(new String[] { "*.txt;*.csv",
							"*.csv", "*.txt", "*" });
					if (wFilename.getText() != null) {
						String fname = transMeta
								.environmentSubstitute(wFilename.getText());
						dialog.setFileName(fname);
					}

					dialog.setFilterNames(new String[] {
							BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FileType.TextAndCSVFiles"),
							BaseMessages.getString(PKG, "System.FileType.CSVFiles"),
							BaseMessages.getString(PKG, "System.FileType.TextFiles"),
							BaseMessages.getString(PKG, "System.FileType.AllFiles") });

					if (dialog.open() != null) {
						String str = dialog.getFilterPath()
								+ System.getProperty("file.separator")
								+ dialog.getFileName();
						wFilename.setText(str);
					}
				}
			}
		});	
		
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
						mb.setMessage(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Connected.OK",host) +Const.CR);
						mb.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Connected.Title.Ok"));
						mb.open();
						ftp.quit();
					}else
					{
						MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
						mb.setMessage(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Connected.NOK.ConnectionBad",host) +Const.CR);
						mb.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Connected.Title.Bad"));
						mb.open(); 
				    }
				} catch(Exception e1)
				{
					MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
					mb.setMessage(e1.getMessage());
					mb.setText(BaseMessages.getString(PKG, "GetFTPFileNamesDialog.Connected.Title.Bad"));
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

		wTabFolder.setSelection(0);

		// Set the shell size, based upon previous time...
		setFileField();
		getData(input);
		ActiveFileField();
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
				wWildcardField.removeAll();

				RowMetaInterface r = transMeta.getPrevStepFields(stepname);
				if (r != null) {
					r.getFieldNames();

					for (int i = 0; i < r.getFieldNames().length; i++) {
						wFilenameField.add(r.getFieldNames()[i]);
						wWildcardField.add(r.getFieldNames()[i]);
					}
				}
			}

		} catch (KettleException ke) {
			new ErrorDialog(
					shell,
					BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FailedToGetFields.DialogTitle"), BaseMessages.getString(PKG, "GetFTPFileNamesDialog.FailedToGetFields.DialogMessage"), ke); //$NON-NLS-1$ //$NON-NLS-2$
		}
	}

	private void ActiveFileField() {
		if (wFileField.getSelection())
			wLimit.setText("0");
		wlFilenameField.setEnabled(wFileField.getSelection());
		wFilenameField.setEnabled(wFileField.getSelection());
		wlWildcardField.setEnabled(wFileField.getSelection());
		wWildcardField.setEnabled(wFileField.getSelection());

		wlFilename.setEnabled(!wFileField.getSelection());
		wbbFilename.setEnabled(!wFileField.getSelection());
		wbaFilename.setEnabled(!wFileField.getSelection());
		wFilename.setEnabled(!wFileField.getSelection());
		wlFilemask.setEnabled(!wFileField.getSelection());
		wFilemask.setEnabled(!wFileField.getSelection());
		wlFilenameList.setEnabled(!wFileField.getSelection());
		wbdFilename.setEnabled(!wFileField.getSelection());
		wbeFilename.setEnabled(!wFileField.getSelection());
		wbShowFiles.setEnabled(!wFileField.getSelection());
		wlFilenameList.setEnabled(!wFileField.getSelection());
		wFilenameList.setEnabled(!wFileField.getSelection());
		wPreview.setEnabled(!wFileField.getSelection());
		wlLimit.setEnabled(!wFileField.getSelection());
		wLimit.setEnabled(!wFileField.getSelection());

	}

	/**
	 * Read the data from the TextFileInputMeta object and show it in this
	 * dialog.
	 * 
	 * @param meta
	 *            The TextFileInputMeta object to obtain the data from.
	 */
	public void getData(GetFTPFileNamesMeta meta) {
		final GetFTPFileNamesMeta in = meta;

		if (in.getFileName() != null) {
			wFilenameList.removeAll();
			for (int i = 0; i < in.getFileName().length; i++) {
				wFilenameList.add(new String[] { in.getFileName()[i],
						in.getFileMask()[i], in.getFileRequired()[i] });
			}
			wFilenameList.removeEmptyRows();
			wFilenameList.setRowNums();
			wFilenameList.optWidth(true);
			wRecursiveSearch.setSelection(in.isSearchRecursively());

			wInclRownum.setSelection(in.includeRowNumber());
			wAddResult.setSelection(in.isAddResultFile());
			wFileField.setSelection(in.isFileField());
			if (in.getRowNumberField() != null)
			{
				wInclRownumField.setText(in.getRowNumberField());
			}
			if (in.getDynamicFilenameField() != null)
			{
				wFilenameField.setText(in.getDynamicFilenameField());
			}
			if (in.getDynamicWildcardField() != null)
			{
				wWildcardField.setText(in.getDynamicWildcardField());
			}
			wLimit.setText("" + in.getRowLimit());

		}
		wStepname.selectAll();
		
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

	private void getInfo(GetFTPFileNamesMeta in) {
		stepname = wStepname.getText(); // return value

		int nrfiles = wFilenameList.getItemCount();
		in.allocate(nrfiles);

		in.setFileName(wFilenameList.getItems(0));
		in.setFileMask(wFilenameList.getItems(1));
		in.setFileRequired(wFilenameList.getItems(2));

		in.setIncludeRowNumber(wInclRownum.getSelection());
		in.setAddResultFile(wAddResult.getSelection());
		in.setDynamicFilenameField(wFilenameField.getText());
		in.setDynamicWildcardField(wWildcardField.getText());
		in.setFileField(wFileField.getSelection());
		in.setRowNumberField(wInclRownumField.getText());
		in.setRowLimit(Const.toLong(wLimit.getText(), 0L));
		in.setSearchRecursively(wRecursiveSearch.getSelection());
		
		in.setHost(wServerName.getText());
		in.setPort(wPort.getText());
		in.setUsername(wUserName.getText());
		in.setPassword(wPassword.getText());
		
		in.setActiveFtpConnectionMode(wActive.getSelection());
		in.setBinaryMode(wBinaryMode.getSelection());
		in.setEncoding(wControlEncoding.getText());
		in.setTimeout(wTimeout.getText());
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
	
	// Preview the data
	private void preview() {
		// Create the XML input step
		GetFTPFileNamesMeta oneMeta = new GetFTPFileNamesMeta();
		getInfo(oneMeta);

		TransMeta previewMeta = TransPreviewFactory
				.generatePreviewTransformation(transMeta, oneMeta,
						wStepname.getText());

		EnterNumberDialog numberDialog = new EnterNumberDialog(
				shell,
				props.getDefaultPreviewSize(),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.PreviewSize.DialogTitle"),
				BaseMessages.getString(PKG, "GetFTPFileNamesDialog.PreviewSize.DialogMessage"));
		int previewSize = numberDialog.open();
		if (previewSize > 0) {
			TransPreviewProgressDialog progressDialog = new TransPreviewProgressDialog(
					shell, previewMeta, new String[] { wStepname.getText() },
					new int[] { previewSize });
			progressDialog.open();

			if (!progressDialog.isCancelled()) {
				Trans trans = progressDialog.getTrans();
				String loggingText = progressDialog.getLoggingText();

				if (trans.getResult() != null
						&& trans.getResult().getNrErrors() > 0) {
					EnterTextDialog etd = new EnterTextDialog(
							shell,
							BaseMessages.getString(PKG, "System.Dialog.Error.Title"),
							BaseMessages.getString(PKG, "GetFTPFileNamesDialog.ErrorInPreview.DialogMessage"),
							loggingText, true);
					etd.setReadOnly();
					etd.open();
				}

				PreviewRowsDialog prd = new PreviewRowsDialog(shell, transMeta,
						SWT.NONE, wStepname.getText(),
						progressDialog.getPreviewRowsMeta(wStepname.getText()),
						progressDialog.getPreviewRows(wStepname.getText()),
						loggingText);
				prd.open();
			}
		}
	}

	public String toString() {
		return this.getClass().getName();
	}
}
