package org.kaltura.getftpfilenames;

import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.regex.Pattern;

import org.pentaho.di.core.Const;
import org.pentaho.di.core.exception.KettleException;
import org.pentaho.di.core.variables.VariableSpace;

import com.enterprisedt.net.ftp.FTPClient;
import com.enterprisedt.net.ftp.FTPException;
import com.enterprisedt.net.ftp.FTPFile;

public class FTPFileInputList 
{
	private ArrayList<FTPFile> files;
	
	public FTPFileInputList()
	{
		files = new ArrayList<FTPFile>();
	}
	
	public int size() 
	{
		return files.size();
	}

	public static FTPFileInputList createFileList(
			FTPClient ftpClient,
			VariableSpace space,
			String[] fileName,
			String[] fileMask,
			boolean includeSubdirs) throws KettleException 
	{
		FTPFileInputList files = new FTPFileInputList();

        // Replace possible environment variables...
        String realfile[] = space.environmentSubstitute(fileName);
        String realmask[] = space.environmentSubstitute(fileMask);

        for (int i = 0; i < realfile.length; i++)
        {
            String onefile = realfile[i];
            String onemask = realmask[i];

            if (Const.isEmpty(onefile)) 
            {
            	continue;
            }
            
            // 
            // If a wildcard is set we search for files
            //
            if (!Const.isEmpty(onemask))
            {
                try
                {
                   addFiles(files, ftpClient, onemask, onefile, includeSubdirs);                    
                }
                catch (Exception e)
                {
                    throw new KettleException(e);
                }
            }
            else
            // A normal file...
            {
                try               
                {
                    FTPFile file = ftpClient.fileDetails(onefile);
                    if (file!=null && !file.isDir())
                    {
                        files.add(file);
                    }
                }
                catch (Exception e)
                {
                    throw new KettleException(e);
                }
            }
        }

        return files;
	}

	private static void addFiles(FTPFileInputList fileList, FTPClient ftpClient, String pattern, String dir, boolean includeSubdirs) throws IOException, FTPException, ParseException
	{
		 // Find all file names that match the wildcard in this directory
        //
        FTPFile[] files = ftpClient.dirDetails(dir);
        //List<FTPFile> fileObjects = new List<FTPFile>();
        for (FTPFile file : files)
        {
        	file.setPath(dir);
        	if(file.isDir())
        	{
        		if (includeSubdirs)
        		{
        			addFiles(fileList, ftpClient, pattern, file.getPath() + "/" + file.getName(), includeSubdirs);
        		}
        	}
        	else
        	{
        		String name = file.getName();
                boolean matches = Pattern.matches(pattern, name);
                if(matches)
            	{
                	fileList.add(file);
            	}
        	}
        }
    }
	
	private void add(FTPFile file)
	{
		files.add(file);		
	}

	public FTPFile get(int index)
	{
		return files.get(index);
	}
}
