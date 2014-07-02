#!/bin/sh - 
#===============================================================================
#          FILE: convert_entries.sh
#         USAGE: ./convert_entries.sh 
#   DESCRIPTION: accepts a CSV file where the last column is entry IDs [this was written for SAP, the CSV format can be easily altered],
#		 and sets the entry source to be in status 2 [so that it can be used to reconvert] as well as the relevant columns in file_sync table.
#		 It then proceeds to call convert_flavor_params.php which performs the actual reconvert.
#       OPTIONS: a path to the aforementioned CSV file.-
#  REQUIREMENTS: ---
#          BUGS: ---
#         NOTES: ---
#        AUTHOR: Jess Portnoy (), jess.portnoy@kaltura.com
#  ORGANIZATION: Kaltura, inc.
#       CREATED: 11/08/12 20:20:06 IST
#      REVISION:  ---
#===============================================================================
#set -o nounset                              # Treat unset variables as an error
#!/bin/sh
if [ $# -ne 1 ];then
    echo "Usage: $0 </path/to/entry/file>"
    exit 1
fi
DB_HOST=''
DB_NAME=
DB_USER=
DB_PASSWD=
dos2unix $1
awk -F "," '{print $NF}' $1 >/tmp/convert_list$$
while read LINE;do
    ENTRY_ID=$LINE
    if [ -n "$ENTRY_ID" ];then
        echo "update flavor_asset set status=2 where entry_id='$ENTRY_ID' and is_original=1;"|mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $DB_NAME
        OBJ_ID=`echo "select id from flavor_asset where is_original=1 and  entry_id='$ENTRY_ID';"|mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD --skip-column-names $DB_NAME`
        echo "update file_sync set status=2 where object_id='$OBJ_ID'"|mysql -h$DB_HOST -u$DB_USER -p$DB_PASSWD $DB_NAME
        php `dirname $0`/convert_flavor_params.php "$ENTRY_ID"
    fi
done < /tmp/convert_list$$
rm  /tmp/convert_list$$
