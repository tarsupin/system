<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the User Plugin ------
-----------------------------------

This plugin sets up and handles the users, including registration, login, passwords, etc.

This plugin is most frequently used to retrieve user information with the following methods:

	User::get($uniID);					// Retrieves the user's data (with their UniID)
	
	User::getDataByHandle($handle);		// Retrieves the user's data (with their user handle)


------------------------------
------ Clearance Levels ------
------------------------------

$clearances = User::clearance();

	//	9	superadmin (webmaster)
	//	8	staff admin
	//	7	management staff
	//	6	moderator, staff
	//	5	staff
	//	4	intern / assistant
	//	3	vip / trusted user
	//	2	user
	//	1	limited user
	//	0	guest
	//	-1	silenced user
	//	-2	restricted user
	//	-3	temporarily banned user
	//	-9	permanently banned user


------------------------------
------ Methods Available ------
------------------------------

$userData	= User::get($uniID, $columns = "*")		// Retrieves & verifies user info
$uniID		= User::getIDByHandle($handle);
$userData	= User::getDataByHandle($handle);		// Retrieves user info based on a handle

$userID = User::authLogin($loginResponse);					// Attempts auth-login; registers if necessary
$userID = User::register($username, $password, $email = "")	// Registers a user (email can be optional).

$userData = User::silentRegister($handle);		// This forces a registration for the provided user

$clearances = User::clearance();

*/

abstract class User {
	
	
/****** Class Variables ******/
	
	// On some pages, it will be useful to cache the user's data for later instances on that page.
	// In those instances, we will save them to User::$cache[$uniID], provided here:
	public static $cache = array();
	
	
/****** Get User Data ******/
	public static function get
	(
		$uniID			// <int> The Uni-Account to retrieve.
	,	$columns = "*"	// <str> The columns you want to retrieve from the users database.
	)					// RETURNS <str:mixed> user data if retrieve was successful, FALSE if empty.
	
	// $userData = User::get($uniID);
	{
		return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,-*`") . " FROM users WHERE uni_id=? LIMIT 1", array($uniID));
	}
	
	
/****** Get UniID from a Handle ******/
	public static function getIDByHandle
	(
		$handle		// <str> The handle to look up.
	)				// RETURNS <int> the UniID associated with the handle, or 0 if not found.
	
	// $uniID = User::getIDByHandle($handle);
	{
		return (int) Database::selectValue("SELECT uni_id FROM users_handles WHERE handle=? LIMIT 1", array($handle));
	}
	
	
/****** Get User's Data from a Handle ******/
	public static function getDataByHandle
	(
		$handle				// <str> The handle to look up.
	,	$columns = "uni_id"	// <str> The columns you want to retrieve from the users database.
	)						// RETURNS <str:mixed> user data associated with the handle provided, array() if empty.
	
	// $userData = User::getDataByHandle($handle, $columns);
	{
		if($uniID = self::getIDByHandle($handle))
		{
			return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,-*`") . " FROM users WHERE uni_id=? LIMIT 1", array($uniID));
		}
		
		return array();
	}
	
	
/****** Login a User (using a valid Auth-API response) ******/
	public static function authLogin
	(
		$loginResponse		// <str:mixed> the API response that was provided from Auth.
	)						// RETURNS <int> The UniID of the user created, 0 if failed.
	
	// $uniID = User::authLogin($loginResponse);
	{
		// Check if User exists
		if($checkUser = Database::selectOne("SELECT uni_id FROM users WHERE uni_id=? LIMIT 1", array($loginResponse['uni_id'])))
		{
			return (int) $checkUser['uni_id'];
		}
		
		// Register User (if necessary)
		if(self::register($loginResponse['uni_id'], $loginResponse['handle'], $loginResponse['display_name'], $loginResponse['timezone']))
		{
			return (int) $loginResponse['uni_id'];
		}
		
		return 0;
	}
	
	
/****** Register a User ******/
	public static function register
	(
		$uniID				// <int> The Uni-Account ID.
	,	$handle				// <str> The handle for this Uni-Account.
	,	$displayName = ""	// <str> The display name of the account.
	,	$timezone = ""		// <str> The timezone of the account.
	)						// RETURNS <bool> TRUE if successful, FALSE if failed.
	
	// User::register($uniID, $handle, "My Name", "America/Chicago");
	{
		Database::startTransaction();
		
		if($pass = Database::query("INSERT INTO `users` (`uni_id`, `clearance`, `handle`, `display_name`, `timezone`, `date_joined`) VALUES (?, ?, ?, ?, ?, ?)", array($uniID, 2, $handle, $displayName, $timezone, time())))
		{
			$pass = Database::query("INSERT INTO `users_handles` (handle, uni_id) VALUES (?, ?)", array($handle, $uniID));
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Silently Register a User (as efficiently as possible, with graceful fail) ******/
# This method is designed to test Auth to see if the user exists and should be registered (since some sites will
# expect the user to exist). If the user is detected, register them.
	public static function silentRegister
	(
		$user		// <mixed> UniID or Handle that you want to silently register.
	)				// RETURNS <str:mixed> the user data that was registered, array() on failure.
	
	// $userData = User::silentRegister($user);
	{
		// Get User Data from Auth
		$packet = array("user" => $user, "columns" => "uni_id, handle, display_name");
		
		$response = Connect::to("auth", "UserData", $packet);
		
		return ($response ? $response : array());
	}
	
	
/****** Return Clearance Values ******/
	public static function clearance (
	)					// RETURNS <int:str> array of clearance levels
	
	// $clearances = User::clearance();
	{
		return array(
			9	=> "Superadmin, Webmaster"
		,	8	=> "Staff Administrator"
		,	7	=> "Staff Management"
		,	6	=> "Moderators, Staff"
		,	5	=> "Staff"
		,	4	=> "Interns, Assistants"
		,	3	=> "VIPs, Trusted Users"
		,	2	=> "User"
		,	1	=> "Limited User"
		,	0	=> "Guest"
		,	-1	=> "Silenced User"
		,	-2	=> "Restricted User"
		,	-5	=> "Temporarily Banned"
		,	-9	=> "Permanently Banned"
		);
	}
}
