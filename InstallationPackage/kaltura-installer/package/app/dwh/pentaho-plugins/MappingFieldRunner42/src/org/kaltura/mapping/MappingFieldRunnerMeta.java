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

package org.kaltura.mapping;

import java.util.ArrayList;
import java.util.Arrays;
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
import org.pentaho.di.core.parameters.UnknownParamException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.row.ValueMetaInterface;
import org.pentaho.di.core.variables.VariableSpace;
import org.pentaho.di.core.xml.XMLHandler;
import org.pentaho.di.i18n.BaseMessages;
import org.pentaho.di.repository.HasRepositoryInterface;
import org.pentaho.di.repository.ObjectId;
import org.pentaho.di.repository.Repository;
import org.pentaho.di.repository.RepositoryDirectory;
import org.pentaho.di.repository.StringObjectId;
import org.pentaho.di.resource.ResourceDefinition;
import org.pentaho.di.resource.ResourceNamingInterface;
import org.pentaho.di.resource.ResourceReference;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.TransMeta.TransformationType;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepIOMeta;
import org.pentaho.di.trans.step.StepIOMetaInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;
import org.pentaho.di.trans.step.errorhandling.Stream;
import org.pentaho.di.trans.step.errorhandling.StreamIcon;
import org.pentaho.di.trans.step.errorhandling.StreamInterface.StreamType;
import org.pentaho.di.trans.steps.mapping.MappingIODefinition;
import org.pentaho.di.trans.steps.mapping.MappingParameters;
import org.pentaho.di.trans.steps.mapping.MappingValueRename;
import org.pentaho.di.trans.steps.mappinginput.MappingInputMeta;
import org.pentaho.di.trans.steps.mappingoutput.MappingOutputMeta;
import org.w3c.dom.Node;

/**
 * Meta-data for the Mapping step: contains name of the (sub-)transformation to
 * execute
 * 
 * @since 22-nov-2005
 * @author Matt
 * 
 */

public class MappingFieldRunnerMeta extends BaseStepMeta implements StepMetaInterface, HasRepositoryInterface {
  private static Class<?>                   PKG = MappingFieldRunnerMeta.class; // for i18n purposes, needed by Translator2!! $NON-NLS-1$
  private String                            fieldName;
  private ObjectId                          transObjectId;
  private boolean executeForEachRow;
  
  private List<MappingIODefinition>         inputMappings;
  private List<MappingIODefinition>         outputMappings;
  private MappingParameters                 mappingParameters;
  
  private boolean allowingMultipleInputs;
  private boolean allowingMultipleOutputs;
  

  /*
   * This repository object is injected from the outside at runtime or at design
   * time. It comes from either Spoon or Trans
   */
  private Repository                        repository;

  public MappingFieldRunnerMeta() {
    super(); // allocate BaseStepMeta

    inputMappings = new ArrayList<MappingIODefinition>();
    outputMappings = new ArrayList<MappingIODefinition>();
    mappingParameters = new MappingParameters();
  }

