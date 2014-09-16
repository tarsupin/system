<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API returns "true" and nothing else. If the API wasn't able to be called, it means there wasn't a connection properly established, and therefore it will return NULL instead.
	
	
------------------------------
------ Calling this API ------
------------------------------
	
	$response = Connect::to($siteHandle, "IsSiteConnected");
	
	
[ Possible Responses ]
	TRUE if a connection was established.
	NULL if no connection was established.

*/

class IsSiteConnected extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 10;				// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 0;				// <int> The minimum clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <bool> TRUE if this API was accessed.
	
	// $this->runAPI()
	{
		return true;
	}
	
}
