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
 * Created on 2-jul-2003
 */

package org.kaltura.dimensionlookup;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;

import org.eclipse.swt.SWT;
import org.eclipse.swt.custom.CCombo;
import org.eclipse.swt.events.FocusAdapter;
import org.eclipse.swt.events.FocusEvent;
import org.eclipse.swt.events.FocusListener;
import org.eclipse.swt.events.ModifyEvent;
import org.eclipse.swt.events.ModifyListener;
import org.eclipse.swt.events.SelectionAdapter;
import org.eclipse.swt.events.SelectionEvent;
import org.eclipse.swt.events.ShellAdapter;
import org.eclipse.swt.events.ShellEvent;
import org.eclipse.swt.layout.FormAttachment;
import org.eclipse.swt.layout.FormData;
import org.eclipse.swt.layout.FormLayout;
import org.eclipse.swt.layout.GridData;
import org.eclipse.swt.layout.GridLayout;
import org.eclipse.swt.widgets.Button;
import org.eclipse.swt.widgets.Composite;
import org.eclipse.swt.widgets.Control;
import org.eclipse.swt.widgets.Display;
import org.eclipse.swt.widgets.Event;
import org.eclipse.swt.widgets.Group;
import org.eclipse.swt.widgets.Label;
import org.eclipse.swt.widgets.Listener;
import org.eclipse.swt.widgets.MessageBox;
import org.eclipse.swt.widgets.Shell;
import org.eclipse.swt.widgets.TableItem;
import org.eclipse.swt.widgets.Text;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.SQLStatement;
import org.pentaho.di.core.database.Database;
import org.pentaho.di.core.database.DatabaseMeta;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.row.ValueMetaInterface;
import org.pentaho.di.i18n.BaseMessages;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDialogInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.ui.core.database.dialog.DatabaseDialog;
import org.pentaho.di.ui.core.database.dialog.DatabaseExplorerDialog;
import org.pentaho.di.ui.core.database.dialog.SQLEditor;
import org.pentaho.di.ui.core.dialog.ErrorDialog;
import org.pentaho.di.ui.core.widget.ColumnInfo;
import org.pentaho.di.ui.core.widget.TableView;
import org.pentaho.di.ui.core.widget.TextVar;
import org.pentaho.di.ui.trans.step.BaseStepDialog;
import org.pentaho.di.ui.trans.step.TableItemInsertListener;

public class CombinationLookupDialog extends BaseStepDialog implements StepDialogInterface
{
	private static Class<?> PKG = CombinationLookupDialog.class;
	
	private CCombo       wReadConnection;
	private CCombo       wWriteConnection;

    private Label        wlSchema;
    private TextVar       wSchema;

	private Label        wlTable;
	private Button       wbTable;
	private TextVar      wTable;

	private Label        wlCommit;
	private Text         wCommit;

	private Label        wlCachesize;
	private Text         wCachesize;
	
	private Label        wlTk;
	private Text         wTk;

	private Group        gTechGroup;
	private FormData     fdTechGroup;
	
	private Label        wlAutoinc;
	private Button       wAutoinc;

	private Label        wlTableMax;
	private Button       wTableMax;

	private Label        wlSeqButton;
	private Button       wSeqButton;
	private Text         wSeq;     

	private Label        wlReplace;
	private Button       wReplace;

	private Label        wlHashcode;
	private Button       wHashcode;

	private Label        wlKey;
	private TableView    wKey;

	private Label        wlHashfield;
	private Text         wHashfield;
	
	private Label        wlLastUpdateField;
	private Text         wLastUpdateField;

	private Button       wGet, wCreate;
	private Listener     lsGet, lsCreate;	
	
	private ColumnInfo[] ciKey;

	private CombinationLookupMeta input;

	private DatabaseMeta cWrite;
	
    private Map<String, Integer> inputFields;
    
	/**
	 * List of ColumnInfo that should have the field names of the selected database table
	 */
	private List<ColumnInfo> tableFieldColumns = new ArrayList<ColumnInfo>();

	public CombinationLookupDialog(Shell parent, Object in, TransMeta transMeta, String sname)
	{
		super(parent, (BaseStepMeta)in, transMeta, sname);
		input=(CombinationLookupMeta)in;
        inputFields =new HashMap<String, Integer>();
	}

