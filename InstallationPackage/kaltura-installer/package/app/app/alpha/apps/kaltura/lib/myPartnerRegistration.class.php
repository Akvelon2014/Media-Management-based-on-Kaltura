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

class myPartnerRegistration
{
	private $partnerParentId = null;

	public function __construct( $partnerParentId = null )
	{
	    set_time_limit(kConf::get('partner_registration_timeout'));
		$this->partnerParentId = $partnerParentId;	
	}
	
	const KALTURA_SUPPORT = "wikisupport@kaltura.com";
	  
	private function str_makerand ($minlength, $maxlength, $useupper, $usespecial, $usenumbers)
	{
		/*
		Description: string str_makerand(int $minlength, int $maxlength, bool $useupper, bool $usespecial, bool $usenumbers)
		returns a randomly generated string of length between $minlength and $maxlength inclusively.

		Notes:
		- If $useupper is true uppercase characters will be used; if false they will be excluded.
		- If $usespecial is true special characters will be used; if false they will be excluded.
		- If $usenumbers is true numerical characters will be used; if false they will be excluded.
		- If $minlength is equal to $maxlength a string of length $maxlength will be returned.
		- Not all special characters are included since they could cause parse errors with queries.
		*/

		$charset = "abcdefghijklmnopqrstuvwxyz";
		if ($useupper) $charset .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if ($usenumbers) $charset .= "0123456789";
		if ($usespecial) $charset .= "~@#$%^*()_+-={}|]["; // Note: using all special characters this reads: "~!@#$%^&*()_+`-={}|\\]?[\":;'><,./";
		if ($minlength > $maxlength) $length = mt_rand ($maxlength, $minlength);
		else $length = mt_rand ($minlength, $maxlength);
		$key = "";
		for ($i=0; $i<$length; $i++) $key .= $charset[(mt_rand(0,(strlen($charset)-1)))];
		return $key;
	}

	const KALTURAS_CMS_REGISTRATION_CONFIRMATION = 50;
	const KALTURAS_DEFAULT_REGISTRATION_CONFIRMATION = 54;
	const KALTURAS_EXISTING_USER_REGISTRATION_CONFIRMATION = 55;
	const KALTURAS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION = 56;
	const KALTURAS_BLACKBOARD_DEFAULT_REGISTRATION_CONFIRMATION = 57;
	
	public function sendRegistrationInformationForPartner ($partner, $skip_emails, $existingUser )
	{
		// email the client with this info
		$adminKuser = kuserPeer::retrieveByPK($partner->getAccountOwnerKuserId());
		$this->sendRegistrationInformation($partner, $adminKuser, $existingUser, null, $partner->getType());
											
		if ( !$skip_emails && kConf::hasParam("report_partner_registration") && kConf::get("report_partner_registration")) 
		{											
			// email the wikisupport@kaltura.com  with this info
			$this->sendRegistrationInformation($partner, $adminKuser, $existingUser, self::KALTURA_SUPPORT );

			// if need to hook into SalesForce - this is the place
			if ( include_once ( "mySalesForceUtils.class.php" ) )
			{
				mySalesForceUtils::sendRegistrationInformationToSalesforce($partner);
			}
			
			// if need to hook into Marketo - this is the place
			if ( include_once ( "myMarketoUtils.class.php" ) )
			{
				myMarketoUtils::sendRegistrationInformation($partner);
			}
		}
	}
	


