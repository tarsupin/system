<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This is an example of a public API. When you send it "ping" it returns "pong!". Otherwise, it returns "You didn't ping.".

------------------------------
------ Calling this API ------
------------------------------
	
	$packet = "ping";
	
	$response = Connect::call("http://example.com/api/PingPong", $packet);
	
	
[ Possible Responses ]
	"pong!" 				// if the packet sent was equal to "ping"
	"You didn't ping."		// if the packet sent was anything else

*/

class PingPong extends API {
	
	
/****** API Variables ******/
	public bool $isPrivate = false;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public string $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public array <int, str> $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	
	
/****** Run the API ******/
	public function runAPI (
	): string					// RETURNS <str> the PingPong response.
	
	// $this->runAPI()
	{
		if($this->data == "ping")
		{
			return "pong!";
		}
		
		return "You didn't ping.";
	}
	
}