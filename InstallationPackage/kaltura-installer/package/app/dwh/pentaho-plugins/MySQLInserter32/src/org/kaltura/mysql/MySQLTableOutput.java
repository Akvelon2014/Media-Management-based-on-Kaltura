package org.kaltura.mysql;

import java.sql.BatchUpdateException;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.Hashtable;
import java.util.List;

import org.pentaho.di.core.Const;
import org.pentaho.di.core.database.Database;
import org.pentaho.di.core.database.DatabaseMeta;
import org.pentaho.di.core.exception.KettleDatabaseBatchException;
import org.pentaho.di.core.exception.KettleDatabaseException;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.exception.KettleStepException;
import org.pentaho.di.core.row.RowDataUtil;
import org.pentaho.di.core.row.RowMeta;
import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.core.row.ValueMetaInterface;
import org.pentaho.di.trans.Trans;
import org.pentaho.di.trans.TransMeta;
import org.pentaho.di.trans.step.BaseStep;
import org.pentaho.di.trans.step.StepDataInterface;
import org.pentaho.di.trans.step.StepInterface;
import org.pentaho.di.trans.step.StepMeta;
import org.pentaho.di.trans.step.StepMetaInterface;

public class MySQLTableOutput extends BaseStep implements StepInterface
{
	private MySQLTableOutputMeta meta;
	private MySQLTableOutputData data;

	public MySQLTableOutput(StepMeta stepMeta, StepDataInterface stepDataInterface, int copyNr, TransMeta transMeta, Trans trans)
	{
		super(stepMeta, stepDataInterface, copyNr, transMeta, trans);
	}

	public boolean processRow(StepMetaInterface smi, StepDataInterface sdi) throws KettleException
	{
		meta = (MySQLTableOutputMeta) smi;
		data = (MySQLTableOutputData) sdi;

		ArrayList<Object[]> rows = new ArrayList<Object[]>();
		for (int counter = 0; counter < data.batchSize; counter++)
		{
			Object[] r = getRow();
			; // this also waits for a previous step to be finished.
			if (r != null)
			{
				rows.add(r);
			}
		}
		if (rows.isEmpty())
		{
			return false;
		}

		if (first)
		{
			first = false;
			data.outputRowMeta = getInputRowMeta().clone();
			meta.getFields(data.outputRowMeta, getStepname(), null, null, this);

			data.insertRowMeta = new RowMeta();

			//
			// Cache the position of the compare fields in Row row
			//
			data.valuenrs = new int[meta.getFieldDatabase().length];
			for (int i = 0; i < meta.getFieldDatabase().length; i++)
			{
				data.valuenrs[i] = getInputRowMeta().indexOfValue(meta.getFieldStream()[i]);
				if (data.valuenrs[i] < 0)
				{
					throw new KettleStepException(Messages.getString(
							"TableOutput.Exception.FieldRequired", meta.getFieldStream()[i])); //$NON-NLS-1$
				}
			}

			for (int i = 0; i < meta.getFieldDatabase().length; i++)
			{
				ValueMetaInterface insValue = getInputRowMeta().searchValueMeta(meta.getFieldStream()[i]);
				if (insValue != null)
				{
					ValueMetaInterface insertValue = insValue.clone();
					insertValue.setName(meta.getFieldDatabase()[i]);
					data.insertRowMeta.addValueMeta(insertValue);
				} else
				{
					throw new KettleStepException(Messages.getString(
							"TableOutput.Exception.FailedToFindField", meta.getFieldStream()[i])); //$NON-NLS-1$ 
				}
			}
		}

		try
		{
			ArrayList<Object[]> outputRowData = writeToTable(getInputRowMeta(), rows);
			if (outputRowData != null)
			{
				for (Object[] row : outputRowData)
				{
					putRow(data.outputRowMeta, row); // in case we want it go
														// further...
					incrementLinesOutput();
				}
			}

			if (checkFeedback(getLinesRead()))
			{
				if (log.isBasic())
					logBasic("linenr " + getLinesRead()); //$NON-NLS-1$
			}
		} catch (KettleException e)
		{
			logError("Because of an error, this step can't continue: ", e);
			setErrors(1);
			stopAll();
			setOutputDone(); // signal end to receiver(s)
			return false;
		}

		return true;
	}

