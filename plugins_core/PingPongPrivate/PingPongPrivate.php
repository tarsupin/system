<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This is an example of a public API. When you send it "ping" it returns "pong!". Otherwise, it returns "You didn't ping.".

------------------------------
------ Calling this API ------
------------------------------
	
	$packet = "ping";
	
	$response = Connect::call("http://example.com/api/PingPongPrivate", $packet);
	
	
[ Possible Responses ]
	"pong!" 				// if the packet sent was equal to "ping"
	"You didn't ping."		// if the packet sent was anything else

*/

class PingPongPrivate extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 10;			// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 0;			// <int> The clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <str> the PingPongPrivate response.
	
	// $this->runAPI()
	{
		if($this->data == "ping")
		{
			return "pong!";
		}
		
		return "You didn't ping.";
	}
	
}