	public String open()
	{
		Shell parent = getParent();
		Display display = parent.getDisplay();

		shell = new Shell(parent, SWT.DIALOG_TRIM | SWT.RESIZE | SWT.MAX | SWT.MIN );
 		props.setLook(shell);
        setShellImage(shell, input);

		FormLayout formLayout = new FormLayout ();
		formLayout.marginWidth  = Const.FORM_MARGIN;
		formLayout.marginHeight = Const.FORM_MARGIN;

		shell.setLayout(formLayout);
		
		shell.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Shell.Title"));

		int middle = props.getMiddlePct();
		int margin = Const.MARGIN;

		ModifyListener lsMod = new ModifyListener()
		{
			public void modifyText(ModifyEvent e)
			{
				input.setChanged();
			}
		};
		FocusListener lsFocusLost = new FocusAdapter() {
			public void focusLost(FocusEvent arg0) {
				setTableFieldCombo();
			}
		};
		backupChanged = input.hasChanged();
		input.getDatabaseReadMeta();
		cWrite = input.getDatabaseWriteMeta();

		// Stepname line
		wlStepname=new Label(shell, SWT.RIGHT);
		wlStepname.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Stepname.Label"));
 		props.setLook(wlStepname);

		fdlStepname=new FormData();
		fdlStepname.left = new FormAttachment(0, 0);
		fdlStepname.right= new FormAttachment(middle, -margin);
		fdlStepname.top  = new FormAttachment(0, margin);
		wlStepname.setLayoutData(fdlStepname);
		wStepname=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		wStepname.setText(stepname);
 		props.setLook(wStepname);
		wStepname.addModifyListener(lsMod);
		fdStepname=new FormData();
		fdStepname.left = new FormAttachment(middle, 0);
		fdStepname.top  = new FormAttachment(0, margin);
		fdStepname.right= new FormAttachment(100, 0);
		wStepname.setLayoutData(fdStepname);

		// Connection line
		wReadConnection = addConnectionLine(shell, wStepname, middle, margin,"ConcurrentCombinationLookupDialog.Database.ReadLabel");
		if (input.getDatabaseReadMeta()==null && transMeta.nrDatabases()==1) wReadConnection.select(0);
		wReadConnection.addModifyListener(lsMod);
		wReadConnection.addModifyListener(new ModifyListener()
			{
				public void modifyText(ModifyEvent e)
				{
					input.setChanged();
				}
			}
		);
		
		// Connection line
		wWriteConnection = addConnectionLine(shell, wReadConnection, middle, margin,"ConcurrentCombinationLookupDialog.Database.WriteLabel");
		if (input.getDatabaseWriteMeta()==null && transMeta.nrDatabases()==1) wWriteConnection.select(0);
		wWriteConnection.addModifyListener(lsMod);
		wWriteConnection.addModifyListener(new ModifyListener()
			{
				public void modifyText(ModifyEvent e)
				{
					// We have new content: change ci connection:
					cWrite = transMeta.findDatabase(wWriteConnection.getText());
					setAutoincUse();
					setSequence();
					input.setChanged();
				}
			}
		);
		
        // Schema line...
        wlSchema=new Label(shell, SWT.RIGHT);
        
        wlSchema.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.TargetSchema.Label")); //$NON-NLS-1$
        props.setLook(wlSchema);
        FormData fdlSchema = new FormData();
        fdlSchema.left = new FormAttachment(0, 0);
        fdlSchema.right= new FormAttachment(middle, -margin);
        fdlSchema.top  = new FormAttachment(wWriteConnection, margin);
        wlSchema.setLayoutData(fdlSchema);

        wSchema=new TextVar(transMeta, shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
        props.setLook(wSchema);
        wSchema.addModifyListener(lsMod);
        wSchema.addFocusListener(lsFocusLost);
        FormData fdSchema = new FormData();
        fdSchema.left = new FormAttachment(middle, 0);
        fdSchema.top  = new FormAttachment(wWriteConnection, margin);
        fdSchema.right= new FormAttachment(100, 0);
        wSchema.setLayoutData(fdSchema);


        // Table line...
		wlTable = new Label(shell, SWT.RIGHT);
		wlTable.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Target.Label")); //$NON-NLS-1$
 		props.setLook(wlTable);
		FormData fdlTable = new FormData();
		fdlTable.left = new FormAttachment(0, 0);
		fdlTable.right = new FormAttachment(middle, -margin);
		fdlTable.top = new FormAttachment(wSchema, margin );
		wlTable.setLayoutData(fdlTable);

		wbTable = new Button(shell, SWT.PUSH | SWT.CENTER);
 		props.setLook(wbTable);
 		wbTable.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.BrowseTable.Button"));
		FormData fdbTable = new FormData();
		fdbTable.right = new FormAttachment(100, 0);
		fdbTable.top = new FormAttachment(wSchema, margin);
		wbTable.setLayoutData(fdbTable);

		wTable = new TextVar(transMeta,shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wTable);
		wTable.addModifyListener(lsMod);
		wTable.addFocusListener(lsFocusLost);
		FormData fdTable = new FormData();
		fdTable.left = new FormAttachment(middle, 0);
		fdTable.top = new FormAttachment(wSchema, margin );
		fdTable.right = new FormAttachment(wbTable, -margin);
		wTable.setLayoutData(fdTable);		
		
		// Commit size ...
		wlCommit=new Label(shell, SWT.RIGHT);
		wlCommit.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Commitsize.Label")); //$NON-NLS-1$
 		props.setLook(wlCommit);
		FormData fdlCommit = new FormData();
		fdlCommit.left = new FormAttachment(0, 0);
		fdlCommit.right= new FormAttachment(middle, -margin);
		fdlCommit.top  = new FormAttachment(wTable, margin);
		wlCommit.setLayoutData(fdlCommit);
		wCommit=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wCommit);
		wCommit.addModifyListener(lsMod);
		FormData fdCommit = new FormData();
		fdCommit.top  = new FormAttachment(wTable, margin);
		fdCommit.left = new FormAttachment(middle, 0);
		fdCommit.right= new FormAttachment(middle+(100-middle)/3, -margin);
		wCommit.setLayoutData(fdCommit);

		// Cache size
		wlCachesize=new Label(shell, SWT.RIGHT);
		wlCachesize.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Cachesize.Label")); //$NON-NLS-1$
 		props.setLook(wlCachesize); 		
		FormData fdlCachesize=new FormData();
		fdlCachesize.top   = new FormAttachment(wTable, margin);
		fdlCachesize.left  = new FormAttachment(wCommit, margin);
		fdlCachesize.right = new FormAttachment(middle+2*(100-middle)/3, -margin);		
		wlCachesize.setLayoutData(fdlCachesize);
		wCachesize=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wCachesize);
		wCachesize.addModifyListener(lsMod);
		FormData fdCachesize=new FormData();
		fdCachesize.top   = new FormAttachment(wTable, margin);
		fdCachesize.left  = new FormAttachment(wlCachesize, margin);
		fdCachesize.right = new FormAttachment(100, 0);
		wCachesize.setLayoutData(fdCachesize);
		wCachesize.setToolTipText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Cachesize.ToolTip")); //$NON-NLS-1$		
			
