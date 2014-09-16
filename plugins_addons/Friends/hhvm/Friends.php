<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Friends Plugin ------
--------------------------------------

This plugin provides tools to identify a user's friends and check their permissions.

Friends have two types of permissions:

	"View Permissions" is how much the user is allowing you to view.
		
		Full Access would mean the friend can see everything the user creates .
		Standard Access means the friend can see most things the user creates, except those listed as trusted only.
		Limited Access means the friend can only see the things that the user creates that are listed as limited.
		Restricted Access means you're basically limited to public information only, but you're listed as a friend.
	
	"Interact Permissions" is what the friend can actually post.
		
		Full Access allows you to post on everything the user has enabled: comments, posts, articles, etc.
		Standard Access allows all standard features for the user.
		Limited Access means you're probably only able to interact with limited functionality, such as "likes".
		Restricted Access means you're probably not able to do much of anything unless it's the true minimum.
		No Access means you can't interact with anything.
		
		Note: Interact permissions are only as strong as the friend's view permissions allow. If the user cannot view
		something, they often won't have the ability to post in relation to it.
		
		
-------------------------------
------ Sync Instructions ------
-------------------------------

This plugin also receives sync instructions from UniFaction when the Sync plugin is activated. It will receive instructions in the following manner:
	
	$syncData['Friend'] = array(
		[$uniID1] = array(
			[$friendID1] = array(
				$viewClearance
			,	$interactClearance
			)
		)
	);
	
There can be any number of $uniID's provided, each with any number of $friendID's provided.

	
-------------------------------
------ Methods Available ------
-------------------------------

// Check if the user and target are friends
Friends::isFriend($uniID, $targetID);

// Get the clearance levels of a friend
$clearance = Friends::getClearance($uniID, $friendID);

// Get a list of a user's friends
$friends = Friends::getList($uniID, [$startPos], [$limit], [$byEngagement]);

*/

abstract class Friends {
	
	
/****** Check if the target user is a friend ******/
	public static function isFriend
	(
		int $uniID			// <int> The UniID of the user.
	,	int $targetID		// <int> The UniID of the target to check if they're a friend.
	): bool					// RETURNS <bool> TRUE if target is a friend, FALSE if not.
	
	// Friends::isFriend($uniID, $targetID);
	{
		return (bool) Database::selectValue("SELECT friend_id FROM users_friends WHERE uni_id=? AND friend_id=? LIMIT 1", array($uniID, $targetID));
	}
	
	
/****** Get the friend's details (returns clearance levels) ******/
	public static function getClearance
	(
		int $uniID			// <int> The UniID of the user.
	,	int $friendID		// <int> The UniID of the friend.
	): array <str, int>					// RETURNS <str:int> the relationship role, FALSE on failure.
	
	// $clearance = Friends::getClearance($uniID, $friendID);
	{
		if(!$request = Database::selectOne("SELECT view_clearance, interact_clearance FROM users_friends WHERE uni_id=? AND friend_id=? LIMIT 1", array($uniID, $friendID)))
		{
			return array();
		}
		
		$request['view_clearance'] = (int) $request['view_clearance'];
		$request['interact_clearance'] = (int) $request['interact_clearance'];
		
		return $request;
	}
	
	
/****** Get the list of friends of a user ******/
	public static function getList
	(
		int $uniID					// <int> The UniID of the user to find friends of.
	,	int $startPos = 0			// <int> The row number to start at.
	,	int $limit = 20				// <int> The number of rows to return.
	): array <int, array<str, mixed>>							// RETURNS <int:[str:mixed]> the list of friends, array() on failure.
	
	// $friends = Friends::getList($uniID, [$startPos], [$limit], [$byEngagement]);
	{
		return Database::selectMultiple("SELECT f.friend_id, u.display_name, u.handle FROM users_friends as f INNER JOIN users as u ON f.friend_id = u.uni_id WHERE f.uni_id=? LIMIT " . ($startPos + 0) . ", " . ($limit + 0), array($uniID));
	}
	
	
/****** Get List of Mutual Friend ******/
	public static function getMutualFriends
	(
		int $uniID		// <int> The uniID of the user.
	,	int $friendID	// <int> The uniID of the friend.
	): array				// RETURNS <array> list of mutual friends, or empty array if none available.
	
	// $friends = Friends::getMutualFriends($uniID, $friendID);
	{
		// For now, just return empty
		// When we build this, we'll want to cache the results for a certain amount of time.
		return array();
		
		/**********************************************
		****** Build a better algorithm for this ******
		**********************************************/
		/*
		$myFriends = array();
		$theirFriends = array();
		
		$getMyFriends = Database::selectMultiple("SELECT friend_uni_id FROM users_friends WHERE uni_id=?", array($uniID));
		$getTheirFriends = Database::selectMultiple("SELECT friend_uni_id FROM users_friends WHERE uni_id=?", array($friendID));
		
		foreach($getMyFriends as $friend)
		{
			$myFriends[] = (int) $friend['friend_uni_id'];
		}
		
		foreach($getTheirFriends as $friend)
		{
			$theirFriends[] = (int) $friend['friend_uni_id'];
		}
		
		$friendIDs = array_intersect($myFriends, $theirFriends);
		
		return Database::selectMultiple("SELECT uni_id, display_name FROM users WHERE uni_id IN (" . implode(", ", $friendIDs) . ")", array());
		*/
	}
	
	
/****** Sync Friends ******/
# This will pull from the Friend Sync Site to maintain the appropriate friends connections.
	public static function sync
	(
		int $lastSyncTime		// <int> The timestamp of the last sync.
	): int						// RETURNS <int> The updated sync time.
	
	// Friends::sync($lastSyncTime);
	{
		// Set the API to use the Post Method
		$settings = array(
			"post"		=> true
		);
		
		// Retrieve the Friends Packet
		if(!$packet = Connect::to("sync_friends", "PullFriends", true, $settings))
		{
			return time();
		}
		
		// Cycle through the list of friend connections provided and update them
		Database::startTransaction();
		
		foreach($packet as $conn)
		{
			// If the friend is designated to be deleted
			if($conn[2] < 1)
			{
				Database::query("DELETE IGNORE FROM users_friends WHERE uni_id=? AND friend_id=? LIMIT 1", array($conn[0], $conn[1]));
				Database::query("DELETE IGNORE FROM users_friends WHERE uni_id=? AND friend_id=? LIMIT 1", array($conn[1], $conn[0]));
			}
			
			// If the friend settings are applied
			else
			{
				Database::query("REPLACE INTO users_friends (uni_id, friend_id, view_clearance, interact_clearance) VALUES (?, ?, ?, ?)", array($conn[0], $conn[1], $conn[2], $conn[3]));
			}
		}
		
		Database::endTransaction();
		
		// No need for a time sync here
		return time();
	}
	
}