	private  function sendRegistrationInformation(Partner $partner, kuser $adminKuser, $existingUser, $recipient_email = null , $partner_type = 1 )
	{
		$mailType = null;
		$bodyParams = array();
		$partnerId = $partner->getId();
		$userName = $adminKuser->getFullName();
		if (!$userName) { $userName = $adminKuser->getPuserId(); }
		$loginEmail = $adminKuser->getEmail();
		$loginData = $adminKuser->getLoginData();
		$hashKey = $loginData->getNewHashKeyIfCurrentInvalid();
		$resetPasswordLink = UserLoginDataPeer::getPassResetLink($hashKey);
		$kmcLink = trim(kConf::get('apphome_url'), '/').'/kmc';
		$contactLink = kConf::get('contact_url');
		$contactPhone = kConf::get('contact_phone_number');		
		$beginnersGuideLink = kConf::get('beginners_tutorial_url');
		$quickStartGuideLink = kConf::get('quick_start_guide_url');
		if ( $recipient_email == null ) $recipient_email = $loginEmail;

		
	 	// send the $cms_email,$cms_password, TWICE !
	 	if(kConf::get('kaltura_installation_type') == 'CE')	{
			$partner_type = 1;
		}
		
		switch($partner_type) { // send different email for different partner types
			case 1: // KMC signup
				if ($existingUser) {
					$mailType = self::KALTURAS_EXISTING_USER_REGISTRATION_CONFIRMATION;
					$bodyParams = array($userName, $loginEmail, $partnerId, $quickStartGuideLink);
				}
				else {
					$mailType = self::KALTURAS_CMS_REGISTRATION_CONFIRMATION;
					$bodyParams = array($userName, $loginEmail, $partnerId, $resetPasswordLink, $kmcLink, $quickStartGuideLink);
				}
				break;
			//blackboard
			case Partner::PARTNER_TYPE_BLACKBOARD:
				if ($existingUser) {
					$mailType = self::KALTURAS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION;
					$bodyParams = array($userName, $loginEmail, $partnerId, $quickStartGuideLink);
				}
				else {
					$mailType = self::KALTURAS_BLACKBOARD_DEFAULT_REGISTRATION_CONFIRMATION;
					$bodyParams = array($resetPasswordLink, $loginEmail, $partnerId, $kmcLink);
				}
				break;	
			default: // all others
			 	if ($existingUser) {
					$mailType = self::KALTURAS_DEFAULT_EXISTING_USER_REGISTRATION_CONFIRMATION;
					$bodyParams = array($userName, $loginEmail, $partnerId, $quickStartGuideLink);
				}
				else {
					$mailType = self::KALTURAS_DEFAULT_REGISTRATION_CONFIRMATION;
					$bodyParams = array($userName, $partnerId, $kmcLink, $loginEmail, $resetPasswordLink);
				}
				break;
		}
		
		kJobsManager::addMailJob(
			null, 
			0, 
			$partnerId, 
			$mailType, 
			kMailJobData::MAIL_PRIORITY_NORMAL, 
			kConf::get ("partner_registration_confirmation_email" ), 
			kConf::get ("partner_registration_confirmation_name" ), 
			$recipient_email, 
			$bodyParams);
	}