		//
		// The Lookup fields: usually the (business) key
		//
		wlKey=new Label(shell, SWT.NONE);
		wlKey.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Keyfields.Label")); //$NON-NLS-1$
 		props.setLook(wlKey);
		FormData fdlKey = new FormData();
		fdlKey.left  = new FormAttachment(0, 0);
		fdlKey.top   = new FormAttachment(wCommit, margin);
		fdlKey.right = new FormAttachment(100, 0);
		wlKey.setLayoutData(fdlKey);

		int nrKeyCols=2;
		int nrKeyRows=(input.getKeyField()!=null?input.getKeyField().length:1);

		ciKey=new ColumnInfo[nrKeyCols];
		ciKey[0]=new ColumnInfo(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.ColumnInfo.DimensionField"),   ColumnInfo.COLUMN_TYPE_CCOMBO, new String[] { "" }, false); //$NON-NLS-1$
		ciKey[1]=new ColumnInfo(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.ColumnInfo.FieldInStream"),   ColumnInfo.COLUMN_TYPE_CCOMBO, new String[] { "" }, false); //$NON-NLS-1$
		tableFieldColumns.add(ciKey[0]);
		wKey=new TableView(transMeta, shell,
						      SWT.BORDER | SWT.FULL_SELECTION | SWT.MULTI | SWT.V_SCROLL | SWT.H_SCROLL,
						      ciKey,
						      nrKeyRows,
						      lsMod,
							  props
						      );

		// THE BUTTONS
		wOK=new Button(shell, SWT.PUSH);
		wOK.setText(BaseMessages.getString(PKG, "System.Button.OK")); //$NON-NLS-1$
		wGet=new Button(shell, SWT.PUSH);
		wGet.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.GetFields.Button")); //$NON-NLS-1$
		wCreate=new Button(shell, SWT.PUSH);
		wCreate.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.SQL.Button")); //$NON-NLS-1$
		wCancel=new Button(shell, SWT.PUSH);
		wCancel.setText(BaseMessages.getString(PKG, "System.Button.Cancel")); //$NON-NLS-1$

		setButtonPositions(new Button[] { wOK, wCancel , wGet, wCreate}, margin, null);

		// Last update field:
		wlLastUpdateField=new Label(shell, SWT.RIGHT);
		wlLastUpdateField.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.LastUpdateField.Label")); //$NON-NLS-1$
 		props.setLook(wlLastUpdateField);
		FormData fdlLastUpdateField = new FormData();
		fdlLastUpdateField.left  = new FormAttachment(0, 0);
		fdlLastUpdateField.right = new FormAttachment(middle, -margin);
		fdlLastUpdateField.bottom= new FormAttachment(wOK, -2*margin);
		wlLastUpdateField.setLayoutData(fdlLastUpdateField);
		wLastUpdateField=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wLastUpdateField);
		wLastUpdateField.addModifyListener(lsMod);
		FormData fdLastUpdateField = new FormData();
		fdLastUpdateField.left  = new FormAttachment(middle, 0);
		fdLastUpdateField.right = new FormAttachment(100, 0);
		fdLastUpdateField.bottom= new FormAttachment(wOK, -2*margin);
		wLastUpdateField.setLayoutData(fdLastUpdateField);

		// Hash field:
		wlHashfield=new Label(shell, SWT.RIGHT);
		wlHashfield.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Hashfield.Label")); //$NON-NLS-1$
 		props.setLook(wlHashfield);
		FormData fdlHashfield = new FormData();
		fdlHashfield.left  = new FormAttachment(0, 0);
		fdlHashfield.right = new FormAttachment(middle, -margin);
		fdlHashfield.bottom= new FormAttachment(wLastUpdateField, -margin);
		wlHashfield.setLayoutData(fdlHashfield);
		wHashfield=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wHashfield);
		wHashfield.addModifyListener(lsMod);
		FormData fdHashfield = new FormData();
		fdHashfield.left  = new FormAttachment(middle, 0);
		fdHashfield.right = new FormAttachment(100, 0);
		fdHashfield.bottom= new FormAttachment(wLastUpdateField, -margin);
		wHashfield.setLayoutData(fdHashfield);

		// Output the input rows or one (1) log-record?
		wlHashcode=new Label(shell, SWT.RIGHT);
		wlHashcode.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Hashcode.Label")); //$NON-NLS-1$
 		props.setLook(wlHashcode);
		FormData fdlHashcode = new FormData();
		fdlHashcode.left  = new FormAttachment(0, 0);
		fdlHashcode.right = new FormAttachment(middle, -margin);
		fdlHashcode.bottom= new FormAttachment(wHashfield, -margin);
		wlHashcode.setLayoutData(fdlHashcode);
		wHashcode=new Button(shell, SWT.CHECK);
 		props.setLook(wHashcode);
		FormData fdHashcode = new FormData();
		fdHashcode.left   = new FormAttachment(middle, 0);
		fdHashcode.right  = new FormAttachment(100, 0);
		fdHashcode.bottom = new FormAttachment(wHashfield, -margin);
		wHashcode.setLayoutData(fdHashcode);
		wHashcode.addSelectionListener(new SelectionAdapter()
			{
				public void widgetSelected(SelectionEvent e)
				{
					enableFields();					
				}
			}
		);

