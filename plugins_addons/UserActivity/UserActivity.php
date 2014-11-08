<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the UserActivity Plugin ------
-------------------------------------------

This plugin will allow you to keep track of the number of users and guests online, or to show lists of active users.


-------------------------------
------ Methods Available ------
-------------------------------

UserActivity::update();

$lastActivity = UserActivity::getUsersLastVisit($uniID);

$activeUsers = UserActivity::getUserActivity([$duration], [$resync], [$limit]);

$userCount = UserActivity::getUsersOnlineCount([$duration], [$resync]);
$guestCount = UserActivity::getGuestsOnlineCount([$duration], [$resync]);

UserActivity::pruneActivity([$duration]);

*/

abstract class UserActivity {
	
	
/****** Update the current user (or guest's) last online activity to now ******/
	public static function update (
	)					// RETURNS <bool> TRUE on success, FALSE on failure
	
	// UserActivity::update();
	{
		// User Activity
		if(Me::$id != 0)
		{
			return Database::query("REPLACE INTO activity_users (uni_id, date_lastVisit) VALUES (?, ?)", array(Me::$id, time()));
		}
		
		// Guest Activity
		$guestIP = $_SERVER['REMOTE_ADDR'];
		
		return Database::query("REPLACE INTO activity_guests (guest_ip, date_lastVisit) VALUES (?, ?)", array($guestIP, time()));
	}
	
	
/****** Get the last visit time of a specific user ******/
	public static function getUsersLastVisit
	(
		$uniID		// <int> The UniID of the last visit.
	)				// RETURNS <int> The timestamp of the last visit, or 0 on failure.
	
	// $lastActivity = UserActivity::getUsersLastVisit($uniID);
	{
		return (int) Database::selectValue("SELECT date_lastVisit FROM activity_users WHERE uni_id=? LIMIT 1", array($uniID));
	}
	
	
/****** Get User Activity ******/
	public static function getUsersOnline
	(
		$duration = 300		// <int> Number of seconds to check user activity over (default: last 5 minutes)
	,	$resync = 0			// <int> Number of seconds before resyncing the count (default: 1/2 the duration)
	,	$limit = 100		// <int> The maximum number of users to show online at once
	)						// RETURNS <array> list of all online users
	
	// $activeUsers = UserActivity::getUsersOnline([$duration], [$resync], [$limit]);
	{
		// Check if data is already cached
		$usersOnline = Cache::get("usersOnline");
		
		if($usersOnline === false)		// If the result is set to false, the data is not cached
		{
			$usersOnline = array();
			$userList = Database::selectMultiple("SELECT u.uni_id, u.handle, u.role FROM activity_users a INNER JOIN users u ON a.uni_id=u.uni_id WHERE a.date_lastVisit >= ? ORDER BY a.date_lastVisit DESC LIMIT 0, " . ($limit + 1), array(time() - $duration));
			
			// If there are more users online than can be displayed
			if(count($userList) > $limit)
			{
				array_pop($userList);
			}
			
			// Loop through each user
			foreach($userList as $user)
			{
				$usersOnline[$user['handle']] = $user;
			}
			
			ksort($usersOnline);
			
			// Handle the resync value
			if($resync == 0)
			{
				$resync = max(30, round($duration / 2));
			}
			
			// Prune old activity data
			if(mt_rand(0, 5 == 1)) { self::pruneActivity($duration * 2); }
			
			// Cache the list of users online
			Cache::set("usersOnline", Serialize::encode($usersOnline), $resync);
		}
		else
		{
			$usersOnline = Serialize::decode($usersOnline);
		}
		
		return $usersOnline;
	}
	
	
/****** Get the number of online users ******/
	public static function getUsersOnlineCount
	(
		$duration = 300		// <int> Number of seconds to check user activity over (default: last 5 minutes)
	,	$resync = 0			// <int> Number of seconds before resyncing the count (default: 1/2 the duration)
	)						// RETURNS <int> the number of users online.
	
	// $userCount = UserActivity::getUsersOnlineCount([$duration], [$resync]);
	{
		// Check if data is already cached
		$userCount = Cache::get("onlineCount-users");
		
		if($userCount === false)		// If the result is set to false, the data is not cached
		{
			$userCount = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM activity_users WHERE date_lastVisit >= ? LIMIT 1", array(time() - $duration));
			
			// Handle the resync value
			if($resync == 0)
			{
				$resync = max(30, round($duration / 2));
			}
			
			// Prune old activity data
			if(mt_rand(0, 5 == 1)) { self::pruneActivity($duration * 2); }
			
			// Cache the user online count
			Cache::set("onlineCount-users", $userCount, $resync);
		}
		
		return (int) $userCount;
	}
	
	
/****** Get the number of guests users ******/
	public static function getGuestsOnlineCount
	(
		$duration = 300		// <int> Number of seconds to check user activity over (default: last 5 minutes)
	,	$resync = 0			// <int> Number of seconds before resyncing the count (default: 1/2 the duration)
	)						// RETURNS <int> the number of users online
	
	// $guestCount = UserActivity::getGuestsOnlineCount([$duration], [$resync]);
	{
		// Check if data is already cached
		$guestCount = Cache::get("onlineCount-guests");
		
		if($guestCount === false)		// If the result is set to false, the data is not cached
		{
			$guestCount = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM activity_guests WHERE date_lastVisit >= ? LIMIT 1", array(time() - $duration));
			
			// Handle the resync value
			if($resync == 0)
			{
				$resync = max(30, round($duration / 2));
			}
			
			// Prune old activity data
			if(mt_rand(0, 5 == 1)) { self::pruneActivity($duration * 2); }
			
			// Cache the user online count
			Cache::set("onlineCount-guests", $guestCount, $resync);
		}
		
		return (int) $guestCount;
	}
	
	
/****** Prune User Activity ******/
	public static function pruneActivity
	(
		$duration = 900		// <int> The amount of time (in seconds) to leave unpruned (default: 15 minutes).
	)						// RETURNS <void>
	
	// UserActivity::pruneActivity([$duration]);
	{
		Database::query("DELETE FROM activity_users WHERE date_lastVisit > ?", array(time() - $duration));
		Database::query("DELETE FROM activity_guests WHERE date_lastVisit > ?", array(time() - $duration));
	}
	
}

