<?php
require_once("KalturaClientBase.php");

class KalturaBaseRestriction extends KalturaObjectBase
{

}

class KalturaAccessControl extends KalturaObjectBase
{
	/**
	 * The id of the Access Control Profile
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The name of the Access Control Profile
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * System name of the Access Control Profile
	 * 
	 *
	 * @var string
	 */
	public $systemName = null;

	/**
	 * The description of the Access Control Profile
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * Creation date as Unix timestamp (In seconds) 
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * True if this Conversion Profile is the default
	 * 
	 *
	 * @var KalturaNullableBoolean
	 */
	public $isDefault = null;

	/**
	 * Array of Access Control Restrictions
	 * 
	 *
	 * @var array of KalturaBaseRestriction
	 */
	public $restrictions;


}

abstract class KalturaSearchItem extends KalturaObjectBase
{

}

class KalturaFilter extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $orderBy = null;

	/**
	 * 
	 *
	 * @var KalturaSearchItem
	 */
	public $advancedSearch;


}

abstract class KalturaAccessControlBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;


}

class KalturaAccessControlFilter extends KalturaAccessControlBaseFilter
{

}

class KalturaFilterPager extends KalturaObjectBase
{
	/**
	 * The number of objects to retrieve. (Default is 30, maximum page size is 500).
	 * 
	 *
	 * @var int
	 */
	public $pageSize = null;

	/**
	 * The page number for which {pageSize} of objects should be retrieved (Default is 1).
	 * 
	 *
	 * @var int
	 */
	public $pageIndex = null;


}

class KalturaAccessControlListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaAccessControl
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaUser extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $screenName = null;

	/**
	 * DEPRECATED
	 *
	 * @var string
	 */
	public $fullName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $email = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $dateOfBirth = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $country = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $state = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $city = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $zip = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbnailUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * Admin tags can be updated only by using an admin session
	 *
	 * @var string
	 */
	public $adminTags = null;

	/**
	 * 
	 *
	 * @var KalturaGender
	 */
	public $gender = null;

	/**
	 * 
	 *
	 * @var KalturaUserStatus
	 */
	public $status = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Last update date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * Can be used to store various partner related data as a string 
	 *
	 * @var string
	 */
	public $partnerData = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $indexedPartnerDataInt = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $indexedPartnerDataString = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $storageSize = null;

	/**
	 * 
	 *
	 * @var string
	 * @insertonly
	 */
	public $password = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $firstName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastName = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isAdmin = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $lastLoginTime = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $statusUpdatedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $deletedAt = null;

	/**
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $loginEnabled = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $roleIds = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $roleNames = null;

	/**
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $isAccountOwner = null;


}

class KalturaAdminUser extends KalturaUser
{

}

class KalturaDynamicEnum extends KalturaObjectBase
{

}

abstract class KalturaOperationAttributes extends KalturaObjectBase
{

}

class KalturaBaseEntry extends KalturaObjectBase
{
	/**
	 * Auto generated 10 characters alphanumeric string
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * Entry name (Min 1 chars)
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Entry description
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The ID of the user who is the owner of this entry 
	 * 
	 *
	 * @var string
	 */
	public $userId = null;

	/**
	 * Entry tags
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * Entry admin tags can be updated only by administrators
	 * 
	 *
	 * @var string
	 */
	public $adminTags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categories = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categoriesIds = null;

	/**
	 * 
	 *
	 * @var KalturaEntryStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * Entry moderation status
	 * 
	 *
	 * @var KalturaEntryModerationStatus
	 * @readonly
	 */
	public $moderationStatus = null;

	/**
	 * Number of moderation requests waiting for this entry
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $moderationCount = null;

	/**
	 * The type of the entry, this is auto filled by the derived entry object
	 * 
	 *
	 * @var KalturaEntryType
	 */
	public $type = null;

	/**
	 * Entry creation date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Entry update date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * Calculated rank
	 * 
	 *
	 * @var float
	 * @readonly
	 */
	public $rank = null;

	/**
	 * The total (sum) of all votes
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalRank = null;

	/**
	 * Number of votes
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $votes = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $groupId = null;

	/**
	 * Can be used to store various partner related data as a string 
	 * 
	 *
	 * @var string
	 */
	public $partnerData = null;

	/**
	 * Download URL for the entry
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $downloadUrl = null;

	/**
	 * Indexed search text for full text search
	 *
	 * @var string
	 * @readonly
	 */
	public $searchText = null;

	/**
	 * License type used for this entry
	 * 
	 *
	 * @var KalturaLicenseType
	 */
	public $licenseType = null;

	/**
	 * Version of the entry data
	 *
	 * @var int
	 * @readonly
	 */
	public $version = null;

	/**
	 * Thumbnail URL
	 * 
	 *
	 * @var string
	 * @insertonly
	 */
	public $thumbnailUrl = null;

	/**
	 * The Access Control ID assigned to this entry (null when not set, send -1 to remove)  
	 * 
	 *
	 * @var int
	 */
	public $accessControlId = null;

	/**
	 * Entry scheduling start date (null when not set, send -1 to remove)
	 * 
	 *
	 * @var int
	 */
	public $startDate = null;

	/**
	 * Entry scheduling end date (null when not set, send -1 to remove)
	 * 
	 *
	 * @var int
	 */
	public $endDate = null;

	/**
	 * Entry external reference id
	 * 
	 *
	 * @var string
	 */
	public $referenceId = null;

	/**
	 * ID of temporary entry that will replace this entry when it's approved and ready for replacement
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $replacingEntryId = null;

	/**
	 * ID of the entry that will be replaced when the replacement approved and this entry is ready
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $replacedEntryId = null;

	/**
	 * Status of the replacement readiness and approval
	 * 
	 *
	 * @var KalturaEntryReplacementStatus
	 * @readonly
	 */
	public $replacementStatus = null;

	/**
	 * Can be used to store various partner related data as a numeric value
	 * 
	 *
	 * @var int
	 */
	public $partnerSortValue = null;

	/**
	 * Override the default ingestion profile  
	 * 
	 *
	 * @var int
	 */
	public $conversionProfileId = null;

	/**
	 * ID of source root entry, used for clipped, skipped and cropped entries that created from another entry  
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $rootEntryId = null;

	/**
	 * clipping, skipping and cropping attributes that used to create this entry  
	 * 
	 *
	 * @var array of KalturaOperationAttributes
	 */
	public $operationAttributes;


}

abstract class KalturaResource extends KalturaObjectBase
{

}

abstract class KalturaBaseEntryBaseFilter extends KalturaFilter
{
	/**
	 * This filter should be in use for retrieving only a specific entry (identified by its entryId).
	 * @var string
	 *
	 * @var string
	 */
	public $idEqual = null;

	/**
	 * This filter should be in use for retrieving few specific entries (string should include comma separated list of entryId strings).
	 * @var string
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idNotIn = null;

	/**
	 * This filter should be in use for retrieving specific entries. It should include only one string to search for in entry names (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $nameLike = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry names, while applying an OR logic to retrieve entries that contain at least one input string (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $nameMultiLikeOr = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry names, while applying an AND logic to retrieve entries that contain all input strings (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $nameMultiLikeAnd = null;

	/**
	 * This filter should be in use for retrieving entries with a specific name.
	 * @var string
	 *
	 * @var string
	 */
	public $nameEqual = null;

	/**
	 * This filter should be in use for retrieving only entries which were uploaded by/assigned to users of a specific Kaltura Partner (identified by Partner ID).
	 * @var int
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * This filter should be in use for retrieving only entries within Kaltura network which were uploaded by/assigned to users of few Kaltura Partners  (string should include comma separated list of PartnerIDs)
	 * @var string
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * This filter parameter should be in use for retrieving only entries, uploaded by/assigned to a specific user (identified by user Id).
	 * @var string
	 *
	 * @var string
	 */
	public $userIdEqual = null;

	/**
	 * This filter should be in use for retrieving specific entries. It should include only one string to search for in entry tags (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $tagsLike = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry tags, while applying an OR logic to retrieve entries that contain at least one input string (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry tags, while applying an AND logic to retrieve entries that contain all input strings (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * This filter should be in use for retrieving specific entries. It should include only one string to search for in entry tags set by an ADMIN user (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $adminTagsLike = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry tags, set by an ADMIN user, while applying an OR logic to retrieve entries that contain at least one input string (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $adminTagsMultiLikeOr = null;

	/**
	 * This filter should be in use for retrieving specific entries. It could include few (comma separated) strings for searching in entry tags, set by an ADMIN user, while applying an AND logic to retrieve entries that contain all input strings (no wildcards, spaces are treated as part of the string).
	 * @var string
	 *
	 * @var string
	 */
	public $adminTagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categoriesMatchAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categoriesMatchOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categoriesIdsMatchAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categoriesIdsMatchOr = null;

	/**
	 * This filter should be in use for retrieving only entries, at a specific {@link ?object=KalturaEntryStatus KalturaEntryStatus}.
	 * @var KalturaEntryStatus
	 *
	 * @var KalturaEntryStatus
	 */
	public $statusEqual = null;

	/**
	 * This filter should be in use for retrieving only entries, not at a specific {@link ?object=KalturaEntryStatus KalturaEntryStatus}.
	 * @var KalturaEntryStatus
	 *
	 * @var KalturaEntryStatus
	 */
	public $statusNotEqual = null;

	/**
	 * This filter should be in use for retrieving only entries, at few specific {@link ?object=KalturaEntryStatus KalturaEntryStatus} (comma separated).
	 * @dynamicType KalturaEntryStatus
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * This filter should be in use for retrieving only entries, not at few specific {@link ?object=KalturaEntryStatus KalturaEntryStatus} (comma separated).
	 * @dynamicType KalturaEntryStatus
	 *
	 * @var string
	 */
	public $statusNotIn = null;

	/**
	 * 
	 *
	 * @var KalturaEntryModerationStatus
	 */
	public $moderationStatusEqual = null;

	/**
	 * 
	 *
	 * @var KalturaEntryModerationStatus
	 */
	public $moderationStatusNotEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $moderationStatusIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $moderationStatusNotIn = null;

	/**
	 * 
	 *
	 * @var KalturaEntryType
	 */
	public $typeEqual = null;

	/**
	 * This filter should be in use for retrieving entries of few {@link ?object=KalturaEntryType KalturaEntryType} (string should include a comma separated list of {@link ?object=KalturaEntryType KalturaEntryType} enumerated parameters).
	 * @dynamicType KalturaEntryType
	 *
	 * @var string
	 */
	public $typeIn = null;

	/**
	 * This filter parameter should be in use for retrieving only entries which were created at Kaltura system after a specific time/date (standard timestamp format).
	 * @var int
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * This filter parameter should be in use for retrieving only entries which were created at Kaltura system before a specific time/date (standard timestamp format).
	 * @var int
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $groupIdEqual = null;

	/**
	 * This filter should be in use for retrieving specific entries while search match the input string within all of the following metadata attributes: name, description, tags, adminTags.
	 * @var string
	 *
	 * @var string
	 */
	public $searchTextMatchAnd = null;

