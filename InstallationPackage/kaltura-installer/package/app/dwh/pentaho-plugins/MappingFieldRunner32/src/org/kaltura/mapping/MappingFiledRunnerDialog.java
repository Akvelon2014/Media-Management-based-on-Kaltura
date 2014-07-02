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
 * Created on 18-mei-2003
 *
 */

package org.kaltura.mapping;

import java.util.ArrayList;
import java.util.List;

import org.eclipse.swt.SWT;
import org.eclipse.swt.custom.CTabFolder;
import org.eclipse.swt.custom.CTabFolder2Adapter;
import org.eclipse.swt.custom.CTabFolderEvent;
import org.eclipse.swt.custom.CTabItem;
import org.eclipse.swt.events.FocusAdapter;
import org.eclipse.swt.events.FocusEvent;
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
import org.eclipse.swt.widgets.Composite;
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
import org.pentaho.di.core.Props;
import org.pentaho.di.core.SourceToTargetMapping;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDialogInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.steps.mapping.MappingIODefinition;
import org.pentaho.di.trans.steps.mapping.MappingParameters;
import org.pentaho.di.trans.steps.mapping.MappingValueRename;
import org.pentaho.di.ui.core.dialog.EnterMappingDialog;
import org.pentaho.di.ui.core.dialog.EnterSelectionDialog;
import org.pentaho.di.ui.core.dialog.ErrorDialog;
import org.pentaho.di.ui.core.widget.ColumnInfo;
import org.pentaho.di.ui.core.widget.ComboVar;
import org.pentaho.di.ui.core.widget.TableView;
import org.pentaho.di.ui.trans.step.BaseStepDialog;

public class MappingFiledRunnerDialog extends BaseStepDialog implements StepDialogInterface
{
	private MappingFiledRunnerMeta mappingMeta;

	private Group gTransGroup;

	private ComboVar wFieldname;
	private Button wExecuteForEachRow;
	private Label wlExecuteForEachRow;
 
	private CTabFolder wTabFolder;

	TransMeta mappingTransMeta = null;

	protected boolean transModified;

	private ModifyListener lsMod;

	private int middle;

	private int margin;

	private MappingParameters mappingParameters;

	private List<MappingIODefinition> inputMappings;

	private List<MappingIODefinition> outputMappings;

	private Button wAddInput;

	private Button wAddOutput;

	private interface ApplyChanges
	{
		public void applyChanges();
	}

	private class MappingParametersTab implements ApplyChanges
	{
		private TableView wMappingParameters;

		private MappingParameters parameters;

		private Button wInheritAll;

		public MappingParametersTab(TableView wMappingParameters, Button wInheritAll, MappingParameters parameters)
		{
			this.wMappingParameters = wMappingParameters;
			this.wInheritAll = wInheritAll;
			this.parameters = parameters;
		}

		public void applyChanges()
		{

			int nrLines = wMappingParameters.nrNonEmpty();
			String variables[] = new String[nrLines];
			String inputFields[] = new String[nrLines];
			parameters.setVariable(variables);
			parameters.setInputField(inputFields);
			for (int i = 0; i < nrLines; i++)
			{
				TableItem item = wMappingParameters.getNonEmpty(i);
				parameters.getVariable()[i] = item.getText(1);
				parameters.getInputField()[i] = item.getText(2);
			}
			parameters.setInheritingAllVariables(wInheritAll.getSelection());
		}
	}

	private class MappingDefinitionTab implements ApplyChanges
	{
		private MappingIODefinition definition;

		private Text wInputStep;

		private Text wOutputStep;

		private Button wMainPath;

		private Text wDescription;

		private TableView wFieldMappings;

		public MappingDefinitionTab(MappingIODefinition definition, Text inputStep, Text outputStep,
				Button mainPath, Text description, TableView fieldMappings)
		{
			super();
			this.definition = definition;
			wInputStep = inputStep;
			wOutputStep = outputStep;
			wMainPath = mainPath;
			wDescription = description;
			wFieldMappings = fieldMappings;
		}

		public void applyChanges()
		{

			// The input step
			definition.setInputStepname(wInputStep.getText());

			// The output step
			definition.setOutputStepname(wOutputStep.getText());

			// The description
			definition.setDescription(wDescription.getText());

			// The main path flag
			definition.setMainDataPath(wMainPath.getSelection());

			// The grid
			//
			int nrLines = wFieldMappings.nrNonEmpty();
			definition.getValueRenames().clear();
			for (int i = 0; i < nrLines; i++)
			{
				TableItem item = wFieldMappings.getNonEmpty(i);
				definition.getValueRenames().add(new MappingValueRename(item.getText(1), item.getText(2)));
			}
		}
	}

	private List<ApplyChanges> changeList;

	private boolean gotPreviousFields;

	public MappingFiledRunnerDialog(Shell parent, Object in, TransMeta tr, String sname)
	{
		super(parent, (BaseStepMeta) in, tr, sname);
		mappingMeta = (MappingFiledRunnerMeta) in;
		transModified = false;

		// Make a copy for our own purposes...
		// This allows us to change everything directly in the classes with
		// listeners.
		// Later we need to copy it to the input class on ok()
		//
		mappingParameters = (MappingParameters) mappingMeta.getMappingParameters().clone();
		inputMappings = new ArrayList<MappingIODefinition>();
		outputMappings = new ArrayList<MappingIODefinition>();
		for (int i = 0; i < mappingMeta.getInputMappings().size(); i++)
			inputMappings.add((MappingIODefinition) mappingMeta.getInputMappings().get(i).clone());
		for (int i = 0; i < mappingMeta.getOutputMappings().size(); i++)
			outputMappings.add((MappingIODefinition) mappingMeta.getOutputMappings().get(i).clone());

		changeList = new ArrayList<ApplyChanges>();
	}

