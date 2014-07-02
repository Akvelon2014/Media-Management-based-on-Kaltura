<?php
/**
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* Modified by Akvelon Inc.
* 2014-06-30
* http://www.akvelon.com/contact-us
*/

$service_url = requestUtils::getHost();

$www_host = kConf::get('www_host');
$https_enabled = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? true : false;
if (kConf::get('kmc_secured_login') || $https_enabled) {
	$flash_dir = 'https://';
}
else {
	$flash_dir = 'http://';
}

$flash_dir .= $www_host .'/'. myContentStorage::getFSFlashRootPath ();

?>

<style>
 body { background-color:#272929 !important; background-image:none !important;}
  div#login { width:500px; margin: 0 auto; text-align:center;}
</style>

<div id="kmcHeader">
	<img src="<?php echo $service_url; ?>/lib/images/kmc/logo_kmc.png" alt="Kaltura CMS" />
	<div id="user_links">
    	<a href="<?php echo $service_url; ?>/content/docs/wams/kmc.html" target="_blank">User Manual</a>
	</div> 
</div><!-- end kmcHeader -->
    
<div id="login">
    <div id="login_swf"><img src="/lib/images/kmc/flash.jpg" alt="Install Flash Player" /><span>You must have flash installed. <a href="http://get.adobe.com/flashplayer/" target="_blank">click here to download</a></span></div>
</div>

<script type="text/javascript">
var options = {
	service_url: "<?php echo $service_url ?>",
	kmc_login_version: "<?php echo $kmc_login_version ?>",
	flash_dir: "<?php echo $flash_dir ?>",
	flashVars: {
		host: "<?php echo $www_host; ?>",
		displayErrorFromServer: "<?php echo ($displayErrorFromServer)? 'true': 'false'; ?>",
		visibleSignup: "<?php echo (kConf::get('kmc_login_show_signup_link'))? 'true': 'false'; ?>",
		hashKey: "<?php echo (isset($setPassHashKey) && $setPassHashKey) ? $setPassHashKey : ''; ?>",
		errorCode: "<?php echo (isset($hashKeyErrorCode) && $hashKeyErrorCode) ? $hashKeyErrorCode : ''; ?>"
	}
};
</script>
<script type="text/javascript" src="/lib/js/kmc.login.js"></script>