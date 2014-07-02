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
 * @package Core
 * @subpackage storage
 */
class kFileSyncUtils implements kObjectChangedEventConsumer
{
	const MAX_CACHED_FILE_SIZE = 2097152;		// 2MB
	const CACHE_KEY_PREFIX = 'fileSyncContent_';
	const FILE_SYNC_CACHE_EXPIRY = 0;			// never expires

	protected static $uncachedObjectTypes = array(
		FileSyncObjectType::ASSET,				// should not cache conversion logs since they can change (batch.logConversion)
	);

	/**
	 * @var array<int order, int storageId>
	 */
	private static $storageProfilesOrder = null;

	public static function file_exists ( FileSyncKey $key , $fetch_from_remote_if_no_local = false )
	{
		KalturaLog::log(__METHOD__." - key [$key], fetch_from_remote_if_no_local [$fetch_from_remote_if_no_local]");
		list ( $file_sync , $local ) = self::getReadyFileSyncForKey( $key , $fetch_from_remote_if_no_local , false  );
		if ( ! $file_sync )
		{
			KalturaLog::log(__METHOD__." - FileSync not found");
			return false;
		}
		else
		{
			$file_sync = self::resolve($file_sync);
		}

		$startTime = microtime(true);

		$file_exists = file_exists ( $file_sync->getFullPath() );

		KalturaLog::log(__METHOD__." - file_exists? [$file_exists] fe took [".(microtime(true)-$startTime)."] path [".$file_sync->getFullPath()."]");

		return $file_exists;
	}

	public static function fileSync_exists ( FileSyncKey $key )
	{
		KalturaLog::log(__METHOD__." - key [$key]");
		list ( $file_sync , $local ) = self::getReadyFileSyncForKey( $key , true , false  );
		if ( ! $file_sync )
		{
			KalturaLog::log(__METHOD__." - FileSync not found");
			return false;
		}
		return true;
	}

	public static function getContentsByFileSync ( FileSync $file_sync , $local = true , $fetch_from_remote_if_no_local = true , $strict = true )
	{
		if ( $local )
		{
			$real_path = realpath( $file_sync->getFullPath() );
			if ( file_exists ( $real_path ) )
			{
				$startTime = microtime(true);
				$contents = file_get_contents( $real_path);
				KalturaLog::log(__METHOD__." - file was found locally at [$real_path] fgc took [".(microtime(true) - $startTime)."]");

				return $contents;
			}
			else
			{
				KalturaLog::log(__METHOD__." - file was not found locally [$real_path]");
				throw new kFileSyncException("Cannot find file on local disk [$real_path] for file sync [" . $file_sync->getId() . "]", kFileSyncException::FILE_DOES_NOT_EXIST_ON_DISK);
			}
		}

		if ( $fetch_from_remote_if_no_local )
		{
			if (!in_array($file_sync->getDc(), kDataCenterMgr::getDcIds()))
			{
				if ( $strict )
				{
					throw new Exception ( "File sync is remote - cannot get contents, id = [" . $file_sync->getId() . "]" );
				}
				else
				{
					return null;
				}
			}
			// if $fetch_from_remote_if_no_local is false - $file_sync shoule be null , this if is in fact redundant
			// TODO - curl to the remote
			$content = kDataCenterMgr::retrieveFileFromRemoteDataCenter( $file_sync );
			return $content;
		}
	}

	public static function file_get_contents ( FileSyncKey $key , $fetch_from_remote_if_no_local = true , $strict = true )
	{
		$cacheStore = kCacheManager::getCache(kCacheManager::MC_GLOBAL_FILESYNC);
		if ($cacheStore)
		{
			$cacheKey = self::CACHE_KEY_PREFIX . "{$key->object_id}_{$key->object_type}_{$key->object_sub_type}_{$key->version}";
			$result = $cacheStore->get($cacheKey);
			if ($result)
			{
				KalturaLog::log("returning from cache, key [$cacheKey] size [".strlen($result)."]");
				return $result;
			}
		}

		KalturaLog::log(__METHOD__." - key [$key], fetch_from_remote_if_no_local [$fetch_from_remote_if_no_local], strict [$strict]");
		list ( $file_sync , $local ) = self::getReadyFileSyncForKey( $key , $fetch_from_remote_if_no_local , $strict );
		if($file_sync)
		{
			$file_sync = self::resolve($file_sync);
		}

		if($file_sync)
		{
			$result = self::getContentsByFileSync ( $file_sync , $local , $fetch_from_remote_if_no_local , $strict );
			if ($cacheStore && $result && strlen($result) < self::MAX_CACHED_FILE_SIZE &&
				!in_array($key->object_type, self::$uncachedObjectTypes))
			{
				KalturaLog::log("saving to cache, key [$cacheKey] size [".strlen($result)."]");
				$cacheStore->set($cacheKey, $result, self::FILE_SYNC_CACHE_EXPIRY);
			}
			return $result;
		}

		KalturaLog::log(__METHOD__." - FileSync not found");
		return null;
	}

	/**
	 *
	 * @param FileSyncKey $key
	 * @param $content
	 * @param $strict - default true. use false if need to override an existing file.
	 */
	public static function file_put_contents ( FileSyncKey $key , $content , $strict = true )
	{
		KalturaLog::log(__METHOD__." - key [$key], strict [$strict]");

		// make sure that there is not yet a record for the key
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$res = FileSyncPeer::doSelectOne( $c );
		if($res)
		{
			if($strict)
				throw new kFileSyncException("key $key already exists", kFileSyncException::FILE_SYNC_ALREADY_EXISTS);

			KalturaLog::err("File Sync key $key already exists");
		}
		else
		{
			KalturaLog::log("File Sync doesn't exist");
		}

		$fullPath = self::getLocalFilePathForKey($key);

		if ( !file_exists( dirname( $fullPath )))
		{
			KalturaLog::log(__METHOD__." - creating directory for file");
			kFile::fullMkdir ( $fullPath );
		}

		// create a file path for the current key - the fileSyncKey should already include the file path
		// place the content there
		file_put_contents ( $fullPath , $content );

		self::createSyncFileForKey( $key , $strict , !is_null($res));
	}

