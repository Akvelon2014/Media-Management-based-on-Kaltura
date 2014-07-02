<?php
/**
 * @package api
 * @subpackage ps2
 */
require_once 'addmoderationAction.class.php';

/**
 * @package api
 * @subpackage ps2
 */
class reportentryAction extends addmoderationAction
{
	public function describe()
	{
		return 
			array (
				"display_name" => "reportEntry",
				"desc" => "" ,
				"in" => array (
					"mandatory" => array ( 
						"moderation" => array ("type" => "moderation", "desc" => ""),
						),
					"optional" => array (
						)
					),
				"out" => array (
					"moderation" => array ("type" => "moderation", "desc" => "")
					),
				"errors" => array (
				)
			); 
	}
	
	protected function ticketType()			{	return self::REQUIED_TICKET_REGULAR;	}

	protected function getStatusToUpdate ( $moderation = null ) 	{		return moderation::MODERATION_STATUS_REVIEW; 	}
	
	protected function fixModeration  ( moderation &$moderation ) 	
	{
		$moderation->setObjectType( moderation::MODERATION_OBJECT_TYPE_ENTRY );

		$entryPartner = $this->getPartner();
		myNotificationMgr::createNotification( kNotificationJobData::NOTIFICATION_TYPE_ENTRY_REPORT, $moderation , $entryPartner->getId());
	}

}
?>