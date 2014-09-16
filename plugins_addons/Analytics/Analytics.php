<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the Analytics Plugin ------
----------------------------------------

This plugin will provide tools to allow simple analytics on the site. This is not meant to be a comprehensive analytics solution.


-------------------------------
------ Methods Available ------
-------------------------------

Analytics::pageTracker([$backupDuration]);
Analytics::customTracker($trackerName, [$backupDuration]);

Analytics::backup($backupDuration, $type = "page-views");

*/

abstract class Analytics {
	
	
/****** Run a Page View Tracker (tracks the current page view into analytics) ******/
	public static function pageTracker
	(
		$backupDuration = 5184000	// <int> Duration to backup logs after (default: 60 days)
	)								// RETURNS <void>
	
	// Analytics::pageTracker([$backupDuration]);
	{
		global $url_relative;
		
		// Determine the time interval
		$timeInterval = floor(time() / 300) * 300;
		
		// Update or insert the page view
		if(!Database::query("UPDATE analytics_page_views SET visits=visits+1 WHERE timestamp_interval=? AND url_path=? LIMIT 1", array($timeInterval, $url_relative)))
		{
			Database::query("INSERT INTO analytics_page_views (timestamp_interval, url_path, visits) VALUES (?, ?, ?)", array($timeInterval, $url_relative, 1));
		}
		
		// 1 in 1000 chance to backup old logs
		if(rand(1, 1000) == 500 && $backupDuration > 0)
		{
			self::backup($backupDuration, "page-views");
		}
	}
	
	
/****** Run a Custom Analytics Tracker (tracks your custom tracker into analytics) ******/
	public static function customTracker
	(
		$trackerName				// <str> The name of the custom tracker
	,	$backupDuration = 5184000	// <int> Duration to backup logs after (default: 60 days)
	)								// RETURNS <void>
	
	// Analytics::customTracker($trackerName, [$backupDuration]);
	{
		// Determine the time interval
		$timeInterval = floor(time() / 300) * 300;
		
		// Update or insert the page view
		if(!Database::query("UPDATE analytics_custom_trackers SET visits=visits+1 WHERE timestamp_interval=? AND custom_tracker=? LIMIT 1", array($timeInterval, $trackerName)))
		{
			Database::query("INSERT INTO analytics_custom_trackers (timestamp_interval, custom_tracker, visits) VALUES (?, ?, ?)", array($timeInterval, $trackerName, 1));
		}
		
		// 1 in 2000 chance to backup old logs
		if(rand(1, 2000) == 500 && $backupDuration > 0)
		{
			self::backup($backupDuration, "custom-trackers");
		}
	}
	
	
/****** Prune Analytics Data ******/
	public static function backup
	(
		$backupDuration			// <int> The duration (in seconds) of backup logs to store (e.g. "past 60 days")
	,	$type = "page-views"	// <str> The type of analytics to back up (page-views, custom-trackers, etc.)
	)							// RETURNS <void>
	
	// Analytics::backup($backupDuration, $type = "page-views");
	{
		// Force a minimum backup of one day
		if($backupDuration < 86400)
		{
			$backupDuration = 86400;
		}
		
		$backupTime = time() - $backupDuration;
		$sqlLimit = 10000;
		
		// If you're backing up the "page view" analytics
		if($type == "page-views")
		{
			DBTransfer::move("analytics_page_views", "analytics_page_views_history", "timestamp_interval < ?", array($backupTime), $sqlLimit);
		}
		
		// If you're backing up the "custom tracker" analytics
		else if($type == "custom-trackers")
		{
			DBTransfer::move("analytics_custom_trackers", "analytics_custom_trackers_history", "timestamp_interval < ?", array($backupTime), $sqlLimit);
		}
	}
	
}

