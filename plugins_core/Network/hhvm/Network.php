<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Network Plugin ------
--------------------------------------

This plugin is used to track and provide site data for other sites on the network, which is required for APIs to function.

Each site tracked includes:
	
	1. Name of the site
	2. The site handle it uses
	3. The clearance level of the site
	4. The URL of the site
	5. The API key that it interacts with this site with
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Get the site data for a connected site
$siteData = Network::get($siteHandle, [$scanAuth]);

// Get the shared API key with another site
$siteKey = Network::key($siteHandle);

// Syncronize this site with another one
Network::syncConnection($siteHandle, [$syncBoth], [$newKey]);

// Set the data you will use with another site
$key = Network::setData($siteHandle, $siteName, $siteURL, [$siteClearance], [$siteKey], [$overwrite]);

// Check if you're connected with a particular site
Network::isConnected($siteHandle);

*/

abstract class Network {
	
	
/****** Retrieve Network Data for a particular site ******/
	public static function get
	(
		string $siteHandle			// <str> The site reference to return the data from.
	,	bool $scanAuth = false	// <bool> If the site is not found, setting this to TRUE will scan AUTH for the site.
	): array <str, str>						// RETURNS <str:str> the data for the site, or array() on failure.
	
	// $siteData = Network::get($siteHandle, [$scanAuth]);
	{
		// Return the site data if you have the site available
		$siteData = Database::selectOne("SELECT site_url, site_key, site_name, site_clearance FROM network_data WHERE site_handle=? LIMIT 1", array($siteHandle));
		
		if($scanAuth == false or $siteData)
		{
			return $siteData;
		}
		
		// If the site wasn't found locally, connect to Auth so that we can retrieve the public information
		// If we don't have a connection to Auth setup, this step will fail
		if(!$siteData = Connect::to("unifaction", "GetSiteInfo", $siteHandle))
		{
			return array();
		}
		
		// Update your local copy for this site
		$siteData['site_key'] = self::setData($siteHandle, $siteData['site_name'], $siteData['site_url']);
		
		// Return the site data (or false if something went wrong)
		return $siteData;
	}
	
	
/****** Retrieve Network Data for a particular site ******/
	public static function key
	(
		string $siteHandle			// <str> The site reference to return the data from.
	): string						// RETURNS <str> the site key, or "" on failure.
	
	// $siteKey = Network::key($siteHandle);
	{
		return (string) Database::selectValue("SELECT site_key FROM network_data WHERE site_handle=? LIMIT 1", array($siteHandle));
	}
	
	
/****** Synchronize this site with another ******/
	public static function syncConnection
	(
		string $siteHandle			// <str> The site handle of the DESTINATION site to synchronize this site with
	,	bool $syncBoth = false	// <bool> TRUE to also sync this site (keep updated / renew keys)
	,	bool $newKey = true		// <bool> TRUE will sync new keys, but ONLY if both sites are being synced.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Network::syncConnection($siteHandle, [$syncBoth], [$newKey]);
	{
		// Prepare the Shared API Key
		if($newKey == true and $syncBoth == true)
		{
			$sharedKey = Security::randHash(mt_rand(65, 80), 75);
		}
		else if(!$sharedKey = self::key($siteHandle))
		{
			$syncBoth = true;
			$sharedKey = Security::randHash(mt_rand(65, 80), 75);
		}
		
		// Preparing the API
		$packet = array(
			"site_handle"	=> $siteHandle		// The site handle for the DESTINATION site
		,	"shared_key"	=> $sharedKey		// The shared key to use between the two sites
		,	"sync_both"		=> $syncBoth		// If TRUE, sync both sites - not just the destination
		);
		
		// Call the API
		if(!$siteData = self::get("unifaction"))
		{
			return false;
		}
		
		$response = Connect::call($siteData['site_url'] . "/api/NetworkSync", $packet, $siteData['site_key']);
		
		return $response ? true : false;
	}
	
	
/****** Set Site Data ******/
	public static function setData
	(
		string $siteHandle			// <str> The site handle of the site to create.
	,	string $siteName			// <str> The site name to set.
	,	string $siteURL			// <str> The URL to set.
	,	string $siteKey = ""		// <str> The key to set (random if none provided).
	,	bool $overwrite = true	// <bool> Any updates will overwrite the last one.
	): string						// RETURNS <str> the site key, or "" on failure.
	
	// $key = Network::setData($siteHandle, $siteName, $siteURL, [$siteKey], [$overwrite]);
	// $key = Network::setData("unifaction", "UniFaction", URL::unifaction_com(), [$siteKey], [$overwrite]);
	{
		// If we're not overwriting the data, check if it already exists.
		$siteData = $overwrite ? array() : Network::get($siteHandle);
		
		// If data does exist, we'll return the existing key.
		if($siteData !== array() and isset($siteData['site_key']))
		{
			return $siteData['site_key'];
		}
		
		// Generate a new site key if one wasn't provided
		$siteKey = ($siteKey !== "" ? $siteKey : Security::randHash(mt_rand(65, 80), 75));
		
		$success = Database::query(($overwrite ? "REPLACE" : "INSERT IGNORE") . " INTO network_data (site_handle, site_name, site_url, site_key) VALUES (?, ?, ?, ?)", array($siteHandle, $siteName, $siteURL, $siteKey));
		
		return $success ? $siteKey : "";
	}
	
	
/****** Set Clearance level for a site ******/
	public static function setClearance
	(
		string $siteHandle			// <str> The site handle of the site to create.
	,	int $clearanceLevel		// <int> The site clearance to set.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Network::setClearance($siteHandle, $clearanceLevel);
	{
		// Check if the site already exists
		if($siteData = Network::get($siteHandle))
		{
			return Database::query("UPDATE network_data SET site_clearance=? WHERE site_handle=? LIMIT 1", array($clearanceLevel, $siteHandle));
		}
		
		return Database::query("INSERT IGNORE INTO network_data (site_handle, site_clearance) VALUES (?, ?)", array($siteHandle, $clearanceLevel));
	}
	
	
/****** Test if another site is connected (registered) with this site ******/
	public static function isConnected
	(
		string $siteHandle		// <str> The handle of the other site to test a connection with.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Network::isConnected($siteHandle);
	{
		// Make sure the data exists on this site
		if(!$siteData = self::get($siteHandle))
		{
			return false;
		}
		
		// Check if the API is already connected
		$response = Connect::call($siteData['site_url'] . "/api/IsSiteConnected", "", $siteData['site_key']);
		
		return $response ? true : false;
	}
	
}