<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class UserAuth {
	
	
/****** Add a User under Auth Identification ******/
	public static function addUser
	(
		$authID			// <int> The AuthID to add the user to.
	,	$uniID			// <int> The UniID to add to the Auth User.
	,	$handle			// <str> The handle to associate with the UniID.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// UserAuth::addUser($authID, $uniID, $handle);
	{
		return Database::query("INSERT IGNORE INTO users_auth_join (auth_id, uni_id, handle) VALUES (?, ?, ?)", array($authID, $uniID, $handle));
	}
	
	
/****** Get List of Users ******/
	public static function getUsers
	(
		$authID			// <int> The AuthID from which to retrieve a list of users.
	)					// RETURNS <int:[str:str]> list of users owned by that AuthID, array() if none.
	
	// $users = UserAuth::getUsers($authID);
	{
		return Database::selectMultiple("SELECT uni_id, handle FROM users_auth_join WHERE auth_id=?", array($authID));
	}
	
	
/****** Get Auth ID by User Handle ******/
	public static function getAuthIDByHandle
	(
		$handle			// <str> The handle to use to retrieve the Auth ID.
	)					// RETURNS <int> the Auth ID, or 0 on failure.
	
	// $authID = UserAuth::getAuthIDByHandle($handle);
	{
		$authID = (int) Database::selectValue("SELECT u.auth_id FROM users_handles uh INNER JOIN users u ON uh.uni_id=u.uni_id WHERE uh.handle=?", array($handle));
		
		return $authID ? $authID : 0;
	}
	
	
/****** Silently Register a User (as efficiently as possible, with graceful fail) ******/
# This method is designed to test Auth to see if the user exists and should be registered (since some sites will
# expect the user to exist). If the user is detected, register them, and do so with UserAuth as well.
	public static function silentRegister
	(
		$user		// <mixed> UniID or User Handle that you want to silently register.
	)				// RETURNS <str:str> the user data that was registered, array() on failure.
	
	// $userData = UserAuth::silentRegister($uniID);
	// $userData = UserAuth::silentRegister($handle);
	{
		// Check if the User Exists
		$siteData = Network::get("auth");
		if(!$response = Connect::call($siteData['site_url'] . "/api/UserRegistered", $user))
		{
			return array();
		}
		
		// Get User Data from Auth
		$packet = array("user" => $user, "columns" => "uni_id, handle, display_name, auth_id");
		
		if($userData = Connect::call($siteData['site_url'] . "/api/UserData", $packet, $siteData['site_key']))
		{
			// Register the user's information on this site gracefully
			Database::startTransaction();
			
			if($pass = User::register($userData['uni_id'], $userData['handle'], $userData['display_name']))
			{
				if($pass = Database::query("UPDATE users SET auth_id=? WHERE uni_id=? LIMIT 1", array($userData['auth_id'], $userData['uni_id'])))
				{
					$pass = UserAuth::addUser($userData['auth_id'], $userData['uni_id'], $userData['handle']);
				}
			}
			
			if(Database::endTransaction($pass))
			{
				return $userData;
			}
		}
		
		return array();
	}
}