  public void loadXML(Node stepnode, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleXMLException {
    try {
      String transId = XMLHandler.getTagValue(stepnode, "trans_object_id");
      transObjectId = Const.isEmpty(transId) ? null : new StringObjectId(transId);

      fieldName = XMLHandler.getTagValue(stepnode, "fieldname"); //$NON-NLS-1$
      String forEachRow = XMLHandler.getTagValue(stepnode, "for_each_row");
      executeForEachRow =  Const.isEmpty(forEachRow) ? false : "Y".equalsIgnoreCase(forEachRow);
      Node mappingsNode = XMLHandler.getSubNode(stepnode, "mappings"); //$NON-NLS-1$
      inputMappings.clear();
      outputMappings.clear();
      
      String multiInput = XMLHandler.getTagValue(stepnode, "allow_multiple_input");
      allowingMultipleInputs = Const.isEmpty(multiInput) ? inputMappings.size()>1 : "Y".equalsIgnoreCase(multiInput);
      String multiOutput = XMLHandler.getTagValue(stepnode, "allow_multiple_output");
      allowingMultipleOutputs = Const.isEmpty(multiOutput) ? outputMappings.size()>1 : "Y".equalsIgnoreCase(multiOutput);
      
      if (mappingsNode != null) {
        // Read all the input mapping definitions...
        //
        Node inputNode = XMLHandler.getSubNode(mappingsNode, "input"); //$NON-NLS-1$
        int nrInputMappings = XMLHandler.countNodes(inputNode, MappingIODefinition.XML_TAG); //$NON-NLS-1$
        for (int i = 0; i < nrInputMappings; i++) {
          Node mappingNode = XMLHandler.getSubNodeByNr(inputNode, MappingIODefinition.XML_TAG, i);
          MappingIODefinition inputMappingDefinition = new MappingIODefinition(mappingNode);
          inputMappings.add(inputMappingDefinition);
        }
        Node outputNode = XMLHandler.getSubNode(mappingsNode, "output"); //$NON-NLS-1$
        int nrOutputMappings = XMLHandler.countNodes(outputNode, MappingIODefinition.XML_TAG); //$NON-NLS-1$
        for (int i = 0; i < nrOutputMappings; i++) {
          Node mappingNode = XMLHandler.getSubNodeByNr(outputNode, MappingIODefinition.XML_TAG, i);
          MappingIODefinition outputMappingDefinition = new MappingIODefinition(mappingNode);
          outputMappings.add(outputMappingDefinition);
        }

        // Load the mapping parameters too..
        //
        Node mappingParametersNode = XMLHandler.getSubNode(mappingsNode, MappingParameters.XML_TAG);
        mappingParameters = new MappingParameters(mappingParametersNode);
      } else {
        // backward compatibility...
        //
        Node inputNode = XMLHandler.getSubNode(stepnode, "input"); //$NON-NLS-1$
        Node outputNode = XMLHandler.getSubNode(stepnode, "output"); //$NON-NLS-1$

        int nrInput = XMLHandler.countNodes(inputNode, "connector"); //$NON-NLS-1$
        int nrOutput = XMLHandler.countNodes(outputNode, "connector"); //$NON-NLS-1$

        // null means: auto-detect
        //
        MappingIODefinition inputMappingDefinition = new MappingIODefinition();
        inputMappingDefinition.setMainDataPath(true);

        for (int i = 0; i < nrInput; i++) {
          Node inputConnector = XMLHandler.getSubNodeByNr(inputNode, "connector", i); //$NON-NLS-1$
          String inputField = XMLHandler.getTagValue(inputConnector, "field"); //$NON-NLS-1$
          String inputMapping = XMLHandler.getTagValue(inputConnector, "mapping"); //$NON-NLS-1$
          inputMappingDefinition.getValueRenames().add(new MappingValueRename(inputField, inputMapping));
        }

        // null means: auto-detect
        //
        MappingIODefinition outputMappingDefinition = new MappingIODefinition();
        outputMappingDefinition.setMainDataPath(true);

        for (int i = 0; i < nrOutput; i++) {
          Node outputConnector = XMLHandler.getSubNodeByNr(outputNode, "connector", i); //$NON-NLS-1$
          String outputField = XMLHandler.getTagValue(outputConnector, "field"); //$NON-NLS-1$
          String outputMapping = XMLHandler.getTagValue(outputConnector, "mapping"); //$NON-NLS-1$
          outputMappingDefinition.getValueRenames().add(new MappingValueRename(outputMapping, outputField));
        }

        // Don't forget to add these to the input and output mapping
        // definitions...
        //
        inputMappings.add(inputMappingDefinition);
        outputMappings.add(outputMappingDefinition);

        // The default is to have no mapping parameters: the concept didn't
        // exist before.
        //
        mappingParameters = new MappingParameters();
        
      }
    } catch (Exception e) {
      throw new KettleXMLException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.ErrorLoadingTransformationStepFromXML"), e); //$NON-NLS-1$
    }
  }

  public Object clone() {
    Object retval = super.clone();
    return retval;
  }

  public String getXML() {
    StringBuffer retval = new StringBuffer(300);

    retval.append("    ").append(XMLHandler.addTagValue("trans_object_id", transObjectId == null ? null : transObjectId.toString()));
    retval.append("    ").append(XMLHandler.addTagValue("fieldname", fieldName)); //$NON-NLS-1$
    retval.append("    ").append(XMLHandler.addTagValue("for_each_row", executeForEachRow) ); //$NON-NLS-1$
    
    retval.append("    ").append(XMLHandler.openTag("mappings")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$

    retval.append("      ").append(XMLHandler.openTag("input")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$
    for (int i = 0; i < inputMappings.size(); i++) {
      retval.append(inputMappings.get(i).getXML());
    }
    retval.append("      ").append(XMLHandler.closeTag("input")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$

    retval.append("      ").append(XMLHandler.openTag("output")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$
    for (int i = 0; i < outputMappings.size(); i++) {
      retval.append(outputMappings.get(i).getXML());
    }
    retval.append("      ").append(XMLHandler.closeTag("output")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$

    // Add the mapping parameters too
    //
    retval.append("      ").append(mappingParameters.getXML()).append(Const.CR); //$NON-NLS-1$

    retval.append("    ").append(XMLHandler.closeTag("mappings")).append(Const.CR); //$NON-NLS-1$ $NON-NLS-2$

    retval.append("    ").append(XMLHandler.addTagValue("allow_multiple_input", allowingMultipleInputs)); //$NON-NLS-1$
    retval.append("    ").append(XMLHandler.addTagValue("allow_multiple_output", allowingMultipleOutputs)); //$NON-NLS-1$

    return retval.toString();
  }

  public void readRep(Repository rep, ObjectId id_step, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleException {
    String transId = rep.getStepAttributeString(id_step, "trans_object_id");
    transObjectId = Const.isEmpty(transId) ? null : new StringObjectId(transId);
    fieldName = rep.getStepAttributeString(id_step, "fieldname"); //$NON-NLS-1$
    executeForEachRow = rep.getStepAttributeBoolean(id_step, "for_each_row");
    inputMappings.clear();
    outputMappings.clear();

    int nrInput = rep.countNrStepAttributes(id_step, "input_field"); //$NON-NLS-1$
    int nrOutput = rep.countNrStepAttributes(id_step, "output_field"); //$NON-NLS-1$

    // Backward compatibility...
    //
    if (nrInput > 0 || nrOutput > 0) {
      MappingIODefinition inputMappingDefinition = new MappingIODefinition(); 
      inputMappingDefinition.setMainDataPath(true);

      for (int i = 0; i < nrInput; i++) {
        String inputField = rep.getStepAttributeString(id_step, i, "input_field"); //$NON-NLS-1$
        String inputMapping = rep.getStepAttributeString(id_step, i, "input_mapping"); //$NON-NLS-1$
        inputMappingDefinition.getValueRenames().add(new MappingValueRename(inputField, inputMapping));
      }

      MappingIODefinition outputMappingDefinition = new MappingIODefinition();
      outputMappingDefinition.setMainDataPath(true);

      for (int i = 0; i < nrOutput; i++) {
        String outputField = rep.getStepAttributeString(id_step, i, "output_field"); //$NON-NLS-1$
        String outputMapping = rep.getStepAttributeString(id_step, i, "output_mapping"); //$NON-NLS-1$
        outputMappingDefinition.getValueRenames().add(new MappingValueRename(outputMapping, outputField));
      }

      // Don't forget to add these to the input and output mapping
      // definitions...
      //
      inputMappings.add(inputMappingDefinition);
      outputMappings.add(outputMappingDefinition);

      // The default is to have no mapping parameters: the concept didn't exist
      // before.
      mappingParameters = new MappingParameters();
    } else {
      nrInput = rep.countNrStepAttributes(id_step, "input_main_path"); //$NON-NLS-1$
      nrOutput = rep.countNrStepAttributes(id_step, "output_main_path"); //$NON-NLS-1$

      for (int i = 0; i < nrInput; i++) {
        inputMappings.add(new MappingIODefinition(rep, id_step, "input_", i));
      }

      for (int i = 0; i < nrOutput; i++) {
        outputMappings.add(new MappingIODefinition(rep, id_step, "output_", i));
      }

      mappingParameters = new MappingParameters(rep, id_step);
    }

    allowingMultipleInputs = rep.getStepAttributeBoolean(id_step, 0, "allow_multiple_input", inputMappings.size()>1);
    allowingMultipleOutputs = rep.getStepAttributeBoolean(id_step, 0, "allow_multiple_output", outputMappings.size()>1);    
  }

  public void saveRep(Repository rep, ObjectId id_transformation, ObjectId id_step) throws KettleException {
    rep.saveStepAttribute(id_transformation, id_step, "trans_object_id", transObjectId==null ? null : transObjectId.toString());
    rep.saveStepAttribute(id_transformation, id_step, "fieldname", fieldName); //$NON-NLS-1$
    rep.saveStepAttribute(id_transformation, id_step, "for_each_row", executeForEachRow);
    for (int i = 0; i < inputMappings.size(); i++) {
      inputMappings.get(i).saveRep(rep, id_transformation, id_step, "input_", i);
    }

    for (int i = 0; i < outputMappings.size(); i++) {
      outputMappings.get(i).saveRep(rep, id_transformation, id_step, "output_", i);
    }

    // save the mapping parameters too
    //
    mappingParameters.saveRep(rep, id_transformation, id_step);

    rep.saveStepAttribute(id_transformation, id_step, 0, "allow_multiple_input", allowingMultipleInputs);
    rep.saveStepAttribute(id_transformation, id_step, 0, "allow_multiple_output", allowingMultipleOutputs);
  }

  public void setDefault() {
    
    MappingIODefinition inputDefinition = new MappingIODefinition(null, null);
    inputDefinition.setMainDataPath(true);
    inputDefinition.setRenamingOnOutput(true);
    inputMappings.add(inputDefinition);
    MappingIODefinition outputDefinition = new MappingIODefinition(null, null);
    outputDefinition.setMainDataPath(true);
    outputMappings.add(outputDefinition);
    
    allowingMultipleInputs=false;
    allowingMultipleOutputs=false;
  }

  public void getFields(RowMetaInterface row, String origin, RowMetaInterface info[], StepMeta nextStep, VariableSpace space) throws KettleStepException {
    // First load some interesting data...

    // Then see which fields get added to the row.
    //
    TransMeta mappingTransMeta = null;
    try {
      mappingTransMeta = loadMappingMeta(fieldName, space);
    } catch (KettleException e) {
      throw new KettleStepException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.UnableToLoadMappingTransformation"), e);
    }

    // The field structure may depend on the input parameters as well (think of parameter replacements in MDX queries for instance)
	if (mappingParameters!=null) {
		
		// See if we need to pass all variables from the parent or not...
		//
		if (mappingParameters.isInheritingAllVariables()) {
			mappingTransMeta.copyVariablesFrom(space);
		}
		
		// Just set the variables in the transformation statically.
		// This just means: set a number of variables or parameter values:
		//
		List<String> subParams = Arrays.asList(mappingTransMeta.listParameters());
		
		for (int i=0;i<mappingParameters.getVariable().length;i++) {
			String name = mappingParameters.getVariable()[i];
			String value = space.environmentSubstitute(mappingParameters.getInputField()[i]);
			if (!Const.isEmpty(name) && !Const.isEmpty(value)) {
				if (subParams.contains(name)){
					try{
						mappingTransMeta.setParameterValue(name, value);	
					}
					catch(UnknownParamException e){
						// this is explicitly checked for up front
					}
				}
				mappingTransMeta.setVariable(name, value);
				
			}
		}
	}    
    
    // Keep track of all the fields that need renaming...
    //
    List<MappingValueRename> inputRenameList = new ArrayList<MappingValueRename>();

    /*
     * Before we ask the mapping outputs anything, we should teach the mapping
     * input steps in the sub-transformation about the data coming in...
     */
    for (MappingIODefinition definition : inputMappings) {

      RowMetaInterface inputRowMeta;

      if (definition.isMainDataPath() || Const.isEmpty(definition.getInputStepname())) {
        // The row metadata, what we pass to the mapping input step
        // definition.getOutputStep(), is "row"
        // However, we do need to re-map some fields...
        // 
        inputRowMeta = row.clone();
        for (MappingValueRename valueRename : definition.getValueRenames()) {
          ValueMetaInterface valueMeta = inputRowMeta.searchValueMeta(valueRename.getSourceValueName());
          if (valueMeta == null) {
            throw new KettleStepException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.UnableToFindField", valueRename.getSourceValueName()));
          }
          valueMeta.setName(valueRename.getTargetValueName());
        }
      } else {
        // The row metadata that goes to the info mapping input comes from the
        // specified step
        // In fact, it's one of the info steps that is going to contain this
        // information...
        //
        String[] infoSteps = getInfoSteps();
        int infoStepIndex = Const.indexOfString(definition.getInputStepname(), infoSteps);
        if (infoStepIndex < 0) {
          throw new KettleStepException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.UnableToFindMetadataInfo", definition.getInputStepname()));
        }
        if (info[infoStepIndex] != null) {
          inputRowMeta = info[infoStepIndex].clone();
        } else {
          inputRowMeta = null;
        }
      }

      // What is this mapping input step?
      //
      StepMeta mappingInputStep = mappingTransMeta.findMappingInputStep(definition.getOutputStepname());

      // We're certain it's a MappingInput step...
      //
      MappingInputMeta mappingInputMeta = (MappingInputMeta) mappingInputStep.getStepMetaInterface();

      // Inform the mapping input step about what it's going to receive...
      //
      mappingInputMeta.setInputRowMeta(inputRowMeta);

      // What values are we changing names for?
      //
      mappingInputMeta.setValueRenames(definition.getValueRenames());

      // Keep a list of the input rename values that need to be changed back at
      // the output
      // 
      if (definition.isRenamingOnOutput())
        MappingFieldRunner.addInputRenames(inputRenameList, definition.getValueRenames());
    }

    // All the mapping steps now know what they will be receiving.
    // That also means that the sub-transformation / mapping has everything it
    // needs.
    // So that means that the MappingOutput steps know exactly what the output
    // is going to be.
    // That could basically be anything.
    // It also could have absolutely no resemblance to what came in on the
    // input.
    // The relative old approach is therefore no longer suited.
    // 
    // OK, but what we *can* do is have the MappingOutput step rename the
    // appropriate fields.
    // The mapping step will tell this step how it's done.
    //
    // Let's look for the mapping output step that is relevant for this actual
    // call...
    //
    MappingIODefinition mappingOutputDefinition = null;
    if (nextStep == null) {
      // This is the main step we read from...
      // Look up the main step to write to.
      // This is the output mapping definition with "main path" enabled.
      //
      for (MappingIODefinition definition : outputMappings) {
        if (definition.isMainDataPath() || Const.isEmpty(definition.getOutputStepname())) {
          // This is the definition to use...
          //
          mappingOutputDefinition = definition;
        }
      }
    } else {
      // Is there an output mapping definition for this step?
      // If so, we can look up the Mapping output step to see what has changed.
      //

      for (MappingIODefinition definition : outputMappings) {
        if (nextStep.getName().equals(definition.getOutputStepname()) || definition.isMainDataPath() || Const.isEmpty(definition.getOutputStepname())) {
          mappingOutputDefinition = definition;
        }
      }
    }

    if (mappingOutputDefinition == null) {
      throw new KettleStepException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.UnableToFindMappingDefinition"));
    }

    // OK, now find the mapping output step in the mapping...
    // This method in TransMeta takes into account a number of things, such as
    // the step not specified, etc.
    // The method never returns null but throws an exception.
    //
    StepMeta mappingOutputStep = mappingTransMeta.findMappingOutputStep(mappingOutputDefinition.getInputStepname());

    // We know it's a mapping output step...
    MappingOutputMeta mappingOutputMeta = (MappingOutputMeta) mappingOutputStep.getStepMetaInterface();

    // Change a few columns.
    mappingOutputMeta.setOutputValueRenames(mappingOutputDefinition.getValueRenames());

    // Perhaps we need to change a few input columns back to the original?
    //
    mappingOutputMeta.setInputValueRenames(inputRenameList);

    // Now we know wat's going to come out of there...
    // This is going to be the full row, including all the remapping, etc.
    //
    RowMetaInterface mappingOutputRowMeta = mappingTransMeta.getStepFields(mappingOutputStep);

    row.clear();
    row.addRowMeta(mappingOutputRowMeta);
  }

  public String[] getInfoSteps() {
    String[] infoSteps = getStepIOMeta().getInfoStepnames();
    // Return null instead of empty array to preserve existing behavior
    return infoSteps.length == 0 ? null : infoSteps;
  }

  public String[] getTargetSteps() {

    List<String> targetSteps = new ArrayList<String>();
    // The infosteps are those steps that are specified in the input mappings
    for (MappingIODefinition definition : outputMappings) {
      if (!definition.isMainDataPath() && !Const.isEmpty(definition.getOutputStepname())) {
        targetSteps.add(definition.getOutputStepname());
      }
    }
    if (targetSteps.isEmpty())
      return null;

    return targetSteps.toArray(new String[targetSteps.size()]);
  }

  public void check(List<CheckResultInterface> remarks, TransMeta transMeta, StepMeta stepinfo, RowMetaInterface prev, String input[], String output[], RowMetaInterface info) {
    CheckResult cr;
    if (prev == null || prev.size() == 0) {
      cr = new CheckResult(CheckResultInterface.TYPE_RESULT_WARNING, BaseMessages.getString(PKG, "MappingFieldRunnerMeta.CheckResult.NotReceivingAnyFields"), stepinfo); //$NON-NLS-1$
      remarks.add(cr);
    } else {
      cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, BaseMessages.getString(PKG, "MappingFieldRunnerMeta.CheckResult.StepReceivingFields", prev.size() + ""), stepinfo); //$NON-NLS-1$ //$NON-NLS-2$
      remarks.add(cr);
    }

    // See if we have input streams leading to this step!
    if (input.length > 0) {
      cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, BaseMessages.getString(PKG, "MappingFieldRunnerMeta.CheckResult.StepReceivingFieldsFromOtherSteps"), stepinfo); //$NON-NLS-1$
      remarks.add(cr);
    } else {
      cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, BaseMessages.getString(PKG, "MappingFieldRunnerMeta.CheckResult.NoInputReceived"), stepinfo); //$NON-NLS-1$
      remarks.add(cr);
    }

    /*
     * TODO re-enable validation code for mappings...
     * 
     * // Change the names of the fields if this is required by the mapping. for
     * (int i=0;i<inputField.length;i++) { if (inputField[i]!=null &&
     * inputField[i].length()>0) { if (inputMapping[i]!=null &&
     * inputMapping[i].length()>0) { if (!inputField[i].equals(inputMapping[i]))
     * // rename these! { int idx = prev.indexOfValue(inputField[i]); if (idx<0)
     * { cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.MappingTargetFieldNotPresent",inputField[i]),
     * stepinfo); //$NON-NLS-1$ //$NON-NLS-2$ remarks.add(cr); } } } else { cr =
     * new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.MappingTargetFieldNotSepecified"
     * ,i+"",inputField[i]), stepinfo); //$NON-NLS-1$ //$NON-NLS-2$
     * //$NON-NLS-3$ remarks.add(cr); } } else { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.InputFieldNotSpecified",i+""), stepinfo);
     * //$NON-NLS-1$ //$NON-NLS-2$ remarks.add(cr); } }
     * 
     * // Then check the fields that get added to the row. //
     * 
     * Repository repository = Repository.getCurrentRepository(); TransMeta
     * mappingTransMeta = null; try { mappingTransMeta =
     * loadMappingMeta(fileName, transName, directoryPath, repository); }
     * catch(KettleException e) { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_OK,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.UnableToLoadMappingTransformation"
     * )+":"+Const.getStackTracker(e), stepinfo); //$NON-NLS-1$ remarks.add(cr);
     * }
     * 
     * if (mappingTransMeta!=null) { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_OK,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.MappingTransformationSpecified"), stepinfo);
     * //$NON-NLS-1$ remarks.add(cr);
     * 
     * StepMeta stepMeta = mappingTransMeta.getMappingOutputStep();
     * 
     * if (stepMeta!=null) { // See which fields are coming out of the mapping
     * output step of the sub-transformation // For these fields we check the
     * existance // RowMetaInterface fields = null; try { fields =
     * mappingTransMeta.getStepFields(stepMeta);
     * 
     * boolean allOK = true;
     * 
     * // Check the fields... for (int i=0;i<outputMapping.length;i++) {
     * ValueMetaInterface v = fields.searchValueMeta(outputMapping[i]); if
     * (v==null) // Not found! { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.MappingOutFieldSpecifiedCouldNotFound"
     * )+outputMapping[i], stepinfo); //$NON-NLS-1$ remarks.add(cr);
     * allOK=false; } }
     * 
     * if (allOK) { cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.AllOutputMappingFieldCouldBeFound"), stepinfo);
     * //$NON-NLS-1$ remarks.add(cr); } } catch(KettleStepException e) { cr =
     * new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.UnableToGetStepOutputFields"
     * )+stepMeta.getName()+"]", stepinfo); //$NON-NLS-1$ //$NON-NLS-2$
     * remarks.add(cr); } } else { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.NoMappingOutputStepSpecified"), stepinfo);
     * //$NON-NLS-1$ remarks.add(cr); } } else { cr = new
     * CheckResult(CheckResultInterface.TYPE_RESULT_ERROR,
     * BaseMessages.getString(PKG,
     * "MappingFieldRunnerMeta.CheckResult.NoMappingSpecified"), stepinfo); //$NON-NLS-1$
     * remarks.add(cr); }
     */
  }

  public StepInterface getStep(StepMeta stepMeta, StepDataInterface stepDataInterface, int cnr, TransMeta tr, Trans trans) {
    return new MappingFieldRunner(stepMeta, stepDataInterface, cnr, tr, trans);
  }

  public StepDataInterface getStepData() {
    return new MappingFieldRunnerData();
  }

  
  /**
   * @return the fileName
   */
  public String getFieldName() {
    return fieldName;
  }

  /**
   * @param fileName
   *          the fileName to set
   */
  public void setFieldName(String fieldName) {
    this.fieldName= fieldName;
  }

   /**
   * @return the inputMappings
   */
  public List<MappingIODefinition> getInputMappings() {
    return inputMappings;
  }

  /**
   * @param inputMappings
   *          the inputMappings to set
   */
  public void setInputMappings(List<MappingIODefinition> inputMappings) {
    this.inputMappings = inputMappings;
    resetStepIoMeta();
  }

  /**
   * @return the outputMappings
   */
  public List<MappingIODefinition> getOutputMappings() {
    return outputMappings;
  }

  /**
   * @param outputMappings
   *          the outputMappings to set
   */
  public void setOutputMappings(List<MappingIODefinition> outputMappings) {
    this.outputMappings = outputMappings;
  }

  /**
   * @return the mappingParameters
   */
  public MappingParameters getMappingParameters() {
    return mappingParameters;
  }

  /**
   * @param mappingParameters
   *          the mappingParameters to set
   */
  public void setMappingParameters(MappingParameters mappingParameters) {
    this.mappingParameters = mappingParameters;
  }

  @Override
  public List<ResourceReference> getResourceDependencies(TransMeta transMeta, StepMeta stepInfo) {
    List<ResourceReference> references = new ArrayList<ResourceReference>(5);
    ResourceReference reference = new ResourceReference(stepInfo);
    references.add(reference);

    return references;
  }

  @Override
  public String exportResources(VariableSpace space, Map<String, ResourceDefinition> definitions, ResourceNamingInterface resourceNamingInterface, Repository repository) throws KettleException {
    try {
      // Try to load the transformation from repository or file.
      // Modify this recursively too...
      // 
      // NOTE: there is no need to clone this step because the caller is
      // responsible for this.
      //
      // First load the mapping metadata...
      //
      TransMeta mappingTransMeta = loadMappingMeta(fieldName, space);

      // Also go down into the mapping transformation and export the files
      // there. (mapping recursively down)
      //
      String proposedNewFilename = mappingTransMeta.exportResources(mappingTransMeta, definitions, resourceNamingInterface, repository);

      // To get a relative path to it, we inject
      // ${Internal.Job.Filename.Directory}
      //
      String newFilename = "${" + Const.INTERNAL_VARIABLE_TRANSFORMATION_FILENAME_DIRECTORY + "}/" + proposedNewFilename;

      // Set the correct filename inside the XML.
      //
      mappingTransMeta.setFilename(newFilename);

      // exports always reside in the root directory, in case we want to turn
      // this into a file repository...
      //
      mappingTransMeta.setRepositoryDirectory(new RepositoryDirectory());

      // change it in the job entry
      //
      fieldName = newFilename;

      return proposedNewFilename;
    } catch (Exception e) {
      throw new KettleException(BaseMessages.getString(PKG, "MappingFieldRunnerMeta.Exception.UnableToLoadTransformation", fieldName)); //$NON-NLS-1$
    }
  }

  /**
   * @return the repository
   */
  public Repository getRepository() {
    return repository;
  }

  /**
   * @param repository
   *          the repository to set
   */
  public void setRepository(Repository repository) {
    this.repository = repository;
  }

  /**
   * @return the transObjectId
   */
  public ObjectId getTransObjectId() {
    return transObjectId;
  }

  /**
   * @param transObjectId
   *          the transObjectId to set
   */
  public void setTransObjectId(ObjectId transObjectId) {
    this.transObjectId = transObjectId;
  }

  @Override
  public StepIOMetaInterface getStepIOMeta() {
    if (ioMeta == null) {
      // TODO Create a dynamic StepIOMeta so that we can more easily manipulate the info streams?
      ioMeta = new StepIOMeta(true, true, true, false, true, false);
      for (MappingIODefinition def : inputMappings) {
        if (isInfoMapping(def)) {
          Stream stream = new Stream(StreamType.INFO, def.getInputStep(), BaseMessages.getString(PKG,
              "MappingFieldRunnerMeta.InfoStream.Description"), StreamIcon.INFO, null); //$NON-NLS-1$
          ioMeta.addStream(stream);
        }
      }
    }
    return ioMeta;
  }
  
  private boolean isInfoMapping(MappingIODefinition def) {
    return !def.isMainDataPath() && !Const.isEmpty(def.getInputStepname());
  }

  /**
   * Remove the cached {@link StepIOMeta} so it is recreated when it is next accessed.
   */
  public void resetStepIoMeta() {
    ioMeta = null;
  }

  public boolean excludeFromRowLayoutVerification() {
    return true;
  }
  
  @Override
  public void searchInfoAndTargetSteps(List<StepMeta> steps) {
    // Assign all StepMeta references for Input Mappings that are INFO inputs
    for(MappingIODefinition def : inputMappings) {
      if(isInfoMapping(def)) {
        def.setInputStep(StepMeta.findStep(steps, def.getInputStepname()));
      }
    }
  }
  
  public TransformationType[] getSupportedTransformationTypes() {
    return new TransformationType[] { TransformationType.Normal, };
  }
  

  /**
   * @return the allowingMultipleInputs
   */
  public boolean isAllowingMultipleInputs() {
    return allowingMultipleInputs;
  }

  /**
   * @param allowingMultipleInputs the allowingMultipleInputs to set
   */
  public void setAllowingMultipleInputs(boolean allowingMultipleInputs) {
    this.allowingMultipleInputs = allowingMultipleInputs;
  }

  /**
   * @return the allowingMultipleOutputs
   */
  public boolean isAllowingMultipleOutputs() {
    return allowingMultipleOutputs;
  }

  /**
   * @param allowingMultipleOutputs the allowingMultipleOutputs to set
   */
  public void setAllowingMultipleOutputs(boolean allowingMultipleOutputs) {
    this.allowingMultipleOutputs = allowingMultipleOutputs;
  }
  
  public synchronized static final TransMeta loadMappingMeta(String fileName,  VariableSpace space) throws KettleException
  {
      TransMeta mappingTransMeta = null;
      
      String realFilename = space.environmentSubstitute(fileName);
   try
      {
      	// OK, load the meta-data from file...
          mappingTransMeta = new TransMeta( realFilename, false ); // don't set internal variables: they belong to the parent thread!
     }
      catch(Exception e)
      {
          throw new KettleException(BaseMessages.getString(PKG,"MappingFieldRunnerMeta.Exception.UnableToLoadMapping"), e);
      }     
      
      return mappingTransMeta;
  }
  /**
   * @return the transName
   */
  public boolean getExecuteForEachRow()
  {
      return executeForEachRow;
  }

  /**
   * @param transName the transName to set
   */
  public void setExecuteEachRow(boolean forEachrow)
  {
      this.executeForEachRow = forEachrow;
  }

}
