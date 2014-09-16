<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the Notification Plugin ------
-------------------------------------------

This plugin provides notification handling tools, such as receiving notifications and displaying them.

For the user's table, `has_notifications` is set to 1 when you have notifications, and 2 when there are global notifications.

These notifications may be triggered from official UniFaction sites (such as to notify the admins of important updates), or from within your own site. You can optionally allow other sites to trigger these alerts by setting their clearance to trusted (or higher) in the network_data table.

Notifications can be categorized. If the category "Updates Available" is triggered, it may provide a notification that there are updates for your plugins available on UniFaction. Every time you receive a notification of a particular category, the notifications will be added to that category.

This plugin can also create and track notifications that are delivered directly to the mods, staff, and admins of the site. This is useful for making sure that the appropriate site handlers are aware of important situations that must have their attention drawn to them.


-------------------------------
------ Methods Available ------
-------------------------------

Notifications::create($uniID, $category, $message, [$url], [$senderID], [$sync]);
Notifications::createGlobal($message, $url, [$sync]);

Notifications::notifyStaff($category, $message, [$minClearance], [$maxClearance], [$url], [$senderID], [$sync]);

$notifications = Notifications::get($uniID);
$notifications = self::getFullList($uniID);
$globalNotes = Notifications::getGlobal($page, $numToShow);

Notifications::sideWidget();

// Synchronize this site's notifications to UniFaction
Notifications::sync($lastTimeSync);

*/