	public String open()
	{
		Shell parent = getParent();
		Display display = parent.getDisplay();

		shell = new Shell(parent, SWT.DIALOG_TRIM | SWT.RESIZE | SWT.MIN | SWT.MAX);
		props.setLook(shell);
		setShellImage(shell, mappingMeta);

		lsMod = new ModifyListener()
		{
			public void modifyText(ModifyEvent e)
			{
				mappingMeta.setChanged();
			}
		};
		changed = mappingMeta.hasChanged();

		FormLayout formLayout = new FormLayout();
		formLayout.marginWidth = Const.FORM_MARGIN;
		formLayout.marginHeight = Const.FORM_MARGIN;

		shell.setLayout(formLayout);
		shell.setText(Messages.getString("MappingFieldRunnerDialog.Shell.Title")); //$NON-NLS-1$

		middle = props.getMiddlePct();
		margin = Const.MARGIN;

		// Stepname line
		wlStepname = new Label(shell, SWT.RIGHT);
		wlStepname.setText(Messages.getString("MappingFieldRunnerDialog.Stepname.Label")); //$NON-NLS-1$
		props.setLook(wlStepname);
		fdlStepname = new FormData();
		fdlStepname.left = new FormAttachment(0, 0);
		fdlStepname.right = new FormAttachment(middle, -margin);
		fdlStepname.top = new FormAttachment(0, margin);
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

		// Show a group with 2 main options: a transformation in the repository
		// or on file
		//

		// //////////////////////////////////////////////////
		// The key creation box
		// //////////////////////////////////////////////////
		//
		gTransGroup = new Group(shell, SWT.SHADOW_ETCHED_IN);
		gTransGroup.setText(Messages.getString("MappingFieldRunnerDialog.TransGroup.Label")); //$NON-NLS-1$;
		gTransGroup.setBackground(shell.getBackground()); // the default looks
		// ugly
		FormLayout transGroupLayout = new FormLayout();
		transGroupLayout.marginLeft = margin * 2;
		transGroupLayout.marginTop = margin * 2;
		transGroupLayout.marginRight = margin * 2;
		transGroupLayout.marginBottom = margin * 2;
		gTransGroup.setLayout(transGroupLayout);

		wFieldname = new ComboVar(transMeta, gTransGroup, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wFieldname);
		wFieldname.addModifyListener(lsMod);
		FormData fdFilename = new FormData();
		fdFilename.left = new FormAttachment(0, 25);
		fdFilename.right = new FormAttachment(100, -margin);
		fdFilename.top = new FormAttachment(0, 0);
		wFieldname.setLayoutData(fdFilename);

		wExecuteForEachRow = new Button(gTransGroup, SWT.CHECK);
		FormData fdExecuteForEachRow = new FormData();
		fdExecuteForEachRow.left = new FormAttachment(0, 25);
		fdExecuteForEachRow.top = new FormAttachment(wFieldname, margin);
		wExecuteForEachRow.setLayoutData(fdExecuteForEachRow);
		
		wlExecuteForEachRow = new Label(gTransGroup, SWT.NONE);
		wlExecuteForEachRow.setText(Messages.getString("MappingFieldRunnerDialog.ForEachRow.Label")); //$NON-NLS-1$
		FormData fdlExecuteForEachRow = new FormData();
		fdlExecuteForEachRow.left = new FormAttachment(wExecuteForEachRow, margin);
		fdlExecuteForEachRow.top = new FormAttachment(wFieldname, margin);
		wlExecuteForEachRow.setLayoutData(fdlExecuteForEachRow);
		
		FormData fdTransGroup = new FormData();
		fdTransGroup.left = new FormAttachment(0, 0);
		fdTransGroup.top = new FormAttachment(wStepname, 2 * margin);
		fdTransGroup.right = new FormAttachment(100, 0);
		// fdTransGroup.bottom = new FormAttachment(wStepname, 350);
		gTransGroup.setLayoutData(fdTransGroup);

		// 
		// Add a tab folder for the parameters and various input and output
		// streams
		//
		wTabFolder = new CTabFolder(shell, SWT.BORDER);
		props.setLook(wTabFolder, Props.WIDGET_STYLE_TAB);
		wTabFolder.setSimple(false);
		wTabFolder.setUnselectedCloseVisible(true);

		FormData fdTabFolder = new FormData();
		fdTabFolder.left = new FormAttachment(0, 0);
		fdTabFolder.right = new FormAttachment(100, 0);
		fdTabFolder.top = new FormAttachment(gTransGroup, margin * 2);
		fdTabFolder.bottom = new FormAttachment(100, -75);
		wTabFolder.setLayoutData(fdTabFolder);

		// Now add buttons that will allow us to add or remove input or output
		// tabs...
		wAddInput = new Button(shell, SWT.PUSH);
		props.setLook(wAddInput);
		wAddInput.setText(Messages.getString("MappingFieldRunnerDialog.button.AddInput"));
		wAddInput.addSelectionListener(new SelectionAdapter()
		{

			@Override
			public void widgetSelected(SelectionEvent event)
			{

				// Simply add a new MappingIODefinition object to the
				// inputMappings
				MappingIODefinition definition = new MappingIODefinition();
				inputMappings.add(definition);
				int index = inputMappings.size() - 1;
				addInputMappingDefinitionTab(definition, index);
			}

		});

		wAddOutput = new Button(shell, SWT.PUSH);
		props.setLook(wAddOutput);
		wAddOutput.setText(Messages.getString("MappingFieldRunnerDialog.button.AddOutput"));

		wAddOutput.addSelectionListener(new SelectionAdapter()
		{

			@Override
			public void widgetSelected(SelectionEvent event)
			{

				// Simply add a new MappingIODefinition object to the
				// inputMappings
				MappingIODefinition definition = new MappingIODefinition();
				outputMappings.add(definition);
				int index = outputMappings.size() - 1;
				addOutputMappingDefinitionTab(definition, index);
			}

		});

		setButtonPositions(new Button[] { wAddInput, wAddOutput }, margin, wTabFolder);

		// Some buttons
		wOK = new Button(shell, SWT.PUSH);
		wOK.setText(Messages.getString("System.Button.OK")); //$NON-NLS-1$
		wCancel = new Button(shell, SWT.PUSH);
		wCancel.setText(Messages.getString("System.Button.Cancel")); //$NON-NLS-1$

		setButtonPositions(new Button[] { wOK, wCancel }, margin, null);

		// Add listeners
		lsCancel = new Listener()
		{
			public void handleEvent(Event e)
			{
				cancel();
			}
		};
		lsOK = new Listener()
		{
			public void handleEvent(Event e)
			{
				ok();
			}
		};

		wCancel.addListener(SWT.Selection, lsCancel);
		wOK.addListener(SWT.Selection, lsOK);

		lsDef = new SelectionAdapter()
		{
			public void widgetDefaultSelected(SelectionEvent e)
			{
				ok();
			}
		};

		wStepname.addSelectionListener(lsDef);
		wFieldname.addSelectionListener(lsDef);
		wExecuteForEachRow.addSelectionListener(lsDef);
		
		// Detect X or ALT-F4 or something that kills this window...
		shell.addShellListener(new ShellAdapter()
		{
			public void shellClosed(ShellEvent e)
			{
				cancel();
			}
		});

		// Set the shell size, based upon previous time...
		setSize();

		getData();
		getFields();
		
		mappingMeta.setChanged(changed);
		wTabFolder.setSelection(0);
		
		shell.open();
		while (!shell.isDisposed())
		{
			if (!display.readAndDispatch())
				display.sleep();
		}
		return stepname;
	}