	/**
	 * This filter should be in use for retrieving specific entries while search match the input string within at least one of the following metadata attributes: name, description, tags, adminTags.
	 * @var string
	 *
	 * @var string
	 */
	public $searchTextMatchOr = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $accessControlIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $accessControlIdIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $startDateGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $startDateLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $startDateGreaterThanOrEqualOrNull = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $startDateLessThanOrEqualOrNull = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $endDateGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $endDateLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $endDateGreaterThanOrEqualOrNull = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $endDateLessThanOrEqualOrNull = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $referenceIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $referenceIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $replacingEntryIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $replacingEntryIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $replacedEntryIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $replacedEntryIdIn = null;

	/**
	 * 
	 *
	 * @var KalturaEntryReplacementStatus
	 */
	public $replacementStatusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $replacementStatusIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerSortValueGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerSortValueLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rootEntryIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rootEntryIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsNameMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsAdminTagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsAdminTagsNameMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsNameMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsAdminTagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsAdminTagsNameMultiLikeAnd = null;


}

class KalturaBaseEntryFilter extends KalturaBaseEntryBaseFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $freeText = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isRoot = null;


}

class KalturaBaseEntryListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaBaseEntry
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaModerationFlag extends KalturaObjectBase
{
	/**
	 * Moderation flag id
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The user id that added the moderation flag
	 *
	 * @var string
	 * @readonly
	 */
	public $userId = null;

	/**
	 * The type of the moderation flag (entry or user)
	 *
	 * @var KalturaModerationObjectType
	 * @readonly
	 */
	public $moderationObjectType = null;

	/**
	 * If moderation flag is set for entry, this is the flagged entry id
	 *
	 * @var string
	 */
	public $flaggedEntryId = null;

	/**
	 * If moderation flag is set for user, this is the flagged user id
	 *
	 * @var string
	 */
	public $flaggedUserId = null;

	/**
	 * The moderation flag status
	 *
	 * @var KalturaModerationFlagStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * The comment that was added to the flag
	 *
	 * @var string
	 */
	public $comments = null;

	/**
	 * 
	 *
	 * @var KalturaModerationFlagType
	 */
	public $flagType = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;


}

class KalturaModerationFlagListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaModerationFlag
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaEntryContextDataParams extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $referrer = null;


}

class KalturaEntryContextDataResult extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var bool
	 */
	public $isSiteRestricted = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isCountryRestricted = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isSessionRestricted = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isIpAddressRestricted = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $previewLength = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isScheduledNow = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isAdmin = null;


}

class KalturaSchedulerStatus extends KalturaObjectBase
{
	/**
	 * The id of the Category
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The configured id of the scheduler
	 * 
	 *
	 * @var int
	 */
	public $schedulerConfiguredId = null;

	/**
	 * The configured id of the job worker
	 * 
	 *
	 * @var int
	 */
	public $workerConfiguredId = null;

	/**
	 * The type of the job worker.
	 * 
	 *
	 * @var KalturaBatchJobType
	 */
	public $workerType = null;

	/**
	 * The status type
	 * 
	 *
	 * @var KalturaSchedulerStatusType
	 */
	public $type = null;

	/**
	 * The status value
	 * 
	 *
	 * @var int
	 */
	public $value = null;

	/**
	 * The id of the scheduler
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $schedulerId = null;

	/**
	 * The id of the worker
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $workerId = null;


}

class KalturaSchedulerConfig extends KalturaObjectBase
{
	/**
	 * The id of the Category
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * Creator name
	 * 
	 *
	 * @var string
	 */
	public $createdBy = null;

	/**
	 * Updater name
	 * 
	 *
	 * @var string
	 */
	public $updatedBy = null;

	/**
	 * Id of the control panel command that created this config item 
	 * 
	 *
	 * @var string
	 */
	public $commandId = null;

	/**
	 * The status of the control panel command 
	 * 
	 *
	 * @var string
	 */
	public $commandStatus = null;

	/**
	 * The id of the scheduler 
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * The configured id of the scheduler 
	 * 
	 *
	 * @var int
	 */
	public $schedulerConfiguredId = null;

	/**
	 * The name of the scheduler 
	 * 
	 *
	 * @var string
	 */
	public $schedulerName = null;

	/**
	 * The id of the job worker
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * The configured id of the job worker
	 * 
	 *
	 * @var int
	 */
	public $workerConfiguredId = null;

	/**
	 * The name of the job worker
	 * 
	 *
	 * @var string
	 */
	public $workerName = null;

	/**
	 * The name of the variable
	 * 
	 *
	 * @var string
	 */
	public $variable = null;

	/**
	 * The part of the variable
	 * 
	 *
	 * @var string
	 */
	public $variablePart = null;

	/**
	 * The value of the variable
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaBaseJob extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $deletedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $processorExpiration = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $executionAttempts = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $lockVersion = null;


}

class KalturaJobData extends KalturaObjectBase
{

}

class KalturaBatchJob extends KalturaBaseJob
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryName = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobType
	 * @readonly
	 */
	public $jobType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $jobSubType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $onStressDivertTo = null;

	/**
	 * 
	 *
	 * @var KalturaJobData
	 */
	public $data;

	/**
	 * 
	 *
	 * @var KalturaBatchJobStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $abort = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $checkAgainTimeout = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $progress = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $message = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatesCount = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $priority = null;

	/**
	 * The id of identical job
	 *
	 * @var int
	 */
	public $twinJobId = null;

	/**
	 * The id of the bulk upload job that initiated this job
	 *
	 * @var int
	 */
	public $bulkJobId = null;

	/**
	 * When one job creates another - the parent should set this parentJobId to be its own id.
	 *
	 * @var int
	 */
	public $parentJobId = null;

	/**
	 * The id of the root parent job
	 *
	 * @var int
	 */
	public $rootJobId = null;

	/**
	 * The time that the job was pulled from the queue
	 *
	 * @var int
	 */
	public $queueTime = null;

	/**
	 * The time that the job was finished or closed as failed
	 *
	 * @var int
	 */
	public $finishTime = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobErrorTypes
	 */
	public $errType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $errNumber = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $fileSize = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $lastWorkerRemote = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $batchIndex = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lastSchedulerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lastWorkerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $dc = null;


}

class KalturaSchedulerWorker extends KalturaObjectBase
{
	/**
	 * The id of the Worker
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The id as configured in the batch config
	 * 
	 *
	 * @var int
	 */
	public $configuredId = null;

	/**
	 * The id of the Scheduler
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * The id of the scheduler as configured in the batch config
	 * 
	 *
	 * @var int
	 */
	public $schedulerConfiguredId = null;

	/**
	 * The worker type
	 * 
	 *
	 * @var KalturaBatchJobType
	 */
	public $type = null;

	/**
	 * The friendly name of the type
	 * 
	 *
	 * @var string
	 */
	public $typeName = null;

	/**
	 * The scheduler name
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Array of the last statuses
	 * 
	 *
	 * @var array of KalturaSchedulerStatus
	 */
	public $statuses;

	/**
	 * Array of the last configs
	 * 
	 *
	 * @var array of KalturaSchedulerConfig
	 */
	public $configs;

	/**
	 * Array of jobs that locked to this worker
	 * 
	 *
	 * @var array of KalturaBatchJob
	 */
	public $lockedJobs;

	/**
	 * Avarage time between creation and queue time
	 * 
	 *
	 * @var int
	 */
	public $avgWait = null;

	/**
	 * Avarage time between queue time end finish time
	 * 
	 *
	 * @var int
	 */
	public $avgWork = null;

	/**
	 * last status time
	 * 
	 *
	 * @var int
	 */
	public $lastStatus = null;

	/**
	 * last status formated
	 * 
	 *
	 * @var string
	 */
	public $lastStatusStr = null;


}

class KalturaScheduler extends KalturaObjectBase
{
	/**
	 * The id of the Scheduler
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The id as configured in the batch config
	 * 
	 *
	 * @var int
	 */
	public $configuredId = null;

	/**
	 * The scheduler name
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * The host name
	 * 
	 *
	 * @var string
	 */
	public $host = null;

	/**
	 * Array of the last statuses
	 * 
	 *
	 * @var array of KalturaSchedulerStatus
	 * @readonly
	 */
	public $statuses;

	/**
	 * Array of the last configs
	 * 
	 *
	 * @var array of KalturaSchedulerConfig
	 * @readonly
	 */
	public $configs;

	/**
	 * Array of the workers
	 * 
	 *
	 * @var array of KalturaSchedulerWorker
	 * @readonly
	 */
	public $workers;

	/**
	 * creation time
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * last status time
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $lastStatus = null;

	/**
	 * last status formated
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $lastStatusStr = null;


}

abstract class KalturaBaseJobBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $idGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $processorExpirationGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $processorExpirationLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $executionAttemptsGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $executionAttemptsLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lockVersionGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lockVersionLessThanOrEqual = null;


}

class KalturaBaseJobFilter extends KalturaBaseJobBaseFilter
{

}

abstract class KalturaBatchJobBaseFilter extends KalturaBaseJobFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $entryIdEqual = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobType
	 */
	public $jobTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $jobTypeIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $jobTypeNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $jobSubTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $jobSubTypeIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $jobSubTypeNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $onStressDivertToEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $onStressDivertToIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $onStressDivertToNotIn = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $abortEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $checkAgainTimeoutGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $checkAgainTimeoutLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $progressGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $progressLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatesCountGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatesCountLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $priorityGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $priorityLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $priorityEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $priorityIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $priorityNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $twinJobIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $twinJobIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $twinJobIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $bulkJobIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $bulkJobIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $bulkJobIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $parentJobIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $parentJobIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $parentJobIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $rootJobIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rootJobIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rootJobIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $queueTimeGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $queueTimeLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $finishTimeGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $finishTimeLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobErrorTypes
	 */
	public $errTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errTypeIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errTypeNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $errNumberEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errNumberIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errNumberNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $fileSizeLessThan = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $fileSizeGreaterThan = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $lastWorkerRemoteEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $schedulerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $schedulerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $schedulerIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $workerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $workerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $workerIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $batchIndexEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $batchIndexIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $batchIndexNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lastSchedulerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastSchedulerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastSchedulerIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $lastWorkerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastWorkerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $lastWorkerIdNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $dcEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $dcIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $dcNotIn = null;


}

class KalturaBatchJobFilter extends KalturaBatchJobBaseFilter
{

}

class KalturaWorkerQueueFilter extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobType
	 */
	public $jobType = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobFilter
	 */
	public $filter;


}

