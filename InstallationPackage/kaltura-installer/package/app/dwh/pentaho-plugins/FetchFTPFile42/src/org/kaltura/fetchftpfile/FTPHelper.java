package org.kaltura.fetchftpfile;

import java.io.IOException;
import java.net.InetAddress;

import org.pentaho.di.core.encryption.Encr;

import com.enterprisedt.net.ftp.FTPClient;
import com.enterprisedt.net.ftp.FTPConnectMode;
import com.enterprisedt.net.ftp.FTPException;
import com.enterprisedt.net.ftp.FTPTransferType;

public class FTPHelper
{
	public static FTPClient connectToFTP(String host, int port, String user, String pw, boolean activeMode, boolean binaryMode, int timeout, String encoding) throws IOException, FTPException
    {
		FTPClient ftpclient;

		 // Create ftp client to host:port ...
        ftpclient = new FTPClient();
        
        ftpclient.setRemoteAddr(InetAddress.getByName(host));
        ftpclient.setRemotePort(port);
        ftpclient.setTimeout(timeout);
        ftpclient.setControlEncoding(encoding);
        ftpclient.setConnectMode(activeMode ? FTPConnectMode.ACTIVE : FTPConnectMode.PASV);
        
        // login to ftp host ...
        ftpclient.connect();     
        
        // login now ...
        String password = Encr.decryptPasswordOptionallyEncrypted(pw); 
        ftpclient.login(user, password);

        ftpclient.setType(binaryMode ? FTPTransferType.BINARY : FTPTransferType.ASCII);
        
        return ftpclient;
    }
	
	public static boolean GetFTPFile(FTPClient ftpClient, String remotePath, String localDir, String localFileName)
	{
		if (ftpClient == null || !ftpClient.connected())
		{
			return false;
		}
		
		try
		{
			ftpClient.get(localDir + "/" + localFileName, remotePath);
		}
		catch (Exception ex)
		{
			if (ex instanceof FTPException)
			{
				// TODO : Handle
			}
			else if (ex instanceof IOException)
			{
				// TODO: Handle
			}
			return false;
		}
		return true;
	}
}
