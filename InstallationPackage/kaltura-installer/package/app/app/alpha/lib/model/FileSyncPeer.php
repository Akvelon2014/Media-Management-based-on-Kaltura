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

/**
 * Subclass for performing query and update operations on the 'file_sync' table.
 *
 * 
 *
 * @package Core
 * @subpackage model
 */ 
class FileSyncPeer extends BaseFileSyncPeer
{
	/* (non-PHPdoc)
	 * @see BaseFileSyncPeer::setDefaultCriteriaFilter()
	 */
	public static function setDefaultCriteriaFilter()
	{
		if(self::$s_criteria_filter == null)
		{
			self::$s_criteria_filter = new criteriaFilter();
		}
		
		$c = new Criteria();
		$c->add(self::STATUS, array(FileSync::FILE_SYNC_STATUS_DELETED, FileSync::FILE_SYNC_STATUS_PURGED), Criteria::NOT_IN);
		self::$s_criteria_filter->setFilter($c);
	}
	
	/**
	 * 
	 * @param FileSyncKey $key
	 * @param Criteria $c
	 * @return Criteria
	 */
	public static function getCriteriaForFileSyncKey ( FileSyncKey $key , Criteria $c = null )
	{
		if ( $c == null ) $c = new Criteria();
		$c->addAnd ( self::OBJECT_ID , $key->object_id );
		$c->addAnd ( self::OBJECT_TYPE , $key->object_type );
		$c->addAnd ( self::OBJECT_SUB_TYPE , $key->object_sub_type );
		$c->addAnd ( self::VERSION , $key->version );
		return $c;
	}

	/**
	 *
	 * @param String $wamsAssetId
	 * @param Criteria $c
	 * @return Criteria
	 */
	public static function getCriteriaForWamsAssetId($wamsAssetId, Criteria $c = null)
	{
		if ($c == null) {
			$c = new Criteria();
		}
		$c->addAnd(self::WAMS_ASSET_ID, $wamsAssetId);
		return $c;
	}


	/**
	 * 
	 * @param FileSyncKey $key
	 * @return FileSync
	 */
	public static function retrieveByFileSyncKey(FileSyncKey $key)
	{
		$c = self::getCriteriaForFileSyncKey($key);
		return self::doSelectOne($c);
	}
	
	/**
	 * @param FileSyncKey $key
	 * @return array
	 */
	public static function retrieveAllByFileSyncKey(FileSyncKey $key)
	{
		$c = self::getCriteriaForFileSyncKey($key);
		return self::doSelect($c);
	}

	/**
	 * @param String $wamsAssetId
	 * @return FileSync
	 */
	public static function retrieveByWamsAssetId($wamsAssetId)
	{
		$c = self::getCriteriaForWamsAssetId($wamsAssetId);
		return self::doSelectOne($c);
	}

	public static function getCacheInvalidationKeys()
	{
		return array(array("fileSync:id=%s", self::ID), array("fileSync:objectId=%s", self::OBJECT_ID));		
	}
}