		// Replace lookup fields in the output stream?
		wlReplace=new Label(shell, SWT.RIGHT);
		wlReplace.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Replace.Label")); //$NON-NLS-1$
 		props.setLook(wlReplace);
		FormData fdlReplace = new FormData();
		fdlReplace.left  = new FormAttachment(0, 0);
		fdlReplace.right = new FormAttachment(middle, -margin);
		fdlReplace.bottom= new FormAttachment(wHashcode, -margin);
		wlReplace.setLayoutData(fdlReplace);
		wReplace=new Button(shell, SWT.CHECK);
 		props.setLook(wReplace);
		FormData fdReplace = new FormData();
		fdReplace.left  = new FormAttachment(middle, 0);
		fdReplace.bottom= new FormAttachment(wHashcode, -margin);
		fdReplace.right = new FormAttachment(100, 0);
		wReplace.setLayoutData(fdReplace);
		wReplace.addSelectionListener(new SelectionAdapter()
			{
				public void widgetSelected(SelectionEvent e)
				{
					enableFields();
				}
			}
		);

		gTechGroup = new Group(shell, SWT.SHADOW_ETCHED_IN);
		gTechGroup.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.TechGroup.Label")); //$NON-NLS-1$;
		GridLayout gridLayout = new GridLayout(3, false);
		gTechGroup.setLayout(gridLayout);
		fdTechGroup=new FormData();
		fdTechGroup.left   = new FormAttachment(middle, 0);
		fdTechGroup.bottom = new FormAttachment(wReplace, -margin);
		fdTechGroup.right  = new FormAttachment(100, 0);
		gTechGroup.setBackground(shell.getBackground()); // the default looks ugly
		gTechGroup.setLayoutData(fdTechGroup);

		// Use maximum of table + 1
		wTableMax=new Button(gTechGroup, SWT.RADIO);
 		props.setLook(wTableMax);
 		wTableMax.setSelection(false);
		GridData gdTableMax = new GridData();
		wTableMax.setLayoutData(gdTableMax);
		wTableMax.setToolTipText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.TableMaximum.Tooltip",Const.CR)); //$NON-NLS-1$ //$NON-NLS-2$
		wlTableMax=new Label(gTechGroup, SWT.LEFT);
		wlTableMax.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.TableMaximum.Label")); //$NON-NLS-1$
 		props.setLook(wlTableMax);
		GridData gdlTableMax = new GridData(GridData.FILL_BOTH);
		gdlTableMax.horizontalSpan = 2; gdlTableMax.verticalSpan = 1;
		wlTableMax.setLayoutData(gdlTableMax);
		
		// Sequence Check Button
		wSeqButton=new Button(gTechGroup, SWT.RADIO);
 		props.setLook(wSeqButton);
 		wSeqButton.setSelection(false);
		GridData gdSeqButton = new GridData();
		wSeqButton.setLayoutData(gdSeqButton);
		wSeqButton.setToolTipText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Sequence.Tooltip",Const.CR)); //$NON-NLS-1$ //$NON-NLS-2$		
		wlSeqButton=new Label(gTechGroup, SWT.LEFT);
		wlSeqButton.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Sequence.Label")); //$NON-NLS-1$
 		props.setLook(wlSeqButton); 	
		GridData gdlSeqButton = new GridData();
		wlSeqButton.setLayoutData(gdlSeqButton);

		wSeq=new Text(gTechGroup, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wSeq);
		wSeq.addModifyListener(lsMod);
		GridData gdSeq = new GridData(GridData.FILL_HORIZONTAL);
		wSeq.setLayoutData(gdSeq);
		wSeq.addFocusListener(new FocusListener() {
			public void focusGained(FocusEvent arg0) {
				input.setTechKeyCreation(CombinationLookupMeta.CREATION_METHOD_SEQUENCE);
				wSeqButton.setSelection(true);
				wAutoinc.setSelection(false);
				wTableMax.setSelection(false);				
			}

			public void focusLost(FocusEvent arg0) {
			} 
		});		
		
		// Use an autoincrement field?
		wAutoinc=new Button(gTechGroup, SWT.RADIO);
 		props.setLook(wAutoinc);
 		wAutoinc.setSelection(false);
		GridData gdAutoinc = new GridData();
		wAutoinc.setLayoutData(gdAutoinc);
		wAutoinc.setToolTipText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.AutoincButton.Tooltip",Const.CR)); //$NON-NLS-1$ //$NON-NLS-2$
		wlAutoinc=new Label(gTechGroup, SWT.LEFT);
		wlAutoinc.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Autoincrement.Label")); //$NON-NLS-1$
 		props.setLook(wlAutoinc);
		GridData gdlAutoinc = new GridData();
		wlAutoinc.setLayoutData(gdlAutoinc);

		setTableMax();
		setSequence();
		setAutoincUse();
		
