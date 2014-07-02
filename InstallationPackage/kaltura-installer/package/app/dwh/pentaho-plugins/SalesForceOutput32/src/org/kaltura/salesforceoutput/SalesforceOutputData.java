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


import java.text.DateFormatSymbols;
import java.text.DecimalFormat;
import java.text.DecimalFormatSymbols;
import java.text.NumberFormat;
import java.text.SimpleDateFormat;

import org.pentaho.di.core.row.RowMetaInterface;
import org.pentaho.di.trans.step.BaseStepData;
import org.pentaho.di.trans.step.StepDataInterface;

import com.sforce.soap.partner.QueryResult;

/*
 * @author Samatar
 * @since 10-06-2007
 */
public class SalesforceOutputData extends BaseStepData implements StepDataInterface 
{
	public int    nr_repeats;
	
	public NumberFormat         nf;
	public DecimalFormat        df;
	public DecimalFormatSymbols dfs;
	public SimpleDateFormat     daf;
	public DateFormatSymbols    dafs;

    public long                rownr;
	public Object[] previousRow;
	public RowMetaInterface inputRowMeta;
	public RowMetaInterface outputRowMeta;
	public RowMetaInterface formatRowMeta;
	public QueryResult qr ;
	public int recordcount;
    public int nrfields;
    public boolean limitReached;
    public long limit;
    public String URL;
    public String Module;
    public String SQL;
    public String Timestamp;
    public int[] fieldnrs;

	/**
	 * 
	 */
	public SalesforceOutputData()
	{
		super();

		nf = NumberFormat.getInstance();
		df = (DecimalFormat)nf;
		dfs=new DecimalFormatSymbols();
		daf = new SimpleDateFormat();
		dafs= new DateFormatSymbols();

		nr_repeats=0;
		qr=null;
		nrfields=0;
		recordcount=0;
		limitReached=false;
		SQL=null;
		Timestamp=null;
		limit=0;
	}
}