	private ArrayList<Object[]> writeToTable(RowMetaInterface rowMeta, ArrayList<Object[]> rows) throws KettleException
	{

		if (rows.isEmpty()) // Stop: last line or error encountered
		{
			if (log.isDetailed())
				logDetailed("Last line inserted: stop");
			return null;
		}

		PreparedStatement insertStatement = null;

		ArrayList<Object[]> insertRowsData = new ArrayList<Object[]>();
		ArrayList<Object[]> outputRowsData = rows;

		String tableName = null;

		boolean sendToErrorRow = false;
		String errorMessage = null;
		boolean rowIsSafe = false;
		int[] updateCounts = null;
		List<Exception> exceptionsList = null;
		boolean batchProblem = false;

		for (Object[] row : rows)
		{
			if (meta.isTableNameInField())
			{
				// Cache the position of the table name field
				if (data.indexOfTableNameField < 0)
				{
					String realTablename = environmentSubstitute(meta.getTableNameField());
					data.indexOfTableNameField = rowMeta.indexOfValue(realTablename);
					if (data.indexOfTableNameField < 0)
					{
						String message = "Unable to find table name field [" + realTablename + "] in input row";
						logError(message);
						throw new KettleStepException(message);
					}
					if (!meta.isTableNameInTable())
					{
						data.insertRowMeta.removeValueMeta(data.indexOfTableNameField);
					}
				}
				tableName = rowMeta.getString(rows.get(0), data.indexOfTableNameField);
				if (!meta.isTableNameInTable())
				{
					// If the name of the table should not be inserted itself,
					// remove the table name
					// from the input row data as well. This forcibly creates a
					// copy of r
					insertRowsData.add(RowDataUtil.removeItem(rowMeta.cloneRow(row), data.indexOfTableNameField));
				} else
				{
					insertRowsData.add(row);
				}
			} else if (meta.isPartitioningEnabled() && (meta.isPartitioningDaily() || meta.isPartitioningMonthly())
					&& (meta.getPartitioningField() != null && meta.getPartitioningField().length() > 0))
			{
				// Initialize some stuff!
				if (data.indexOfPartitioningField < 0)
				{
					data.indexOfPartitioningField = rowMeta.indexOfValue(environmentSubstitute(meta
							.getPartitioningField()));
					if (data.indexOfPartitioningField < 0)
					{
						throw new KettleStepException("Unable to find field [" + meta.getPartitioningField()
								+ "] in the input row!");
					}

					if (meta.isPartitioningDaily())
					{
						data.dateFormater = new SimpleDateFormat("yyyyMMdd");
					} else
					{
						data.dateFormater = new SimpleDateFormat("yyyyMM");
					}
				}

				ValueMetaInterface partitioningValue = rowMeta.getValueMeta(data.indexOfPartitioningField);
				if (!partitioningValue.isDate() || row[data.indexOfPartitioningField] == null)
				{
					throw new KettleStepException(
							"Sorry, the partitioning field needs to contain a data value and can't be empty!");
				}

				Object partitioningValueData = rowMeta.getDate(row, data.indexOfPartitioningField);
				tableName = environmentSubstitute(meta.getTablename()) + "_"
						+ data.dateFormater.format((Date) partitioningValueData);
				insertRowsData.add(row);
			} else
			{
				tableName = data.tableName;
				insertRowsData.add(row);
			}

			if (Const.isEmpty(tableName))
			{
				throw new KettleStepException("The tablename is not defined (empty)");
			}
		}

		if (!data.preparedStatements.containsKey(tableName))
		{
			data.preparedStatements.put(tableName, new Hashtable<Integer, PreparedStatement>());
		}

		insertStatement = (PreparedStatement) data.preparedStatements.get(tableName).get(rows.size());
		if (insertStatement == null)
		{
			String sql = getInsertStatement(environmentSubstitute(meta.getSchemaName()), tableName, data.insertRowMeta,
					rows.size());
			if (log.isDetailed())
				logDetailed("Prepared statement : " + sql);
			insertStatement = data.db.prepareSQL(sql);

			if (!data.preparedStatements.containsKey(tableName))
			{
				data.preparedStatements.put(tableName, new Hashtable<Integer, PreparedStatement>());
			}

			data.preparedStatements.get(tableName).put(rows.size(), insertStatement);
		}

		try
		{
			// For PG & GP, we add a savepoint before the row.
			// Then revert to the savepoint afterwards... (not a transaction, so
			// hopefully still fast)
			//
			if (data.specialErrorHandling)
			{
				data.savepoint = data.db.setSavepoint();
			}

			RowMeta insertRowMeta = new RowMeta();
			for (int i = 0; i < rows.size(); i++)
			{
				for (int j = 0; j < data.valuenrs.length; j++)
				{
					insertRowMeta.addValueMeta(data.insertRowMeta.getValueMeta(j));
				}
			}
			data.db.setValues(insertRowMeta, toArray(insertRowsData), insertStatement);
			data.db.insertRow(insertStatement, data.batchMode, false); // false:
																		// no
																		// commit,
																		// it is
																		// handled
																		// in
																		// this
																		// step
																		// different

			// Get a commit counter per prepared statement to keep track of
			// separate tables, etc.
			//
			Integer commitCounter = data.commitCounterMap.get(tableName);
			if (commitCounter == null)
			{
				commitCounter = Integer.valueOf(1);
			} else
			{
				commitCounter++;
			}
			data.commitCounterMap.put(tableName, Integer.valueOf(commitCounter.intValue()));

			// Release the savepoint if needed
			//
			if (data.specialErrorHandling)
			{
				if (data.releaseSavepoint)
				{
					data.db.releaseSavepoint(data.savepoint);
				}
			}

			// Perform a commit if needed
			//

			if ((data.commitSize > 0) && ((commitCounter % data.commitSize) == 0))
			{
				if (data.batchMode)
				{
					try
					{
						insertStatement.executeBatch();
						data.db.commit();
						insertStatement.clearBatch();
					} catch (BatchUpdateException ex)
					{
						KettleDatabaseBatchException kdbe = new KettleDatabaseBatchException("Error updating batch", ex);
						kdbe.setUpdateCounts(ex.getUpdateCounts());
						List<Exception> exceptions = new ArrayList<Exception>();

						// 'seed' the loop with the root exception
						SQLException nextException = ex;
						do
						{
							exceptions.add(nextException);
							// while current exception has next exception, add
							// to list
						} while ((nextException = nextException.getNextException()) != null);
						kdbe.setExceptionsList(exceptions);
						throw kdbe;
					} catch (SQLException ex)
					{
						throw new KettleDatabaseException("Error inserting row", ex);
					} catch (Exception ex)
					{
						throw new KettleDatabaseException("Unexpected error inserting row", ex);
					}
				} else
				{
					// insertRow normal commit
					data.db.commit();
				}
				// Clear the batch/commit counter...
				//
				data.commitCounterMap.put(tableName, Integer.valueOf(0));
				rowIsSafe = true;
			} else
			{
				rowIsSafe = false;
			}
		} catch (KettleDatabaseBatchException be)
		{
			errorMessage = be.toString();
			batchProblem = true;
			sendToErrorRow = true;
			updateCounts = be.getUpdateCounts();
			exceptionsList = be.getExceptionsList();

			if (getStepMeta().isDoingErrorHandling())
			{
				data.db.clearBatch(insertStatement);
				data.db.commit(true);
			} else
			{
				data.db.clearBatch(insertStatement);
				data.db.rollback();
				StringBuffer msg = new StringBuffer("Error batch inserting rows into table [" + tableName + "].");
				msg.append(Const.CR);
				msg.append("Errors encountered (first 10):").append(Const.CR);
				for (int x = 0; x < be.getExceptionsList().size() && x < 10; x++)
				{
					Exception exception = be.getExceptionsList().get(x);
					if (exception.getMessage() != null)
						msg.append(exception.getMessage()).append(Const.CR);
				}
				throw new KettleException(msg.toString(), be);
			}
		} catch (KettleDatabaseException dbe)
		{
			if (getStepMeta().isDoingErrorHandling())
			{
				if (data.specialErrorHandling)
				{
					data.db.rollback(data.savepoint);
					if (data.releaseSavepoint)
					{
						data.db.releaseSavepoint(data.savepoint);
					}
					// data.db.commit(true); // force a commit on the connection
					// too.
				}

				sendToErrorRow = true;
				errorMessage = dbe.toString();
			} else
			{
				if (meta.ignoreErrors())
				{
					if (data.warnings < 20)
					{
						if (log.isBasic())
							logBasic("WARNING: Couldn't insert row into table." + Const.CR + dbe.getMessage());
					} else if (data.warnings == 20)
					{
						if (log.isBasic())
							logBasic("FINAL WARNING (no more then 20 displayed): Couldn't insert row into table: "
									+ Const.CR + dbe.getMessage());
					}
					data.warnings++;
				} else
				{
					setErrors(getErrors() + 1);
					data.db.rollback();
					throw new KettleException("Error inserting row into table [" + tableName + "]", dbe);
				}
			}
		}

		if (data.batchMode)
		{
			if (sendToErrorRow)
			{
				if (batchProblem)
				{
					for (Object[] row : outputRowsData)
					{
						data.batchBuffer.add(row);
					}
					outputRowsData = null;
					processBatchException(errorMessage, updateCounts, exceptionsList);
				} else
				{
					// Simply add this row to the error row
					for (Object[] row : outputRowsData)
					{
						putError(rowMeta, row, 1L, errorMessage, null, "TOP001");
					}
					outputRowsData = null;
				}
			} else
			{
				for (Object[] row : outputRowsData)
				{
					data.batchBuffer.add(row);
				}
				outputRowsData = null;

				if (rowIsSafe) // A commit was done and the rows are all safe
								// (no error)
				{
					for (int i = 0; i < data.batchBuffer.size(); i++)
					{
						Object[] row = (Object[]) data.batchBuffer.get(i);
						putRow(data.outputRowMeta, row);
						incrementLinesOutput();
					}
					// Clear the buffer
					data.batchBuffer.clear();
				}
			}
		} else
		{
			if (sendToErrorRow)
			{
				for (Object[] row : outputRowsData)
				{
					putError(rowMeta, row, 1L, errorMessage, null, "TOP001");
				}
				outputRowsData = null;
			}
		}

		return outputRowsData;
	}

