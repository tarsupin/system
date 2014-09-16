<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the APITrack Plugin ------
---------------------------------------

This plugin is used to track API usage per site, and to make sure that sites are not absuing their access rights to your APIs.

For example, if a site was checking to see if a user's avatar exists on every page view through the AvatarExists API (which would connected to our site on every page view) rather than storing that information locally in an efficient manner, this system will be able to identify that they are abusing the API system and shut down their API privileges.

This tracker records the following:

	1. The site that is calling the API.
	2. The cycle (year + month) during which the API is being called.
	3. The API that is being called.
	4. The number of connections the site made to the API during this cycle.
	
The reason the tracker uses this information in a cycle is because each month the API usage is reset. This allows sites that have gone over their limit for the month to be able to access the APIs again.


-------------------------------------------
------ API Costs and Upgraded Access ------
-------------------------------------------

API access costs credits (generally a very small amount of credits) to use. Each API can cost a different amount, but the value is set with microcredits, which are 1/10,000th of a credit. A typical API should run approximately 10 microcredits. Light APIs (API's that don't require much computational power) should be less, and Heavy API's should be more.

All sites have a basic level of API access that they get for free. Thus, sites can connect automatically through the UniFaction system without having to pay. Large sites (such as sites that have a lot of traffic and need more connections to UniFaction's tools) may want to upgrade their payment plan so that their API use doesn't run out.

The API expenses for these connections will have to be compiled through reports on UniFaction on occassion. This is the primary reason for the API Tracker. Each individual site needs to be able to track the API budget independently and then report to UniFaction with full API reports.


-------------------------------
------ Methods Available ------
-------------------------------

// Retrieve the number of times the site has accessed this API
$timesAccessed = APITrack::get($siteHandle, $apiName);

// Updates the API Tracker (increases the access count by 1)
APITrack::track($siteHandle, $apiName);

*/

abstract class APITrack {
	
	
/****** Get the track information for this API ******/
	public static function get
	(
		$siteHandle		// <str> The site handle realted to this API track.
	,	$apiName		// <str> The name of the API.
	)					// <int> The number of times the API has been accessed.
	
	// $timesAccessed = APITrack::get($siteHandle, $apiName);
	{
		$timesAccessed = (int) Database::selectValue("SELECT times_accessed FROM api_tracker WHERE site_handle=? AND cycle=? AND api_name=? LIMIT 1", array($siteHandle, date("Ym"), $apiName));
		
		return $timesAccessed ? $timesAccessed : 0;
	}
	
	
/****** Update the API Tracker ******/
	public static function track
	(
		$siteHandle		// <str> The site handle realted to this API track.
	,	$apiName		// <str> The name of the API.
	)					// <bool> TRUE on success, FALSE on error.
	
	// APITrack::track($siteHandle, $apiName);
	{
		if($timesAccessed = self::get($siteHandle, $apiName))
		{
			return Database::query("UPDATE api_tracker SET times_accessed=times_accessed+1 WHERE site_handle=? AND cycle=? AND api_name=? LIMIT 1", array($siteHandle, date("Ym"), $apiName));
		}
		
		return Database::query("INSERT IGNORE INTO api_tracker (site_handle, cycle, api_name, times_accessed) VALUES (?, ?, ?, ?)", array($siteHandle, date("Ym"), $apiName, 1));
	}
	
}