		// Technical key field:
		wlTk=new Label(shell, SWT.RIGHT);
		wlTk.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.TechnicalKey.Label")); //$NON-NLS-1$
 		props.setLook(wlTk);
		FormData fdlTk = new FormData();
		fdlTk.left   = new FormAttachment(0, 0);
		fdlTk.right  = new FormAttachment(middle, -margin);
		fdlTk.bottom = new FormAttachment(gTechGroup, -margin);
		wlTk.setLayoutData(fdlTk);
		wTk=new Text(shell, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
 		props.setLook(wTk);
		FormData fdTk = new FormData();
		fdTk.left   = new FormAttachment(middle, 0);
		fdTk.bottom = new FormAttachment(gTechGroup, -margin);
		fdTk.right  = new FormAttachment(100, 0);
		wTk.setLayoutData(fdTk);

		FormData fdKey = new FormData();
		fdKey.left  = new FormAttachment(0, 0);
		fdKey.top   = new FormAttachment(wlKey, margin);
		fdKey.right = new FormAttachment(100, 0);
		fdKey.bottom= new FormAttachment(wTk, -margin);
		wKey.setLayoutData(fdKey);
		
	    // 
        // Search the fields in the background
        //
        
        final Runnable runnable = new Runnable()
        {
            public void run()
            {
                StepMeta stepMeta = transMeta.findStep(stepname);
                if (stepMeta!=null)
                {
                    try
                    {
                    	RowMetaInterface row = transMeta.getPrevStepFields(stepMeta);
                        
                        // Remember these fields...
                        for (int i=0;i<row.size();i++)
                        {
                        	inputFields.put(row.getValueMeta(i).getName(), Integer.valueOf(i));
                        }
                        
                        setComboBoxes(); 
                    }
                    catch(KettleException e)
                    {
                        log.logError(toString(),BaseMessages.getString(PKG, "System.Dialog.GetFieldsFailed.Message"));
                    }
                }
            }
        };
        new Thread(runnable).start();

		// Add listeners
		lsOK       = new Listener() { public void handleEvent(Event e) { ok();         } };
		lsGet      = new Listener() { public void handleEvent(Event e) { get();        } };
		lsCreate   = new Listener() { public void handleEvent(Event e) { create();     } };
		lsCancel   = new Listener() { public void handleEvent(Event e) { cancel();     } };

		wOK.addListener    (SWT.Selection, lsOK    );
		wGet.addListener   (SWT.Selection, lsGet   );
		wCreate.addListener(SWT.Selection, lsCreate);
		wCancel.addListener(SWT.Selection, lsCancel);

		lsDef=new SelectionAdapter() { public void widgetDefaultSelected(SelectionEvent e) { ok(); } };

		wStepname.addSelectionListener( lsDef );
		wSchema.addSelectionListener( lsDef );
        wTable.addSelectionListener( lsDef );
        wCommit.addSelectionListener( lsDef );
        wSeq.addSelectionListener( lsDef );
        wTk.addSelectionListener( lsDef );
        wCachesize.addSelectionListener( lsDef );
        wHashfield.addSelectionListener( lsDef );
        
		// Detect X or ALT-F4 or something that kills this window...
		shell.addShellListener(	new ShellAdapter() { public void shellClosed(ShellEvent e) { cancel(); } } );

		wbTable.addSelectionListener
		(
			new SelectionAdapter()
			{
				public void widgetSelected(SelectionEvent e)
				{
					getTableName();
				}
			}
		);

		// Set the shell size, based upon previous time...
		setSize();

		getData();
		setTableFieldCombo();
		input.setChanged(backupChanged);
		
		shell.open();
		while (!shell.isDisposed())
		{
				if (!display.readAndDispatch()) display.sleep();
		}
		
		return stepname;
	}
	protected void setComboBoxes()
    {
        // Something was changed in the row.
        //
		final Map<String, Integer> fields = new HashMap<String, Integer>();
        
        // Add the currentMeta fields...
        fields.putAll(inputFields);
        
        Set<String> keySet = fields.keySet();
        List<String> entries = new ArrayList<String>(keySet);
        
        String[] fieldNames= (String[]) entries.toArray(new String[entries.size()]);
        Const.sortStrings(fieldNames);
        // Key fields
        ciKey[1].setComboValues(fieldNames);
    }
	public void enableFields()
	{
		wHashfield.setEnabled(wHashcode.getSelection());
		wHashfield.setVisible(wHashcode.getSelection());
		wlHashfield.setEnabled(wHashcode.getSelection());
	}
	private void setTableFieldCombo(){
		Runnable fieldLoader = new Runnable() {
			public void run() {
				//clear
				for (int i = 0; i < tableFieldColumns.size(); i++) {
					ColumnInfo colInfo = (ColumnInfo) tableFieldColumns.get(i);
					colInfo.setComboValues(new String[] {});
				}
				if (!Const.isEmpty(wTable.getText())) {
					DatabaseMeta ci = transMeta.findDatabase(wReadConnection.getText());
					if (ci != null) {
						Database db = new Database(BaseStepMeta.loggingObject, ci);
						try {
							db.connect();

							String schemaTable = ci	.getQuotedSchemaTableCombination(transMeta.environmentSubstitute(wSchema
											.getText()), transMeta.environmentSubstitute(wTable.getText()));
							RowMetaInterface r = db.getTableFields(schemaTable);
							if (null != r) {
								String[] fieldNames = r.getFieldNames();
								if (null != fieldNames) {
									for (int i = 0; i < tableFieldColumns.size(); i++) {
										ColumnInfo colInfo = (ColumnInfo) tableFieldColumns.get(i);
										colInfo.setComboValues(fieldNames);
									}
								}
							}
						} catch (Exception e) {
							for (int i = 0; i < tableFieldColumns.size(); i++) {
								ColumnInfo colInfo = (ColumnInfo) tableFieldColumns	.get(i);
								colInfo.setComboValues(new String[] {});
							}
							// ignore any errors here. drop downs will not be
							// filled, but no problem for the user
						}
					}
				}
			}
		};
		shell.getDisplay().asyncExec(fieldLoader);
	}
	public void setAutoincUse()
	{
		boolean enable = (cWrite == null) || cWrite.supportsAutoinc();
		wlAutoinc.setEnabled(enable);
		wAutoinc.setEnabled(enable);
		if ( enable == false && 
			 wAutoinc.getSelection() == true )
		{
			wAutoinc.setSelection(false);
			wSeqButton.setSelection(false);
			wTableMax.setSelection(true);
		}		
	}