	private Object[] toArray(ArrayList<Object[]> rows)
	{
		if (rows.isEmpty())
		{
			return null;
		}
		int r = 0;

		Object[] res = new Object[data.valuenrs.length * rows.size()];
		for (Object[] row : rows)
		{
			for (int i = 0; i < data.valuenrs.length; i++)
			{
				res[r++] = row[data.valuenrs[i]];
			}
		}
		return res;
	}

	private void processBatchException(String errorMessage, int[] updateCounts, List<Exception> exceptionsList) throws KettleException
	{
		// There was an error with the commit
		// We should put all the failing rows out there...
		//
		if (updateCounts != null)
		{
			int errNr = 0;
			for (int i = 0; i < updateCounts.length; i++)
			{
				Object[] row = (Object[]) data.batchBuffer.get(i);
				if (updateCounts[i] > 0)
				{
					// send the error foward
					putRow(data.outputRowMeta, row);
					incrementLinesOutput();
				} else
				{
					String exMessage = errorMessage;
					if (errNr < exceptionsList.size())
					{
						SQLException se = (SQLException) exceptionsList.get(errNr);
						errNr++;
						exMessage = se.toString();
					}
					putError(data.outputRowMeta, row, 1L, exMessage, null, "TOP0002");
				}
			}
		} else
		{
			// If we don't have update counts, it probably means the DB doesn't
			// support it.
			// In this case we don't have a choice but to consider all inserted
			// rows to be error rows.
			//
			for (int i = 0; i < data.batchBuffer.size(); i++)
			{
				Object[] row = (Object[]) data.batchBuffer.get(i);
				putError(data.outputRowMeta, row, 1L, errorMessage, null, "TOP0003");
			}
		}

		// Clear the buffer afterwards...
		data.batchBuffer.clear();
	}