	/**
	 * Function creates new partner, saves all the required data to it, and copies objects & filesyncs of template content to its ID.
	 * @param string $partner_name
	 * @param string $contact
	 * @param string $email
	 * @param CommercialUseType $ID_is_for
	 * @param string $SDK_terms_agreement
	 * @param string $description
	 * @param string $website_url
	 * @param string $password
	 * @param Partner $partner
	 * @param int $templatePartnerId
	 * @return Partner
	 */
	private function createNewPartner( $partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password = null , $partner = null, $templatePartnerId = null )
	{
		$secret = md5($this->str_makerand(5,10,true, false, true));
		$admin_secret = md5($this->str_makerand(5,10,true, false, true));

		$newPartner = new Partner();
		if ($partner_name)
			$newPartner->setPartnerName($partner_name);
		$newPartner->setAdminSecret($admin_secret);
		$newPartner->setSecret($secret);
		$newPartner->setAdminName($contact);
		$newPartner->setAdminEmail($email);
		$newPartner->setUrl1($website_url);
		if ($ID_is_for === "commercial_use" || $ID_is_for === CommercialUseType::COMMERCIAL_USE)
			$newPartner->setCommercialUse(true);
		else //($ID_is_for == "non-commercial_use") || $ID_is_for === CommercialUseType::NON_COMMERCIAL_USE)
			$newPartner->setCommercialUse(false);
		$newPartner->setDescription($description);
		$newPartner->setKsMaxExpiryInSeconds(86400);
		$newPartner->setModerateContent(false);
		$newPartner->setNotify(false);
		$newPartner->setAppearInSearch(mySearchUtils::DISPLAY_IN_SEARCH_PARTNER_ONLY);
		$newPartner->setIsFirstLogin(true);
		/* fix drupal5 module partner type */
		//var_dump($description);
		
		if ( $this->partnerParentId )
		{
			// this is a child partner of some VAR/partner GROUP
			$newPartner->setPartnerParentId( $this->partnerParentId );
			$newPartner->setMonitorUsage(PartnerFreeTrialType::NO_LIMIT);
			$parentPartner = PartnerPeer::retrieveByPK($this->partnerParentId);
			if ($parentPartner)
				$newPartner->setPartnerPackage($parentPartner->getPartnerPackage());
		}
		
		if(substr_count($description, 'Drupal module|'))
		{
			$newPartner->setType(102);
			if($partner) $partner->setType(102);
		}
		
		if ( $partner )
		{
			if ( $partner->getType() ) $newPartner->setType ( $partner->getType() );
			if ( $partner->getContentCategories() ) $newPartner->setContentCategories( $partner->getContentCategories() );
			if ( $partner->getPhone() ) $newPartner->setPhone( $partner->getPhone() );
			if ( $partner->getDescribeYourself() ) $newPartner->setDescribeYourself( $partner->getDescribeYourself() );
			if ( $partner->getAdultContent() ) $newPartner->setAdultContent( $partner->getAdultContent() );
			if ( $partner->getDefConversionProfileType() ) $newPartner->setDefConversionProfileType( $partner->getDefConversionProfileType() );
			// new fields of registration form
			if ( $partner->getFirstName() ) $newPartner->setFirstName( $partner->getFirstName() );
			if ( $partner->getLastName() ) $newPartner->setLastName( $partner->getLastName() );
			if ( $partner->getCountry() ) $newPartner->setCountry( $partner->getCountry() );
			if ( $partner->getState() ) $newPartner->setState( $partner->getState() );
			if ( $partner->getAdditionalParams() && is_array($partner->getAdditionalParams()) && count($partner->getAdditionalParams())) $newPartner->setAdditionalParams( $partner->getAdditionalParams() );
			if ($partner->getWamsAccountName()) $newPartner->setWamsAccountName($partner->getWamsAccountName());
			if ($partner->getWamsAccountKey()) $newPartner->setWamsAccountKey($partner->getWamsAccountKey());

		}
		$newPartner->save();

		// if name was left empty - which should not happen - use id as name
		if ( ! $partner_name ) $partner_name = $newPartner->getId();
		$newPartner->setPartnerName( $partner_name );
		$newPartner->setPrefix($newPartner->getId());
		$newPartner->setPartnerAlias(md5($newPartner->getId().'kaltura partner'));

		// set default conversion profile for trial accounts
		if ($newPartner->getType() == Partner::PARTNER_TYPE_KMC)
		{
			$newPartner->setDefConversionProfileType( ConversionProfile::DEFAULT_TRIAL_COVERSION_PROFILE_TYPE );
		}
		
		$newPartner->save();

		$partner_id = $newPartner->getId();
		widget::createDefaultWidgetForPartner( $partner_id , $this->createNewSubPartner ( $newPartner ) );
		
		$fromPartner = PartnerPeer::retrieveByPK($templatePartnerId ? $templatePartnerId : kConf::get("template_partner_id"));
	 	if (!$fromPartner)
	 		KalturaLog::log("Template content partner was not found!");
 		else
	 		myPartnerUtils::copyTemplateContent($fromPartner, $newPartner, true);
	 		
	 	if ($newPartner->getType() == Partner::PARTNER_TYPE_WORDPRESS)
	 		kPermissionManager::setPs2Permission($newPartner);
	 		
		$newPartner->setKmcVersion(kConf::get('new_partner_kmc_version'));
		$newPartner->save();
		
		return $newPartner;
	}

