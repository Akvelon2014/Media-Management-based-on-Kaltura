package org.kaltura.getftpfilenames;

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
        String password = Encr.decryptPasswordOptionallyEncrypted(pw);
        // login now ...
        ftpclient.login(user, password);

        ftpclient.setType(binaryMode ? FTPTransferType.BINARY : FTPTransferType.ASCII);
        
        return ftpclient;
    }
}
