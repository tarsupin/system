<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Sync Plugin ------
-----------------------------------

This plugin performs critical functions for synchronizing data between sites.

The general structure of the synchronization system is that each SYNC SITE has a specific structure type that it synchronizes. For example, a SYNC SITE might collect all of the "Notification" instructions that have been generated.

There are many reasons why a site may want to synchronize data, such as for the following purposes:
	
	* Notifications
	* Friend Synchronization
	* News Updates
	* Search Engine Updates
	* Ad Updates
	* Tag Synchronization
	* Feeds
	* Comments
	* and more...

The Sync::run() method, which automatically runs the full system, is activated on several conditions:
	
	* Heavy traffic on the site will cause it to activate more frequently.
		* Every time users log in, the Sync methods may attempt to run.
	* The admins logging into the control panel may activate Sync (such as to retrieve new plugins / updates).
	* The admin can force a Sync to automatically occur.
	* The SYNC SITE may tell a server remotely that it needs to update, causing it to.
		* Higher priority with UniFaction may cause synchronization more frequently, such as VIP/Trusted statuses.
	* A CRON system can be used to activate requests on a timer (optional).
	
	
--------------------------
------ Sync Methods ------
--------------------------

Every plugin that runs synchronization has a ::sync() method that can be called automatically with Sync::run() or Sync::runSync($plugin). Every ::sync() method follows certain rules:

	1. It must accept $lastSyncTime as the first parameter, which is the timestamp of the last sync.
	
		* The last sync may not necessarily be last time it was run, but the first instance of when it needs to start
			tracking data.
			
			For example, if the last time it synced it retrieved 1000 rows and the last entry was fifteen days ago, it
			will set the last sync time to fifteen days ago. This way, upon syncing again, it won't lose any sync data.
	
	2. It must prepare (or accept) a packet from the connection.
	
		* It connects to the API on the relevant sync site.
			* For push syncs, it prepares a packet and submits it.
			* For pull syncs, it requests the packet and interprets it.
		
	3. It must return a sync time where the next sync should occur on.
	
	4. Optionally (and desirably) they can indicate the amount the delay should be modified based on the percentage of
		entries parsed vs. the total amount allowed to parse.
		
		By setting this value, the plugin can automatically adjust to an intelligent resource usage.
		
		The standard rate of synchronization should be 40% of it's total capacity. So if 1000 rows is the max range
		allowed to parse at once, then it should on average be parsing 400 rows.
	
	
------------------------------------------------
------ Sync "Pull" Request Method Example ------
------------------------------------------------
	
	public static function sync ($lastSyncTime)
	{
		// Pull the Sync Data
		$syncData = Connect::to("sync_friends", "PullSyncData", true);
		
		// Loop through each entry and add it to the appropriate table
		foreach($syncData as $entry) {
			self::interpretData($syncData);
		}
		
		// Return the final entry's timestamp
		return $syncData[count($syncData) - 1]['timestamp'];
	}
	
	
------------------------------------------------
------ Sync "Push" Request Method Example ------
------------------------------------------------
	
	public static function sync ($lastSyncTime)
	{	
		// Prepare Values
		$packet = array();
		$limit = 1000;
		
		// Retrieve a list of notifications to sync
		$results = Database::selectMultiple("SELECT * FROM notifications WHERE date_created > ? AND sync_unifaction=? ORDER BY date_created DESC LIMIT " . ($limit + 0), array($lastSyncTime, 1));
		
		// Get the last time sync
		$finalSync = (count($results) < $limit ? time() : $results[$count($results) - 1]['date_created']);
		
		// Cycle through the results and add them to the packet
		foreach($results as $res)
		{
			$packet[] = array($res['uni_id'], $res['sender_id'], substr($res['message'], 0, 255), $res['url'], $res['date_created']);
		}
		
		// Set the API to use the Post Method
		$settings = array(
			"post"		=> true
		);
		
		// Run the Notification API
		return Connect::to("sync_notifications", "PushNotifications", $packet, $settings) ? $finalSync : $lastSyncTime;
	}
	
-------------------------------
------ Methods Available ------
-------------------------------

// Run the entire sync system (automatically activates the most relevant syncs)
Sync::run($pluginList);

// Run a synchronization for a specific plugin
Sync::runSync($plugin, [$force]);

// Retrieves the sync tracker for a designated plugin
$tracker = Sync::getTracker($plugin);

