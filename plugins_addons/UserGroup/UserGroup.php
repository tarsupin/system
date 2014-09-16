<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class UserGroup {
	
	
/****** Get a list of groups that a user belongs to ******/
	public static function getGroup
	(
		$groupID	// <int> The GroupID to get data for.
	)				// RETURNS <str:str> the group data retrieved.
	
	// $groupData = UserGroup::getGroup($groupID);
	{
		return Database::selectOne("SELECT type, title FROM groups WHERE group_id=?", array($groupID));
	}
	
	
/****** Get a list of users in a group ******/
	public static function getUsersByGroup
	(
		$groupID			// <int> The GroupID from which to retrieve a list of users.
	,	$minClearance = 0	// <int> The minimum level of clearance a user needs to be returned in this list.
	,	$maxClearance = 9	// <int> The maximum level of clearance a user needs to be returned in this list.
	)						// RETURNS <int:int> list of users in the group and their corresponding clearance.
	
	// $users = UserGroup::getUsersByGroup($groupID, [$minClearance], [$maxClearance]);
	{
		$userList = array();
		
		$results = Database::selectMultiple("SELECT uni_id, clearance FROM groups_users WHERE group_id=? AND clearance BETWEEN ? AND ?", array($groupID, $minClearance, $maxClearance));
		
		foreach($results as $res)
		{
			$userList[(int) $res['uni_id']] = (int) $res['clearance'];
		}
		
		return $userList;
	}
	
	
/****** Get a list of groups that a user belongs to ******/
	public static function getGroupsByUser
	(
		$uniID		// <int> The UniID to retrieve a list of groups of.
	)				// RETURNS <int:int> list of users owned by that AuthID, array() if none.
	
	// $groups = UserGroup::getGroupsByUser($uniID);
	{
		$groupList = array();
		
		$results = Database::selectMultiple("SELECT group_id FROM groups_users_join WHERE uni_id=? AND clearance BETWEEN ? AND ?", array($groupID, $minClearance, $maxClearance));
		
		foreach($results as $res)
		{
			$groupList[] = $res['group_id'];
		}
		
		return $groupList;
	}
	
}