class KalturaBatchQueuesStatus extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var KalturaBatchJobType
	 */
	public $jobType = null;

	/**
	 * The worker configured id
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * The friendly name of the type
	 * 
	 *
	 * @var string
	 */
	public $typeName = null;

	/**
	 * The size of the queue
	 * 
	 *
	 * @var int
	 */
	public $size = null;

	/**
	 * The avarage wait time
	 * 
	 *
	 * @var int
	 */
	public $waitTime = null;


}

class KalturaControlPanelCommand extends KalturaObjectBase
{
	/**
	 * The id of the Category
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Creator name
	 * 
	 *
	 * @var string
	 */
	public $createdBy = null;

	/**
	 * Update date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * Updater name
	 * 
	 *
	 * @var string
	 */
	public $updatedBy = null;

	/**
	 * Creator id
	 * 
	 *
	 * @var int
	 */
	public $createdById = null;

	/**
	 * The id of the scheduler that the command refers to
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * The id of the scheduler worker that the command refers to
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * The id of the scheduler worker as configured in the ini file
	 * 
	 *
	 * @var int
	 */
	public $workerConfiguredId = null;

	/**
	 * The name of the scheduler worker that the command refers to
	 * 
	 *
	 * @var int
	 */
	public $workerName = null;

	/**
	 * The index of the batch process that the command refers to
	 * 
	 *
	 * @var int
	 */
	public $batchIndex = null;

	/**
	 * The command type - stop / start / config
	 * 
	 *
	 * @var KalturaControlPanelCommandType
	 */
	public $type = null;

	/**
	 * The command target type - data center / scheduler / job / job type
	 * 
	 *
	 * @var KalturaControlPanelCommandTargetType
	 */
	public $targetType = null;

	/**
	 * The command status
	 * 
	 *
	 * @var KalturaControlPanelCommandStatus
	 */
	public $status = null;

	/**
	 * The reason for the command
	 * 
	 *
	 * @var string
	 */
	public $cause = null;

	/**
	 * Command description
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * Error description
	 * 
	 *
	 * @var string
	 */
	public $errorDescription = null;


}

class KalturaSchedulerStatusResponse extends KalturaObjectBase
{
	/**
	 * The status of all queues on the server
	 * 
	 *
	 * @var array of KalturaBatchQueuesStatus
	 */
	public $queuesStatus;

	/**
	 * The commands that sent from the control panel
	 * 
	 *
	 * @var array of KalturaControlPanelCommand
	 */
	public $controlPanelCommands;

	/**
	 * The configuration that sent from the control panel
	 * 
	 *
	 * @var array of KalturaSchedulerConfig
	 */
	public $schedulerConfigs;


}

abstract class KalturaControlPanelCommandBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdByIdEqual = null;

	/**
	 * 
	 *
	 * @var KalturaControlPanelCommandType
	 */
	public $typeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $typeIn = null;

	/**
	 * 
	 *
	 * @var KalturaControlPanelCommandTargetType
	 */
	public $targetTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $targetTypeIn = null;

	/**
	 * 
	 *
	 * @var KalturaControlPanelCommandStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;


}

class KalturaControlPanelCommandFilter extends KalturaControlPanelCommandBaseFilter
{

}

class KalturaControlPanelCommandListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaControlPanelCommand
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaSchedulerListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaScheduler
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaSchedulerWorkerListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaSchedulerWorker
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaExclusiveLockKey extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $schedulerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $workerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $batchIndex = null;


}

class KalturaFreeJobResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var KalturaBatchJob
	 * @readonly
	 */
	public $job;

	/**
	 * 
	 *
	 * @var KalturaBatchJobType
	 * @readonly
	 */
	public $jobType = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $queueSize = null;


}

class KalturaBulkUploadPluginData extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $field = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaBulkUploadResult extends KalturaObjectBase
{
	/**
	 * The id of the result
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The id of the parent job
	 * 
	 *
	 * @var int
	 */
	public $bulkUploadJobId = null;

	/**
	 * The index of the line in the CSV
	 * 
	 *
	 * @var int
	 */
	public $lineIndex = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $entryStatus = null;

	/**
	 * The data as recieved in the csv
	 * 
	 *
	 * @var string
	 */
	public $rowData = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $url = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $contentType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $conversionProfileId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $accessControlProfileId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $category = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $scheduleStartDate = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $scheduleEndDate = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbnailUrl = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $thumbnailSaved = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerData = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errorDescription = null;

	/**
	 * 
	 *
	 * @var array of KalturaBulkUploadPluginData
	 */
	public $pluginsData;


}

class KalturaConvertCollectionFlavorData extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsOutputId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $readyBehavior = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $videoBitrate = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $audioBitrate = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileSyncRemoteUrl = null;


}

class KalturaMediaInfo extends KalturaObjectBase
{
	/**
	 * The id of the media info
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * The id of the related flavor asset
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * The file size
	 * 
	 *
	 * @var int
	 */
	public $fileSize = null;

	/**
	 * The container format
	 * 
	 *
	 * @var string
	 */
	public $containerFormat = null;

	/**
	 * The container id
	 * 
	 *
	 * @var string
	 */
	public $containerId = null;

	/**
	 * The container profile
	 * 
	 *
	 * @var string
	 */
	public $containerProfile = null;

	/**
	 * The container duration
	 * 
	 *
	 * @var int
	 */
	public $containerDuration = null;

	/**
	 * The container bit rate
	 * 
	 *
	 * @var int
	 */
	public $containerBitRate = null;

	/**
	 * The video format
	 * 
	 *
	 * @var string
	 */
	public $videoFormat = null;

	/**
	 * The video codec id
	 * 
	 *
	 * @var string
	 */
	public $videoCodecId = null;

	/**
	 * The video duration
	 * 
	 *
	 * @var int
	 */
	public $videoDuration = null;

	/**
	 * The video bit rate
	 * 
	 *
	 * @var int
	 */
	public $videoBitRate = null;

	/**
	 * The video bit rate mode
	 * 
	 *
	 * @var KalturaBitRateMode
	 */
	public $videoBitRateMode = null;

	/**
	 * The video width
	 * 
	 *
	 * @var int
	 */
	public $videoWidth = null;

	/**
	 * The video height
	 * 
	 *
	 * @var int
	 */
	public $videoHeight = null;

	/**
	 * The video frame rate
	 * 
	 *
	 * @var float
	 */
	public $videoFrameRate = null;

	/**
	 * The video display aspect ratio (dar)
	 * 
	 *
	 * @var float
	 */
	public $videoDar = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $videoRotation = null;

	/**
	 * The audio format
	 * 
	 *
	 * @var string
	 */
	public $audioFormat = null;

	/**
	 * The audio codec id
	 * 
	 *
	 * @var string
	 */
	public $audioCodecId = null;

	/**
	 * The audio duration
	 * 
	 *
	 * @var int
	 */
	public $audioDuration = null;

	/**
	 * The audio bit rate
	 * 
	 *
	 * @var int
	 */
	public $audioBitRate = null;

	/**
	 * The audio bit rate mode
	 * 
	 *
	 * @var KalturaBitRateMode
	 */
	public $audioBitRateMode = null;

	/**
	 * The number of audio channels
	 * 
	 *
	 * @var int
	 */
	public $audioChannels = null;

	/**
	 * The audio sampling rate
	 * 
	 *
	 * @var int
	 */
	public $audioSamplingRate = null;

	/**
	 * The audio resolution
	 * 
	 *
	 * @var int
	 */
	public $audioResolution = null;

	/**
	 * The writing library
	 * 
	 *
	 * @var string
	 */
	public $writingLib = null;

	/**
	 * The data as returned by the mediainfo command line
	 * 
	 *
	 * @var string
	 */
	public $rawData = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $multiStreamInfo = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $scanType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $multiStream = null;


}

class KalturaNotification extends KalturaBaseJob
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $puserId = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationType
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $objectId = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationData = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $numberOfAttempts = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationResult = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationObjectType
	 */
	public $objType = null;


}

class KalturaKeyValue extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $key = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaPartner extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $website = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationUrl = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $appearInSearch = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * deprecated - lastName and firstName replaces this field
	 *
	 * @var string
	 */
	public $adminName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $adminEmail = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var KalturaCommercialUseType
	 */
	public $commercialUse = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $landingPage = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $userLandingPage = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $contentCategories = null;

	/**
	 * 
	 *
	 * @var KalturaPartnerType
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $phone = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $describeYourself = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $adultContent = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defConversionProfileType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $notify = null;

	/**
	 * 
	 *
	 * @var KalturaPartnerStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $allowQuickEdit = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mergeEntryLists = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationsConfig = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $maxUploadSize = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerPackage = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $secret = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $adminSecret = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $cmsPassword = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $allowMultiNotification = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $adminLoginUsersQuota = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $adminUserId = null;

	/**
	 * firstName and lastName replace the old (deprecated) adminName
	 *
	 * @var string
	 */
	public $firstName = null;

	/**
	 * lastName and firstName replace the old (deprecated) adminName
	 *
	 * @var string
	 */
	public $lastName = null;

	/**
	 * country code (2char) - this field is optional
	 * 
	 *
	 * @var string
	 */
	public $country = null;

	/**
	 * state code (2char) - this field is optional
	 *
	 * @var string
	 */
	public $state = null;

	/**
	 * 
	 *
	 * @var array of KalturaKeyValue
	 * @insertonly
	 */
	public $additionalParams;


}

class KalturaBatchGetExclusiveNotificationJobsResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaNotification
	 * @readonly
	 */
	public $notifications;

	/**
	 * 
	 *
	 * @var array of KalturaPartner
	 * @readonly
	 */
	public $partners;


}

class KalturaFileExistsResponse extends KalturaObjectBase
{
	/**
	 * Indicates if the file exists
	 * 
	 *
	 * @var bool
	 */
	public $exists = null;

	/**
	 * Indicates if the file size is right
	 * 
	 *
	 * @var bool
	 */
	public $sizeOk = null;


}

class KalturaBulkUpload extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $uploadedBy = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $uploadedByUserId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $uploadedOn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $numOfEntries = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $logFileUrl = null;

	/**
	 * DEPRECATED
	 *
	 * @var string
	 */
	public $csvFileUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $bulkFileUrl = null;

	/**
	 * 
	 *
	 * @var array of KalturaBulkUploadResult
	 */
	public $results;

	/**
	 * 
	 *
	 * @var string
	 */
	public $error = null;

	/**
	 * 
	 *
	 * @var KalturaBatchJobErrorTypes
	 */
	public $errorType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $errorNumber = null;


}

class KalturaBulkUploadListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaBulkUpload
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaCategory extends KalturaObjectBase
{
	/**
	 * The id of the Category
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $parentId = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $depth = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The name of the Category. 
	 * The following characters are not allowed: '<', '>', ','
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * The full name of the Category
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $fullName = null;

	/**
	 * Number of entries in this Category (including child categories)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $entriesCount = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;


}

abstract class KalturaCategoryBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $parentIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $parentIdIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $depthEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fullNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fullNameStartsWith = null;


}

class KalturaCategoryFilter extends KalturaCategoryBaseFilter
{

}

class KalturaCategoryListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaCategory
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaConversionProfileAssetParamsBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $conversionProfileIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $conversionProfileIdIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $assetParamsIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetParamsIdIn = null;

	/**
	 * 
	 *
	 * @var KalturaFlavorReadyBehaviorType
	 */
	public $readyBehaviorEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $readyBehaviorIn = null;

	/**
	 * 
	 *
	 * @var KalturaAssetParamsOrigin
	 */
	public $originEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $originIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameIn = null;


}

abstract class KalturaConversionProfileBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var KalturaConversionProfileStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultEntryIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultEntryIdIn = null;


}

class KalturaConversionProfileFilter extends KalturaConversionProfileBaseFilter
{

}

abstract class KalturaAssetParamsBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameIn = null;

	/**
	 * 
	 *
	 * @var KalturaNullableBoolean
	 */
	public $isSystemDefaultEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsEqual = null;

	/**
	 * 
	 *
	 * @var KalturaContainerFormat
	 */
	public $formatEqual = null;


}

class KalturaAssetParamsFilter extends KalturaAssetParamsBaseFilter
{

}

class KalturaConversionProfileAssetParamsFilter extends KalturaConversionProfileAssetParamsBaseFilter
{
	/**
	 * 
	 *
	 * @var KalturaConversionProfileFilter
	 */
	public $conversionProfileIdFilter;

	/**
	 * 
	 *
	 * @var KalturaAssetParamsFilter
	 */
	public $assetParamsIdFilter;


}

class KalturaConversionProfileAssetParams extends KalturaObjectBase
{
	/**
	 * The id of the conversion profile
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $conversionProfileId = null;

	/**
	 * The id of the asset params
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $assetParamsId = null;

	/**
	 * The ingestion origin of the asset params
	 * 
	 *
	 * @var KalturaFlavorReadyBehaviorType
	 */
	public $readyBehavior = null;

	/**
	 * The ingestion origin of the asset params
	 * 
	 *
	 * @var KalturaAssetParamsOrigin
	 */
	public $origin = null;

	/**
	 * Asset params system name
	 * 
	 *
	 * @var string
	 */
	public $systemName = null;


}

class KalturaConversionProfileAssetParamsListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaConversionProfileAssetParams
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaCropDimensions extends KalturaObjectBase
{
	/**
	 * Crop left point
	 * 
	 *
	 * @var int
	 */
	public $left = null;

	/**
	 * Crop top point
	 * 
	 *
	 * @var int
	 */
	public $top = null;

	/**
	 * Crop width
	 * 
	 *
	 * @var int
	 */
	public $width = null;

	/**
	 * Crop height
	 * 
	 *
	 * @var int
	 */
	public $height = null;


}

class KalturaConversionProfile extends KalturaObjectBase
{
	/**
	 * The id of the Conversion Profile
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var KalturaConversionProfileStatus
	 */
	public $status = null;

	/**
	 * The name of the Conversion Profile
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * System name of the Conversion Profile
	 * 
	 *
	 * @var string
	 */
	public $systemName = null;

	/**
	 * Comma separated tags
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * The description of the Conversion Profile
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * ID of the default entry to be used for template data
	 * 
	 *
	 * @var string
	 */
	public $defaultEntryId = null;

	/**
	 * Creation date as Unix timestamp (In seconds) 
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * List of included flavor ids (comma separated)
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsIds = null;

	/**
	 * Indicates that this conversion profile is system default
	 * 
	 *
	 * @var KalturaNullableBoolean
	 */
	public $isDefault = null;

	/**
	 * Indicates that this conversion profile is partner default
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $isPartnerDefault = null;

	/**
	 * Cropping dimensions
	 * DEPRECATED
	 *
	 * @var KalturaCropDimensions
	 */
	public $cropDimensions;

	/**
	 * Clipping start position (in miliseconds)
	 * DEPRECATED
	 *
	 * @var int
	 */
	public $clipStart = null;

	/**
	 * Clipping duration (in miliseconds)
	 * DEPRECATED
	 *
	 * @var int
	 */
	public $clipDuration = null;

	/**
	 * XSL to transform ingestion MRSS XML
	 * 
	 *
	 * @var string
	 */
	public $xslTransformation = null;

	/**
	 * ID of default storage profile to be used for linked net-storage file syncs
	 * 
	 *
	 * @var int
	 */
	public $storageProfileId = null;


}

class KalturaConversionProfileListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaConversionProfile
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaDataEntry extends KalturaBaseEntry
{
	/**
	 * The data of the entry
	 *
	 * @var string
	 */
	public $dataContent = null;

	/**
	 * indicator whether to return the object for get action with the dataContent field.
	 *
	 * @var bool
	 * @insertonly
	 */
	public $retrieveDataContentByGet = null;


}

abstract class KalturaDataEntryBaseFilter extends KalturaBaseEntryFilter
{

}

class KalturaDataEntryFilter extends KalturaDataEntryBaseFilter
{

}

class KalturaDataListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaDataEntry
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaConversionAttribute extends KalturaObjectBase
{
	/**
	 * The id of the flavor params, set to null for source flavor
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsId = null;

	/**
	 * Attribute name  
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * Attribute value  
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaEmailIngestionProfile extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $emailAddress = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $mailboxId = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $conversionProfile2Id = null;

	/**
	 * 
	 *
	 * @var KalturaEntryModerationStatus
	 */
	public $moderationStatus = null;

	/**
	 * 
	 *
	 * @var KalturaEmailIngestionProfileStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultCategory = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultUserId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultTags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $defaultAdminTags = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $maxAttachmentSizeKbytes = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $maxAttachmentsPerMail = null;


}

class KalturaPlayableEntry extends KalturaBaseEntry
{
	/**
	 * Number of plays
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $plays = null;

	/**
	 * Number of views
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $views = null;

	/**
	 * The width in pixels
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $width = null;

	/**
	 * The height in pixels
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $height = null;

	/**
	 * The duration in seconds
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $duration = null;

	/**
	 * The duration in miliseconds
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $msDuration = null;

	/**
	 * The duration type (short for 0-4 mins, medium for 4-20 mins, long for 20+ mins)
	 * 
	 *
	 * @var KalturaDurationType
	 * @readonly
	 */
	public $durationType = null;


}

class KalturaMediaEntry extends KalturaPlayableEntry
{
	/**
	 * The media type of the entry
	 * 
	 *
	 * @var KalturaMediaType
	 * @insertonly
	 */
	public $mediaType = null;

	/**
	 * Override the default conversion quality
	 * DEPRECATED - use conversionProfileId instead
	 *
	 * @var string
	 * @insertonly
	 */
	public $conversionQuality = null;

	/**
	 * The source type of the entry 
	 *
	 * @var KalturaSourceType
	 * @insertonly
	 */
	public $sourceType = null;

	/**
	 * The search provider type used to import this entry
	 *
	 * @var KalturaSearchProviderType
	 * @insertonly
	 */
	public $searchProviderType = null;

	/**
	 * The ID of the media in the importing site
	 *
	 * @var string
	 * @insertonly
	 */
	public $searchProviderId = null;

	/**
	 * The user name used for credits
	 *
	 * @var string
	 */
	public $creditUserName = null;

	/**
	 * The URL for credits
	 *
	 * @var string
	 */
	public $creditUrl = null;

	/**
	 * The media date extracted from EXIF data (For images) as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $mediaDate = null;

	/**
	 * The URL used for playback. This is not the download URL.
	 *
	 * @var string
	 * @readonly
	 */
	public $dataUrl = null;

	/**
	 * Comma separated flavor params ids that exists for this media entry
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $flavorParamsIds = null;


}

class KalturaAsset extends KalturaObjectBase
{
	/**
	 * The ID of the Flavor Asset
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * The entry ID of the Flavor Asset
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $entryId = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The status of the Flavor Asset
	 * 
	 *
	 * @var KalturaFlavorAssetStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * The version of the Flavor Asset
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $version = null;

	/**
	 * The size (in KBytes) of the Flavor Asset
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $size = null;

	/**
	 * Tags used to identify the Flavor Asset in various scenarios
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * The file extension
	 * 
	 *
	 * @var string
	 * @insertonly
	 */
	public $fileExt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $deletedAt = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $description = null;


}

class KalturaFlavorAsset extends KalturaAsset
{
	/**
	 * The Flavor Params used to create this Flavor Asset
	 * 
	 *
	 * @var int
	 * @insertonly
	 */
	public $flavorParamsId = null;

	/**
	 * The width of the Flavor Asset 
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $width = null;

	/**
	 * The height of the Flavor Asset
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $height = null;

	/**
	 * The overall bitrate (in KBits) of the Flavor Asset 
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $bitrate = null;

	/**
	 * The frame rate (in FPS) of the Flavor Asset
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $frameRate = null;

	/**
	 * True if this Flavor Asset is the original source
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $isOriginal = null;

	/**
	 * True if this Flavor Asset is playable in KDP
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $isWeb = null;

	/**
	 * The container format
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $containerFormat = null;

	/**
	 * The video codec
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $videoCodecId = null;


}

abstract class KalturaContentResource extends KalturaResource
{

}

abstract class KalturaAssetBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryIdIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var KalturaFlavorAssetStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusNotIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $sizeGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $sizeLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $deletedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $deletedAtLessThanOrEqual = null;


}

class KalturaAssetFilter extends KalturaAssetBaseFilter
{

}

class KalturaFlavorAssetListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaFlavorAsset
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaString extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaAssetParams extends KalturaObjectBase
{
	/**
	 * The id of the Flavor Params
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * The name of the Flavor Params
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * System name of the Flavor Params
	 * 
	 *
	 * @var string
	 */
	public $systemName = null;

	/**
	 * The description of the Flavor Params
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * True if those Flavor Params are part of system defaults
	 * 
	 *
	 * @var KalturaNullableBoolean
	 * @readonly
	 */
	public $isSystemDefault = null;

	/**
	 * The Flavor Params tags are used to identify the flavor for different usage (e.g. web, hd, mobile)
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * The container format of the Flavor Params
	 * 
	 *
	 * @var KalturaContainerFormat
	 */
	public $format = null;

	/**
	 * Array of partner permisison names that required for using this asset params
	 * 
	 *
	 * @var array of KalturaString
	 */
	public $requiredPermissions;


}

class KalturaFlavorParams extends KalturaAssetParams
{
	/**
	 * The video codec of the Flavor Params
	 * 
	 *
	 * @var KalturaVideoCodec
	 */
	public $videoCodec = null;

	/**
	 * The video bitrate (in KBits) of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $videoBitrate = null;

	/**
	 * The audio codec of the Flavor Params
	 * 
	 *
	 * @var KalturaAudioCodec
	 */
	public $audioCodec = null;

