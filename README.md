Media Management based on Kaltura
=================================
This project provides integration of Kaltura CE with Microsoft Azure Media Services. Integration between Kaltura Server and Microsoft Azure makes it possible to move the storage and encoding of media to the Microsoft Azure platform. For instance, a video or audio file that has been uploaded to KMC, can then be transmitted to Microsoft Azure Media Services and encoded. Kaltura player can also pull media content directly from Microsoft Azure, significantly decreasing, the network and operational load on the Kaltura Server.

# Getting Started
## Prepare a developer machine 
You may use ready to run VM Image from VM Depot on Microsoft Azure. Follow the instructions from [“Uploading an Image from VM Depot”](https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/wiki/Deploying-Kaltura-On-Microsoft-Azure#uploading-an-image-from-vm-depot) and [“Create a VM Instance”](https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/wiki/Deploying-Kaltura-On-Microsoft-Azure#create-a-vm-instance) sections at Installation Manual to set up VM at Microsoft Azure.

Or you may prepare developer environment yourself. Follow the instructions from the “Create Developer Machine” below.

## Download source code
Repository contains the installer of Kaltura CE 6 with integrated Microsoft Azure support. So to get fresh copy of distribution please perform the following steps:

1. Clone this repository to your VM

   ```
   cd ~/
   git clone https://github.com/Akvelon2014/Media-Management-based-on-Kaltura.git
   ```
2. Run installation script

   ```
   cd Media-Management-based-on-Kaltura/InstallationPackage/kaltura-installer
   sudo php install.php
   ```
3. Follow the instructions at [“Installation process”](https://github.com/Akvelon2014/Media-Management-based-on-Kaltura/wiki/Deploying-Kaltura-On-Microsoft-Azure#installation-process) section at Installation Manual 

## Use
Performing above steps you will get ready to go Kaltura server. Please find 
* Admin Console at `http(s)://<your domain>/admin_console`
* Kaltura Management Console (KMC) at `http(s)://<your domain>`

First steps to go:
* Create Media Service at Microsoft Azure Portal.
* Create a publisher for access KMC at Admin Console.
* Input Media Service credentials at publisher configure form (or KMC Account Settings form).

# Contribute 
## Kaltura Server
Kaltura server source files are located at `/opt/kaltura` by default. You can change scripts there for debugging. 

Noteworthy that Kaltura installer generates some files during installation process. So please apply your changes to installer folder `InstallationPackage/kaltura-installer/package/app`. Than try to install server on the clean machine. Check if all your changes are in place. And commit changes after that.

## Kaltura Management Console (KMC)
KMC is implemented on flash. So Kaltura server contains only flash binaries.

You can find KMC source code at KMC folder of the repository. Compile KMC with your changes and put binaries to `web/flash/kmc/v5.23.2` folder of your server.

## Kaltura Dynamic Player (KDP)
KDP is implemented on flash. So Kaltura server contains only flash binaries.

You can find KDP source code at KDP folder of the repository. Compile KDP with your changes and put binaries to `web/flash/kdp3/v3.6.11` folder of your server.

## Kaltura Management Console Login (KMC-Login)
Kaltura Management Console Login is implemented on flash. So Kaltura server contains only flash binaries.

You can find Kaltura Management Console Login source code at KMC-LOGIN folder of the repository. Compile kmc-login with your changes and put binaries to `web/flash/kmc/login/v1.2.2` folder of your server.

# Create Developer Machine
Preform following steps to create machine for development:

1. Install CentOS 6 x64 (“Minimal” variation is enough)
2. Install additional packages: 

   ```
   sudo yum update
   sudo yum install git wget dos2unix php-cli php-mysql php-gd \
   mysql-server memcached httpd mailx ImageMagick \
   php-pecl-apc php-pecl-memcache php-xml cronie java-1.6.0-openjdk 
   sudo yum --enablerepo=centosplus install mod_php
   ```
3. Edit `/etc/selinux/config` and change `SELINUX=enforcing` to `SELINUX=disabled`.
4. Reboot system
5. Setup PHP and MySQL

   ```
   cp /etc/php.ini php.ini.apache.backup
   sed -e "s/^request_order = \"GP\"/request_order = \"CGP\"/g" \
   /etc/php.ini > /tmp/php.ini.configured && \
   sudo cp /tmp/php.ini.configured /etc/php.ini
   ```
   ```
   cp /etc/my.cnf my.cnf.backup
   sed -e "s/^thread_stack\t\t= 192K/thread_stack\t\t= 256K/g" \
   /etc/my.cnf > /tmp/my.cnf.configured && \
   sudo cp /tmp/my.cnf.configured /etc/my.cnf
   ```
   ```
   sed -e "s/^\[mysqld\]/\[mysqld\]\nlower_case_table_names = 1/g" \
   /etc/my.cnf > /tmp/my.cnf.configured && \
   sudo cp /tmp/my.cnf.configured /etc/my.cnf
   ```
6. Insure that `/etc/my.cnf` has this lines

   ```
   lower_case_table_names = 1
   thread_stack = 262144
   open_files_limit = 20000
   ```
7. Uncomment at `/etc/httpd/conf/httpd.conf` line:

   `LoadModule filter_module modules/mod_filter.so`
8. Make Services Start at Boot

   ```
   sudo service mysqld restart
   sudo chkconfig --level 2345 mysqld on
   sudo service mysqld start
   sudo chkconfig --level 2345 httpd on
   sudo service httpd start
   ```
9. Turn off iptables

   ```
   sudo service iptables stop
   sudo chkconfig iptables off
   ```
10. Install Pentaho

   ```
   sudo mkdir /usr/local/pentaho
   cd /usr/local/pentaho
   sudo wget http://sourceforge.net/projects/pentaho/files/Data%20Integration/4.2.1-stable/pdi-ce-4.2.1-stable.tar.gz
   sudo tar xzf pdi-ce-4.2.1-stable.tar.gz
   sudo mv data-integration pdi
   ```
