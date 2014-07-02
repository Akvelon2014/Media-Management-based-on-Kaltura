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

package org.kaltura.mysql;

import java.util.List;
import java.util.Map;

import org.pentaho.di.core.CheckResult;
import org.pentaho.di.core.CheckResultInterface;
import org.pentaho.di.core.Const;
import org.pentaho.di.core.Counter;
import org.pentaho.di.core.SQLStatement;
import org.pentaho.di.core.database.Database;
import org.pentaho.di.core.database.DatabaseMeta;
import org.pentaho.di.core.exception.KettleDatabaseException;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.exception.KettleXMLException;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.row.ValueMetaInterface;
import org.pentaho.di.core.variables.VariableSpace;
import org.pentaho.di.core.xml.XMLHandler;
import org.pentaho.di.repository.Repository;
import org.pentaho.di.shared.SharedObjectInterface;
import org.pentaho.di.trans.DatabaseImpact;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStepMeta;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;
import org.w3c.dom.Node;

public class MySQLTableOutputMeta extends BaseStepMeta implements StepMetaInterface
{
	private DatabaseMeta databaseMeta;
    private String       schemaName;
	private String       tablename;
	private String       commitSize;
	private boolean      truncateTable;
	private boolean      ignoreErrors;
	private boolean      useBatchUpdate;
    
    private boolean      partitioningEnabled;
    private String       partitioningField;
    private boolean      partitioningDaily;
    private boolean      partitioningMonthly;
    	
    private boolean      tableNameInField;
    private String       tableNameField;
    private boolean      tableNameInTable;
       
    /** Fields containing the values in the input stream to insert */
    private String[]     fieldStream;

    /** Fields in the table to insert */
    private String[]     fieldDatabase;

	private String batchSize;

    /**
     * @return Returns the tableNameInTable.
     */
    public boolean isTableNameInTable()
    {
        return tableNameInTable;
    }

    /**
     * @param tableNameInTable The tableNameInTable to set.
     */
    public void setTableNameInTable(boolean tableNameInTable)
    {
        this.tableNameInTable = tableNameInTable;
    }

    /**
     * @return Returns the tableNameField.
     */
    public String getTableNameField()
    {
        return tableNameField;
    }

    /**
     * @param tableNameField The tableNameField to set.
     */
    public void setTableNameField(String tableNameField)
    {
        this.tableNameField = tableNameField;
    }

    /**
     * @return Returns the tableNameInField.
     */
    public boolean isTableNameInField()
    {
        return tableNameInField;
    }

    /**
     * @param tableNameInField The tableNameInField to set.
     */
    public void setTableNameInField(boolean tableNameInField)
    {
        this.tableNameInField = tableNameInField;
    }

    
    /**
     * @return Returns the partitioningDaily.
     */
    public boolean isPartitioningDaily()
    {
        return partitioningDaily;
    }

    /**
     * @param partitioningDaily The partitioningDaily to set.
     */
    public void setPartitioningDaily(boolean partitioningDaily)
    {
        this.partitioningDaily = partitioningDaily;
    }

    /**
     * @return Returns the partitioningMontly.
     */
    public boolean isPartitioningMonthly()
    {
        return partitioningMonthly;
    }

    /**
     * @param partitioningMontly The partitioningMontly to set.
     */
    public void setPartitioningMonthly(boolean partitioningMontly)
    {
        this.partitioningMonthly = partitioningMontly;
    }

    /**
     * @return Returns the partitioningEnabled.
     */
    public boolean isPartitioningEnabled()
    {
        return partitioningEnabled;
    }

    /**
     * @param partitioningEnabled The partitioningEnabled to set.
     */
    public void setPartitioningEnabled(boolean partitioningEnabled)
    {
        this.partitioningEnabled = partitioningEnabled;
    }

    /**
     * @return Returns the partitioningField.
     */
    public String getPartitioningField()
    {
        return partitioningField;
    }