abstract class Notifications {
	
	
/****** Create a Standard Notification ******/
	public static function create
	(
		int $uniID				// <int> The UniID to create a notification for.
	,	string $category			// <str> The category (type) of notification.
	,	string $message			// <str> The message (what the notification says); 150 characters.
	,	string $url = ""			// <str> The url that you can follow (if you click the notification).
	,	int $senderID = 0		// <int> The uni_id responsible for sending the notification (0 if server).
	,	bool $sync = false		// <bool> Set to TRUE to sync this notification with Auth.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Notifications::create($uniID, $category, $message, [$url], [$senderID], [$sync]);
	{
		Database::startTransaction();
		
		// Create the new Notification
		if($success = Database::query("INSERT INTO notifications (uni_id, sender_id, category, message, url, date_created, sync_unifaction) VALUES (?, ?, ?, ?, ?, ?, ?)", array($uniID, $senderID, $category, $message, $url, time(), ($sync ? 1 : 0))))
		{
			// Check for excess Notifications
			Database::query("DELETE FROM `notifications` WHERE uni_id=? AND category=? AND date_created <= (SELECT date_created FROM (SELECT date_created FROM `notifications` WHERE uni_id=? AND category=? ORDER BY date_created DESC LIMIT 1 OFFSET 5) foo)", array($uniID, $category, $uniID, $category));
			
			// Clear old notifications every once in a while
			if(mt_rand(0, 10000) == 250)
			{
				Database::query("DELETE FROM notifications WHERE date_created < ?", array((time() - (3600 * 24 * 31))));
			}
			
			// Set this user's notifications to 1
			Database::query("UPDATE users SET has_notifications=? WHERE uni_id=? AND has_notifications=? LIMIT 1", array(1, $uniID, 0));
		}
		
		return Database::endTransaction($success);
	}
	
	
/****** Create a Global Notification ******/
	public static function createGlobal
	(
		string $message			// <str> The notification message.
	,	string $url				// <str> The URL to use for the notification.
	,	bool $sync = true		// <bool> TRUE to sync this global update with Auth.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Notifications::createGlobal($message, $url);
	{
		Database::startTransaction();
		
		// Create the new Global Notification
		if($success = Database::query("INSERT INTO notifications_global (message, url, date_created, sync_unifaction) VALUES (?, ?, ?, ?)", array($message, $url, time(), ($sync ? 1 : 0))))
		{
			// Set ALL user notification levels to 2
			Database::query("UPDATE users SET has_notifications=?", array(2));
		}
		
		return Database::endTransaction($success);
	}
	
	
/****** Create a Notification for Staff Members ******/
	public static function notifyStaff
	(
		string $category			// <str> The category (type) of notification.
	,	string $message			// <str> The message (what the notification says); 150 characters.
	,	int $minClearance = 5	// <int> The minimum clearance level to send this notification to.
	,	int $maxClearance = 10	// <int> The maximum clearance level to send this notification to.
	,	string $url = ""			// <str> The url that you can follow (if you click the notification).
	,	int $senderID = 0		// <int> The uni_id responsible for sending the notification (0 if server).
	,	bool $sync = false		// <bool> Set to TRUE to sync this notification with Auth.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Notifications::notifyStaff($category, $message, [$minClearance], [$maxClearance], [$url], [$senderID], [$sync]);
	{
		// Prepare Values
		$minClearance = max(5, $minClearance);
		$maxClearance = min(10, $maxClearance);
		$success = true;
		$staffList = array();
		
		// Get a list of staff that meet the clearance level indicated
		if($staff = Database::selectMultiple("SELECT uni_id FROM users WHERE clearance BETWEEN ? AND ?", array($minClearance, $maxClearance)))
		{
			Database::startTransaction();
			
			foreach($staff as $member)
			{
				$staffList[] = (int) $member['uni_id'];
				$success = self::create((int) $member['uni_id'], $category, $message, $url, $senderID, $sync) ? $success : false;
			}
			
			Database::endTransaction();
		}
		
		return $success;
	}
	
	
/****** Get Standard Notifications ******/
	public static function get
	(
		int $uniID				// <int> The UniID to return notifications for.
	,	bool $fullList = false	// <bool> Set to TRUE to get the full list (rather than a small list).
	): array <str, int>						// RETURNS <str:int> the list of notifications, array() on failure.
	
	// $notifications = Notifications::get($uniID, [$fullList]);
	{
		if($fullList) { return self::getFullList($uniID); }
		
		// Get Notifications by Cache (if available)
		$checkData = Cache::get("noti:" . $uniID);
		
		if($checkData)
		{
			return json_decode($checkData);
		}
		
		// Get Notifications by Database
		$retList = array();
		$results = Database::selectMultiple("SELECT category, COUNT(*) as totalNum FROM notifications WHERE uni_id=? GROUP BY category", array($uniID));
		
		foreach($results as $res)
		{
			$retList[$res['category']] = (int) $res['totalNum'];
		}
		
		// Store the notifications for 10 minutes
		Cache::set("noti:" . $uniID, json_encode($retList), 600);
		
		return $retList;
	}
	
	
/****** Get Full List of Standard Notifications ******/
	private static function getFullList
	(
		int $uniID				// <int> The Uni-Account to return notifications for.
	): array <int, array<str, str>>						// RETURNS <int:[str:str]> the list of notifications, array() on failure.
	
	// $notifications = self::getFullList($uniID);
	{
		return Database::selectMultiple("SELECT * FROM notifications WHERE uni_id=? ORDER BY category, date_created DESC", array($uniID));
	}
	
	
/****** Get Global Notifications ******/
	public static function getGlobal
	(
		int $page = 1			// <int> The current page of notifications to view.
	,	int $numToShow = 20		// <int> The number of global notifications to return.
	): array <int, array<str, str>>						// RETURNS <int:[str:str]> the list of global notifications, FALSE on failure.
	
	// $globalNotes = Notifications::getGlobal($page, $numToShow);
	{
		return Database::selectMultiple("SELECT * FROM notifications_global ORDER BY date_created DESC LIMIT " . (($page - 1) * $numToShow) . ", " . ($numToShow + 0), array());
	}
	
	
/****** Notifications Widget (for the sidebar) ******/
	public static function sideWidget (
	): string				// RETURNS <str> HTML of a sidebar widget (or hidden), FALSE on failure.
	
	// echo Notifications::sideWidget();
	{
		// If the user has notifications, attempt to call notifications
		if(isset(Me::$vals['has_notifications']) && Me::$vals['has_notifications'] > 0)
		{
			$sideBarNotes = self::get(Me::$id);
			
			if(count($sideBarNotes) > 0)
			{
				$html = '
				<div class="panel-box">
					<a href="/user-panel/notifications" class="panel-head">Notifications<span class="icon-circle-right nav-arrow"></span></a>
					<ul class="panel-notes">';
				
				// Show Global Updates (if any new available)
				if(Me::$vals['has_notifications'] > 1)
				{
					$html .= '
					<li class="nav-note"><a href="/user-panel/notifications"><span style="color:#ee6060;">Global Updates</span></a></li>';
				}
				
				// Show list of Standard Notifications
				foreach($sideBarNotes as $cat => $count)
				{
					$html .= '
					<li class="nav-note"><a href="/user-panel/notifications"><span>' . $cat . ' <span style="color:#ee6060;">(' . $count . ')</span></span></a></li>';
				}
				
				$html .= '
					</ul>
				</div>';
				
				return $html;
			}
		}
		
		return "";
	}
	
	
/****** Sync Notifications ******/
# Note: Only some notifications actually get sent to Auth. Many are local only.
	public static function sync
	(
		int $lastSyncTime		// <int> The timestamp of the last sync.
	): int						// RETURNS <int> The updated sync time.
	
	// Notifications::sync($lastSyncTime);
	{
		// Prepare Values
		$packet = array();
		$limit = 1000;
		
		// Retrieve a list of notifications to sync
		$results = Database::selectMultiple("SELECT * FROM notifications WHERE date_created > ? AND sync_unifaction=? ORDER BY date_created DESC LIMIT " . ($limit + 0), array($lastSyncTime, 1));
		
		// Get the last time sync
		$finalSync = (count($results) < $limit ? time() : (int) $results[$count($results) - 1]['date_created']);
		
		// Cycle through the results and add them to the packet
		foreach($results as $res)
		{
			$packet[] = array((int) $res['uni_id'], $res['sender_id'], substr($res['message'], 0, 255), $res['url'], (int) $res['date_created']);
		}
		
		// Set the API to use the Post Method
		$settings = array(
			"post"		=> true
		);
		
		// Run the Notification API
		$syncKnown = Connect::to("sync_notifications", "PushNotifications", $packet, $settings) ? $finalSync : $lastSyncTime;
		
		return ((int) $syncKnown > $finalSync ? (int) $syncKnown : $finalSync);
	}
}