	public void setTableMax()
	{
		wlTableMax.setEnabled(true);
		wTableMax.setEnabled(true);
	}
	
	public void setSequence()
	{
		boolean seq = (cWrite == null) || cWrite.supportsSequences();
		wSeq.setEnabled(seq);
		wlSeqButton.setEnabled(seq);
		wSeqButton.setEnabled(seq);
		if ( seq == false && 
			 wSeqButton.getSelection() == true ) 
		{
		    wAutoinc.setSelection(false);
			wSeqButton.setSelection(false);
			wTableMax.setSelection(true);
		}		
	}

	/**
	 * Copy information from the meta-data input to the dialog fields.
	 */
	public void getData()
	{
		int i;
		log.logDebug(toString(), BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Log.GettingKeyInfo")); //$NON-NLS-1$

		if (input.getKeyField()!=null)
		for (i=0;i<input.getKeyField().length;i++)
		{
			TableItem item = wKey.table.getItem(i);
			if (input.getKeyLookup()[i]!=null) item.setText(1, input.getKeyLookup()[i]);
			if (input.getKeyField()[i]!=null)  item.setText(2, input.getKeyField()[i]);			
		}

		wReplace.setSelection( input.replaceFields() );
		wHashcode.setSelection( input.useHash() );
		wHashfield.setEnabled(input.useHash());
		wHashfield.setVisible(input.useHash());
		wlHashfield.setEnabled(input.useHash());
		
		String techKeyCreation = input.getTechKeyCreation(); 
		if ( techKeyCreation == null )  {		    
		    // Determine the creation of the technical key for
			// backwards compatibility. Can probably be removed at
			// version 3.x or so (Sven Boden).
		    DatabaseMeta database = input.getDatabaseWriteMeta(); 
		    if ( database == null || ! database.supportsAutoinc() )  
		    {
 			    input.setUseAutoinc(false);			
		    }		
		    wAutoinc.setSelection(input.isUseAutoinc());
		    
		    wSeqButton.setSelection(input.getSequenceFrom() != null && input.getSequenceFrom().length() > 0);
		    if ( input.isUseAutoinc() == false && 
			     (input.getSequenceFrom() == null || input.getSequenceFrom().length() <= 0) ) 
		    {
 			    wTableMax.setSelection(true); 			    
		    }
		    
			if ( database != null && database.supportsSequences() && 
				 input.getSequenceFrom() != null) 
			{
				wSeq.setText(input.getSequenceFrom());
				input.setUseAutoinc(false);
				wTableMax.setSelection(false);
			}
		}
		else
		{
		    // KETTLE post 2.2 version:
			// The "creation" field now determines the behaviour of the
			// key creation.
			if ( CombinationLookupMeta.CREATION_METHOD_AUTOINC.equals(techKeyCreation))  
			{
			    wAutoinc.setSelection(true);
			}
			else if ( ( CombinationLookupMeta.CREATION_METHOD_SEQUENCE.equals(techKeyCreation)) )
			{
				wSeqButton.setSelection(true);
			}
			else // the rest
			{
				wTableMax.setSelection(true);
				input.setTechKeyCreation(CombinationLookupMeta.CREATION_METHOD_TABLEMAX);
			}
			if ( input.getSequenceFrom() != null )
			{
    	        wSeq.setText(input.getSequenceFrom());
			}
		}
		setAutoincUse();
		setSequence();
		setTableMax();
        if (input.getSchemaName()!=null)        wSchema.setText( input.getSchemaName() );
  		if (input.getTablename()!=null)         wTable.setText( input.getTablename() );
		if (input.getTechnicalKeyField()!=null) wTk.setText(input.getTechnicalKeyField());

		if (input.getDatabaseReadMeta()!=null) wReadConnection.setText(input.getDatabaseReadMeta().getName());
		if (input.getDatabaseWriteMeta()!=null) wWriteConnection.setText(input.getDatabaseWriteMeta().getName());
		else if (transMeta.nrDatabases()==1)
		{
			wReadConnection.setText( transMeta.getDatabase(0).getName() );
		}
		if (input.getHashField()!=null)    wHashfield.setText(input.getHashField());

		wCommit.setText(""+input.getCommitSize()); //$NON-NLS-1$
		wCachesize.setText(""+input.getCacheSize()); //$NON-NLS-1$
		
		wLastUpdateField.setText( Const.NVL( input.getLastUpdateField(), "") );

		wKey.setRowNums();
		wKey.optWidth(true);

		wStepname.selectAll();
	}

	private void cancel()
	{
		stepname=null;
		input.setChanged(backupChanged);
		dispose();
	}

	private void ok()
	{
		if (Const.isEmpty(wStepname.getText())) return;

		CombinationLookupMeta oldMetaState = (CombinationLookupMeta)input.clone();
		
		getInfo(input);
		stepname = wStepname.getText(); // return value

		if (transMeta.findDatabase(wReadConnection.getText())==null)
		{
			MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
			mb.setMessage(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoValidConnection.DialogMessage")); //$NON-NLS-1$
			mb.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoValidConnection.DialogTitle")); //$NON-NLS-1$
			mb.open();
		}
		if (transMeta.findDatabase(wWriteConnection.getText())==null)
		{
			MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
			mb.setMessage(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoValidConnection.DialogMessage")); //$NON-NLS-1$
			mb.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoValidConnection.DialogTitle")); //$NON-NLS-1$
			mb.open();
		}
		
		if ( ! input.equals(oldMetaState) )  
		{
			input.setChanged();
		}
		dispose();
	}

	private void getInfo(CombinationLookupMeta in)
	{
		int nrkeys         = wKey.nrNonEmpty();

		in.allocate(nrkeys);

		log.logDebug(toString(), BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Log.SomeKeysFound",String.valueOf(nrkeys))); //$NON-NLS-1$ //$NON-NLS-2$
		for (int i=0;i<nrkeys;i++)
		{
			TableItem item = wKey.getNonEmpty(i);
			in.getKeyLookup()[i] = item.getText(1);
			in.getKeyField()[i]  = item.getText(2);			
		}

		in.setUseAutoinc( wAutoinc.getSelection() && wAutoinc.isEnabled() );
		in.setReplaceFields( wReplace.getSelection() );
		in.setUseHash( wHashcode.getSelection() );
		in.setHashField( wHashfield.getText() );
        in.setSchemaName( wSchema.getText() );
		in.setTablename( wTable.getText() );
		in.setTechnicalKeyField( wTk.getText() );
		if ( wAutoinc.getSelection() == true )  
		{
			in.setTechKeyCreation(CombinationLookupMeta.CREATION_METHOD_AUTOINC);
			in.setUseAutoinc( true );   // for downwards compatibility
			in.setSequenceFrom( null );
		}
		else if ( wSeqButton.getSelection() == true )
		{
			in.setTechKeyCreation(CombinationLookupMeta.CREATION_METHOD_SEQUENCE);
			in.setUseAutoinc(false);
			in.setSequenceFrom( wSeq.getText() );
		}
		else  // all the rest
		{
			in.setTechKeyCreation(CombinationLookupMeta.CREATION_METHOD_TABLEMAX);
			in.setUseAutoinc( false );
			in.setSequenceFrom( null );
		}
		
		in.setDatabaseReadMeta( transMeta.findDatabase(wReadConnection.getText()) );
		in.setDatabaseWriteMeta( transMeta.findDatabase(wWriteConnection.getText()) );

		in.setCommitSize( Const.toInt(wCommit.getText(), 0) );
		in.setCacheSize( Const.toInt(wCachesize.getText(), 0) );
		
		in.setLastUpdateField( wLastUpdateField.getText() );
	}

	private void getTableName()
	{
		DatabaseMeta inf = null;
		// New class: SelectTableDialog
		int connr = wReadConnection.getSelectionIndex();
		if (connr >= 0) inf = transMeta.getDatabase(connr);

		if (inf != null)
		{
			log.logDebug(toString(), BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.Log.LookingAtConnection", inf.toString()));

			DatabaseExplorerDialog std = new DatabaseExplorerDialog(shell, SWT.NONE, inf, transMeta.getDatabases());
            std.setSelectedSchemaAndTable(wSchema.getText(), wTable.getText());
            if (std.open())
			{
                wSchema.setText(Const.NVL(std.getSchemaName(), ""));
				wTable.setText(Const.NVL(std.getTableName(), ""));
			}
		}
		else
		{
			MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR);
			mb.setMessage(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.ConnectionError2.DialogMessage"));
			mb.setText(BaseMessages.getString(PKG, "System.Dialog.Error.Title"));
			mb.open();
		}
	}	

	private void get()
	{
		try
		{
			RowMetaInterface r = transMeta.getPrevStepFields(stepname);
			if (r!=null && !r.isEmpty())
			{
                BaseStepDialog.getFieldsFromPrevious(r, wKey, 1, new int[] { 1, 2 }, new int[] {}, -1, -1, new TableItemInsertListener()
                    {
                        public boolean tableItemInserted(TableItem tableItem, ValueMetaInterface v)
                        {
                            tableItem.setText(3, "N"); //$NON-NLS-1$
                            return true;
                        }
                    }
                );
			}
		}
		catch(KettleException ke)
		{
			new ErrorDialog(shell, BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.UnableToGetFieldsError.DialogTitle"), BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.UnableToGetFieldsError.DialogMessage"), ke); //$NON-NLS-1$ //$NON-NLS-2$
		}
	}

	/** 
	 *  Generate code for create table. Conversions done by database.
	 */
	private void create()
	{
		try
		{
			// Gather info...
			CombinationLookupMeta info = new CombinationLookupMeta();
			getInfo(info);
			String name = stepname;  // new name might not yet be linked to other steps!
			StepMeta stepMeta = new StepMeta(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.StepMeta.Title"), name, info); //$NON-NLS-1$
			RowMetaInterface prev = transMeta.getPrevStepFields(stepname);

			SQLStatement sql = info.getSQLStatements(transMeta, stepMeta, prev);
			if (!sql.hasError())
			{
				if (sql.hasSQL())
				{
					SQLEditor sqledit = new SQLEditor(shell, SWT.NONE, info.getDatabaseReadMeta(), transMeta.getDbCache(), sql.getSQL());
					sqledit.open();
				}
				else
				{
					MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_INFORMATION );
					mb.setMessage(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoSQLNeeds.DialogMessage")); //$NON-NLS-1$
					mb.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NoSQLNeeds.DialogTitle")); //$NON-NLS-1$
					mb.open();
				}
			}
			else
			{
				MessageBox mb = new MessageBox(shell, SWT.OK | SWT.ICON_ERROR );
				mb.setMessage(sql.getError());
				mb.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.SQLError.DialogTitle")); //$NON-NLS-1$
				mb.open();
			}
		}
		catch(KettleException ke)
		{
			new ErrorDialog(shell, BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.UnableToCreateSQL.DialogTitle"), BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.UnableToCreateSQL.DialogMessage"), ke); //$NON-NLS-1$ //$NON-NLS-2$
		}
	}

	public String toString()
	{
		return this.getClass().getName();
	}
	
	public CCombo addConnectionLine(Composite parent, Control previous, int middle, int margin, String label) 
	{
			Label wlConnection = new Label(parent, SWT.RIGHT); 
			Button wbnConnection = new Button(parent,SWT.PUSH);
			Button wbeConnection = new Button(parent, SWT.PUSH);
		    final CCombo wConnection;
		    final FormData fdlConnection, fdbConnection, fdeConnection, fdConnection;

		    wConnection = new CCombo(parent, SWT.BORDER | SWT.READ_ONLY);
		    props.setLook(wConnection);

		    addDatabases(wConnection);

		    wlConnection.setText(BaseMessages.getString(PKG, label)); //$NON-NLS-1$
		    props.setLook(wlConnection);
		    fdlConnection = new FormData();
		    fdlConnection.left = new FormAttachment(0, 0);
		    fdlConnection.right = new FormAttachment(middle, -margin);
		    if (previous != null)
		      fdlConnection.top = new FormAttachment(previous, margin);
		    else
		      fdlConnection.top = new FormAttachment(0, 0);
		    wlConnection.setLayoutData(fdlConnection);

		    // 
		    // NEW button
		    //
		    wbnConnection.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.NewConnectionButton.Label")); //$NON-NLS-1$
		    wbnConnection.addSelectionListener(new SelectionAdapter() {
		      public void widgetSelected(SelectionEvent e) {
		        DatabaseMeta databaseMeta = new DatabaseMeta();
		        databaseMeta.shareVariablesWith(transMeta);
		        DatabaseDialog cid = new DatabaseDialog(shell, databaseMeta);
		        cid.setModalDialog(true);
		        if (cid.open() != null) {
		          transMeta.addDatabase(databaseMeta);
		          wConnection.removeAll();
		          addDatabases(wConnection);
		          selectDatabase(wConnection, databaseMeta.getName());
		        }
		      }
		    });
		    fdbConnection = new FormData();
		    fdbConnection.right = new FormAttachment(100, 0);
		    if (previous != null)
		      fdbConnection.top = new FormAttachment(previous, margin);
		    else
		      fdbConnection.top = new FormAttachment(0, 0);
		    wbnConnection.setLayoutData(fdbConnection);

		    //
		    // Edit button
		    //
		    wbeConnection.setText(BaseMessages.getString(PKG, "ConcurrentCombinationLookupDialog.EditConnectionButton.Label")); //$NON-NLS-1$
		    wbeConnection.addSelectionListener(new SelectionAdapter() {
		      public void widgetSelected(SelectionEvent e) {
		        DatabaseMeta databaseMeta = transMeta.findDatabase(wConnection.getText());
		        if (databaseMeta != null) {
		          databaseMeta.shareVariablesWith(transMeta);
		          DatabaseDialog cid = new DatabaseDialog(shell, databaseMeta);
		          cid.setModalDialog(true);
		          if (cid.open() != null) {
		            wConnection.removeAll();
		            addDatabases(wConnection);
		            selectDatabase(wConnection, databaseMeta.getName());
		          }
		        }
		      }
		    });
		    fdeConnection = new FormData();
		    fdeConnection.right = new FormAttachment(wbnConnection, -margin);
		    if (previous != null)
		      fdeConnection.top = new FormAttachment(previous, margin);
		    else
		      fdeConnection.top = new FormAttachment(0, 0);
		    wbeConnection.setLayoutData(fdeConnection);

		    //
		    // what's left of the line: combo box
		    //
		    fdConnection = new FormData();
		    fdConnection.left = new FormAttachment(middle, 0);
		    if (previous != null)
		      fdConnection.top = new FormAttachment(previous, margin);
		    else
		      fdConnection.top = new FormAttachment(0, 0);
		    fdConnection.right = new FormAttachment(wbeConnection, -margin);
		    wConnection.setLayoutData(fdConnection);

		    return wConnection;
		  }
}