	/**
	 * The audio bitrate (in KBits) of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $audioBitrate = null;

	/**
	 * The number of audio channels for "downmixing"
	 * 
	 *
	 * @var int
	 */
	public $audioChannels = null;

	/**
	 * The audio sample rate of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $audioSampleRate = null;

	/**
	 * The desired width of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $width = null;

	/**
	 * The desired height of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $height = null;

	/**
	 * The frame rate of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $frameRate = null;

	/**
	 * The gop size of the Flavor Params
	 * 
	 *
	 * @var int
	 */
	public $gopSize = null;

	/**
	 * The list of conversion engines (comma separated)
	 * 
	 *
	 * @var string
	 */
	public $conversionEngines = null;

	/**
	 * The list of conversion engines extra params (separated with "|")
	 * 
	 *
	 * @var string
	 */
	public $conversionEnginesExtraParams = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $twoPass = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $deinterlice = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $rotate = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $operators = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $engineVersion = null;


}

class KalturaFlavorAssetWithParams extends KalturaObjectBase
{
	/**
	 * The Flavor Asset (Can be null when there are params without asset)
	 * 
	 *
	 * @var KalturaFlavorAsset
	 */
	public $flavorAsset;

	/**
	 * The Flavor Params
	 * 
	 *
	 * @var KalturaFlavorParams
	 */
	public $flavorParams;

	/**
	 * The entry id
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;


}

abstract class KalturaFlavorParamsBaseFilter extends KalturaAssetParamsFilter
{

}

class KalturaFlavorParamsFilter extends KalturaFlavorParamsBaseFilter
{

}

class KalturaFlavorParamsListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaFlavorParams
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaBatchJobResponse extends KalturaObjectBase
{
	/**
	 * The main batch job
	 * 
	 *
	 * @var KalturaBatchJob
	 */
	public $batchJob;

	/**
	 * All batch jobs that reference the main job as root
	 * 
	 *
	 * @var array of KalturaBatchJob
	 */
	public $childBatchJobs;


}

class KalturaMailJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var KalturaMailType
	 */
	public $mailType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mailPriority = null;

	/**
	 * 
	 *
	 * @var KalturaMailJobStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $recipientName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $recipientEmail = null;

	/**
	 * kuserId  
	 *
	 * @var int
	 */
	public $recipientId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fromName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fromEmail = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $bodyParams = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $subjectParams = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $templatePath = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $culture = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $campaignId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $minSendDate = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isHtml = null;


}

class KalturaBatchJobFilterExt extends KalturaBatchJobFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $jobTypeAndSubTypeIn = null;


}

class KalturaBatchJobListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaBatchJob
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaLiveStreamBitrate extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $bitrate = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $width = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $height = null;


}

class KalturaLiveStreamEntry extends KalturaMediaEntry
{
	/**
	 * The message to be presented when the stream is offline
	 * 
	 *
	 * @var string
	 */
	public $offlineMessage = null;

	/**
	 * The stream id as provided by the provider
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $streamRemoteId = null;

	/**
	 * The backup stream id as provided by the provider
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $streamRemoteBackupId = null;

	/**
	 * Array of supported bitrates
	 * 
	 *
	 * @var array of KalturaLiveStreamBitrate
	 */
	public $bitrates;

	/**
	 * 
	 *
	 * @var string
	 */
	public $primaryBroadcastingUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $secondaryBroadcastingUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $streamName = null;


}

class KalturaLiveStreamAdminEntry extends KalturaLiveStreamEntry
{
	/**
	 * The broadcast primary ip
	 * 
	 *
	 * @var string
	 */
	public $encodingIP1 = null;

	/**
	 * The broadcast secondary ip
	 * 
	 *
	 * @var string
	 */
	public $encodingIP2 = null;

	/**
	 * The broadcast password
	 * 
	 *
	 * @var string
	 */
	public $streamPassword = null;

	/**
	 * The broadcast username
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $streamUsername = null;


}

abstract class KalturaPlayableEntryBaseFilter extends KalturaBaseEntryFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $durationLessThan = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $durationGreaterThan = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $durationLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $durationGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $msDurationLessThan = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $msDurationGreaterThan = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $msDurationLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $msDurationGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $durationTypeMatchOr = null;


}

class KalturaPlayableEntryFilter extends KalturaPlayableEntryBaseFilter
{

}

abstract class KalturaMediaEntryBaseFilter extends KalturaPlayableEntryFilter
{
	/**
	 * 
	 *
	 * @var KalturaMediaType
	 */
	public $mediaTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $mediaTypeIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mediaDateGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mediaDateLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsIdsMatchOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsIdsMatchAnd = null;


}

class KalturaMediaEntryFilter extends KalturaMediaEntryBaseFilter
{

}

abstract class KalturaLiveStreamEntryBaseFilter extends KalturaMediaEntryFilter
{

}

class KalturaLiveStreamEntryFilter extends KalturaLiveStreamEntryBaseFilter
{

}

class KalturaLiveStreamListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaLiveStreamEntry
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaSearch extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $keyWords = null;

	/**
	 * 
	 *
	 * @var KalturaSearchProviderType
	 */
	public $searchSource = null;

	/**
	 * 
	 *
	 * @var KalturaMediaType
	 */
	public $mediaType = null;

	/**
	 * Use this field to pass dynamic data for searching
	 * For example - if you set this field to "mymovies_$partner_id"
	 * The $partner_id will be automatically replcaed with your real partner Id
	 * 
	 *
	 * @var string
	 */
	public $extraData = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $authData = null;


}

class KalturaSearchResult extends KalturaSearch
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $url = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sourceLink = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $credit = null;

	/**
	 * 
	 *
	 * @var KalturaLicenseType
	 */
	public $licenseType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flashPlaybackType = null;


}

class KalturaMediaListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaMediaEntry
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaMixEntry extends KalturaPlayableEntry
{
	/**
	 * Indicates whether the user has submited a real thumbnail to the mix (Not the one that was generated automaticaly)
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $hasRealThumbnail = null;

	/**
	 * The editor type used to edit the metadata
	 * 
	 *
	 * @var KalturaEditorType
	 */
	public $editorType = null;

	/**
	 * The xml data of the mix
	 *
	 * @var string
	 */
	public $dataContent = null;


}

abstract class KalturaMixEntryBaseFilter extends KalturaPlayableEntryFilter
{

}

class KalturaMixEntryFilter extends KalturaMixEntryBaseFilter
{

}

class KalturaMixListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaMixEntry
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaClientNotification extends KalturaObjectBase
{
	/**
	 * The URL where the notification should be sent to 
	 *
	 * @var string
	 */
	public $url = null;

	/**
	 * The serialized notification data to send
	 *
	 * @var string
	 */
	public $data = null;


}

class KalturaPartnerUsage extends KalturaObjectBase
{
	/**
	 * Partner total hosting in GB on the disk
	 * 
	 *
	 * @var float
	 * @readonly
	 */
	public $hostingGB = null;

	/**
	 * percent of usage out of partner's package. if usageGB is 5 and package is 10GB, this value will be 50
	 * 
	 *
	 * @var float
	 * @readonly
	 */
	public $Percent = null;

	/**
	 * package total BW - actually this is usage, which represents BW+storage
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $packageBW = null;

	/**
	 * total usage in GB - including bandwidth and storage
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $usageGB = null;

	/**
	 * date when partner reached the limit of his package (timestamp)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $reachedLimitDate = null;

	/**
	 * a semi-colon separated list of comma-separated key-values to represent a usage graph.
	 * keys could be 1-12 for a year view (1,1.2;2,1.1;3,0.9;...;12,1.4;)
	 * keys could be 1-[28,29,30,31] depending on the requested month, for a daily view in a given month (1,0.4;2,0.2;...;31,0.1;)
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $usageGraph = null;


}

abstract class KalturaPermissionItem extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionItemType
	 * @readonly
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;


}

abstract class KalturaPermissionItemBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionItemType
	 */
	public $typeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $typeIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;


}

class KalturaPermissionItemFilter extends KalturaPermissionItemBaseFilter
{

}

class KalturaPermissionItemListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaPermissionItem
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaPermission extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionType
	 * @readonly
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $friendlyName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $dependsOnPermissionNames = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $permissionItemsIds = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerGroup = null;


}

abstract class KalturaPermissionBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionType
	 */
	public $typeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $typeIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $friendlyNameLike = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $descriptionLike = null;

	/**
	 * 
	 *
	 * @var KalturaPermissionStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $dependsOnPermissionNamesMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $dependsOnPermissionNamesMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;


}

class KalturaPermissionFilter extends KalturaPermissionBaseFilter
{

}

class KalturaPermissionListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaPermission
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaMediaEntryFilterForPlaylist extends KalturaMediaEntryFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $limit = null;


}

class KalturaPlaylist extends KalturaBaseEntry
{
	/**
	 * Content of the playlist - 
	 * XML if the playlistType is dynamic 
	 * text if the playlistType is static 
	 * url if the playlistType is mRss 
	 *
	 * @var string
	 */
	public $playlistContent = null;

	/**
	 * 
	 *
	 * @var array of KalturaMediaEntryFilterForPlaylist
	 */
	public $filters;

	/**
	 * 
	 *
	 * @var int
	 */
	public $totalResults = null;

	/**
	 * Type of playlist  
	 *
	 * @var KalturaPlaylistType
	 */
	public $playlistType = null;

	/**
	 * Number of plays
	 *
	 * @var int
	 * @readonly
	 */
	public $plays = null;

	/**
	 * Number of views
	 *
	 * @var int
	 * @readonly
	 */
	public $views = null;

	/**
	 * The duration in seconds
	 *
	 * @var int
	 * @readonly
	 */
	public $duration = null;


}

abstract class KalturaPlaylistBaseFilter extends KalturaBaseEntryFilter
{

}

class KalturaPlaylistFilter extends KalturaPlaylistBaseFilter
{

}

class KalturaPlaylistListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaPlaylist
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaReportInputFilter extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $fromDate = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $toDate = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $keywords = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $searchInTags = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $searchInAdminTags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categories = null;

	/**
	 * time zone offset in minutes
	 *
	 * @var int
	 */
	public $timeZoneOffset = null;


}

class KalturaReportGraph extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $data = null;


}

class KalturaReportTotal extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $header = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $data = null;


}

class KalturaReportTable extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $header = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $data = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaSearchResultResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaSearchResult
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var bool
	 * @readonly
	 */
	public $needMediaInfo = null;


}

class KalturaSearchAuthData extends KalturaObjectBase
{
	/**
	 * The authentication data that further should be used for search
	 * 
	 *
	 * @var string
	 */
	public $authData = null;

	/**
	 * Login URL when user need to sign-in and authorize the search
	 *
	 * @var string
	 */
	public $loginUrl = null;