	public static function moveToFile ( FileSyncKey $source_key , $target_file_path, $delete_source = true , $overwrite = true)
	{
		try
		{
			list ($fileSync, $local) = self::getReadyFileSyncForKey ( $source_key, false , true );
		}
		catch(Exception $ex)
		{
			KalturaLog::log('could not load ready file sync for key '.$source_key);
			return false;
		}
		$file_path = $fileSync->getFullPath();

		if(file_exists($target_file_path))
		{
			KalturaLog::log(__METHOD__." - $target_file_path file exists");
			if(!$overwrite)
			{
				KalturaLog::log("target [$target_file_path] exists, not overwriting");
				return false;
			}
			elseif($target_file_path != $fileSync->getFullPath())
			{
				@unlink($target_file_path);
			}
			else
			{
				// target and source are the same, we do not want to delete the existing target
				// to avoid cases where copy fails and current file is lost
			}
		}
		else
		{
			KalturaLog::log(__METHOD__." - $target_file_path file doesnt exist");
		}

		// make sure folder exists
		kFile::fullMkdir($target_file_path);

		$copyResult = copy($file_path, $target_file_path);
		if($copyResult)
		{
			// if root in original fileSync also exists in new path (common root)
			// remove it from the new path
			if(substr_count($target_file_path, $fileSync->getFileRoot()))
			{
				$target_file_path = str_replace($fileSync->getFileRoot(), '', $target_file_path);
			}
			else
			{
				// old & new paths doesn't share root, set new root to empty string
				$fileSync->setFileRoot('');
			}
			// new path will be set either to full path or relative after common root
			$fileSync->setFilePath($target_file_path);
			$fileSync->save();
			// delete source
			if($delete_source)
			{
				// delete the source file, if fails - do not output error
				@unlink($file_path);
			}
			KalturaLog::log("successfully copied file to [$target_file_path] and updated fileSync");
			return true;
		}
		else
		{
			KalturaLog::log("copy failed - not changing filesync");
			return false;
		}
	}

	private static function checkDeletedEntry ( FileSyncKey $target_key, $wams_asset_id = null, $file_path = null ) {
		Propel::disableInstancePooling();
		$entry = self::getEntryForKey($target_key);
		Propel::enableInstancePooling();
		if ((!is_null($entry)) && ($entry->getStatus() != entryStatus::DELETED)) {
			return false;
		}

		if (!empty($wams_asset_id)) {
			kWAMS::getInstance($target_key->getPartnerId())->deleteAssetById($wams_asset_id);
		}

		if (!empty($file_path)) {
			unlink($file_path);
		}

		return true;
	}

	public static function moveFromFile ( $temp_file_path , FileSyncKey $target_key , $strict = true, $copyOnly = false, $cacheOnly = false)
	{
		KalturaLog::log(__METHOD__." - move file: [$temp_file_path] to key [$target_key], ");

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $target_key );

