#!/bin/bash

#---------------------------------------------------------------------------
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
# Modified by Akvelon Inc.
# 2014-06-30
# http://www.akvelon.com/contact-us
#---------------------------------------------------------------------------

if [ -L $0 ];then
	REAL_SCRIPT=`readlink $0`
else
	REAL_SCRIPT=$0
fi
. `dirname $REAL_SCRIPT`/../../../configurations/system.ini

POPULATE_FROM_LOG="populateFromLog.php"
SERVER=$1
if [ -z "$SERVER" ];then
        echo "No Sphinx conf. Exiting."
        exit 1
fi
KP=$(pgrep -f $POPULATE_FROM_LOG)
MAINT=$BASE_DIR/maintenance
if [[ "X$KP" = "X" && ! -f $MAINT ]]
      then
          echo "$POPULATE_FROM_LOG `hostname` was restarted"
	  cd $APP_DIR/plugins/sphinx_search/scripts
	  php $POPULATE_FROM_LOG ${SERVER} >> $LOG_DIR/kaltura_sphinx_populate.log 2>&1 &
      fi