	/**
	 * Information when there was an error
	 *
	 * @var string
	 */
	public $message = null;


}

class KalturaStartWidgetSessionResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $ks = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $userId = null;


}

class KalturaStatsEvent extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $clientVer = null;

	/**
	 * 
	 *
	 * @var KalturaStatsEventType
	 */
	public $eventType = null;

	/**
	 * the client's timestamp of this event
	 * 
	 *
	 * @var float
	 */
	public $eventTimestamp = null;

	/**
	 * a unique string generated by the client that will represent the client-side session: the primary component will pass it on to other components that sprout from it
	 *
	 * @var string
	 */
	public $sessionId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * the UV cookie - creates in the operational system and should be passed on ofr every event 
	 *
	 * @var string
	 */
	public $uniqueViewer = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $widgetId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $uiconfId = null;

	/**
	 * the partner's user id 
	 *
	 * @var string
	 */
	public $userId = null;

	/**
	 * the timestamp along the video when the event happend 
	 *
	 * @var int
	 */
	public $currentPoint = null;

	/**
	 * the duration of the video in milliseconds - will make it much faster than quering the db for each entry 
	 *
	 * @var int
	 */
	public $duration = null;

	/**
	 * will be retrieved from the request of the user 
	 *
	 * @var string
	 * @readonly
	 */
	public $userIp = null;

	/**
	 * the time in milliseconds the event took
	 *
	 * @var int
	 */
	public $processDuration = null;

	/**
	 * the id of the GUI control - will be used in the future to better understand what the user clicked
	 *
	 * @var string
	 */
	public $controlId = null;

	/**
	 * true if the user ever used seek in this session 
	 *
	 * @var bool
	 */
	public $seek = null;

	/**
	 * timestamp of the new point on the timeline of the video after the user seeks 
	 *
	 * @var int
	 */
	public $newPoint = null;

	/**
	 * the referrer of the client
	 *
	 * @var string
	 */
	public $referrer = null;

	/**
	 * will indicate if the event is thrown for the first video in the session
	 *
	 * @var bool
	 */
	public $isFirstInSession = null;


}

class KalturaStatsKmcEvent extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $clientVer = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $kmcEventActionPath = null;

	/**
	 * 
	 *
	 * @var KalturaStatsKmcEventType
	 */
	public $kmcEventType = null;

	/**
	 * the client's timestamp of this event
	 * 
	 *
	 * @var float
	 */
	public $eventTimestamp = null;

	/**
	 * a unique string generated by the client that will represent the client-side session: the primary component will pass it on to other components that sprout from it
	 *
	 * @var string
	 */
	public $sessionId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $widgetId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $uiconfId = null;

	/**
	 * the partner's user id 
	 *
	 * @var string
	 */
	public $userId = null;

	/**
	 * will be retrieved from the request of the user 
	 *
	 * @var string
	 * @readonly
	 */
	public $userIp = null;


}

class KalturaCEError extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $browser = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $serverIp = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $serverOs = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $phpVersion = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $ceAdminEmail = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $data = null;


}

abstract class KalturaStorageProfileBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemNameIn = null;

	/**
	 * 
	 *
	 * @var KalturaStorageProfileStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var KalturaStorageProfileProtocol
	 */
	public $protocolEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $protocolIn = null;


}

class KalturaStorageProfileFilter extends KalturaStorageProfileBaseFilter
{

}

class KalturaStorageProfile extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $systemName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $desciption = null;

	/**
	 * 
	 *
	 * @var KalturaStorageProfileStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var KalturaStorageProfileProtocol
	 */
	public $protocol = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $storageUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $storageBaseDir = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $storageUsername = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $storagePassword = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $storageFtpPassiveMode = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $deliveryHttpBaseUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $deliveryRmpBaseUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $deliveryIisBaseUrl = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $minFileSize = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $maxFileSize = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsIds = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $maxConcurrentConnections = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $pathManagerClass = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $urlManagerClass = null;

	/**
	 * No need to create enum for temp field
	 * 
	 *
	 * @var int
	 */
	public $trigger = null;


}

class KalturaStorageProfileListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaStorageProfile
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaBaseSyndicationFeed extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $feedUrl = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * link a playlist that will set what content the feed will include
	 * if empty, all content will be included in feed
	 * 
	 *
	 * @var string
	 */
	public $playlistId = null;

	/**
	 * feed name
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * feed status
	 * 
	 *
	 * @var KalturaSyndicationFeedStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * feed type
	 * 
	 *
	 * @var KalturaSyndicationFeedType
	 * @insertonly
	 */
	public $type = null;

	/**
	 * Base URL for each video, on the partners site
	 * This is required by all syndication types.
	 *
	 * @var string
	 */
	public $landingPage = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * allow_embed tells google OR yahoo weather to allow embedding the video on google OR yahoo video results
	 * or just to provide a link to the landing page.
	 * it is applied on the video-player_loc property in the XML (google)
	 * and addes media-player tag (yahoo)
	 *
	 * @var bool
	 */
	public $allowEmbed = null;

	/**
	 * Select a uiconf ID as player skin to include in the kwidget url
	 *
	 * @var int
	 */
	public $playerUiconfId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $flavorParamId = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $transcodeExistingContent = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $addToDefaultConversionProfile = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $categories = null;


}

abstract class KalturaBaseSyndicationFeedBaseFilter extends KalturaFilter
{

}

class KalturaBaseSyndicationFeedFilter extends KalturaBaseSyndicationFeedBaseFilter
{

}

class KalturaBaseSyndicationFeedListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaBaseSyndicationFeed
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaSyndicationFeedEntryCount extends KalturaObjectBase
{
	/**
	 * the total count of entries that should appear in the feed without flavor filtering
	 *
	 * @var int
	 */
	public $totalEntryCount = null;

	/**
	 * count of entries that will appear in the feed (including all relevant filters)
	 *
	 * @var int
	 */
	public $actualEntryCount = null;

	/**
	 * count of entries that requires transcoding in order to be included in feed
	 *
	 * @var int
	 */
	public $requireTranscodingCount = null;


}

class KalturaThumbAsset extends KalturaAsset
{
	/**
	 * The Flavor Params used to create this Flavor Asset
	 * 
	 *
	 * @var int
	 * @insertonly
	 */
	public $thumbParamsId = null;

	/**
	 * The width of the Flavor Asset 
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $width = null;

	/**
	 * The height of the Flavor Asset
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $height = null;


}

class KalturaThumbParams extends KalturaAssetParams
{
	/**
	 * 
	 *
	 * @var KalturaThumbCropType
	 */
	public $cropType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $quality = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $cropX = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $cropY = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $cropWidth = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $cropHeight = null;

	/**
	 * 
	 *
	 * @var float
	 */
	public $videoOffset = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $width = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $height = null;

	/**
	 * 
	 *
	 * @var float
	 */
	public $scaleWidth = null;

	/**
	 * 
	 *
	 * @var float
	 */
	public $scaleHeight = null;

	/**
	 * Hexadecimal value
	 *
	 * @var string
	 */
	public $backgroundColor = null;

	/**
	 * Id of the flavor params or the thumbnail params to be used as source for the thumbnail creation
	 *
	 * @var int
	 */
	public $sourceParamsId = null;


}

class KalturaThumbAssetListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaThumbAsset
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaThumbParamsBaseFilter extends KalturaAssetParamsFilter
{

}

class KalturaThumbParamsFilter extends KalturaThumbParamsBaseFilter
{

}

class KalturaThumbParamsListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaThumbParams
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaUiConf extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * Name of the uiConf, this is not a primary key
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var KalturaUiConfObjType
	 */
	public $objType = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $objTypeAsString = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $width = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $height = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $htmlParams = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $swfUrl = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $confFilePath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $confFile = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $confFileFeatures = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $confVars = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $useCdn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $swfUrlVersion = null;

	/**
	 * Entry creation date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Entry creation date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * 
	 *
	 * @var KalturaUiConfCreationMode
	 */
	public $creationMode = null;


}

abstract class KalturaUiConfBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameLike = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var KalturaUiConfObjType
	 */
	public $objTypeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $objTypeIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var KalturaUiConfCreationMode
	 */
	public $creationModeEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $creationModeIn = null;


}

class KalturaUiConfFilter extends KalturaUiConfBaseFilter
{

}

class KalturaUiConfListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaUiConf
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaUiConfTypeInfo extends KalturaObjectBase
{
	/**
	 * UiConf Type
	 * 
	 *
	 * @var KalturaUiConfObjType
	 */
	public $type = null;

	/**
	 * Available versions
	 * 
	 *
	 * @var array of KalturaString
	 */
	public $versions;

	/**
	 * The direcotry this type is saved at
	 * 
	 *
	 * @var string
	 */
	public $directory = null;

	/**
	 * Filename for this UiConf type
	 * 
	 *
	 * @var string
	 */
	public $filename = null;


}

class KalturaUploadResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $uploadTokenId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $fileSize = null;

	/**
	 * 
	 *
	 * @var KalturaUploadErrorCode
	 */
	public $errorCode = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $errorDescription = null;


}

class KalturaUploadToken extends KalturaObjectBase
{
	/**
	 * Upload token unique ID
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * Partner ID of the upload token
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * User id for the upload token
	 *
	 * @var string
	 * @readonly
	 */
	public $userId = null;

	/**
	 * Status of the upload token
	 *
	 * @var KalturaUploadTokenStatus
	 * @readonly
	 */
	public $status = null;

	/**
	 * Name of the file for the upload token, can be empty when the upload token is created and will be updated internally after the file is uploaded
	 *
	 * @var string
	 * @insertonly
	 */
	public $fileName = null;

	/**
	 * File size in bytes, can be empty when the upload token is created and will be updated internally after the file is uploaded
	 *
	 * @var float
	 * @insertonly
	 */
	public $fileSize = null;

	/**
	 * Uploaded file size in bytes, can be used to identify how many bytes were uploaded before resuming
	 *
	 * @var float
	 * @readonly
	 */
	public $uploadedFileSize = null;

	/**
	 * Creation date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * Last update date as Unix timestamp (In seconds)
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;


}

abstract class KalturaUploadTokenBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $userIdEqual = null;

	/**
	 * 
	 *
	 * @var KalturaUploadTokenStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;


}

class KalturaUploadTokenFilter extends KalturaUploadTokenBaseFilter
{

}

class KalturaUploadTokenListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaUploadToken
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaUserRole extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $name = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $description = null;

	/**
	 * 
	 *
	 * @var KalturaUserRoleStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $permissionNames = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tags = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;


}

abstract class KalturaUserRoleBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $descriptionLike = null;

	/**
	 * 
	 *
	 * @var KalturaUserRoleStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerIdIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;


}

class KalturaUserRoleFilter extends KalturaUserRoleBaseFilter
{

}

class KalturaUserRoleListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaUserRole
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaUserBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $screenNameLike = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $screenNameStartsWith = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $emailLike = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $emailStartsWith = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $tagsMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var KalturaUserStatus
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $isAdminEqual = null;


}

class KalturaUserFilter extends KalturaUserBaseFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $loginEnabledEqual = null;


}

class KalturaUserListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaUser
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

class KalturaWidget extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $id = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sourceWidgetId = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $rootWidgetId = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $partnerId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $uiConfId = null;

	/**
	 * 
	 *
	 * @var KalturaWidgetSecurityType
	 */
	public $securityType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $securityPolicy = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $createdAt = null;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $updatedAt = null;

