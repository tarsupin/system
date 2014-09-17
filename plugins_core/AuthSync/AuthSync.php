<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API is designed to provide a safe mechanism for establishing and synchronizing your site with others on the UniFaction network.

To understand how this works, consider that all sites that you have not been established with have no form of encrypted communication with you. However, all of them are already networked with UniFaction and can perform encrypted communication with UniFaction. Therefore, rather than attempting to communicate over plaintext, UniFaction acts as the encrypted channel and passes the appropriate encrypted information to each site.
	
This API is essential for proper security within phpTesla for three primary reasons:

	1. Any time you set up a shared key with another site, this makes sure that only you and the other site will be able to interpret the keys that were provided.
	
	2. If your keys are ever compromised, this API allows allow UniFaction to quickly re-secure your entire key list.
	
	3. Rather than having to manually update your keys, UniFaction can automate this process. This is both more secure and means admins are more likely to update (which is good security practice).
	
	
------------------------------
------ Calling this API ------
------------------------------

UniFaction is authorized to call this API:
	
	$packet = array(
		"handle"		// The site (by site handle) to synchronize with your site.
	,	"name"			// The name of the site you are synchronizing with.
	,	"url"			// The URL of the site you are synchronizing with.
	,	"clearance"		// The clearance level of the site you are synchronizing with.
	,	"key"			// The shared API key that you and the connected site will synchronize.
	);
	
	$response = Connect::to($siteHandle, "AuthSync", $packet);
	
	
[ Possible Responses ]
	TRUE if the site was properly synchronized.
	FALSE if the site failed to synchronize properly.

*/

class AuthSync extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 10000;		// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 8;			// <int> The minimum clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $this->runAPI()
	{
		// Update the network connection data
		if(Network::setData($this->data['handle'], $this->data['name'], $this->data['url'], $this->data['key']))
		{
			// Update the network clearance level
			if(Network::setClearance($this->data['handle'], (int) $this->data['clearance']))
			{
				return true;
			}
		}
		
		return false;
	}
	
}
