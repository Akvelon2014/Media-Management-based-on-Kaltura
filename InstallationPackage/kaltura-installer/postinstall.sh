#!/bin/sh
#---------------------------------------------------------------------------
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.
#
# Copyright 2014 Akvelon Inc.
# http://www.akvelon.com/contact-us
#---------------------------------------------------------------------------

sudo echo '127.0.0.1 '$1 >> /etc/hosts

sudo mkdir /usr/local/var
sudo mkdir /usr/local/var/data
sudo chmod -R 0777 /usr/local/var/data
sudo mkdir $2/dwh/logs
sudo chmod -R 0777 $2/dwh/logs
sudo chkconfig memcached on
sudo mkdir $2/app/cache/response
sudo mkdir $2/app/cache/response/cache_v2
sudo chmod -R 0777 $2/app/cache

sudo echo '/etc/init.d/sphinx_watch.sh start' >> /etc/rc.local
sudo echo $2'/app/scripts/sphinx_watch.sh start' >> /etc/rc.local
sudo echo $2'/app/scripts/serviceBatchMgr.sh restart' >> /etc/rc.local
sudo echo $2'/app/plugins/sphinx_search/scripts/populateFromLog.php' >> /etc/rc.local

sudo /etc/init.d/memcached start
sudo /etc/init.d/sphinx_watch.sh start
sudo $2/app/scripts/sphinx_watch.sh start
sudo $2/app/scripts/serviceBatchMgr.sh restart

sudo service httpd restart