	/**
	 * Can be used to store various partner related data as a string 
	 *
	 * @var string
	 */
	public $partnerData = null;

	/**
	 * 
	 *
	 * @var string
	 * @readonly
	 */
	public $widgetHTML = null;


}

abstract class KalturaWidgetBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $sourceWidgetIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rootWidgetIdEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $entryIdEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $uiConfIdEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $createdAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $updatedAtLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerDataLike = null;


}

class KalturaWidgetFilter extends KalturaWidgetBaseFilter
{

}

class KalturaWidgetListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaWidget
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaPartnerBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $idEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $idIn = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameLike = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameMultiLikeOr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameMultiLikeAnd = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $nameEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $statusEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $statusIn = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerPackageEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerPackageGreaterThanOrEqual = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $partnerPackageLessThanOrEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $partnerNameDescriptionWebsiteAdminNameAdminEmailLike = null;


}

class KalturaPartnerFilter extends KalturaPartnerBaseFilter
{

}

class KalturaPartnerListResponse extends KalturaObjectBase
{
	/**
	 * 
	 *
	 * @var array of KalturaPartner
	 * @readonly
	 */
	public $objects;

	/**
	 * 
	 *
	 * @var int
	 * @readonly
	 */
	public $totalCount = null;


}

abstract class KalturaFlavorParamsOutputBaseFilter extends KalturaFlavorParamsFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsVersionEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetVersionEqual = null;


}

class KalturaFlavorParamsOutputFilter extends KalturaFlavorParamsOutputBaseFilter
{

}

class KalturaFlavorParamsOutput extends KalturaFlavorParams
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $commandLinesStr = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorParamsVersion = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetVersion = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $readyBehavior = null;


}

abstract class KalturaThumbParamsOutputBaseFilter extends KalturaThumbParamsFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $thumbParamsIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbParamsVersionEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetVersionEqual = null;


}

class KalturaThumbParamsOutputFilter extends KalturaThumbParamsOutputBaseFilter
{

}

class KalturaThumbParamsOutput extends KalturaThumbParams
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $thumbParamsId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbParamsVersion = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetVersion = null;


}

abstract class KalturaMediaInfoBaseFilter extends KalturaFilter
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetIdEqual = null;


}

class KalturaMediaInfoFilter extends KalturaMediaInfoBaseFilter
{

}

class KalturaCountryRestriction extends KalturaBaseRestriction
{
	/**
	 * Country restriction type (Allow or deny)
	 * 
	 *
	 * @var KalturaCountryRestrictionType
	 */
	public $countryRestrictionType = null;

	/**
	 * Comma separated list of country codes to allow to deny 
	 * 
	 *
	 * @var string
	 */
	public $countryList = null;


}

class KalturaDirectoryRestriction extends KalturaBaseRestriction
{
	/**
	 * Kaltura directory restriction type
	 * 
	 *
	 * @var KalturaDirectoryRestrictionType
	 */
	public $directoryRestrictionType = null;


}

class KalturaIpAddressRestriction extends KalturaBaseRestriction
{
	/**
	 * Ip address restriction type (Allow or deny)
	 * 
	 *
	 * @var KalturaIpAddressRestrictionType
	 */
	public $ipAddressRestrictionType = null;

	/**
	 * Comma separated list of ip address to allow to deny 
	 * 
	 *
	 * @var string
	 */
	public $ipAddressList = null;


}

class KalturaSessionRestriction extends KalturaBaseRestriction
{

}

class KalturaPreviewRestriction extends KalturaSessionRestriction
{
	/**
	 * The preview restriction length 
	 * 
	 *
	 * @var int
	 */
	public $previewLength = null;


}

class KalturaSiteRestriction extends KalturaBaseRestriction
{
	/**
	 * The site restriction type (allow or deny)
	 * 
	 *
	 * @var KalturaSiteRestrictionType
	 */
	public $siteRestrictionType = null;

	/**
	 * Comma separated list of sites (domains) to allow or deny
	 * 
	 *
	 * @var string
	 */
	public $siteList = null;


}

class KalturaSearchCondition extends KalturaSearchItem
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $field = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $value = null;


}

class KalturaSearchComparableCondition extends KalturaSearchCondition
{
	/**
	 * 
	 *
	 * @var KalturaSearchConditionComparison
	 */
	public $comparison = null;


}

class KalturaSearchOperator extends KalturaSearchItem
{
	/**
	 * 
	 *
	 * @var KalturaSearchOperatorType
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var array of KalturaSearchItem
	 */
	public $items;


}

abstract class KalturaMailJobBaseFilter extends KalturaBaseJobFilter
{

}

class KalturaMailJobFilter extends KalturaMailJobBaseFilter
{

}

abstract class KalturaNotificationBaseFilter extends KalturaBaseJobFilter
{

}

class KalturaNotificationFilter extends KalturaNotificationBaseFilter
{

}

abstract class KalturaAssetParamsOutputBaseFilter extends KalturaAssetParamsFilter
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $assetParamsIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetParamsVersionEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetIdEqual = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetVersionEqual = null;


}

class KalturaAssetParamsOutputFilter extends KalturaAssetParamsOutputBaseFilter
{

}

abstract class KalturaFlavorAssetBaseFilter extends KalturaAssetFilter
{

}

class KalturaFlavorAssetFilter extends KalturaFlavorAssetBaseFilter
{

}

abstract class KalturaMediaFlavorParamsBaseFilter extends KalturaFlavorParamsFilter
{

}

class KalturaMediaFlavorParamsFilter extends KalturaMediaFlavorParamsBaseFilter
{

}

abstract class KalturaMediaFlavorParamsOutputBaseFilter extends KalturaFlavorParamsOutputFilter
{

}

class KalturaMediaFlavorParamsOutputFilter extends KalturaMediaFlavorParamsOutputBaseFilter
{

}

abstract class KalturaThumbAssetBaseFilter extends KalturaAssetFilter
{

}

class KalturaThumbAssetFilter extends KalturaThumbAssetBaseFilter
{

}

abstract class KalturaLiveStreamAdminEntryBaseFilter extends KalturaLiveStreamEntryFilter
{

}

class KalturaLiveStreamAdminEntryFilter extends KalturaLiveStreamAdminEntryBaseFilter
{

}

abstract class KalturaAdminUserBaseFilter extends KalturaUserFilter
{

}

class KalturaAdminUserFilter extends KalturaAdminUserBaseFilter
{

}

abstract class KalturaGoogleVideoSyndicationFeedBaseFilter extends KalturaBaseSyndicationFeedFilter
{

}

class KalturaGoogleVideoSyndicationFeedFilter extends KalturaGoogleVideoSyndicationFeedBaseFilter
{

}

abstract class KalturaITunesSyndicationFeedBaseFilter extends KalturaBaseSyndicationFeedFilter
{

}

class KalturaITunesSyndicationFeedFilter extends KalturaITunesSyndicationFeedBaseFilter
{

}

abstract class KalturaTubeMogulSyndicationFeedBaseFilter extends KalturaBaseSyndicationFeedFilter
{

}

class KalturaTubeMogulSyndicationFeedFilter extends KalturaTubeMogulSyndicationFeedBaseFilter
{

}

abstract class KalturaYahooSyndicationFeedBaseFilter extends KalturaBaseSyndicationFeedFilter
{

}

class KalturaYahooSyndicationFeedFilter extends KalturaYahooSyndicationFeedBaseFilter
{

}

abstract class KalturaApiActionPermissionItemBaseFilter extends KalturaPermissionItemFilter
{

}

class KalturaApiActionPermissionItemFilter extends KalturaApiActionPermissionItemBaseFilter
{

}

abstract class KalturaApiParameterPermissionItemBaseFilter extends KalturaPermissionItemFilter
{

}

class KalturaApiParameterPermissionItemFilter extends KalturaApiParameterPermissionItemBaseFilter
{

}

abstract class KalturaGenericSyndicationFeedBaseFilter extends KalturaBaseSyndicationFeedFilter
{

}

class KalturaGenericSyndicationFeedFilter extends KalturaGenericSyndicationFeedBaseFilter
{

}

abstract class KalturaGenericXsltSyndicationFeedBaseFilter extends KalturaGenericSyndicationFeedFilter
{

}

class KalturaGenericXsltSyndicationFeedFilter extends KalturaGenericXsltSyndicationFeedBaseFilter
{

}

class KalturaClipAttributes extends KalturaOperationAttributes
{
	/**
	 * Offset in milliseconds
	 *
	 * @var int
	 */
	public $offset = null;

	/**
	 * Duration in milliseconds
	 *
	 * @var int
	 */
	public $duration = null;


}

class KalturaAssetParamsResourceContainer extends KalturaResource
{
	/**
	 * The content resource to associate with asset params
	 *
	 * @var KalturaContentResource
	 */
	public $resource;

	/**
	 * The asset params to associate with the reaource
	 *
	 * @var int
	 */
	public $assetParamsId = null;


}

class KalturaAssetResource extends KalturaContentResource
{
	/**
	 * ID of the source asset 
	 *
	 * @var string
	 */
	public $assetId = null;


}

class KalturaAssetsParamsResourceContainers extends KalturaResource
{
	/**
	 * Array of resources associated with asset params ids
	 *
	 * @var array of KalturaAssetParamsResourceContainer
	 */
	public $resources;


}

abstract class KalturaDataCenterContentResource extends KalturaContentResource
{

}

class KalturaEntryResource extends KalturaContentResource
{
	/**
	 * ID of the source entry 
	 *
	 * @var string
	 */
	public $entryId = null;

	/**
	 * ID of the source flavor params, set to null to use the source flavor
	 *
	 * @var int
	 */
	public $flavorParamsId = null;


}

class KalturaFileSyncResource extends KalturaContentResource
{
	/**
	 * The object type of the file sync object 
	 *
	 * @var int
	 */
	public $fileSyncObjectType = null;

	/**
	 * The object sub-type of the file sync object 
	 *
	 * @var int
	 */
	public $objectSubType = null;

	/**
	 * The object id of the file sync object 
	 *
	 * @var string
	 */
	public $objectId = null;

	/**
	 * The version of the file sync object 
	 *
	 * @var string
	 */
	public $version = null;


}

class KalturaLocalFileResource extends KalturaDataCenterContentResource
{
	/**
	 * Full path to the local file 
	 *
	 * @var string
	 */
	public $localFilePath = null;


}