		if($cacheOnly)
			$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_CACHE);
		else
			$c->add(FileSyncPeer::FILE_TYPE, array(FileSync::FILE_SYNC_FILE_TYPE_FILE, FileSync::FILE_SYNC_FILE_TYPE_LINK), Criteria::IN);

		$existsFileSync = FileSyncPeer::doSelectOne( $c );
		if($existsFileSync)
		{
			KalturaLog::log(__METHOD__." - file already exists");
			if($strict)
				throw new Exception ( "key [" . $target_key . "] already exists");
		}

		$targetFullPath = self::getLocalFilePathForKey($target_key);
		if(!$targetFullPath)
		{
			$targetFullPath = kPathManager::getFilePath($target_key);
			KalturaLog::log(__METHOD__." - Generated new path [$targetFullPath]");
		}

		if ( !file_exists( dirname( $targetFullPath )))
		{
			KalturaLog::log(__METHOD__." - creating directory for file");
			kFile::fullMkdir ( $targetFullPath );
		}

		if ( file_exists( $temp_file_path ))
		{
			KalturaLog::log(__METHOD__." - $temp_file_path file exists");
		}
		else
		{
			KalturaLog::log(__METHOD__." - $temp_file_path file doesnt exist");
		}

		$wamsSupportedFormats = kWAMS::getSupportedFormats();
		$fileExtension = pathinfo($temp_file_path, PATHINFO_EXTENSION);
		if (in_array($fileExtension, $wamsSupportedFormats)) {
			$assetName = self::getWamsAssetNameForKey($target_key);
			if (empty($assetName)) {
				$assetName = pathinfo($temp_file_path, PATHINFO_BASENAME);
			}

			$wamsAssetId = kWAMS::getInstance($target_key->getPartnerId())->publishFileToWAMS($assetName, $temp_file_path);
			if (!empty($wamsAssetId)) {

				if (self::checkDeletedEntry($target_key, $wamsAssetId, $temp_file_path)) {
					return;
				}

				self::addFromWAMS($wamsAssetId, $target_key, $strict, $cacheOnly);

				if (!self::isAttachment($target_key)) {
					$wamsTempFilePath = kWAMS::getTempFilePathForAssetId($wamsAssetId, pathinfo($targetFullPath, PATHINFO_EXTENSION));
					rename($temp_file_path, $wamsTempFilePath);
				}
				else {
					unlink($temp_file_path);
				}
				return;
			}
			else {
				KalturaLog::err("File [$temp_file_path] not published to WAMS");
			}
		}

		if($copyOnly)
		{
			$success = copy($temp_file_path, $targetFullPath);
		}
		else
		{
			$success = kFile::moveFile($temp_file_path, $targetFullPath);
		}

		if($success)
		{
			if(!$existsFileSync) {
				self::createSyncFileForKey($target_key, $strict, false, $cacheOnly);
			}
		}
		else
		{
			KalturaLog::log(__METHOD__." - could not move file from [$temp_file_path] to [{$targetFullPath}]");
			throw new Exception ( "Could not move file from [$temp_file_path] to [{$targetFullPath}]");
		}
	}

	/**
	 * Add filesync from WAMS file
	 * @param string $wamsAssetId
	 * @param FileSyncKey $target_key
	 * @param bool $strict
	 * @param bool $cacheOnly
	 * @throws Exception If target key already exists
	 */
	public static function addFromWAMS ( $wamsAssetId , FileSyncKey $target_key , $strict = true, $cacheOnly = false)
	{
		if (self::checkDeletedEntry($target_key, $wamsAssetId)) {
			return;
		}
		KalturaLog::log(__METHOD__." - assetId: [$wamsAssetId] to key [$target_key], ");

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $target_key );

		if($cacheOnly)
			$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_CACHE);
		else
			$c->add(FileSyncPeer::FILE_TYPE, array(FileSync::FILE_SYNC_FILE_TYPE_FILE, FileSync::FILE_SYNC_FILE_TYPE_LINK), Criteria::IN);

		$existsFileSync = FileSyncPeer::doSelectOne( $c );
		if($existsFileSync)
		{
			KalturaLog::log(__METHOD__." - file already exists");
			if($strict)
				throw new Exception ( "key [" . $target_key . "] already exists");
		}

		if(!$existsFileSync) {
			$syncFile = self::createSyncFileForKey($target_key, $strict, false, $cacheOnly, true);
		}
		else {
			$syncFile = $existsFileSync;
		}

		$kWAMS = kWAMS::getInstance($target_key->getPartnerId());
		$fileSize = $kWAMS->getFileSizeForAssetId($wamsAssetId, $syncFile->getFileExt());
		$wamsURL = $kWAMS->getUrlForAssetId($wamsAssetId, $syncFile->getFileExt());
		$syncFile->setFileSize($fileSize);
		$syncFile->setWamsAssetId($wamsAssetId);
		$syncFile->setWamsUrl($wamsURL);
		$syncFile->save();

		// publishing may take a lot of time. need to double check
		self::checkDeletedEntry($target_key, $wamsAssetId);
	}

	public static function copyFromSyncKey(FileSyncKey $source_key, FileSyncKey $target_key, $strict = true)
	{
		KalturaLog::log(__METHOD__." - copy to url: source_key [$source_key], target_key [$target_key]");

		// check if source exists
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $source_key );
		$srcRes = FileSyncPeer::doSelectOne( $c );
		if ( !$srcRes )
		{
			KalturaLog::log(__METHOD__." - file does not exists");
			throw new Exception ( "key [" . $source_key . "] does not exists");
		}

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $target_key );
		$destRes = FileSyncPeer::doSelectOne( $c );
		if ( $destRes && $strict )
		{
			KalturaLog::log(__METHOD__." - url already exists");
			throw new Exception ( "key [" . $target_key . "] already exists");
		}

		self::createSyncFileForKey( $target_key , $strict , $destRes != null );
	}

	public static function copyFromFile ($temp_file_path , FileSyncKey $target_key , $strict = true)
	{
		KalturaLog::log(__METHOD__." - copy file: [$temp_file_path] to key [$target_key], ");
		kFileSyncUtils::moveFromFile($temp_file_path, $target_key, $strict, true);
	}


	/**
	 *
	 * @param FileSyncKey $source_key
	 * @param FileSyncKey $target_key
	 * @param boolean $fetch_from_remote_if_no_local
	 * @param boolean $strict  - will throw exception if not found
	 */
	public static function hardCopy ( FileSyncKey $source_key , FileSyncKey $target_key , $fetch_from_remote_if_no_local = true , $strict = true  )
	{
		// TODO - this implementation is the NAIVE one and can cause problems with big files
		// a better implementation will be to copy the files on disk incase of local files
		// BETTER - use the link feature (not yet implemented)
		$content = self::file_get_contents( $source_key , $fetch_from_remote_if_no_local , $strict );
		self::file_put_contents( $target_key , $content , $strict );
	}

	/**
	 * resolve the source filesync when a FileSync input is a LINK
	 *
	 * @param FileSync $file
	 * @return FileSync
	 */
	public static function resolve(FileSync $file)
	{
		$parent = null;
		if($file->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_LINK)
		{
			$source_file_sync = FileSyncPeer::retrieveByPK($file->getLinkedId());
			if(!$source_file_sync)
				return $file;

			$parent = self::resolve($source_file_sync);
		}
		if(!$parent)
		{
			return $file;
		}
		else
		{
			return $parent;
		}
	}

	/**
	 *
	 * @param FileSyncKey $source_key
	 * @param FileSyncKey $target_key
	 */
	public static function softCopy ( FileSyncKey $source_key , FileSyncKey $target_key )
	{
		# create new rows in table - type FILE_SYNC_FILE_TYPE_LINK , links to existing objects in table
		# each row links to the source in the same DC
		self::createSyncFileLinkForKey($target_key, $source_key);
	}

	/**
	 * Get the FileSyncKey object by its file sync object
	 *
	 * @param FileSync $fileSync
	 * @return FileSyncKey
	 */
	public static function getKeyForFileSync(FileSync $fileSync)
	{
		$key = new FileSyncKey();

		$key->object_type = $fileSync->getObjectType();
		$key->object_id = $fileSync->getObjectId();
		$key->version = $fileSync->getVersion();
		$key->object_sub_type = $fileSync->getObjectSubType();
		$key->partner_id = $fileSync->getPartnerId();

		return $key;
	}

	/**
	 * Get the local FileSync object by its key
	 *
	 * @param FileSyncKey $key
	 * @param boolean $strict  - will throw exception if not found
	 * @return FileSync
	 */
	public static function getLocalFileSyncForKey ( FileSyncKey $key , $strict = true )
	{
		$dc = kDataCenterMgr::getCurrentDc();
		$dc_id = $dc["id"];
		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->addAnd ( FileSyncPeer::DC , $dc_id );

		$file_sync_list = FileSyncPeer::doSelect( $c );
		if ( $file_sync_list == null )
		{
			if ( $strict )
				throw new Exception ( "Cannot find ANY FileSync for " . ( $key ) );
			else
				return false;
		}
		if ( count($file_sync_list) > 1 )
		{
			// something bad happened! on one DC, FileSyncKey should be unique
		}
		return $file_sync_list[0];
	}

	/**
	 * Get all the external FileSync objects by its key
	 *
	 * @param FileSyncKey $key
	 * @return array<FileSync>
	 */
	public static function getAllReadyExternalFileSyncsForKey(FileSyncKey $key)
	{
		if(is_null($key->partner_id))
			throw new kFileSyncException("partner id not defined for key [$key]", kFileSyncException::FILE_SYNC_PARTNER_ID_NOT_DEFINED);

		self::prepareStorageProfilesForSort($key->partner_id);

		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::DC, self::$storageProfilesOrder, Criteria::IN);

		$fileSyncs = FileSyncPeer::doSelect($c);
		if(
			count($fileSyncs) > 1
			&&
			PermissionPeer::isValidForPartner(PermissionName::FEATURE_REMOTE_STORAGE_DELIVERY_PRIORITY, $key->partner_id)
		)
		{
			uasort($fileSyncs, array('self', 'compareStorageProfiles'));
		}
		return $fileSyncs;
	}

	/**
	 * Get the READY external FileSync object by its key
	 *
	 * @param FileSyncKey $key
	 * @param int $externalStorageId
	 * @return FileSync
	 */
	public static function getReadyExternalFileSyncForKey(FileSyncKey $key, $externalStorageId = null)
	{
		return self::getExternalFileSyncForKeyByStatus($key, $externalStorageId, array(FileSync::FILE_SYNC_STATUS_READY));
	}

	/**
	 * Get the READY/PENDING external FileSync object by its key
	 *
	 * @param FileSyncKey $key
	 * @param int $externalStorageId
	 * @return FileSync
	 */
	public static function getReadyPendingExternalFileSyncForKey(FileSyncKey $key, $externalStorageId = null)
	{
		return self::getExternalFileSyncForKeyByStatus($key, $externalStorageId, array(FileSync::FILE_SYNC_STATUS_READY, FileSync::FILE_SYNC_STATUS_PENDING));
	}


	/**
	 * Get the external FileSync object by its key and statuses
	 *
	 * @param FileSyncKey $key
	 * @param int $externalStorageId
	 * @param array $statuses an array of required status values
	 * @return FileSync
	 */
	protected static function getExternalFileSyncForKeyByStatus(FileSyncKey $key, $externalStorageId = null, $statuses = array())
	{
		if(is_null($key->partner_id))
			throw new kFileSyncException("partner id not defined for key [$key]", kFileSyncException::FILE_SYNC_PARTNER_ID_NOT_DEFINED);

		self::prepareStorageProfilesForSort($key->partner_id);

		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );

		if(is_null($externalStorageId))
		{
			$c->addAnd ( FileSyncPeer::FILE_TYPE , FileSync::FILE_SYNC_FILE_TYPE_URL ); // any external
			$c->addAnd ( FileSyncPeer::DC , self::$storageProfilesOrder, Criteria::IN );
		}
		else
		{
			$c->addAnd ( FileSyncPeer::DC , $externalStorageId );
		}

		if (!empty($statuses)) {
			$c->addAnd ( FileSyncPeer::STATUS , $statuses, Criteria::IN );
		}


		if(!PermissionPeer::isValidForPartner(PermissionName::FEATURE_REMOTE_STORAGE_DELIVERY_PRIORITY, $key->partner_id))
			return FileSyncPeer::doSelectOne($c);

		$fileSyncs = FileSyncPeer::doSelect($c);
		if(count($fileSyncs) > 1)
			uasort($fileSyncs, array('self', 'compareStorageProfiles'));

		return reset($fileSyncs);
	}

	/**
	 * @param FileSync $fileSyncA
	 * @param FileSync $fileSyncB
	 * @return number
	 */
	public static function compareStorageProfiles($fileSyncA, $fileSyncB)
	{
		if(!is_array(self::$storageProfilesOrder) || !count(self::$storageProfilesOrder))
			return 0;

		$a = array_search($fileSyncA->getDc(), self::$storageProfilesOrder);
		$b = array_search($fileSyncB->getDc(), self::$storageProfilesOrder);

		if ($a == $b)
			return 0;

		return ($a < $b) ? -1 : 1;
	}

	/**
	 * Prepare storage profiles array for sorting
	 *
	 * @param int $partnerId
	 */
	protected static function prepareStorageProfilesForSort($partnerId)
	{
		if(!is_null(self::$storageProfilesOrder))
		{
			return;
		}

		$criteria = new Criteria();
		$criteria->add(StorageProfilePeer::PARTNER_ID, $partnerId);
		$criteria->add(StorageProfilePeer::DELIVERY_STATUS, StorageProfileDeliveryStatus::BLOCKED, Criteria::NOT_EQUAL);
		$criteria->addAscendingOrderByColumn(StorageProfilePeer::DELIVERY_PRIORITY);

		// Using doSelect instead of doSelectStmt for the ID column so that we can take adavntage of the query cache
		self::$storageProfilesOrder = array();
		$results = StorageProfilePeer::doSelect($criteria);
		foreach ($results as $result)
		{
			self::$storageProfilesOrder[] = $result->getId();
		}
	}

	/**
	 * Get the internal from kaltura data centers only FileSync object by its key
	 *
	 * @param FileSyncKey $key
	 * @return FileSync
	 */
	public static function getReadyInternalFileSyncForKey(FileSyncKey $key)
	{
		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->addAnd ( FileSyncPeer::FILE_TYPE , FileSync::FILE_SYNC_FILE_TYPE_URL, Criteria::NOT_EQUAL);
		$c->addAnd ( FileSyncPeer::STATUS , FileSync::FILE_SYNC_STATUS_READY );

		return FileSyncPeer::doSelectOne( $c );
	}


	/**
	 * Create a path on disk for the LOCAL FileSync that is coupled with the key.
	 * Returns the NON-SAVED FileSync populated with the fileRoot and filePath
	 *
	 * @param FileSyncKey $key
	 * @param boolean $strict  - will throw exception if not found
	 * @return FileSync
	 */
	public static function createLocalPathForKey ( FileSyncKey $key , $strict = true )
	{
		$file_sync = self::getLocalFileSyncForKey ( $key , $strict );
		if ( $file_sync )
		{
			list($file_root, $real_path) = kPathManager::getFilePathArr($key);
			$file_sync->setFileRoot ( $file_root );
			$file_sync->setFilePath ( $real_path );
		}
		else
		{
			$error = __METHOD__. " Cannot find object type [" . $key->getObjectType() . "] with object_id [" . $key->getObjectId() . "] for FileSync id [" . $key->getId() . "]";
			KalturaLog::log($error);
			throw new Exception ( $error );
		}

		return $file_sync;
	}

	/**
	 *
	 * @param FileSyncKey $key
	 * @param boolean $strict  - will throw exception if not found
	 * @return FileSync
	 */
	public static function getOriginFileSyncForKey ( FileSyncKey $key , $strict = true )
	{
		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->addAnd ( FileSyncPeer::ORIGINAL , 1 );

		$file_sync_list = FileSyncPeer::doSelect( $c );
		if ( $file_sync_list == null )
		{
			if ( $strict )
				throw new Exception ( "Cannot find ANY FileSync for " . ( $key ) );
			else
				return false;
		}
		if ( count($file_sync_list) > 1 )
		{
			// something bad happened! on one DC, FileSyncKey should be unique
		}

		return $file_sync_list[0];
	}

	/**
	 *
	 * @param FileSyncKey $key
	 * @param boolean $fetch_from_remote_if_no_local
	 * @param boolean $strict  - will throw exception if not found
	 * @return array
	 */
	public static function getReadyFileSyncForKey ( FileSyncKey $key , $fetch_from_remote_if_no_local = false , $strict = true )
	{
		KalturaLog::log(__METHOD__." - key [$key], fetch_from_remote_if_no_local [$fetch_from_remote_if_no_local], strict [$strict]");
		$dc = kDataCenterMgr::getCurrentDc();
		$dc_id = $dc["id"];
		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		if ( ! $fetch_from_remote_if_no_local )
		{
			// if $fetch_from_remote_if_no_local is true - don't restrict to the current DC - this will save an extra hit to the DB in case the file is not present
			$c->addAnd ( FileSyncPeer::DC , $dc_id );
		}
		// saerch only for ready
		$c->addAnd ( FileSyncPeer::STATUS , FileSync::FILE_SYNC_STATUS_READY );
		$c->addAscendingOrderByColumn(FileSyncPeer::DC); // favor local data centers instead of remote storage locations

		$file_sync_list = FileSyncPeer::doSelect( $c );
		if ( $file_sync_list == null )
		{
			KalturaLog::log(__METHOD__." - FileSync was not found");
			if ( $strict )
				throw new Exception ( "Cannot find ANY FileSync for " . ( $key ) );
			else
				return array ( null , false );
		}

		$desired_file_sync = null;
		$local = false;
		foreach ( $file_sync_list as $file_sync )
		{
			$tmp_file_sync = $file_sync;
			// make sure not link and work on original
			if($file_sync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_LINK)
			{
				$tmp_file_sync = self::resolve($file_sync);
				if ($tmp_file_sync->getStatus() != FileSync::FILE_SYNC_STATUS_READY)
					continue;
			}

			// always prefer the current dc
			if ( $tmp_file_sync->getDc() == $dc_id)
			{
				$desired_file_sync = $tmp_file_sync;
				$local = true;
				break;
			}
			else if ( $fetch_from_remote_if_no_local == true &&
				($desired_file_sync == null || $tmp_file_sync->getDc() < $desired_file_sync->getDc()) )			// prefer local file syncs if they exist
			{
				$desired_file_sync = $tmp_file_sync;
			}
		}

		if ( $desired_file_sync )
		{
			if ($local)
				KalturaLog::log(__METHOD__." - FileSync was found locally");
			else
				KalturaLog::log(__METHOD__." - FileSync was found but doesn't exists locally");

			return array ( $desired_file_sync , $local );
		}

		KalturaLog::log(__METHOD__." - exact FileSync was not found");

		if ( $strict )
			throw new Exception ( "Cannot find EXACT FileSync for " . ( $key ) );
		else
			return array ( null , false );
	}

	/**
	 *
	 * @param FileSyncKey $key
	 * @return string
	 */
	public static function getLocalFilePathForKey ( FileSyncKey $key , $strict = false )
	{
		KalturaLog::log(__METHOD__." - key [$key], strict [$strict]");
		$file_sync = self::getLocalFileSyncForKey( $key , $strict );
		if ( $file_sync )
		{
			$parent_file_sync = self::resolve($file_sync);
			$path = $parent_file_sync->getFileRoot() . $parent_file_sync->getFilePath();
			KalturaLog::log(__METHOD__." - path [$path]");
			return $path;
		}

		// TODO - should return null if doesn't exists
		return kPathManager::getFilePath($key);
	}

	/**
	 *
	 * @param FileSyncKey $key
	 * @return string
	 */
	public static function getRelativeFilePathForKey ( FileSyncKey $key , $strict = false )
	{
		KalturaLog::log(__METHOD__." - key [$key], strict [$strict]");
		$file_sync = self::getLocalFileSyncForKey( $key , $strict );
		if ( $file_sync )
		{
			$parent_file_sync = self::resolve($file_sync);
			$path = $parent_file_sync->getFilePath();
			KalturaLog::log(__METHOD__." - path [$path]");
			return $path;
		}
	}

	public static function getReadyLocalFilePathForKey( FileSyncKey $key , $strict = false )
	{
		KalturaLog::log(__METHOD__." - key [$key], strict [$strict]");
		list ( $file_sync , $local )= self::getReadyFileSyncForKey( $key , false , $strict );
		if ( $file_sync )
		{
			$parent_file_sync = self::resolve($file_sync);
			$path = $parent_file_sync->getFileRoot() . $parent_file_sync->getFilePath();
			KalturaLog::log(__METHOD__." - path [$path]");
			return $path;
		}
	}

	/**
	 * Getting WAMS Asset Id for File Sync Key
	 * @param FileSyncKey $key
	 * @param bool $strict
	 * @return string
	 */
	public static function getWamsAssetIdForKey(FileSyncKey $key, $strict = false)
	{
		KalturaLog::log(__METHOD__ . " - key [$key], strict [$strict]");
		$file_sync = self::getLocalFileSyncForKey( $key , $strict );
		if ( $file_sync )
		{
			$parent_file_sync = self::resolve($file_sync);
			$wamsAssetId = $parent_file_sync->getWamsAssetId();
			KalturaLog::log(__METHOD__." - wamsAssetId [$wamsAssetId]");
			return $wamsAssetId;
		}

		return NULL;
	}

	/**
	 * Check for attachment
	 * @param FileSyncKey $key
	 * @return bool
	 */
	public static function isAttachment( FileSyncKey $key ) {
		$asset = self::retrieveObjectForSyncKey($key);
		if (!($asset instanceof asset)) {
			return false;
		}
		$attachmentName = $asset->getAttachmentName();
		return !empty($attachmentName);
	}

	/**
	 * Getting WAMS Asset Name for File Sync Key
	 * @param FileSyncKey $key
	 * @return string
	 */
	public static function getWamsAssetNameForKey ( FileSyncKey $key ) {
		$asset = self::retrieveObjectForSyncKey($key);
		if (!($asset instanceof asset)) {
			return null;
		}
		$entry = $asset->getentry();
		if (!($entry instanceof entry)) {
			return null;
		}

		$replacedId = $entry->getReplacedEntryId();
		if (!empty($replacedId)) {
			$entry = entryPeer::retrieveByPK($replacedId);
		}

		$entryName = $entry->getName();
		$attachmentName = $asset->getAttachmentName();
		$flavorParamsName = $asset->getFlavorParamsName();

		if (!empty($attachmentName)) {
			return $entryName . ' - related file - ' . $attachmentName;
		}
		elseif (!empty($flavorParamsName)) {
			return $entryName . ' - ' . $flavorParamsName;
		} else {
			return $entryName;
		}
	}

	/**
	 * Getting WAMS Asset URL for File Sync Key
	 * @param FileSyncKey $key
	 * @param bool $strict
	 * @return string
	 */
	public static function getWamsURLForKey(FileSyncKey $key, $strict = false)
	{
		KalturaLog::log(__METHOD__ . " - key [$key], strict [$strict]");
		$file_sync = self::getLocalFileSyncForKey($key, $strict);
		if ($file_sync) {
			$parent_file_sync = self::resolve($file_sync);
			$wamsURL = $parent_file_sync->getWamsUrl();
			KalturaLog::log(__METHOD__ . " -  wamsURL [$wamsURL]");
			return $wamsURL;
		}

		return null;
	}

	/**
	 * Getting Entry for File Sync Key
	 * @param FileSyncKey $key
	 * @return entry
	 */
	public static function getEntryForKey ( FileSyncKey $key ) {
		$flavorAsset = self::retrieveObjectForSyncKey($key);
		$entry = $flavorAsset->getentry();
		if ($entry instanceof entry) {
			return $entry;
		}

		return null;
	}

	/**
	 * Clear WAMS fields and delete asset
	 * @param FileSyncKey $key
	 * @param bool $strict
	 */
	public static function clearWAMSDataForKey ( FileSyncKey $key, $strict = false ) {
		$file_sync = self::getLocalFileSyncForKey($key, $strict);
		if ($file_sync) {
			$parent_file_sync = self::resolve($file_sync);
			$wamsAssetId = $parent_file_sync->getWamsAssetId();
			if (!empty($wamsAssetId)) {
				kWAMS::getInstance($parent_file_sync->getPartnerId())->deleteAssetById($wamsAssetId);
			}
			$parent_file_sync->setWamsAssetId(null);
			$parent_file_sync->setWamsUrl(null);
			$parent_file_sync->save();
		}
	}

	/**
	 * @param FileSyncKey $key
	 * @param $file_root
	 * @param $real_path
	 * @param $strict
	 * @return SyncFile
	 */
	public static function createSyncFileForKey ( FileSyncKey $key , $strict = true , $already_exists = false, $cacheOnly = false, $wamsFile = false)
	{
		KalturaLog::log(__METHOD__." - key [$key], strict[$strict], already_exists[$already_exists]");
		// TODO - see that if in strict mode - there are no duplicate keys -> update existing records AND set the other DC's records to PENDING
		$dc = kDataCenterMgr::getCurrentDc();
		$dc_id = $dc["id"];

		// create a FileSync for the current DC with status READY
		if ( $already_exists )
		{
			$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
			$c->add (FileSyncPeer::DC, $dc_id);
			if($cacheOnly)
				$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_CACHE);

			$current_dc_file_sync = FileSyncPeer::doSelectOne( $c );
		}
		else
		{
			list($file_root, $real_path) = kPathManager::getFilePathArr($key);

			$current_dc_file_sync = FileSync::createForFileSyncKey( $key );
			$current_dc_file_sync->setDc( $dc_id );
			$current_dc_file_sync->setFileRoot ( $file_root );
			$current_dc_file_sync->setFilePath ( $real_path );
			$current_dc_file_sync->setPartnerId ( $key->partner_id);
			$current_dc_file_sync->setOriginal ( 1 );
		}

		$full_path = $current_dc_file_sync->getFullPath();
		if ($wamsFile) {
			$current_dc_file_sync->setFileSize( -1 );
			$current_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_READY );
		}
		elseif ( file_exists( $full_path ) )
		{
			$current_dc_file_sync->setFileSizeFromPath ( $full_path );
			$current_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_READY );
		}
		else
		{
			$current_dc_file_sync->setFileSize ( -1 );

			if ($strict)
				$current_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_ERROR );
			else
				$current_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_PENDING );
		}
		if($cacheOnly)
			$current_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_CACHE );
		else
			$current_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_FILE );

		$current_dc_file_sync->save();

		if($cacheOnly)
			return $current_dc_file_sync;

		// create records for all other DCs with status PENDING
		if ( $already_exists )
		{
			$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
			$c->add ( FileSyncPeer::DC , $dc_id , Criteria::NOT_IN );
			$remote_dc_file_sync_list  = FileSyncPeer::doSelect( $c );

			foreach ( $remote_dc_file_sync_list as $remote_dc_file_sync )
			{
				$remote_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_PENDING );
				$remote_dc_file_sync->setPartnerID ( $key->partner_id );
				$remote_dc_file_sync->save();
			}
		}
		else
		{
			$other_dcs = kDataCenterMgr::getAllDcs( );
			foreach ( $other_dcs as $remote_dc )
			{
				$remote_dc_file_sync = FileSync::createForFileSyncKey( $key );
				$remote_dc_file_sync->setDc( $remote_dc["id"] );
				$remote_dc_file_sync->setStatus( FileSync::FILE_SYNC_STATUS_PENDING );
				$remote_dc_file_sync->setFileType( FileSync::FILE_SYNC_FILE_TYPE_FILE );
				$remote_dc_file_sync->setOriginal ( 0 );
				$remote_dc_file_sync->setPartnerID ( $key->partner_id );
				$remote_dc_file_sync->save();

				kEventsManager::raiseEvent(new kObjectAddedEvent($remote_dc_file_sync));
			}
			kEventsManager::raiseEvent(new kObjectAddedEvent($current_dc_file_sync));
		}

		return $current_dc_file_sync;
	}

	/**
	 * @param FileSyncKey $key
	 * @param StorageProfile $externalStorage
	 * @return SyncFile
	 */
	public static function createPendingExternalSyncFileForKey(FileSyncKey $key, StorageProfile $externalStorage)
	{
		$externalStorageId = $externalStorage->getId();
		KalturaLog::log(__METHOD__." - key [$key], externalStorage [$externalStorageId]");

		list($fileRoot, $realPath) = kPathManager::getFilePathArr($key, $externalStorageId);

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->add(FileSyncPeer::DC, $externalStorageId);
		$fileSync = FileSyncPeer::doSelectOne($c);

		if(!$fileSync)
			$fileSync = FileSync::createForFileSyncKey($key);

		$fileSync->setDc( $externalStorageId );
		$fileSync->setFileRoot ( $fileRoot );
		$fileSync->setFilePath ( $realPath );
		$fileSync->setFileSize ( -1 );
		$fileSync->setStatus( FileSync::FILE_SYNC_STATUS_PENDING );
		$fileSync->setOriginal ( false );

		if($externalStorage->getProtocol() == StorageProfile::STORAGE_KALTURA_DC)
		{
			$fileSync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_FILE );
		}
		else
		{
			$fileSync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_URL );
		}
		$fileSync->save();

		kEventsManager::raiseEvent(new kObjectAddedEvent($fileSync));

		return $fileSync;
	}

	/**
	 * @param FileSyncKey $key
	 * @param string $url
	 * @param StorageProfile $externalStorage
	 * @return SyncFile
	 */
	public static function createReadyExternalSyncFileForKey(FileSyncKey $key, $url, StorageProfile $externalStorage)
	{
		$externalStorageId = $externalStorage->getId();
		KalturaLog::log(__METHOD__." - key [$key], externalStorage [$externalStorageId]");

		$fileRoot = $externalStorage->getDeliveryHttpBaseUrl();
		$filePath = str_replace($fileRoot, '', $url);

		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		$c->add(FileSyncPeer::DC, $externalStorageId);
		$fileSync = FileSyncPeer::doSelectOne($c);

		if(!$fileSync)
			$fileSync = FileSync::createForFileSyncKey($key);

		$fileSync->setDc		( $externalStorageId );
		$fileSync->setFileRoot	( $fileRoot );
		$fileSync->setFilePath	( $filePath );
		$fileSync->setFileSize	( -1 );
		$fileSync->setStatus	( FileSync::FILE_SYNC_STATUS_READY );
		$fileSync->setOriginal	( false );
		$fileSync->setFileType	( FileSync::FILE_SYNC_FILE_TYPE_URL );
		$fileSync->save();

		kEventsManager::raiseEvent(new kObjectAddedEvent($fileSync));

		return $fileSync;
	}

	/**
	 * @param FileSyncKey $key
	 * @param $file_root
	 * @param $real_path
	 * @return SyncFile
	 */
	public static function createSyncFileLinkForKey ( FileSyncKey $target_key , FileSyncKey $source_key )
	{
		KalturaLog::log(__METHOD__." - target_key [$target_key], source_key [$source_key]");
		// TODO - see that if in strict mode - there are no duplicate keys -> update existing records AND set the other DC's records to PENDING
		$dc = kDataCenterMgr::getCurrentDc();
		$dc_id = $dc["id"];

		list($sourceFile, $local) = self::getReadyFileSyncForKey($source_key, true, false);
		if (!$sourceFile)
		{
			KalturaLog::log(__METHOD__." - Warning: no source. target_key [$target_key], source_key [$source_key] ");
			return null;
		}

		$sourceFile = self::resolve($sourceFile); // we only want to link to a source and not to a link.

		// create a FileSync for the current DC with status READY
		$current_dc_file_sync = FileSync::createForFileSyncKey( $target_key );
		$current_dc_file_sync->setPartnerId ( $target_key->partner_id);
		$current_dc_file_sync->setFileSize ( -1 );
		$current_dc_file_sync->setStatus( $sourceFile->getStatus() );
		$current_dc_file_sync->setOriginal ( 1 );

		if($sourceFile->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
		{
			$current_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_URL );
			$current_dc_file_sync->setDc( $sourceFile->getDc() );
			$current_dc_file_sync->setFileRoot( $sourceFile->getFileRoot() );
			$current_dc_file_sync->setFilePath( $sourceFile->getFilePath() );
		}
		else
		{
			$current_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_LINK );
			$current_dc_file_sync->setDc( $dc_id );
			$current_dc_file_sync->setLinkedId( $sourceFile->getId() );
		}

		$current_dc_file_sync->save();

		//increment link_count for this DC source]
		if($sourceFile->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_URL)
			self::incrementLinkCountForFileSync($sourceFile);

		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $source_key );
		$file_sync_list = FileSyncPeer::doSelect( $c );
		$source_file_syncs = array();
		foreach($file_sync_list as $file_sync)
		{
			$file_sync = self::resolve($file_sync); // we only want to link to a source and not to a link.
			$source_file_syncs[$file_sync->getDc()] = $file_sync;
		}

		foreach ( $source_file_syncs as $remote_dc_id => $source_file_sync )
		{
			if($source_file_sync->getDc() == $current_dc_file_sync->getDc())
				continue;

			$remote_dc_file_sync = FileSync::createForFileSyncKey( $target_key );
			$remote_dc_file_sync->setDc( $remote_dc_id );
			$remote_dc_file_sync->setStatus( $source_file_sync->getStatus() );
			$remote_dc_file_sync->setOriginal ( 0 );

			if($source_file_sync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
			{
				$remote_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_URL );
				$remote_dc_file_sync->setFileRoot ( $source_file_sync->getFileRoot() );
				$remote_dc_file_sync->setFilePath ( $source_file_sync->getFilePath() );
			}
			else
			{
				$remote_dc_file_sync->setFileType ( FileSync::FILE_SYNC_FILE_TYPE_LINK );
				$remote_dc_file_sync->setLinkedId ( $source_file_sync->getId() );
			}
			$remote_dc_file_sync->setPartnerID ( $target_key->partner_id );
			$remote_dc_file_sync->save();

			// increment link_cont for remote DCs sources
			if($source_file_sync->getFileType() != FileSync::FILE_SYNC_FILE_TYPE_URL)
				self::incrementLinkCountForFileSync($source_file_sync);

			kEventsManager::raiseEvent(new kObjectAddedEvent($remote_dc_file_sync));
		}
		kEventsManager::raiseEvent(new kObjectAddedEvent($current_dc_file_sync));
	}

	/**
	 * increment the link_count field on a source file_sync record
	 *
	 * @param FileSync $fileSync
	 * @return void
	 */
	private static function incrementLinkCountForFileSync(FileSync $fileSync)
	{
		$current_count = (((int)$fileSync->getLinkCount())? $fileSync->getLinkCount(): 0) + 1;
		$fileSync->setLinkCount($current_count);
		$fileSync->save();
	}

	/**
	 * mark file as deleted, return deleted version
	 * @param FileSyncKey $key
	 * @param bool $strict
	 * @param bool $fromKalturaDcsOnly
	 * @return string
	 */
	public static function deleteSyncFileForKey( FileSyncKey $key , $strict = false , $fromKalturaDcsOnly = false)
	{
		if ( !$key )
		{
			if ( $strict )
				throw new Exception ( "Empty key");
			return null;
		}

		// first check if fileSync is source or link
		$file = FileSyncPeer::retrieveByFileSyncKey($key);
		if(!$file)
			return null;

		if($file->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_LINK)
		{
			$newStatus = FileSync::FILE_SYNC_STATUS_PURGED;
		}
		elseif($file->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_URL)
		{
			$newStatus = FileSync::FILE_SYNC_STATUS_DELETED;
		}
		else
		{
			if($file->getLinkCount() == 0)
			{
				$newStatus = FileSync::FILE_SYNC_STATUS_DELETED;
			}
			else
			{
				$newStatus = FileSync::FILE_SYNC_STATUS_PURGED;
				self::convertLinksToFiles($key);
			}
		}

		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey( $key );
		if($fromKalturaDcsOnly)
			$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL, Criteria::NOT_EQUAL);

		$file_sync_list = FileSyncPeer::doSelect( $c );
		foreach($file_sync_list as $file_sync)
		{
			$wams_asset_id = $file_sync->getWamsAssetId();
			if (!empty($wams_asset_id)) {
				if ($newStatus == FileSync::FILE_SYNC_STATUS_DELETED) {
					self::clearWAMSDataForKey($key, $strict);
				}
				else {
					$file_sync->setWamsAssetId(null);
					$file_sync->setWamsUrl(null);
				}
			}
			$file_sync->setFileSize('0');
			$file_sync->setStatus($newStatus);
			$file_sync->save();
		}
	}

	/**
	 * gets a source file of current DC, will make sure all links points to that source
	 * are converted to files on all DCs
	 *
	 * @param FileSyncKey $key
	 * @return void
	 */
	public static function convertLinksToFiles(FileSyncKey $key)
	{
		// fetch sources from all DCs
		$c = new Criteria();
		$c = FileSyncPeer::getCriteriaForFileSyncKey($key);
		$fileSyncList = FileSyncPeer::doSelect($c);
		foreach($fileSyncList as $fileSync)
		{
			/* @var $fileSync FileSync */

			// for each source, find its links and fix them
			$c = new Criteria();

			$c->add(FileSyncPeer::DC, $fileSync->getDc());
			$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_LINK);
			$c->add(FileSyncPeer::LINKED_ID, $fileSync->getId());
			$c->addAscendingOrderByColumn(FileSyncPeer::PARTNER_ID);

			//relink the links into groups of 100 links
			$c->setLimit(100);

			$links = FileSyncPeer::doSelect($c);
			while(count($links))
			{
				// choose the first link and convert it to file
				$firstLink = array_shift($links);
				/* @var $firstLink FileSync */
				if($firstLink)
				{
					$firstLink->setStatus($fileSync->getStatus());
					$firstLink->setFileSize($fileSync->getFileSize());
					$firstLink->setFileRoot($fileSync->getFileRoot());
					$firstLink->setFilePath($fileSync->getFilePath());
					$firstLink->setWamsAssetId($fileSync->getWamsAssetId());
					$firstLink->setWamsUrl($fileSync->getWamsUrl());
					$firstLink->setFileType(FileSync::FILE_SYNC_FILE_TYPE_FILE);
					$firstLink->setLinkedId(0); // keep it zero instead of null, that's the only way to know it used to be a link.
					$firstLink->setLinkCount(count($links));
					$firstLink->save();
				}

				// change all the rest of the links to point on the new file sync
				foreach($links as $link)
				{
					/* @var $link FileSync */
					$link->setStatus($fileSync->getStatus());
					$link->setLinkedId($firstLink->getId());
					$link->save();
				}

				FileSyncPeer::clearInstancePool();
				$links = FileSyncPeer::doSelect($c);
			}
		}
	}

	/**
	 * mark file as undeleted, return ?
	 * @param FileSyncKey $key
	 * @return unknown_type
	 */
	public static function undeleteSyncFile( FileSyncKey $key )
	{
		// TODO - implement undelete, remember to undelete all DC's
	}

	/**
	 *
	 * @param FileSync $key
	 * @return ISyncableFile
	 */
	public static function retrieveObjectForFileSync ( FileSync $file_sync )
	{
		KalturaLog::log(__METHOD__." - FileSync id [" . $file_sync->getId() . "]" );
		return kFileSyncObjectManager::retrieveObject( $file_sync->getObjectType(), $file_sync->getObjectId() );
	}

	/**
	 *
	 * @param FileSyncKey $sync_key
	 * @return ISyncableFile
	 */
	public static function retrieveObjectForSyncKey ( FileSyncKey  $sync_key )
	{
		return kFileSyncObjectManager::retrieveObject( $sync_key->object_type, $sync_key->object_id );
	}

	/* (non-PHPdoc)
	 * @see kObjectChangedEventConsumer::objectChanged()
	 */
	public function objectChanged(BaseObject $object, array $modifiedColumns)
	{
		/* @var $object FileSync */

		$c = new Criteria();
		$c->add(FileSyncPeer::DC, $object->getDc());
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_LINK);
		$c->add(FileSyncPeer::LINKED_ID, $object->getId());

		$links = FileSyncPeer::doSelect($c);
		foreach($links as $link)
		{
			$link->setStatus($object->getStatus());
			$link->save();
		}
	}

	/* (non-PHPdoc)
	 * @see kObjectChangedEventConsumer::shouldConsumeChangedEvent()
	 */
	public function shouldConsumeChangedEvent(BaseObject $object, array $modifiedColumns)
	{
		$noneValidStatuses = array(
			FileSync::FILE_SYNC_STATUS_DELETED,
			FileSync::FILE_SYNC_STATUS_PURGED,
		);

		if(	$object instanceof FileSync
			&& $object->getLinkCount()
			&& in_array(FileSyncPeer::STATUS, $modifiedColumns)
			&& !in_array($object->getStatus(), $noneValidStatuses)
		)
			return true;

		return false;
	}
}