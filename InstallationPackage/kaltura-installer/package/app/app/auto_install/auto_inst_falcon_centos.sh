#!/bin/sh -e
. `dirname $0`/kaltura.rc
if [ $# -eq 1 ];then
    INSTALL_DIR=$1
else
    INSTALL_DIR=`pwd`
fi
install_deps
create_mysql_user
setup_pentaho
set_selinux_permissive
set_php_ini
echo "Starting needed daemons.."
for i in httpd memcached crond;do
    /etc/init.d/$i restart
    chkconfig $i on
done
cd $INSTALL_DIR && php install.php -s user_input.ini
sed -i 's@ = @=@g' $INSTALL_DIR/user_input.ini
. $INSTALL_DIR/user_input.ini
# Add the "kaltura" user
create_kalt_user
fix_permissions
configure_apache
set_serial
configure_dwh
configure_red5
for i in sphinx_watch.sh serviceBatchMgr.sh;do
	chkconfig $i on
done
/etc/init.d/serviceBatchMgr.sh restart
create_kaltura_profile
create_partner
upload_assets $TEST_PARTNER_ID
disable_monitoring_tab
check_port_connectivity