	private function createNewSubPartner($newPartner)
	{
		$pid = $newPartner->getId();
		$subpid = ($pid*100);

		// TODO: save this, when implementation is ready

		return $subpid;
	}

	// if the adminKuser already exists - use his password - it should always be the same one for a given email !!
	private function createNewAdminKuser($newPartner , $existing_password )
	{		
		// generate a new password if not given
		if ( $existing_password != null ) {
			$password = $existing_password;
		}
		else {
			$password = UserLoginDataPeer::generateNewPassword();
		}
		
		// create the user
		$kuser = new kuser();
		$kuser->setEmail($newPartner->getAdminEmail());
		
		list($firstName, $lastName) = kString::nameSplit($newPartner->getAdminName());
		$kuser->setFirstName($firstName);
		$kuser->setLastName($lastName);

		$kuser->setPartnerId($newPartner->getId());
		$kuser->setIsAdmin(true);
		$kuser->setPuserId($newPartner->getAdminEmail());

		$kuser = kuserPeer::addUser($kuser, $password, false, false); //this also saves the kuser and adds a user_login_data record
		
		$loginData = UserLoginDataPeer::retrieveByPK($kuser->getLoginDataId());
	
		return array($password, $loginData->getPasswordHashKey(), $kuser->getId());
	}

	public function initNewPartner($partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password = null , $partner = null, $ignorePassword = false, $templatePartnerId = null  )
	{
		// Validate input fields
		if( $partner_name == "" )
			throw new SignupException("Please fill in the Partner's name" , SignupException::INVALID_FIELD_VALUE);
			
		if ($contact == "")
			throw new SignupException('Please fill in Administrator\'s details', SignupException::INVALID_FIELD_VALUE);

		if ($email == "")
			throw new SignupException('Please fill in Administrator\'s Email Address', SignupException::INVALID_FIELD_VALUE);
		
			
		if(!kString::isEmailString($email))
			throw new SignupException('Invalid email address', SignupException::INVALID_FIELD_VALUE);

		if ($description == "")
			throw new SignupException('Please fill in description', SignupException::INVALID_FIELD_VALUE);

		if ( ($ID_is_for !== CommercialUseType::COMMERCIAL_USE) && ($ID_is_for !== CommercialUseType::NON_COMMERCIAL_USE) &&
			 ($ID_is_for !== "commercial_use") && ($ID_is_for !== "non-commercial_use") ) //string values left for backward compatibility
			throw new SignupException('Invalid field value.\nSorry.', SignupException::UNKNOWN_ERROR);

		if ($SDK_terms_agreement != "yes")
			throw new SignupException('You haven`t approved Terms & Conds.', SignupException::INVALID_FIELD_VALUE);
						
		
		$existingLoginData = UserLoginDataPeer::getByEmail($email);
		if ($existingLoginData && !$ignorePassword)
		{
			// if a another user already existing with the same adminEmail, new account will be created only if the right password was given
			if (!$password)
			{
				throw new SignupException("User with email [$email] already exists in system.", SignupException::EMAIL_ALREADY_EXISTS );
			}
			else if ($existingLoginData->isPasswordValid($password))
			{
				KalturaLog::log('Login id ['.$email.'] already used, and given password is valid. Creating new partner with this same login id');
			}
			else
			{
				throw new SignupException("Invalid password for user with email [$email].", SignupException::EMAIL_ALREADY_EXISTS );
			}
		}
			
			
		// TODO: log request
		$newPartner = NULL;
		$newSubPartner = NULL;
		try {
		    //validate that the template partner object counts do not exceed the limits stated in the local.ini
		    $templatePartner = PartnerPeer::retrieveByPK($templatePartnerId ? $templatePartnerId : kConf::get('template_partner_id'));
		    $this->validateTemplatePartner($templatePartner);
			// create the new partner
			$newPartner = $this->createNewPartner($partner_name , $contact, $email, $ID_is_for, $SDK_terms_agreement, $description, $website_url , $password , $partner , $templatePartnerId);

			// create the sub partner
			// TODO: when ready, add here the saving of this value, currently it will be only
			// a random value, being passed to the user, and never saved
			$newSubPartnerId = $this->createNewSubPartner($newPartner);

			// create a new admin_kuser for the user,
			// so he will be able to login to the system (including permissions)
			list($newAdminKuserPassword, $newPassHashKey, $kuserId) = $this->createNewAdminKuser($newPartner , $password );
			$newPartner->setAccountOwnerKuserId($kuserId);
			$newPartner->save();
			
			$this->setAllTemplateEntriesToAdminKuser($newPartner->getId(), $kuserId);

			return array($newPartner->getId(), $newSubPartnerId, $newAdminKuserPassword, $newPassHashKey);
		}
		catch (Exception $e) {
			//TODO: revert all changes, depending where and why we failed

			throw $e;
		}
	}
	