// Sets the sync tracker for a designated plugin
Sync::setTracker($plugin, $trackerTime, $syncTime, [$delay], [$overwrite]);

*/

abstract class Sync {
	
	
/****** A generic "run" command to automate the entire Sync system ******/
	public static function run
	(
		array <int, str> $pluginList = array()	// <int:str> The list of plugins to check for syncing. Default: all of them.
	): bool							// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Sync::run($pluginList);
	{
		// Prepare SQL Filters
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("plugin" => $pluginList));
		
		// Prepare Values
		$timestamp = time();
		$runPlugin = "";
		$need = 0;
		
		// Retrieve a list of plugins and determine which plugin needs to synchronize the most
		if($results = Database::selectMultiple("SELECT * FROM sync_tracker" . ($sqlWhere ? " WHERE " . $sqlWhere : ""), $sqlArray))
		{
			foreach($results as $result)
			{
				$check = $timestamp - ((int) $result['tracker_time'] + (int) $result['delay']);
				
				if($check > 0 and $check > $need)
				{
					$need = $check;
					$runPlugin = $result['plugin'];
				}
			}
		}
		
		// If there was a plugin selected for synchronizing, run it
		if($runPlugin)
		{
			return self::runSync($runPlugin, true);
		}
		
		return false;
	}
	
	
/****** Sync a plugin ******/
	public static function runSync
	(
		string $plugin			// <str> The plugin to synchronize.
	,	bool $force = false	// <bool> TRUE will force the sync to occur, FALSE will check if it's a valid time.
	): bool					// RETURNS <bool> TRUE if synchronized, FALSE if not.
	
	// Sync::runSync($plugin, [$force]);
	{
		// Get the plugin's tracker data
		$tracker = self::getTracker($plugin);
		
		// Recognize Variables
		$tracker['tracker_time'] = (int) $tracker['tracker_time'];
		$tracker['delay'] = (int) $tracker['delay'];
		$tracker['sync_time'] = (int) $tracker['sync_time'];
		
		// Check if it's a valid time to check for updates
		if(!$force and $tracker['tracker_time'] + $tracker['delay'] < time())
		{
			$force = true;
		}
		
		// Run the plugin's sync function
		if($force and method_exists($plugin, "sync"))
		{
			// Run the Plugin's Synchronization
			$syncTime = call_user_func(array($plugin, "sync"), $tracker['sync_time']);
			
			// Update the tracker
			self::setTracker($plugin, time(), (int) $syncTime);
			
			return true;
		}
		
		return false;
	}
	
	
/****** Check a plugin's tracker data ******/
	public static function getTracker
	(
		string $plugin		// <str> The plugin to retrieve the last sync time for.
	): array <str, mixed>				// RETURNS <str:mixed> The tracker data for a plugin, array() on failure.
	
	// $tracker = Sync::getTracker($plugin);
	{
		return Database::selectOne("SELECT tracker_time, sync_time, delay FROM sync_tracker WHERE plugin=? LIMIT 1", array($plugin));
	}
	
	
/****** Update a plugin's tracker data ******/
	public static function setTracker
	(
		string $plugin				// <str> The plugin to set the sync time for.
	,	int $trackerTime		// <int> The timestamp of the last time the plugin attempted a synchronization.
	,	int $syncTime			// <int> The final sync time received (where to start the next sync).
	,	int $delay = -1			// <int> The amount of time that must pass before syncing again (default is 5 minutes).
	,	bool $overwrite = false	// <bool> TRUE if you force the maximum time, FALSE if overwriting is possible (can set lower).
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Sync::setTracker($plugin, $trackerTime, $syncTime, [$delay], [$overwrite]);
	{
		if($tracker = Database::selectOne("SELECT tracker_time, sync_time, delay FROM sync_tracker WHERE plugin=? LIMIT 1", array($plugin)))
		{
			$delay = ($delay == -1 ? (int) $tracker['delay'] : $delay);
			
			return Database::query("UPDATE sync_tracker SET tracker_time=?, sync_time=?, delay=? WHERE plugin=? LIMIT 1", array($trackerTime, $syncTime, $delay, $plugin));
		}
		
		$delay = ($delay == -1 ? 300 : $delay);
		
		return Database::query("INSERT INTO sync_tracker (plugin, tracker_time, sync_time, delay) VALUES (?, ?, ?, ?)", array($plugin, $trackerTime, $syncTime, $delay));
	}
	
}