    /**
     * @param partitioningField The partitioningField to set.
     */
    public void setPartitioningField(String partitioningField)
    {
        this.partitioningField = partitioningField;
    }

    
    public MySQLTableOutputMeta()
	{
		super(); // allocate BaseStepMeta
		useBatchUpdate=true;
		commitSize="1000";
		batchSize="1";
		
		fieldStream   = new String[0];
		fieldDatabase = new String[0];
	}
    
	public void allocate(int nrRows)
	{
		fieldStream   = new String[nrRows];
		fieldDatabase = new String[nrRows];
	}    
	
	public void loadXML(Node stepnode, List<DatabaseMeta> databases, Map<String, Counter> counters)
		throws KettleXMLException
	{
		readData(stepnode, databases);
	}

	public Object clone()
	{
		MySQLTableOutputMeta retval = (MySQLTableOutputMeta)super.clone();
		
		int nrStream   = fieldStream.length;
		int nrDatabase = fieldDatabase.length;

		retval.fieldStream   = new String[nrStream];
		retval.fieldDatabase = new String[nrDatabase];

		for (int i = 0; i < nrStream; i++)
		{
			retval.fieldStream[i] = fieldStream[i];
		}
		
		for (int i = 0; i < nrDatabase; i++)
		{
			retval.fieldDatabase[i] = fieldDatabase[i];
		}	
		
		return retval;
	}
	
	/**
     * @return Returns the database.
     */
    public DatabaseMeta getDatabaseMeta()
    {
        return databaseMeta;
    }
    
    /**
     * @param database The database to set.
     */
    public void setDatabaseMeta(DatabaseMeta database)
    {
        this.databaseMeta = database;
    }
    
    /**
     * @return Returns the commitSize.
     */
    public String getCommitSize()
    {
        return commitSize;
    }
    
    /**
     * @param commitSize The commitSize to set.
     */
    public void setCommitSize(int commitSizeInt)
    {
        this.commitSize = Integer.toString(commitSizeInt);
    }

    /**
     * @param commitSize The commitSize to set.
     */
    public void setCommitSize(String commitSize)
    {
        this.commitSize = commitSize;
    }
    
	public String getBatchSize()
    {
        return batchSize;
    }
	
    /**
     * @param batchSize The commitSize to set.
     */
    public void setBatchSize(int batchSizeInt)
    {
        this.batchSize = Integer.toString(batchSizeInt);
    }

    /**
     * @param commitSize The commitSize to set.
     */
    public void setBatchSize(String batchSize)
    {
        this.batchSize = batchSize;
    }

    /**
     * @return Returns the tablename.
     */
    public String getTablename()
    {
        return tablename;
    }
    
    /**
     * @param tablename The tablename to set.
     */
    public void setTablename(String tablename)
    {
        this.tablename = tablename;
    }
    
    /**
     * @return Returns the truncate table flag.
     */
    public boolean truncateTable()
    {
        return truncateTable;
    }
    
    /**
     * @param truncateTable The truncate table flag to set.
     */
    public void setTruncateTable(boolean truncateTable)
    {
        this.truncateTable = truncateTable;
    }
    
    /**
     * @param ignoreErrors The ignore errors flag to set.
     */
    public void setIgnoreErrors(boolean ignoreErrors)
    {
        this.ignoreErrors = ignoreErrors;
    }
    
    /**
     * @return Returns the ignore errors flag.
     */
    public boolean ignoreErrors()
    {
        return ignoreErrors;
    }
    
    /**
     * @param useBatchUpdate The useBatchUpdate flag to set.
     */
    public void setUseBatchUpdate(boolean useBatchUpdate)
    {
        this.useBatchUpdate = useBatchUpdate;
    }
    
