<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the Notification Plugin ------
-------------------------------------------

This plugin provides notification handling tools, such as receiving notifications and displaying them.


-------------------------------
------ Methods Available ------
-------------------------------

Notifications::create($uniID, $url, $message);

Notifications::get($uniID, [$page], [$returnNum]);

+Notifications::createGlobal($message, $url, [$sync]);
+Notifications::notifyStaff($category, $message, [$minClearance], [$maxClearance], [$url], [$senderID], [$sync]);

*/

abstract class Notifications {
	
	
/****** Get a user's notifications ******/
	public static function get
	(
		int $uniID				// <int> The UniID to get notifications from.
	,	int $page = 1			// <int> The page to start at (pagination value).
	,	int $returnNum = 5		// <int> The total number of rows to return.
	): array <int, array<str, mixed>>						// RETURNS <int:[str:mixed]> the list of notifications for the user.
	
	// $notifications = Notifications::get($uniID, [$page], [$returnNum]);
	{
		// Prepare the Packet
		$packet = array("uni_id" => 1);
		
		if($page != 1) { $packet['page'] = $page; }
		if($returnNum != 5) { $packet['return_num'] = $returnNum; }
		
		// Connect to this API from UniFaction
		return Connect::to("sync_notifications", "GetNotificationsAPI", $packet);
	}
	
	
/****** Create a Standard Notification ******/
	public static function create
	(
		int $uniID				// <int> The UniID to create a notification for.
	,	string $url = ""			// <str> The url that you can follow (if you click the notification).
	,	string $message			// <str> The message (what the notification says); 150 characters.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Notifications::create($uniID, $url, $message);
	{
		// Prepare the Packet with list of notifications
		$packet = array("uni_id" => $uniID, "url" => $url, "message" => $message);
		
		// Run the API
		return Connect::to("sync_notifications", "AddNotificationAPI", $packet);
	}
	
}