	/**
	 * Validate the amount of core and plugin objects found on the template partner.
	 * @param Partner $templatePartner
	 */
	private function validateTemplatePartner (Partner $templatePartner)
	{
	    //access control profiles
	    $c = new Criteria();
 		$c->add(accessControlPeer::PARTNER_ID, $templatePartner->getId());
 		$count = accessControlPeer::doCount($c);
 		
        if ($count > kConf::get('copy_partner_limit_ac_profiles'))
        {
            throw new kCoreException("Template partner's number of [accessControlProfiles] objects exceed allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
        }
        
        //categories
        categoryPeer::setUseCriteriaFilter(false);
 		$c = new Criteria();
 		$c->addAnd(categoryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(categoryPeer::STATUS, CategoryStatus::ACTIVE);
 		$count = categoryPeer::doCount($c);
 	    if ($count > kConf::get('copy_partner_limit_categories'))
        {
            throw new kCoreException("Template partner's number of [category] objects exceed allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
        }
        
 		categoryPeer::setUseCriteriaFilter(true);
 		
 		//conversion profiles
	    $c = new Criteria();
 		$c->add(conversionProfile2Peer::PARTNER_ID, $templatePartner->getId());
 		$count = conversionProfile2Peer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_conversion_profiles'))
 		{
 		    throw new kCoreException("Template partner's number of [conversionProfile] objects exceeds allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		//entries
 		entryPeer::setUseCriteriaFilter ( false ); 
 		$c = new Criteria();
 		$c->addAnd(entryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(entryPeer::TYPE, entryType::MEDIA_CLIP);
 		$c->addAnd(entryPeer::STATUS, entryStatus::READY);
 		$count = entryPeer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_entries'))
 		{
 		    throw new kCoreException("Template partner's number of MEDIA_CLIP objects exceed allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		entryPeer::setUseCriteriaFilter ( true );
 		
 		//playlists
		entryPeer::setUseCriteriaFilter ( false );
 		$c = new Criteria();
 		$c->addAnd(entryPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(entryPeer::TYPE, entryType::PLAYLIST);
 		$c->addAnd(entryPeer::STATUS, entryStatus::READY);
 		$count = entryPeer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_playlists'))
 		{
 		    throw new kCoreException("Template partner's number of PLAYLIST objects exceed allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		
 		entryPeer::setUseCriteriaFilter ( true );
 		
 		//flavor params
	    $c = new Criteria();
 		$c->add(assetParamsPeer::PARTNER_ID, $templatePartner->getId());
 		$count = assetParamsPeer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_flavor_params'))
 		{
 		    throw new kCoreException("Template partner's number of [flavorParams] objects exceeds allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		
 		//uiconfs
 		uiConfPeer::setUseCriteriaFilter(false);
 		$c = new Criteria();
 		$c->addAnd(uiConfPeer::PARTNER_ID, $templatePartner->getId());
 		$c->addAnd(uiConfPeer::OBJ_TYPE, array (uiConf::UI_CONF_TYPE_KDP3, uiConf::UI_CONF_TYPE_WIDGET), Criteria::IN);
 		$c->addAnd(uiConfPeer::STATUS, uiConf::UI_CONF_STATUS_READY);
 		$count = uiConfPeer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_ui_confs'))
 		{
 		    throw new kCoreException("Template partner's number of [uiconf] objects exceeds allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		uiConfPeer::setUseCriteriaFilter ( true );
 		
 		//user roles
 		UserRolePeer::setUseCriteriaFilter ( false );
 		$c = new Criteria();
 		$c->addAnd(UserRolePeer::PARTNER_ID, $templatePartner->getId(), Criteria::EQUAL);
 		$count = UserRolePeer::doCount($c);
 		if ($count > kConf::get('copy_partner_limit_user_roles'))
 		{
 		    throw new kCoreException("Template partner's number of [userRole] objects exceed allowed limit", kCoreException::TEMPLATE_PARTNER_COPY_LIMIT_EXCEEDED);
 		}
 		UserRolePeer::setUseCriteriaFilter ( true );
 		
 		$validatorPlugins = KalturaPluginManager::getPluginInstances('IKalturaObjectValidator');
 		foreach ($validatorPlugins as $validatorPlugins)
 		{
 		    $validatorPlugins->validateObject ($templatePartner, IKalturaObjectValidator::OPERATION_COPY);
 		}
        
	}
	
	private function setAllTemplateEntriesToAdminKuser($partnerId, $kuserId)
	{
		$c = new Criteria();
		$c->addAnd(entryPeer::PARTNER_ID, $partnerId, Criteria::EQUAL);
		entryPeer::setUseCriteriaFilter(false);
		$allEntries = entryPeer::doSelect($c);
		entryPeer::setUseCriteriaFilter(true);
		
		
		// set the new partner id into the default category criteria filter
 		$defaultCategoryFilter = categoryPeer::getCriteriaFilter()->getFilter();
 		$oldPartnerIdCategory = $defaultCategoryFilter->get(categoryPeer::PARTNER_ID);
 		$defaultCategoryFilter->remove(categoryPeer::PARTNER_ID);
 		$defaultCategoryFilter->addAnd(categoryPeer::PARTNER_ID, $partnerId);
 		
 		// set the new partner id into the default category criteria filter
 		$defaultCategoryEntryFilter = categoryEntryPeer::getCriteriaFilter()->getFilter();
 		$oldPartnerIdCategoryEntry = $defaultCategoryFilter->get(categoryEntryPeer::PARTNER_ID);
 		$defaultCategoryEntryFilter->remove(categoryEntryPeer::PARTNER_ID);
 		$defaultCategoryEntryFilter->addAnd(categoryEntryPeer::PARTNER_ID, $partnerId);

		foreach ($allEntries as $entry)
		{
			$entry->setKuserId($kuserId);
			$entry->setCreatorKuserId($kuserId);
			$entry->save();
		}
		
		kEventsManager::flushEvents();
		
		// restore the original partner id in the default category criteria filter
		$defaultCategoryFilter->remove(categoryPeer::PARTNER_ID);
 		$defaultCategoryFilter->addAnd(categoryPeer::PARTNER_ID, $oldPartnerIdCategory);
 		
 		$defaultCategoryEntryFilter->remove(categoryEntryPeer::PARTNER_ID);
 		$defaultCategoryEntryFilter->addAnd(categoryEntryPeer::PARTNER_ID, $oldPartnerIdCategoryEntry);
	}
}


class SignupException extends Exception
{
    const UNKNOWN_ERROR = 500;
    const INVALID_FIELD_VALUE = 501;
    const EMAIL_ALREADY_EXISTS = 502;
    const PASSWORD_STRUCTURE_INVALID = 503;

    // Redefine the exception so message/code isn't optional
    public function __construct($message, $code) {
        // some code

        // make sure everything is assigned properly
        parent::__construct($message, $code);

    }
}
