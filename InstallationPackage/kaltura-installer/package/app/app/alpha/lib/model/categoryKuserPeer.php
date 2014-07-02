<?php


/**
 * Skeleton subclass for performing query and update operations on the 'category_kuser' table.
 *
 * 
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package Core
 * @subpackage model
 */
class categoryKuserPeer extends BasecategoryKuserPeer {
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $kuserId
	 * @param $con
	 * 
	 * @return categoryKuser
	 */
	public static function retrieveByCategoryIdAndKuserId($categoryId, $kuserId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryKuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryKuserPeer::KUSER_ID, $kuserId);

		return categoryKuserPeer::doSelectOne($criteria, $con);
	}
	
	/**
	 * 
	 * @param int $kuserId
	 * @return bool - no need to fetch the objects
	 */
	public static function isCategroyKuserExistsForKuser($kuserId)
	{
		$criteria = new Criteria();

		$criteria->add(categoryKuserPeer::KUSER_ID, $kuserId);
		
		$categoryKuser = categoryKuserPeer::doSelectOne($criteria);
		
		if($categoryKuser)
			return true;
			
		return false;
	}
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $kuserId
	 * @param $con
	 * 
	 * @return categoryKuser
	 */
	public static function retrieveByCategoryIdAndActiveKuserId($categoryId, $kuserId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryKuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryKuserPeer::KUSER_ID, $kuserId);
		$criteria->add(categoryKuserPeer::STATUS, CategoryKuserStatus::ACTIVE);

		return categoryKuserPeer::doSelectOne($criteria, $con);
	}
	
	/**
	 * 
	 * @param array $categoriesIds
	 * @param int $kuserId
	 * @param $con
	 * 
	 * @return categoryKuser
	 */
	public static function retrieveByCategoriesIdsAndActiveKuserId($categoriesIds, $kuserId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryKuserPeer::CATEGORY_ID, $categoriesIds, Criteria::IN);
		$criteria->add(categoryKuserPeer::KUSER_ID, $kuserId);
		$criteria->add(categoryKuserPeer::STATUS, CategoryKuserStatus::ACTIVE);

		return categoryKuserPeer::doSelectOne($criteria, $con);
	}
	
	/**
	 * 
	 * @param int $categoryId
	 * @param int $kuserId
	 * @param $con
	 * 
	 * @return array
	 */
	public static function retrieveActiveKusersByCategoryId($categoryId, $con = null)
	{
		$criteria = new Criteria();

		$criteria->add(categoryKuserPeer::CATEGORY_ID, $categoryId);
		$criteria->add(categoryKuserPeer::STATUS, CategoryKuserStatus::ACTIVE);

		return categoryKuserPeer::doSelect($criteria, $con);
	}
	
} // categoryKuserPeer