	public String getInsertStatement(String schemaName, String tableName, RowMetaInterface fields, int rowcount)
	{
		StringBuffer ins = new StringBuffer(128);
		ins.append("INSERT INTO `");
		if (schemaName != null)
		{
			ins.append(schemaName).append("`.`");
		}
		ins.append(tableName).append("` (");

		// now add the names in the row:
		for (int i = 0; i < meta.getFieldDatabase().length; i++)
		{
			if (i > 0)
				ins.append(", ");
			String name = meta.getFieldDatabase()[i];
			ins.append("`").append(name).append("`");
		}
		ins.append(") VALUES (");

		for (int r = 0; r < rowcount; r++)
		{
			if (r > 0)
				ins.append(", (");

			// Add place holders...
			for (int i = 0; i < meta.getFieldDatabase().length; i++)
			{
				if (i > 0)
					ins.append(", ");
				ins.append(" ?");
			}
			ins.append(')');
		}
		return ins.toString();
	}

	public boolean init(StepMetaInterface smi, StepDataInterface sdi)
	{
		meta = (MySQLTableOutputMeta) smi;
		data = (MySQLTableOutputData) sdi;

		if (super.init(smi, sdi))
		{
			try
			{
				data.databaseMeta = meta.getDatabaseMeta();

				// Batch updates are not supported on PostgreSQL (and
				// look-a-likes) together with error handling (PDI-366)
				//
				data.specialErrorHandling = getStepMeta().isDoingErrorHandling()
						&& (meta.getDatabaseMeta().getDatabaseType() == DatabaseMeta.TYPE_DATABASE_POSTGRES || meta
								.getDatabaseMeta().getDatabaseType() == DatabaseMeta.TYPE_DATABASE_GREENPLUM);

				// get the boolean that indicates whether or not we can release
				// savepoints
				// and set in in data.
				data.releaseSavepoint = data.specialErrorHandling;

				data.commitSize = Integer.parseInt(environmentSubstitute(meta.getCommitSize()));
				data.batchSize = Integer.parseInt(environmentSubstitute(meta.getBatchSize()));
				data.batchMode = data.commitSize > 0 && meta.useBatchUpdate();

				// Batch updates are not supported in case we are running with
				// transactions in the transformation.
				// It is also disabled when we return keys...
				//
				data.specialErrorHandling |= getTransMeta().isUsingUniqueConnections();

				if (data.batchMode && data.specialErrorHandling)
				{
					data.batchMode = false;
					if (log.isBasic())
						logBasic(Messages.getString("TableOutput.Log.BatchModeDisabled"));
				}

				if (meta.getDatabaseMeta() == null)
				{
					throw new KettleException(Messages.getString("TableOutput.Exception.DatabaseNeedsToBeSelected"));
				}
				if (meta.getDatabaseMeta() == null)
				{
					logError(Messages.getString("TableOutput.Init.ConnectionMissing", getStepname()));
					return false;
				}
				data.db = new Database(meta.getDatabaseMeta());
				data.db.shareVariablesWith(this);

				if (getTransMeta().isUsingUniqueConnections())
				{
					synchronized (getTrans())
					{
						data.db.connect(getTrans().getThreadName(), getPartitionID());
					}
				} else
				{
					data.db.connect(getPartitionID());
				}

				if (log.isBasic())
					logBasic("Connected to database [" + meta.getDatabaseMeta() + "] (commit=" + data.commitSize + ")");

				// Postpone commit as long as possible. PDI-2091
				//
				if (data.commitSize == 0)
				{
					data.commitSize = Integer.MAX_VALUE;
				}
				data.db.setCommit(data.commitSize);

				if (!meta.isPartitioningEnabled() && !meta.isTableNameInField())
				{
					data.tableName = environmentSubstitute(meta.getTablename());

					// Only the first one truncates in a non-partitioned step
					// copy
					//
					if (meta.truncateTable()
							&& ((getCopy() == 0 && getUniqueStepNrAcrossSlaves() == 0) || !Const
									.isEmpty(getPartitionID())))
					{
						data.db.truncateTable(environmentSubstitute(meta.getSchemaName()),
								environmentSubstitute(meta.getTablename()));
					}
				}

				return true;
			} catch (KettleException e)
			{
				logError("An error occurred intialising this step: " + e.getMessage());
				stopAll();
				setErrors(1);
			}
		}
		return false;
	}