class KalturaOperationResource extends KalturaContentResource
{
	/**
	 * 
	 *
	 * @var KalturaContentResource
	 */
	public $resource;

	/**
	 * 
	 *
	 * @var array of KalturaOperationAttributes
	 */
	public $operationAttributes;

	/**
	 * ID of alternative asset params to be used instead of the system default flavor params 
	 *
	 * @var int
	 */
	public $assetParamsId = null;


}

class KalturaUrlResource extends KalturaContentResource
{
	/**
	 * Remote URL, FTP, HTTP or HTTPS 
	 *
	 * @var string
	 */
	public $url = null;


}

class KalturaRemoteStorageResource extends KalturaUrlResource
{
	/**
	 * ID of storage profile to be associated with the created file sync, used for file serving URL composing. 
	 *
	 * @var int
	 */
	public $storageProfileId = null;


}

class KalturaUploadedFileTokenResource extends KalturaDataCenterContentResource
{
	/**
	 * Token that returned from upload.upload action or uploadToken.add action. 
	 *
	 * @var string
	 */
	public $token = null;


}

class KalturaWebcamTokenResource extends KalturaDataCenterContentResource
{
	/**
	 * Token that returned from media server such as FMS or red5. 
	 *
	 * @var string
	 */
	public $token = null;


}

class KalturaMailJob extends KalturaBaseJob
{
	/**
	 * 
	 *
	 * @var KalturaMailType
	 */
	public $mailType = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mailPriority = null;

	/**
	 * 
	 *
	 * @var KalturaMailJobStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $recipientName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $recipientEmail = null;

	/**
	 * kuserId  
	 *
	 * @var int
	 */
	public $recipientId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fromName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $fromEmail = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $bodyParams = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $subjectParams = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $templatePath = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $culture = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $campaignId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $minSendDate = null;


}

class KalturaBulkDownloadJobData extends KalturaJobData
{
	/**
	 * Comma separated list of entry ids
	 * 
	 *
	 * @var string
	 */
	public $entryIds = null;

	/**
	 * Flavor params id to use for conversion
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsId = null;

	/**
	 * The id of the requesting user
	 * 
	 *
	 * @var string
	 */
	public $puserId = null;


}

class KalturaBulkUploadJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $userId = null;

	/**
	 * The screen name of the user
	 * 
	 *
	 * @var string
	 */
	public $uploadedBy = null;

	/**
	 * Selected profile id for all bulk entries
	 * 
	 *
	 * @var int
	 */
	public $conversionProfileId = null;

	/**
	 * Created by the API
	 * 
	 *
	 * @var string
	 */
	public $resultsFileLocalPath = null;

	/**
	 * Created by the API
	 * 
	 *
	 * @var string
	 */
	public $resultsFileUrl = null;

	/**
	 * Number of created entries
	 * 
	 *
	 * @var int
	 */
	public $numOfEntries = null;

	/**
	 * The bulk upload file path
	 *
	 * @var string
	 */
	public $filePath = null;


}

class KalturaCaptureThumbJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncLocalPath = null;

	/**
	 * The translated path as used by the scheduler
	 *
	 * @var string
	 */
	public $actualSrcFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncRemoteUrl = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $thumbParamsOutputId = null;

	/**
	 * 
	 *
	 * @var KalturaThumbParamsOutput
	 */
	public $thumbParamsOutput;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbAssetId = null;

	/**
	 * 
	 *
	 * @var KalturaAssetType
	 */
	public $srcAssetType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $thumbPath = null;


}

class KalturaConvartableJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncLocalPath = null;

	/**
	 * The translated path as used by the scheduler
	 *
	 * @var string
	 */
	public $actualSrcFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncRemoteUrl = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $engineVersion = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $flavorParamsOutputId = null;

	/**
	 * 
	 *
	 * @var KalturaFlavorParamsOutput
	 */
	public $flavorParamsOutput;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mediaInfoId = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $currentOperationSet = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $currentOperationIndex = null;


}

class KalturaConvertCollectionJobData extends KalturaConvartableJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $destDirLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destDirRemoteUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileName = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $inputXmlLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $inputXmlRemoteUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $commandLinesStr = null;

	/**
	 * 
	 *
	 * @var array of KalturaConvertCollectionFlavorData
	 */
	public $flavors;


}

class KalturaConvertJobData extends KalturaConvartableJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileSyncRemoteUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $logFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $remoteMediaId = null;


}

class KalturaConvertProfileJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $inputFileSyncLocalPath = null;

	/**
	 * The height of last created thumbnail, will be used to comapare if this thumbnail is the best we can have
	 * 
	 *
	 * @var int
	 */
	public $thumbHeight = null;

	/**
	 * The bit rate of last created thumbnail, will be used to comapare if this thumbnail is the best we can have
	 * 
	 *
	 * @var int
	 */
	public $thumbBitrate = null;


}

class KalturaExtractMediaJobData extends KalturaConvartableJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;


}

class KalturaFlattenJobData extends KalturaJobData
{

}

class KalturaImportJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;


}

class KalturaNotificationJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $userId = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationType
	 */
	public $type = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $typeAsString = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $objectId = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationStatus
	 */
	public $status = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $data = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $numberOfAttempts = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $notificationResult = null;

	/**
	 * 
	 *
	 * @var KalturaNotificationObjectType
	 */
	public $objType = null;


}

class KalturaPostConvertJobData extends KalturaConvartableJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $flavorAssetId = null;

	/**
	 * Indicates if a thumbnail should be created
	 * 
	 *
	 * @var bool
	 */
	public $createThumb = null;

	/**
	 * The path of the created thumbnail
	 * 
	 *
	 * @var string
	 */
	public $thumbPath = null;

	/**
	 * The position of the thumbnail in the media file
	 * 
	 *
	 * @var int
	 */
	public $thumbOffset = null;

	/**
	 * The height of the movie, will be used to comapare if this thumbnail is the best we can have
	 * 
	 *
	 * @var int
	 */
	public $thumbHeight = null;

	/**
	 * The bit rate of the movie, will be used to comapare if this thumbnail is the best we can have
	 * 
	 *
	 * @var int
	 */
	public $thumbBitrate = null;


}

class KalturaProvisionJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $streamID = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $backupStreamID = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $rtmp = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $encoderIP = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $backupEncoderIP = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $encoderPassword = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $encoderUsername = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $endDate = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $returnVal = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $mediaType = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $primaryBroadcastingUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $secondaryBroadcastingUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $streamName = null;


}

class KalturaPullJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileLocalPath = null;


}

class KalturaRemoteConvertJobData extends KalturaConvartableJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileUrl = null;

	/**
	 * Should be set by the API
	 * 
	 *
	 * @var string
	 */
	public $destFileUrl = null;


}

class KalturaStorageJobData extends KalturaJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $serverUrl = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $serverUsername = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $serverPassword = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $ftpPassiveMode = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncLocalPath = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $srcFileSyncId = null;


}

class KalturaStorageDeleteJobData extends KalturaStorageJobData
{

}

class KalturaStorageExportJobData extends KalturaStorageJobData
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $destFileSyncStoredPath = null;

	/**
	 * 
	 *
	 * @var bool
	 */
	public $force = null;


}

class KalturaAssetParamsOutput extends KalturaAssetParams
{
	/**
	 * 
	 *
	 * @var int
	 */
	public $assetParamsId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetParamsVersion = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetId = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $assetVersion = null;

	/**
	 * 
	 *
	 * @var int
	 */
	public $readyBehavior = null;


}

class KalturaMediaFlavorParams extends KalturaFlavorParams
{

}

class KalturaMediaFlavorParamsOutput extends KalturaFlavorParamsOutput
{

}

class KalturaApiActionPermissionItem extends KalturaPermissionItem
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $service = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $action = null;


}

class KalturaApiParameterPermissionItem extends KalturaPermissionItem
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $object = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $parameter = null;

	/**
	 * 
	 *
	 * @var KalturaApiParameterPermissionItemAction
	 */
	public $action = null;


}

class KalturaGenericSyndicationFeed extends KalturaBaseSyndicationFeed
{
	/**
	 * feed description
	 * 
	 *
	 * @var string
	 */
	public $feedDescription = null;

	/**
	 * feed landing page (i.e publisher website)
	 * 
	 *
	 * @var string
	 */
	public $feedLandingPage = null;


}

class KalturaGenericXsltSyndicationFeed extends KalturaGenericSyndicationFeed
{
	/**
	 * 
	 *
	 * @var string
	 */
	public $xslt = null;


}

class KalturaGoogleVideoSyndicationFeed extends KalturaBaseSyndicationFeed
{
	/**
	 * 
	 *
	 * @var KalturaGoogleSyndicationFeedAdultValues
	 */
	public $adultContent = null;


}

class KalturaITunesSyndicationFeed extends KalturaBaseSyndicationFeed
{
	/**
	 * feed description
	 * 
	 *
	 * @var string
	 */
	public $feedDescription = null;

	/**
	 * feed language
	 * 
	 *
	 * @var string
	 */
	public $language = null;

	/**
	 * feed landing page (i.e publisher website)
	 * 
	 *
	 * @var string
	 */
	public $feedLandingPage = null;

	/**
	 * author/publisher name
	 * 
	 *
	 * @var string
	 */
	public $ownerName = null;

	/**
	 * publisher email
	 * 
	 *
	 * @var string
	 */
	public $ownerEmail = null;

	/**
	 * podcast thumbnail
	 * 
	 *
	 * @var string
	 */
	public $feedImageUrl = null;

	/**
	 * 
	 *
	 * @var KalturaITunesSyndicationFeedCategories
	 * @readonly
	 */
	public $category = null;

	/**
	 * 
	 *
	 * @var KalturaITunesSyndicationFeedAdultValues
	 */
	public $adultContent = null;

	/**
	 * 
	 *
	 * @var string
	 */
	public $feedAuthor = null;


}

class KalturaTubeMogulSyndicationFeed extends KalturaBaseSyndicationFeed
{
	/**
	 * 
	 *
	 * @var KalturaTubeMogulSyndicationFeedCategories
	 * @readonly
	 */
	public $category = null;


}

class KalturaYahooSyndicationFeed extends KalturaBaseSyndicationFeed
{
	/**
	 * 
	 *
	 * @var KalturaYahooSyndicationFeedCategories
	 * @readonly
	 */
	public $category = null;

	/**
	 * 
	 *
	 * @var KalturaYahooSyndicationFeedAdultValues
	 */
	public $adultContent = null;

	/**
	 * feed description
	 * 
	 *
	 * @var string
	 */
	public $feedDescription = null;

	/**
	 * feed landing page (i.e publisher website)
	 * 
	 *
	 * @var string
	 */
	public $feedLandingPage = null;


}