    /**
     * @return Returns the useBatchUpdate flag.
     */
    public boolean useBatchUpdate()
    {
        return useBatchUpdate;
    }
    
    
	private void readData(Node stepnode, List<? extends SharedObjectInterface> databases) throws KettleXMLException
	{
		try
		{
			String con     = XMLHandler.getTagValue(stepnode, "connection");
			databaseMeta   = DatabaseMeta.findDatabase(databases, con);
            schemaName     = XMLHandler.getTagValue(stepnode, "schema");
			tablename      = XMLHandler.getTagValue(stepnode, "table");
			commitSize     = XMLHandler.getTagValue(stepnode, "commit");
			batchSize	   = XMLHandler.getTagValue(stepnode, "batchsize");
			truncateTable  = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "truncate"));
			ignoreErrors   = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "ignore_errors"));
			useBatchUpdate = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "use_batch"));
			
			partitioningEnabled  = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "partitioning_enabled"));
            partitioningField    = XMLHandler.getTagValue(stepnode, "partitioning_field");
            partitioningDaily    = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "partitioning_daily"));
            partitioningMonthly  = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "partitioning_monthly"));
            
            tableNameInField     = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "tablename_in_field"));
            tableNameField       = XMLHandler.getTagValue(stepnode, "tablename_field");
            tableNameInTable     = "Y".equalsIgnoreCase(XMLHandler.getTagValue(stepnode, "tablename_in_table"));
            
			Node fields = XMLHandler.getSubNode(stepnode, "fields");   //$NON-NLS-1$
			int nrRows  = XMLHandler.countNodes(fields, "field");      //$NON-NLS-1$
			
			allocate(nrRows);
			
			for (int i=0;i<nrRows;i++)
			{
				Node knode = XMLHandler.getSubNodeByNr(fields, "field", i);         //$NON-NLS-1$
				
				fieldDatabase   [i] = XMLHandler.getTagValue(knode, "column_name");  //$NON-NLS-1$
				fieldStream     [i] = XMLHandler.getTagValue(knode, "stream_name"); //$NON-NLS-1$
			}
        }
		catch(Exception e)
		{
			throw new KettleXMLException("Unable to load step info from XML", e);
		}
	}

	public void setDefault()
	{
		databaseMeta = null;
		tablename      = "";
		commitSize = "1000";
		batchSize = "1";
        
        partitioningEnabled = false;
        partitioningMonthly = true;
        partitioningField   = "";
        tableNameInTable    = true;
        tableNameField      = "";     
	}

	public String getXML()
	{
		StringBuilder retval=new StringBuilder();
		
		retval.append("    "+XMLHandler.addTagValue("connection",     databaseMeta==null?"":databaseMeta.getName()));
        retval.append("    "+XMLHandler.addTagValue("schema",         schemaName));
		retval.append("    "+XMLHandler.addTagValue("table",          tablename));
		retval.append("    "+XMLHandler.addTagValue("commit",         commitSize));
		retval.append("    "+XMLHandler.addTagValue("batchsize",         batchSize));
		retval.append("    "+XMLHandler.addTagValue("truncate",       truncateTable));
		retval.append("    "+XMLHandler.addTagValue("ignore_errors",  ignoreErrors));
		retval.append("    "+XMLHandler.addTagValue("use_batch",      useBatchUpdate));

        retval.append("    "+XMLHandler.addTagValue("partitioning_enabled",   partitioningEnabled));
        retval.append("    "+XMLHandler.addTagValue("partitioning_field",     partitioningField));
        retval.append("    "+XMLHandler.addTagValue("partitioning_daily",     partitioningDaily));
        retval.append("    "+XMLHandler.addTagValue("partitioning_monthly",   partitioningMonthly));
        
        retval.append("    "+XMLHandler.addTagValue("tablename_in_field", tableNameInField));
        retval.append("    "+XMLHandler.addTagValue("tablename_field", tableNameField));
        retval.append("    "+XMLHandler.addTagValue("tablename_in_table", tableNameInTable));
       
		retval.append("    <fields>").append(Const.CR); //$NON-NLS-1$

		for (int i=0;i<fieldDatabase.length;i++)
		{
			retval.append("        <field>").append(Const.CR); //$NON-NLS-1$
			retval.append("          ").append(XMLHandler.addTagValue("column_name", fieldDatabase[i])); //$NON-NLS-1$ //$NON-NLS-2$
			retval.append("          ").append(XMLHandler.addTagValue("stream_name", fieldStream[i])); //$NON-NLS-1$ //$NON-NLS-2$
			retval.append("        </field>").append(Const.CR); //$NON-NLS-1$
		}
		retval.append("    </fields>").append(Const.CR); //$NON-NLS-1$

		return retval.toString();
	}

	public void readRep(Repository rep, long id_step, List<DatabaseMeta> databases, Map<String, Counter> counters) throws KettleException
	{
		try
		{
			long id_connection =   rep.getStepAttributeInteger(id_step, "id_connection"); 
			databaseMeta       = DatabaseMeta.findDatabase( databases, id_connection);
            schemaName         =   rep.getStepAttributeString (id_step, "schema");
			tablename          =   rep.getStepAttributeString (id_step, "table");
			long commitSizeInt =   rep.getStepAttributeInteger(id_step, "commit");
			commitSize         =   rep.getStepAttributeString(id_step, "commit");
			if (Const.isEmpty(commitSize)) {
				commitSize=Long.toString(commitSizeInt);
			}
			long batchSizeInt =   rep.getStepAttributeInteger(id_step, "batchsize");
			batchSize         =   rep.getStepAttributeString(id_step, "batchsize");
			if (Const.isEmpty(batchSize)) {
				batchSize=Long.toString(batchSizeInt);
			}
			truncateTable    =      rep.getStepAttributeBoolean(id_step, "truncate"); 
			ignoreErrors     =      rep.getStepAttributeBoolean(id_step, "ignore_errors"); 
			useBatchUpdate   =      rep.getStepAttributeBoolean(id_step, "use_batch"); 
            
            partitioningEnabled   = rep.getStepAttributeBoolean(id_step, "partitioning_enabled"); 
            partitioningField     = rep.getStepAttributeString (id_step, "partitioning_field"); 
            partitioningDaily     = rep.getStepAttributeBoolean(id_step, "partitioning_daily"); 
            partitioningMonthly   = rep.getStepAttributeBoolean(id_step, "partitioning_monthly"); 

            tableNameInField      = rep.getStepAttributeBoolean(id_step, "tablename_in_field"); 
            tableNameField        = rep.getStepAttributeString (id_step, "tablename_field"); 
            tableNameInTable      = rep.getStepAttributeBoolean(id_step, "tablename_in_table");
                       
			int nrCols    = rep.countNrStepAttributes(id_step, "column_name"); //$NON-NLS-1$
			int nrStreams = rep.countNrStepAttributes(id_step, "stream_name"); //$NON-NLS-1$
			
			int nrRows = (nrCols < nrStreams ? nrStreams : nrCols);
			allocate(nrRows);
			
			for (int idx=0; idx < nrRows; idx++)
			{
				fieldDatabase[idx] = Const.NVL(rep.getStepAttributeString(id_step, 
						                                                  idx, "column_name"), ""); //$NON-NLS-1$ //$NON-NLS-2$
				fieldStream[idx]   = Const.NVL(rep.getStepAttributeString(id_step, 
						                                                  idx, "stream_name"), ""); //$NON-NLS-1$ //$NON-NLS-2$
			}
		}
		catch(Exception e)
		{
			throw new KettleException("Unexpected error reading step information from the repository", e);
		}
	}

	public void saveRep(Repository rep, long id_transformation, long id_step) throws KettleException
	{
		try
		{
			rep.saveStepAttribute(id_transformation, id_step, "id_connection",   databaseMeta==null?-1:databaseMeta.getID());
            rep.saveStepAttribute(id_transformation, id_step, "schema",          schemaName);
			rep.saveStepAttribute(id_transformation, id_step, "table",       	 tablename);
			rep.saveStepAttribute(id_transformation, id_step, "commit",          commitSize);
			rep.saveStepAttribute(id_transformation, id_step, "batchsize",          batchSize);
			rep.saveStepAttribute(id_transformation, id_step, "truncate",        truncateTable);
			rep.saveStepAttribute(id_transformation, id_step, "ignore_errors",   ignoreErrors);
			rep.saveStepAttribute(id_transformation, id_step, "use_batch",       useBatchUpdate);
			
            rep.saveStepAttribute(id_transformation, id_step, "partitioning_enabled", partitioningEnabled);
            rep.saveStepAttribute(id_transformation, id_step, "partitioning_field",   partitioningField);
            rep.saveStepAttribute(id_transformation, id_step, "partitioning_daily",   partitioningDaily);
            rep.saveStepAttribute(id_transformation, id_step, "partitioning_monthly", partitioningMonthly);
            
            rep.saveStepAttribute(id_transformation, id_step, "tablename_in_field", tableNameInField);
            rep.saveStepAttribute(id_transformation, id_step, "tablename_field" ,   tableNameField);
            rep.saveStepAttribute(id_transformation, id_step, "tablename_in_table", tableNameInTable);

        	int nrRows = (fieldDatabase.length < fieldStream.length ? fieldStream.length :
        		                                                      fieldDatabase.length);
			for (int idx=0; idx < nrRows; idx++)
			{
				String columnName = (idx < fieldDatabase.length ? fieldDatabase[idx] : "");
				String streamName = (idx < fieldStream.length   ? fieldStream[idx] : "");
				rep.saveStepAttribute(id_transformation, id_step, idx, "column_name", columnName); //$NON-NLS-1$
				rep.saveStepAttribute(id_transformation, id_step, idx, "stream_name", streamName); //$NON-NLS-1$
			}
            
			// Also, save the step-database relationship!
			if (databaseMeta!=null)  { 
				rep.insertStepDatabase(id_transformation, id_step, databaseMeta.getID());			
			}
		}
		catch(Exception e)
		{
			throw new KettleException("Unable to save step information to the repository for id_step="+id_step, e);
		}
	}

	public void check(List<CheckResultInterface> remarks, TransMeta transMeta, StepMeta stepMeta, RowMetaInterface prev, String input[], String output[], RowMetaInterface info)
	{
		if (databaseMeta!=null)
		{
			CheckResult cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.ConnectionExists"), stepMeta);
			remarks.add(cr);

			Database db = new Database(databaseMeta);
			db.shareVariablesWith(transMeta);
			try
			{
				db.connect();
				
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.ConnectionOk"), stepMeta);
				remarks.add(cr);

				if (!Const.isEmpty(tablename))
				{
                    String schemaTable = databaseMeta.getQuotedSchemaTableCombination(db.environmentSubstitute(schemaName), db.environmentSubstitute(tablename));
					// Check if this table exists...
					if (db.checkTableExists(schemaTable))
					{
						cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.TableAccessible", schemaTable), stepMeta);
						remarks.add(cr);

						RowMetaInterface r = db.getTableFields(schemaTable);
						if (r!=null)
						{
							cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.TableOk", schemaTable), stepMeta);
							remarks.add(cr);

							String error_message = "";
							boolean error_found = false;
							// OK, we have the table fields.
							// Now see what we can find as previous step...
							if (prev!=null && prev.size()>0)
							{
								cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.FieldsReceived", ""+prev.size()), stepMeta);
								remarks.add(cr);
	
								
								// Specifying the column names explicitly
								for (int i=0;i<getFieldDatabase().length;i++)
								{
									int idx = r.indexOfValue(getFieldDatabase()[i]);
									if (idx<0) 
									{
										error_message+="\t\t"+ getFieldDatabase()[i] + Const.CR;
										error_found=true;
									} 
								}
								if (error_found) 
								{
									error_message=Messages.getString( "TableOutputMeta.CheckResult.FieldsSpecifiedNotInTable", error_message);

									cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, error_message, stepMeta);
									remarks.add(cr);
								}
								else
								{
									cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.AllFieldsFoundInOutput"), stepMeta);
									remarks.add(cr);
								}
							
	
								error_message="";
								
								// Specifying the column names explicitly									
								for (int i=0;i<getFieldStream().length;i++)
								{
									int idx = prev.indexOfValue(getFieldStream()[i]);
									if (idx<0) 
									{
										error_message+="\t\t"+getFieldStream()[i]+Const.CR;
										error_found=true;
									} 
								}
								if (error_found) 
								{
									error_message=Messages.getString( "TableOutputMeta.CheckResult.FieldsSpecifiedNotFound", error_message);

									cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, error_message, stepMeta);
									remarks.add(cr);
								}
								else
								{
									cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.AllFieldsFound"), stepMeta);
									remarks.add(cr);
								}														
							}
							else
							{
								cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.NoFields"), stepMeta);
								remarks.add(cr);
							}
						}
						else
						{
							cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.TableNotAccessible"), stepMeta);
							remarks.add(cr);
						}
					}
					else
					{
						cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.TableError", schemaTable), stepMeta);
						remarks.add(cr);
					}
				}
				else
				{
					cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.NoTableName"), stepMeta);
					remarks.add(cr);
				}
			}
			catch(KettleException e)
			{
				cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.UndefinedError", e.getMessage()), stepMeta);
				remarks.add(cr);
			}
			finally
			{
				db.disconnect();
			}
		}
		else
		{
			CheckResult cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.NoConnection"), stepMeta);
			remarks.add(cr);
		}
		
		// See if we have input streams leading to this step!
		if (input.length>0)
		{
			CheckResult cr = new CheckResult(CheckResultInterface.TYPE_RESULT_OK, Messages.getString( "TableOutputMeta.CheckResult.ExpectedInputOk"), stepMeta);
			remarks.add(cr);
		}
		else
		{
			CheckResult cr = new CheckResult(CheckResultInterface.TYPE_RESULT_ERROR, Messages.getString( "TableOutputMeta.CheckResult.ExpectedInputError"), stepMeta);
			remarks.add(cr);
		}
	}

	public StepInterface getStep(StepMeta stepMeta, StepDataInterface stepDataInterface, int cnr, TransMeta transMeta, Trans trans)
	{
		return new MySQLTableOutput(stepMeta, stepDataInterface, cnr, transMeta, trans);
	}
	
	public StepDataInterface getStepData()
	{
		return new MySQLTableOutputData();
	}
	
	public void analyseImpact(List<DatabaseImpact> impact, TransMeta transMeta, StepMeta stepMeta, RowMetaInterface prev, String input[], String output[], RowMetaInterface info)
	{
		if (truncateTable)
		{
			DatabaseImpact ii = new DatabaseImpact( DatabaseImpact.TYPE_IMPACT_TRUNCATE, 
											transMeta.getName(),
											stepMeta.getName(),
											databaseMeta.getDatabaseName(),
											tablename,
											"",
											"",
											"",
											"",
											"Truncate of table"
											);
			impact.add(ii);

		}
		// The values that are entering this step are in "prev":
		if (prev!=null)
		{
			for (int i=0;i<prev.size();i++)
			{
				ValueMetaInterface v = prev.getValueMeta(i);
				DatabaseImpact ii = new DatabaseImpact( DatabaseImpact.TYPE_IMPACT_WRITE, 
												transMeta.getName(),
												stepMeta.getName(),
												databaseMeta.getDatabaseName(),
												tablename,
												v.getName(),
												v.getName(),
                                                v!=null?v.getOrigin():"?",
												"",
												"Type = "+v.toStringMeta()
												);
				impact.add(ii);
			}
		}
	}

	public SQLStatement getSQLStatements(TransMeta transMeta, StepMeta stepMeta, RowMetaInterface prev)
	{
		SQLStatement retval = new SQLStatement(stepMeta.getName(), databaseMeta, null); // default: nothing to do!
	
		if (databaseMeta!=null)
		{
			if (prev!=null && prev.size()>0)
			{
				if (!Const.isEmpty(tablename))
				{
					Database db = new Database(databaseMeta);
					db.shareVariablesWith(transMeta);
					try
					{
						db.connect();
						
                        String schemaTable = databaseMeta.getQuotedSchemaTableCombination(schemaName, tablename);
                        String cr_table = db.getDDL(schemaTable, prev);
						
						// Empty string means: nothing to do: set it to null...
						if (cr_table==null || cr_table.length()==0) cr_table=null;
						
						retval.setSQL(cr_table);
					}
					catch(KettleDatabaseException dbe)
					{
						retval.setError(Messages.getString( "TableOutputMeta.Error.ErrorConnecting", dbe.getMessage()));
					}
					finally
					{
						db.disconnect();
					}
				}
				else
				{
					retval.setError(Messages.getString( "TableOutputMeta.Error.NoTable"));
				}
			}
			else
			{
				retval.setError(Messages.getString( "TableOutputMeta.Error.NoInput"));
			}
		}
		else
		{
			retval.setError(Messages.getString( "TableOutputMeta.Error.NoConnection"));
		}

		return retval;
	}

    public RowMetaInterface getRequiredFields(VariableSpace space) throws KettleException
    {
    	String realTableName = space.environmentSubstitute(tablename);
    	String realSchemaName = space.environmentSubstitute(schemaName);
    	
        if (databaseMeta!=null)
        {
            Database db = new Database(databaseMeta);
            try
            {
                db.connect();
                
                if (!Const.isEmpty(realTableName))
                {
                    String schemaTable = databaseMeta.getQuotedSchemaTableCombination(realSchemaName, realTableName);
                    
                    // Check if this table exists...
                    if (db.checkTableExists(schemaTable))
                    {
                        return db.getTableFields(schemaTable);
                    }
                    else
                    {
                        throw new KettleException(Messages.getString( "TableOutputMeta.Exception.TableNotFound"));
                    }
                }
                else
                {
                    throw new KettleException(Messages.getString( "TableOutputMeta.Exception.TableNotSpecified"));
                }
            }
            catch(Exception e)
            {
                throw new KettleException(Messages.getString( "TableOutputMeta.Exception.ErrorGettingFields"), e);
            }
            finally
            {
                db.disconnect();
            }
        }
        else
        {
            throw new KettleException(Messages.getString( "TableOutputMeta.Exception.ConnectionNotDefined"));
        }

    }
    
    public DatabaseMeta[] getUsedDatabaseConnections()
    {
        if (databaseMeta!=null) 
        {
            return new DatabaseMeta[] { databaseMeta };
        }
        else
        {
            return super.getUsedDatabaseConnections();
        }
    }
    
    /**
     * @return Fields containing the values in the input stream to insert.
     */
    public String[] getFieldStream()
    {
        return fieldStream;
    }
    
    /**
     * @param fieldStream The fields containing the values in the input stream to insert in the table.
     */
    public void setFieldStream(String[] fieldStream)
    {
        this.fieldStream = fieldStream;
    }

    /**
     * @return Fields containing the fieldnames in the database insert.
     */
    public String[] getFieldDatabase()
    {
        return fieldDatabase;
    }
    
    /**
     * @param fieldDatabase The fields containing the names of the fields to insert.
     */
    public void setFieldDatabase(String[] fieldDatabase)
    {
        this.fieldDatabase = fieldDatabase;
    }    

    /**
     * @return the schemaName
     */
    public String getSchemaName()
    {
        return schemaName;
    }

    /**
     * @param schemaName the schemaName to set
     */
    public void setSchemaName(String schemaName)
    {
        this.schemaName = schemaName;
    }
    
    public boolean supportsErrorHandling()
    {
        return true;
    }    
}