	public void dispose(StepMetaInterface smi, StepDataInterface sdi)
	{
		meta = (MySQLTableOutputMeta) smi;
		data = (MySQLTableOutputData) sdi;

		if (data.db != null)
		{
			try
			{
				for (String schemaTable : data.preparedStatements.keySet())
				{
					// Get a commit counter per prepared statement to keep track
					// of separate tables, etc.
					//
					Integer batchCounter = data.commitCounterMap.get(schemaTable);
					if (batchCounter == null)
					{
						batchCounter = 0;
					}

					for (PreparedStatement insertStatement : data.preparedStatements.get(schemaTable).values())
					{
						data.db.emptyAndCommit(insertStatement, data.batchMode, batchCounter);
					}
				}
				for (int i = 0; i < data.batchBuffer.size(); i++)
				{
					Object[] row = (Object[]) data.batchBuffer.get(i);
					putRow(data.outputRowMeta, row);
					incrementLinesOutput();
				}
				// Clear the buffer
				data.batchBuffer.clear();
			} catch (KettleDatabaseBatchException be)
			{
				if (getStepMeta().isDoingErrorHandling())
				{
					// Right at the back we are experiencing a batch commit
					// problem...
					// OK, we have the numbers...
					try
					{
						processBatchException(be.toString(), be.getUpdateCounts(), be.getExceptionsList());
					} catch (KettleException e)
					{
						logError("Unexpected error processing batch error", e);
						setErrors(1);
						stopAll();
					}
				} else
				{
					logError("Unexpected batch update error committing the database connection.", be);
					setErrors(1);
					stopAll();
				}
			} catch (Exception dbe)
			{
				logError("Unexpected error committing the database connection.", dbe);
				logError(Const.getStackTracker(dbe));
				setErrors(1);
				stopAll();
			} finally
			{
				setOutputDone();

				if (getErrors() > 0)
				{
					try
					{
						data.db.rollback();
					} catch (KettleDatabaseException e)
					{
						logError("Unexpected error rolling back the database connection.", e);
					}
				}

				data.db.disconnect();
			}
			super.dispose(smi, sdi);
		}
	}

	@Override
	public void run()
	{
		BaseStep.runStepThread(this, meta, data);
	}
}