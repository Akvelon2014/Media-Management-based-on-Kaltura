<?php
/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Copyright 2014 Akvelon Inc.
* http://www.akvelon.com/contact-us
*/

//create first default partner
$url = 'http://localhost/api_v3/index.php?service=partner&action=register';
$params = array(
    'cmsPassword' => $argv[2], 
    'partner:objectType' => 'KalturaPartner', // for crerate partner
	'partner:name' => 'Default',
	'partner:adminName' => 'Default',
	'partner:adminEmail' => $argv[1],
	'partner:description' => 'default partner',
	'partner:wamsAccountName' => $argv[3],
	'partner:wamsAccountKey' => $argv[4],
);
file_get_contents($url, false, stream_context_create(array(
    'http' => array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => http_build_query($params)
    )
)));
?>