	/**
	 * Copy information from the meta-data input to the dialog fields.
	 */
	public void getData()
	{
		wStepname.selectAll();

		wFieldname.setText(Const.NVL(mappingMeta.getFieldName(), ""));
		wExecuteForEachRow.setSelection(mappingMeta.getExecuteForEachRow());
		
		// Add the parameters tab
		addParametersTab(mappingParameters);
		wTabFolder.setSelection(0);

		// Now add the input stream tabs: where is our data coming from?
		for (int i = 0; i < inputMappings.size(); i++)
		{
			addInputMappingDefinitionTab(inputMappings.get(i), i);
		}

		// Now add the output stream tabs: where is our data going to?
		for (int i = 0; i < outputMappings.size(); i++)
		{
			addOutputMappingDefinitionTab(outputMappings.get(i), i);
		}

	}

	private void addOutputMappingDefinitionTab(MappingIODefinition definition, int index)
	{
		addMappingDefinitionTab(outputMappings.get(index), index + 1 + inputMappings.size(), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.Title"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.Tooltip"), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.label.InputSourceStepName"), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.label.OutputTargetStepName"), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.label.Description"), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.column.SourceField"), 
				Messages.getString("MappingFieldRunnerDialog.OutputTab.column.TargetField"), false);

	}

	private void addInputMappingDefinitionTab(MappingIODefinition definition, int index)
	{
		addMappingDefinitionTab(definition, index + 1, Messages.getString("MappingFieldRunnerDialog.InputTab.Title"),
				Messages.getString("MappingFieldRunnerDialog.InputTab.Tooltip"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.label.InputSourceStepName"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.label.OutputTargetStepName"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.label.Description"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.column.SourceField"), 
				Messages.getString("MappingFieldRunnerDialog.InputTab.column.TargetField"), true);

	}

	private void addParametersTab(final MappingParameters parameters)
	{

		CTabItem wParametersTab = new CTabItem(wTabFolder, SWT.NONE);
		wParametersTab.setText(Messages.getString("MappingFieldRunnerDialog.Parameters.Title")); //$NON-NLS-1$
		wParametersTab.setToolTipText(Messages.getString("MappingFieldRunnerDialog.Parameters.Tooltip")); //$NON-NLS-1$

		Composite wParametersComposite = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wParametersComposite);

		FormLayout parameterTabLayout = new FormLayout();
		parameterTabLayout.marginWidth = Const.FORM_MARGIN;
		parameterTabLayout.marginHeight = Const.FORM_MARGIN;
		wParametersComposite.setLayout(parameterTabLayout);

		// Add a checkbox: inherit all variables...
		//
		Button wInheritAll = new Button(wParametersComposite, SWT.CHECK);
		wInheritAll.setText(Messages.getString("MappingFieldRunnerDialog.Parameters.InheritAll"));
		props.setLook(wInheritAll);
		FormData fdInheritAll = new FormData();
		fdInheritAll.bottom = new FormAttachment(100,0);
		fdInheritAll.left = new FormAttachment(0,0);
		fdInheritAll.right = new FormAttachment(100,-30);
		wInheritAll.setLayoutData(fdInheritAll);
		wInheritAll.setSelection(parameters.isInheritingAllVariables());
		
		// Now add a tableview with the 2 columns to specify: input and output
		// fields for the source and target steps.
		//
		ColumnInfo[] colinfo = new ColumnInfo[] {
				new ColumnInfo(
						Messages.getString("MappingFieldRunnerDialog.Parameters.column.Variable"), ColumnInfo.COLUMN_TYPE_TEXT, false, false), //$NON-NLS-1$
				new ColumnInfo(
						Messages.getString("MappingFieldRunnerDialog.Parameters.column.ValueOrField"), ColumnInfo.COLUMN_TYPE_TEXT, false, false), //$NON-NLS-1$
		};
		colinfo[1].setUsingVariables(true);

		final TableView wMappingParameters = new TableView(transMeta, wParametersComposite,
				SWT.FULL_SELECTION | SWT.SINGLE | SWT.BORDER, colinfo, parameters.getVariable().length,
				lsMod, props);
		props.setLook(wMappingParameters);
		FormData fdMappings = new FormData();
		fdMappings.left = new FormAttachment(0, 0);
		fdMappings.right = new FormAttachment(100, 0);
		fdMappings.top = new FormAttachment(0, 0);
		fdMappings.bottom = new FormAttachment(wInheritAll, -margin*2);
		wMappingParameters.setLayoutData(fdMappings);

		for (int i = 0; i < parameters.getVariable().length; i++)
		{
			TableItem tableItem = wMappingParameters.table.getItem(i);
			tableItem.setText(1, parameters.getVariable()[i]);
			tableItem.setText(2, parameters.getInputField()[i]);
		}
		wMappingParameters.setRowNums();
		wMappingParameters.optWidth(true);

		FormData fdParametersComposite = new FormData();
		fdParametersComposite.left = new FormAttachment(0, 0);
		fdParametersComposite.top = new FormAttachment(0, 0);
		fdParametersComposite.right = new FormAttachment(100, 0);
		fdParametersComposite.bottom = new FormAttachment(100, 0);
		wParametersComposite.setLayoutData(fdParametersComposite);

		wParametersComposite.layout();
		wParametersTab.setControl(wParametersComposite);

		changeList.add(new MappingParametersTab(wMappingParameters, wInheritAll, parameters));
	}

	protected String selectTransformationStepname(boolean getTransformationStep, boolean mappingInput)
	{
		String dialogTitle = Messages.getString("MappingFieldRunnerDialog.SelectTransStep.Title");
		String dialogMessage = Messages.getString("MappingFieldRunnerDialog.SelectTransStep.Message");
		if (getTransformationStep)
		{
			dialogTitle = Messages.getString("MappingFieldRunnerDialog.SelectTransStep.Title");
			dialogMessage = Messages.getString("MappingFieldRunnerDialog.SelectTransStep.Message");
			String[] stepnames;
			if (mappingInput)
			{
				stepnames = transMeta.getPrevStepNames(stepMeta);
			} else
			{
				stepnames = transMeta.getNextStepNames(stepMeta);
			}
			EnterSelectionDialog dialog = new EnterSelectionDialog(shell, stepnames, dialogTitle,
					dialogMessage);
			return dialog.open();
		} else
		{
			dialogTitle = Messages.getString("MappingFieldRunnerDialog.SelectMappingStep.Title");
			dialogMessage = Messages.getString("MappingFieldRunnerDialog.SelectMappingStep.Message");

			String[] stepnames = getMappingSteps(mappingTransMeta, mappingInput);
			EnterSelectionDialog dialog = new EnterSelectionDialog(shell, stepnames, dialogTitle,
					dialogMessage);
			return dialog.open();
		}
	}

	public static String[] getMappingSteps(TransMeta mappingTransMeta, boolean mappingInput)
	{
		List<StepMeta> steps = new ArrayList<StepMeta>();
		for (StepMeta stepMeta : mappingTransMeta.getSteps())
		{
			if (mappingInput && stepMeta.getStepID().equals("MappingInput"))
			{
				steps.add(stepMeta);
			}
			if (!mappingInput && stepMeta.getStepID().equals("MappingOutput"))
			{
				steps.add(stepMeta);
			}
		}
		String[] stepnames = new String[steps.size()];
		for (int i = 0; i < stepnames.length; i++)
			stepnames[i] = steps.get(i).getName();

		return stepnames;

	}

	public RowMetaInterface getFieldsFromStep(String stepname, boolean getTransformationStep,
			boolean mappingInput) throws KettleException
	{
		if (!(mappingInput ^ getTransformationStep))
		{
			if (Const.isEmpty(stepname))
			{
				// If we don't have a specified stepname we return the input row
				// metadata
				//
				return transMeta.getPrevStepFields(this.stepname);
			} else
			{
				// OK, a fieldname is specified...
				// See if we can find it...
				StepMeta stepMeta = transMeta.findStep(stepname);
				if (stepMeta == null)
				{
					throw new KettleException(Messages.getString("MappingFieldRunnerDialog.Exception.SpecifiedStepWasNotFound", stepname));
				}
				return transMeta.getStepFields(stepMeta);
			}

		} 
		else
		{
			if (mappingTransMeta==null) {
				throw new KettleException(Messages.getString("MappingFieldRunnerDialog.Exception.NoMappingSpecified"));
			}

			if (Const.isEmpty(stepname))
			{
				// If we don't have a specified stepname we select the one and
				// only "mapping input" step.
				//
				String[] stepnames = getMappingSteps(mappingTransMeta, mappingInput);
				if (stepnames.length > 1)
				{
					throw new KettleException(Messages.getString("MappingFieldRunnerDialog.Exception.OnlyOneMappingInputStepAllowed", "" + stepnames.length));
				}
				if (stepnames.length == 0)
				{
					throw new KettleException(Messages.getString("MappingFieldRunnerDialog.Exception.OneMappingInputStepRequired", "" + stepnames.length));
				}
				return mappingTransMeta.getStepFields(stepnames[0]);
			} 
			else
			{
				// OK, a fieldname is specified...
				// See if we can find it...
				StepMeta stepMeta = mappingTransMeta.findStep(stepname);
				if (stepMeta == null)
				{
					throw new KettleException(Messages.getString("MappingFieldRunnerDialog.Exception.SpecifiedStepWasNotFound", stepname));
				}
				return mappingTransMeta.getStepFields(stepMeta);
			}
		}
	}

	private void addMappingDefinitionTab(final MappingIODefinition definition, int index,
			final String tabTitle, final String tabTooltip, String inputStepLabel, String outputStepLabel,
			String descriptionLabel, String sourceColumnLabel, String targetColumnLabel, final boolean input)
	{

		final CTabItem wTab;
		if (index >= wTabFolder.getItemCount())
		{
			wTab = new CTabItem(wTabFolder, SWT.CLOSE);
		} else
		{
			wTab = new CTabItem(wTabFolder, SWT.CLOSE, index);
		}
		setMappingDefinitionTabNameAndToolTip(wTab, tabTitle, tabTooltip, definition, input);

		Composite wInputComposite = new Composite(wTabFolder, SWT.NONE);
		props.setLook(wInputComposite);

		FormLayout tabLayout = new FormLayout();
		tabLayout.marginWidth = Const.FORM_MARGIN;
		tabLayout.marginHeight = Const.FORM_MARGIN;
		wInputComposite.setLayout(tabLayout);

		// What's the stepname to read from? (empty is OK too)
		//
		Button wbInputStep = new Button(wInputComposite, SWT.PUSH);
		props.setLook(wbInputStep);
		wbInputStep.setText(Messages.getString("MappingFieldRunnerDialog.button.SourceStepName"));
		FormData fdbInputStep = new FormData();
		fdbInputStep.top = new FormAttachment(0, 0);
		fdbInputStep.right = new FormAttachment(100, 0); // First one in the
		// left top corner
		wbInputStep.setLayoutData(fdbInputStep);

		Label wlInputStep = new Label(wInputComposite, SWT.RIGHT);
		props.setLook(wlInputStep);
		wlInputStep.setText(inputStepLabel); //$NON-NLS-1$
		FormData fdlInputStep = new FormData();
		fdlInputStep.top = new FormAttachment(wbInputStep, 0, SWT.CENTER);
		fdlInputStep.left = new FormAttachment(0, 0); // First one in the left
		// top corner
		fdlInputStep.right = new FormAttachment(middle, -margin);
		wlInputStep.setLayoutData(fdlInputStep);

		final Text wInputStep = new Text(wInputComposite, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wInputStep);
		wInputStep.setText(Const.NVL(definition.getInputStepname(), ""));
		wInputStep.addModifyListener(lsMod);
		FormData fdInputStep = new FormData();
		fdInputStep.top = new FormAttachment(wbInputStep, 0, SWT.CENTER);
		fdInputStep.left = new FormAttachment(middle, 0); // To the right of
		// the label
		fdInputStep.right = new FormAttachment(wbInputStep, -margin);
		wInputStep.setLayoutData(fdInputStep);
		wInputStep.addFocusListener(new FocusAdapter()
		{
			@Override
			public void focusLost(FocusEvent event)
			{
				definition.setInputStepname(wInputStep.getText());
				setMappingDefinitionTabNameAndToolTip(wTab, tabTitle, tabTooltip, definition, input);
			}
		});
		wbInputStep.addSelectionListener(new SelectionAdapter()
		{
			@Override
			public void widgetSelected(SelectionEvent event)
			{
				String stepName = selectTransformationStepname(input, input);
				if (stepName != null)
				{
					wInputStep.setText(stepName);
					definition.setInputStepname(stepName);
					setMappingDefinitionTabNameAndToolTip(wTab, tabTitle, tabTooltip, definition, input);
				}
			}
		});

		// What's the step name to read from? (empty is OK too)
		//
		Button wbOutputStep = new Button(wInputComposite, SWT.PUSH);
		props.setLook(wbOutputStep);
		wbOutputStep.setText(Messages.getString("MappingFieldRunnerDialog.button.SourceStepName"));
		FormData fdbOutputStep = new FormData();
		fdbOutputStep.top = new FormAttachment(wbInputStep, margin);
		fdbOutputStep.right = new FormAttachment(100, 0);
		wbOutputStep.setLayoutData(fdbOutputStep);

		Label wlOutputStep = new Label(wInputComposite, SWT.RIGHT);
		props.setLook(wlOutputStep);
		wlOutputStep.setText(outputStepLabel); //$NON-NLS-1$
		FormData fdlOutputStep = new FormData();
		fdlOutputStep.top = new FormAttachment(wbOutputStep, 0, SWT.CENTER);
		fdlOutputStep.left = new FormAttachment(0, 0);
		fdlOutputStep.right = new FormAttachment(middle, -margin);
		wlOutputStep.setLayoutData(fdlOutputStep);

		final Text wOutputStep = new Text(wInputComposite, SWT.SINGLE | SWT.LEFT | SWT.BORDER);
		props.setLook(wOutputStep);
		wOutputStep.setText(Const.NVL(definition.getOutputStepname(), ""));
		wOutputStep.addModifyListener(lsMod);
		FormData fdOutputStep = new FormData();
		fdOutputStep.top = new FormAttachment(wbOutputStep, 0, SWT.CENTER);
		fdOutputStep.left = new FormAttachment(middle, 0); // To the right of
		// the label
		fdOutputStep.right = new FormAttachment(wbOutputStep, -margin);
		wOutputStep.setLayoutData(fdOutputStep);

		// Add a checkbox to indicate the main step to read from, the main data
		// path...
		//
		Label wlMainPath = new Label(wInputComposite, SWT.RIGHT);
		props.setLook(wlMainPath);
		wlMainPath.setText(Messages.getString("MappingFieldRunnerDialog.input.MainDataPath")); //$NON-NLS-1$
		FormData fdlMainPath = new FormData();
		fdlMainPath.top = new FormAttachment(wbOutputStep, margin);
		fdlMainPath.left = new FormAttachment(0, 0);
		fdlMainPath.right = new FormAttachment(middle, -margin);
		wlMainPath.setLayoutData(fdlMainPath);

		Button wMainPath = new Button(wInputComposite, SWT.CHECK);
		props.setLook(wMainPath);
		FormData fdMainPath = new FormData();
		fdMainPath.top = new FormAttachment(wbOutputStep, margin);
		fdMainPath.left = new FormAttachment(middle, 0);
		// fdMainPath.right = new FormAttachment(100, 0); // who cares, it's a
		// checkbox
		wMainPath.setLayoutData(fdMainPath);

		wMainPath.setSelection(definition.isMainDataPath());
		wMainPath.addSelectionListener(new SelectionAdapter()
		{

			@Override
			public void widgetSelected(SelectionEvent event)
			{
				definition.setMainDataPath(!definition.isMainDataPath()); // flip
				// the
				// switch
			}

		});

		// Add a checkbox to indicate that all output mappings need to rename
		// the values back...
		//
		Label wlRenameOutput = new Label(wInputComposite, SWT.RIGHT);
		props.setLook(wlRenameOutput);
		wlRenameOutput.setText(Messages.getString("MappingFieldRunnerDialog.input.RenamingOnOutput")); //$NON-NLS-1$
		FormData fdlRenameOutput = new FormData();
		fdlRenameOutput.top = new FormAttachment(wMainPath, margin);
		fdlRenameOutput.left = new FormAttachment(0, 0);
		fdlRenameOutput.right = new FormAttachment(middle, -margin);
		wlRenameOutput.setLayoutData(fdlRenameOutput);

		Button wRenameOutput = new Button(wInputComposite, SWT.CHECK);
		props.setLook(wRenameOutput);
		FormData fdRenameOutput = new FormData();
		fdRenameOutput.top = new FormAttachment(wMainPath, margin);
		fdRenameOutput.left = new FormAttachment(middle, 0);
		// fdRenameOutput.right = new FormAttachment(100, 0); // who cares, it's
		// a check box
		wRenameOutput.setLayoutData(fdRenameOutput);

		wRenameOutput.setSelection(definition.isRenamingOnOutput());
		wRenameOutput.addSelectionListener(new SelectionAdapter()
		{

			@Override
			public void widgetSelected(SelectionEvent event)
			{
				definition.setRenamingOnOutput(!definition.isRenamingOnOutput()); // flip
				// the
				// switch
			}

		});

		// Allow for a small description
		//
		Label wlDescription = new Label(wInputComposite, SWT.RIGHT);
		props.setLook(wlDescription);
		wlDescription.setText(descriptionLabel); //$NON-NLS-1$
		FormData fdlDescription = new FormData();
		fdlDescription.top = new FormAttachment(wRenameOutput, margin);
		fdlDescription.left = new FormAttachment(0, 0); // First one in the left
		// top corner
		fdlDescription.right = new FormAttachment(middle, -margin);
		wlDescription.setLayoutData(fdlDescription);

		final Text wDescription = new Text(wInputComposite, SWT.MULTI | SWT.LEFT | SWT.BORDER | SWT.V_SCROLL
				| SWT.H_SCROLL);
		props.setLook(wDescription);
		wDescription.setText(Const.NVL(definition.getDescription(), ""));
		wDescription.addModifyListener(lsMod);
		FormData fdDescription = new FormData();
		fdDescription.top = new FormAttachment(wRenameOutput, margin);
		fdDescription.bottom = new FormAttachment(wRenameOutput, 100 + margin);
		fdDescription.left = new FormAttachment(middle, 0); // To the right of
		// the label
		fdDescription.right = new FormAttachment(wbOutputStep, -margin);
		wDescription.setLayoutData(fdDescription);
		wDescription.addFocusListener(new FocusAdapter()
		{
			@Override
			public void focusLost(FocusEvent event)
			{
				definition.setDescription(wDescription.getText());
			}
		});

		// Now add a table view with the 2 columns to specify: input and output
		// fields for the source and target steps.
		//
		final Button wbEnterMapping = new Button(wInputComposite, SWT.PUSH);
		props.setLook(wbEnterMapping);
		wbEnterMapping.setText(Messages.getString("MappingFieldRunnerDialog.button.EnterMapping"));
		FormData fdbEnterMapping = new FormData();
		fdbEnterMapping.top = new FormAttachment(wDescription, margin * 2);
		fdbEnterMapping.right = new FormAttachment(100, 0); // First one in the
		// left top corner
		wbEnterMapping.setLayoutData(fdbEnterMapping);

		ColumnInfo[] colinfo = new ColumnInfo[] {
				new ColumnInfo(sourceColumnLabel, ColumnInfo.COLUMN_TYPE_TEXT, false, false), //$NON-NLS-1$
				new ColumnInfo(targetColumnLabel, ColumnInfo.COLUMN_TYPE_TEXT, false, false), //$NON-NLS-1$
		};
		final TableView wFieldMappings = new TableView(transMeta, wInputComposite, SWT.FULL_SELECTION
				| SWT.SINGLE | SWT.BORDER, colinfo, 1, lsMod, props);
		props.setLook(wFieldMappings);
		FormData fdMappings = new FormData();
		fdMappings.left = new FormAttachment(0, 0);
		fdMappings.right = new FormAttachment(wbEnterMapping, -margin);
		fdMappings.top = new FormAttachment(wDescription, margin * 2);
		fdMappings.bottom = new FormAttachment(100, -20);
		wFieldMappings.setLayoutData(fdMappings);

		for (MappingValueRename valueRename : definition.getValueRenames())
		{
			TableItem tableItem = new TableItem(wFieldMappings.table, SWT.NONE);
			tableItem.setText(1, valueRename.getSourceValueName());
			tableItem.setText(2, valueRename.getTargetValueName());
		}
		wFieldMappings.removeEmptyRows();
		wFieldMappings.setRowNums();
		wFieldMappings.optWidth(true);

		wbEnterMapping.addSelectionListener(new SelectionAdapter()
		{

			@Override
			public void widgetSelected(SelectionEvent arg0)
			{
				try
				{
					RowMetaInterface sourceRowMeta = getFieldsFromStep(wInputStep.getText(), true, input);
					RowMetaInterface targetRowMeta = getFieldsFromStep(wOutputStep.getText(), false, input);
					String sourceFields[] = sourceRowMeta.getFieldNames();
					String targetFields[] = targetRowMeta.getFieldNames();

					EnterMappingDialog dialog = new EnterMappingDialog(shell, sourceFields, targetFields);
					List<SourceToTargetMapping> mappings = dialog.open();
					if (mappings != null)
					{
						// first clear the dialog...
						wFieldMappings.clearAll(false);

						// 
						definition.getValueRenames().clear();

						// Now add the new values...
						for (int i = 0; i < mappings.size(); i++)
						{
							SourceToTargetMapping mapping = mappings.get(i);
							TableItem item = new TableItem(wFieldMappings.table, SWT.NONE);
							item.setText(1, mapping.getSourceString(sourceFields));
							item.setText(2, mapping.getTargetString(targetFields));

							String source = input ? item.getText(1) : item.getText(2);
							String target = input ? item.getText(2) : item.getText(1);
							definition.getValueRenames().add(new MappingValueRename(source, target));
						}
						wFieldMappings.removeEmptyRows();
						wFieldMappings.setRowNums();
						wFieldMappings.optWidth(true);
					}
				} catch (KettleException e)
				{
					new ErrorDialog(shell, Messages.getString("System.Dialog.Error.Title"), Messages.getString("MappingFieldRunnerDialog.Exception.ErrorGettingMappingSourceAndTargetFields", e.toString()), e);
				}
			}

		});

		wOutputStep.addFocusListener(new FocusAdapter()
		{
			@Override
			public void focusLost(FocusEvent event)
			{
				definition.setOutputStepname(wOutputStep.getText());
				try
				{
					enableMappingButton(wbEnterMapping, input, wInputStep.getText(), wOutputStep.getText());
				} catch (KettleException e)
				{
					// Show the missing/wrong step name error
					// 
					new ErrorDialog(shell, "Error", "Unexpected error", e);
				}
			}
		});
		wbOutputStep.addSelectionListener(new SelectionAdapter()
		{
			@Override
			public void widgetSelected(SelectionEvent event)
			{
				String stepName = selectTransformationStepname(!input, input);
				if (stepName != null)
				{
					wOutputStep.setText(stepName);
					definition.setOutputStepname(stepName);
					try
					{
						enableMappingButton(wbEnterMapping, input, wInputStep.getText(), wOutputStep
								.getText());
					} catch (KettleException e)
					{
						// Show the missing/wrong stepname error
						new ErrorDialog(shell, "Error", "Unexpected error", e);
					}
				}
			}
		});

		FormData fdParametersComposite = new FormData();
		fdParametersComposite.left = new FormAttachment(0, 0);
		fdParametersComposite.top = new FormAttachment(0, 0);
		fdParametersComposite.right = new FormAttachment(100, 0);
		fdParametersComposite.bottom = new FormAttachment(100, 0);
		wInputComposite.setLayoutData(fdParametersComposite);

		wInputComposite.layout();
		wTab.setControl(wInputComposite);

		final ApplyChanges applyChanges = new MappingDefinitionTab(definition, wInputStep, wOutputStep,
				wMainPath, wDescription, wFieldMappings);
		changeList.add(applyChanges);

		// OK, suppose for some weird reason the user wants to remove an input
		// or output tab...
		wTabFolder.addCTabFolder2Listener(new CTabFolder2Adapter()
		{

			@Override
			public void close(CTabFolderEvent event)
			{
				if (event.item.equals(wTab))
				{
					// The user has the audacity to try and close this mapping
					// definition tab.
					// We really should warn him that this is a bad idea...
					MessageBox box = new MessageBox(shell, SWT.YES | SWT.NO);
					box.setText(Messages.getString("MappingFieldRunnerDialog.CloseDefinitionTabAreYouSure.Title"));
					box.setMessage(Messages.getString("MappingFieldRunnerDialog.CloseDefinitionTabAreYouSure.Message"));
					int answer = box.open();
					if (answer != SWT.YES)
					{
						event.doit = false;
					} else
					{
						// Remove it from our list to make sure it's gone...
						if (input)
							inputMappings.remove(definition);
						else
							outputMappings.remove(definition);

						// remove it from the changeList too...
						// Otherwise the dialog leaks memory.
						// 
						changeList.remove(applyChanges);
					}
				}
			}

		});

		wTabFolder.setSelection(wTab);

	}

	/**
	 * Enables or disables the mapping button. We can only enable it if the
	 * target steps allows a mapping to be made against it.
	 * 
	 * @param button
	 *            The button to disable or enable
	 * @param input
	 *            input or output. If it's true, we keep the button enabled all
	 *            the time.
	 * @param sourceStepname
	 *            The mapping output step
	 * @param targetStepname
	 *            The target step to verify
	 * @throws KettleException
	 */
	private void enableMappingButton(final Button button, boolean input, String sourceStepname,
			String targetStepname) throws KettleException
	{
		if (input)
			return; // nothing to do

		boolean enabled = false;

		if (mappingTransMeta != null)
		{
			StepMeta mappingInputStep = mappingTransMeta.findMappingInputStep(sourceStepname);
			if (mappingInputStep != null)
			{
				StepMeta mappingOutputStep = transMeta.findMappingOutputStep(targetStepname);
				RowMetaInterface requiredFields = mappingOutputStep.getStepMetaInterface().getRequiredFields(transMeta);
				if (requiredFields != null && requiredFields.size() > 0)
				{
					enabled = true;
				}
			}
		}

		button.setEnabled(enabled);
	}

	private void setMappingDefinitionTabNameAndToolTip(CTabItem wTab, String tabTitle, String tabTooltip,
			MappingIODefinition definition, boolean input)
	{

		String stepname;
		if (input)
		{
			stepname = definition.getInputStepname();
		} else
		{
			stepname = definition.getOutputStepname();
		}
		String description = definition.getDescription();

		if (Const.isEmpty(stepname))
		{
			wTab.setText(tabTitle); //$NON-NLS-1$
		} else
		{
			wTab.setText(tabTitle + " : " + stepname); //$NON-NLS-1$ $NON-NLS-2$
		}
		String tooltip = tabTooltip; //$NON-NLS-1$
		if (!Const.isEmpty(stepname))
		{
			tooltip += Const.CR + Const.CR + stepname;
		}
		if (!Const.isEmpty(description))
		{
			tooltip += Const.CR + Const.CR + description;
		}
		wTab.setToolTipText(tooltip); //$NON-NLS-1$
	}

	private void cancel()
	{
		stepname = null;
		mappingMeta.setChanged(changed);
		dispose();
	}

	private void ok()
	{
		if (Const.isEmpty(wStepname.getText())) return;

		stepname = wStepname.getText(); // return value

		mappingMeta.setFieldName(wFieldname.getText());
		mappingMeta.setExecuteEachRow(wExecuteForEachRow.getSelection());
		// Load the information on the tabs, optionally do some
		// verifications...
		// 
		collectInformation();

		mappingMeta.setMappingParameters(mappingParameters);
		mappingMeta.setInputMappings(inputMappings);
		mappingMeta.setOutputMappings(outputMappings);

		mappingMeta.setChanged(true);

		dispose();
	}

	private void collectInformation()
	{
		for (ApplyChanges applyChanges : changeList)
		{
			applyChanges.applyChanges(); // collect information from all
			// tabs...
		}
	}
	
	private void getFields()
	 {
		if(!gotPreviousFields)
		{
		 try{
			 String field=wFieldname.getText();
			 RowMetaInterface r = transMeta.getPrevStepFields(stepname);
			 if(r!=null)
			  {
				 wFieldname.setItems(r.getFieldNames());
			  }
			 if(field!=null) wFieldname.setText(field);
		 	}catch(KettleException ke){
				new ErrorDialog(shell, Messages.getString("TableOutputDialog.FailedToGetFields.DialogTitle"), Messages.getString("TableOutputDialog.FailedToGetFields.DialogMessage"), ke);
			}
		 	gotPreviousFields=true;
		}
	 }
}
