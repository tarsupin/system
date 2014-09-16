<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------
------ About the API Plugin ------
----------------------------------

This plugin is used to create or handle APIs that other phpTesla sites can interact with. Other sites will connect to this API using the Connect:: plugin.


----------------------------------------
------ How to create your own API ------
----------------------------------------

To create your own API, you need to do the following:

	1. Create a plugin that extends the API class.
	2. Set the API settings that apply to your API.
	3. Create the runAPI() method.
	4. Use $this->data to interact with the array (or value) that was sent to the API.
	5. Your runAPI() method must return the value you want your API to respond with.
	
	
For example, here is a simple working example of the "PingPong" API Plugin:

	class PingPong extends API
	{	
		// API Settings
		public $isPrivate = false;
		public $encryptType = "";
		public $allowedSites = array();
		
		public function runAPI()
		{
			return $this->data == "ping" ? "pong" : "You didn't ping.";
		}
	}
	
	
And to call the "PingPong" API Plugin:
	
	// Call the "PingPong" API plugin on "auth"
	Connect::$isPrivate = false; // because this is a public API
	$response = Connect::to("auth", "PingPong", "ping");
	
	
The PingPong API will wait for you to send it data. If you respond "pong!" if you send it "ping" - otherwise, it will respond "You didn't ping.".

You can add your API Plugin to your site like any other plugin, either in your system plugins or your application plugin.


------------------------------
------ The API Settings ------
------------------------------

There are a few API settings that you need to consider:
	
	// Public vs. Private
	public $isPrivate = true;		// Requires the site calling your API to have a shared API key with your site.
	public $isPrivate = false;		// This is an open API. Anyone can call it, and the data sent is unencrypted.
	
	// Encrypted Response vs. Unencrypted Response
	# Most APIs are unencrypted since they typically only respond with TRUE, NULL, or other non-private data.
	# However, if an API responds with private data, make sure you encrypt it with an appropriate algorithm.
	# To encrypt an API with an encryption algorithm of your choice, just set the algorithm to use:
	public $encryptType = "";			// The default setting. The response is unencrypted.	
	public $encryptType = "default";	// Encrypts response with the "default" algorithm (see "Encrypt" class).
	
	// The list of allowed sites
	public $allowedSites = array();		// All sites that can interact with you (such as with shared keys) can use it.
	public $allowedSites = array("social", "chat");		// Only the sites listed (by site handle) can use this API.
	
	// Micro Credit Cost
	# This helps mitigate the issue of sites using our API's haphazardly, such as to check if an avatar exists every page
	# view rather than storing that information locally. It does this by charging a very tiny transaction for using the
	# API call. If the site's budget doesn't account for the costs, it cannot access our API's any longer.
	# Every site receives free credits towards API usage, so this does not mean there are actually API charges accruing
	# when these values are set.
	# Try to base this value on the computational cost of the API.
	public $microCredits = 1;		// This will charge the site 0.0001 credits for using this API
	
	// Minimum Clearance
	# If this value is set above 0, the API must be private. Only sites that were granted a clearance higher than this
	# value are permitted to use the API.
	public $minClearance = 0;		// This value is open to the public.
	

----------------------------------
------ Returning API Errors ------
----------------------------------

If you want to return an alert message with the API (generally an error, but doesn't have to be), you can simply set $this->alert to the message you want to return. The Connect plugin will be able to read it with Connect::$alert;

For example, if the API was attempting to identify a user's timestamp, the API can return FALSE and send the error "User does not exist" or "User did not set a timestamp" depending on what happened. For example:
	
	if(!$user)
	{
		$this->error = "User was not found.";
		return false;
	}
	else if(!$user['timestamp'] == "")
	{
		$this->alert = "User did not set a timestamp.";
		return false;
	}
	
------------------------------
------ Sending MetaData ------
------------------------------

If there is any additional information you'd like to send back about the API, you can send it by setting $this->meta["YOURVALUE"];

This is useful for encrypted packets that have non-encrypted information to pass along, particularly if the meta data doesn't fit into the same variable type as the other information does.

Using the value is simple:
	
	$this->meta["API_used_on"] = time();
	$this->meta["rand_value"] = mt_rand(0, 100);
	
The API will automatically send it back, and it can be received on the other end with the ['meta'] packet.


-------------------------------
------ Methods Available ------
-------------------------------

// Initializes the API (and interprets the data sent to it)
$dataReceived = new API();

// Returns the API's response (and ends the script)
API::respond($response);

*/

class API {
	
	
/****** Plugin Variables ******/
	
	// API Settings
	public bool $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public string $encryptType = "";			// <str> The type of encryption to respond with. Or "" for no encryption.
	public array <int, str> $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public int $microCredits = 10;			// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public int $minClearance = 0;			// <int> The minimum clearance level required to use this API.
	public bool $apiSafeguard = true;		// <bool> TRUE to run safeguarding measures with this API (prevent re-use).
	
	// Connection Data
	public mixed $data = "";			// <mixed> The API data that was sent.
	public string $apiHandle = "";		// <str> The site handle that connected to the API.
	public int $apiClearance = 0;	// <int> The clearance level of the site using this API.
	public string $apiKey = "";		// <str> The API key that is being used to connect.
	public string $apiSalt = "";		// <str> The API salt that is being used to connect.
	public string $apiConf = "";		// <str> The API confirmation value to prove the encryption wasn't tampered.
	
	// Alerts
	public array <str, mixed> $meta = array();		// <str:mixed> Mixed metadata that can be sent along with the data (non-encrypted)
	public string $alert = "";			// <str> If set, this will send an alert along with the API response.
	
	
/****** Run the Full API, including interpreting the response ******/
	public function __construct (
	): mixed					// RETURNS <mixed> the interpretation of the message
	
	// $dataReceived = new API();
	{
		// Get Post Data, if applicable
		if(isset($_POST['postData']))
		{
			$this->data = $_POST['postData'];
		}
		
		// If we're not using POST, retrieve the $_GET data
		else if(isset($_GET['api']))
		{
			// Make sure the API data was sent
			$this->data = $_GET['api'];
		}
		
		// If no data was sent, end now
		else
		{
			$this->alert = "Error: #00";
			return false;
		}
		
		// If the API is private, make sure the proper information was included
		if($this->isPrivate)
		{
			if(!isset($_GET['site']) or !isset($_GET['salt']) or !isset($_GET['conf']))
			{
				$this->alert = "Error: #6272";
				return false;
			}
			
			// If there are only certain sites that are allowed to access this API, validate that here:
			if($this->allowedSites != array())
			{
				if(!in_array($_GET['site'], $this->allowedSites))
				{
					$this->alert = "Error: #5173";
					return false;
				}
			}
			
			// Prepare the connection data for this API
			$siteData = Network::get($_GET['site']);
			
			$this->apiHandle = $_GET['site'];
			$this->apiClearance = $siteData['site_clearance'];
			$this->apiKey = $siteData['site_key'];
			$this->apiSalt = $_GET['salt'];
			$this->apiConf = $_GET['conf'];
			
			// Make sure the site connecting has the appropriate clearance
			if($this->apiClearance < $this->minClearance)
			{
				$this->alert = "Error: #37347";
				return false;
			}
			
			// Get the API Data
			$listen = Decrypt::run($this->apiKey . $this->apiSalt, $this->data);
			
			// Make sure we pass the confirmation test (to prove that the data wasn't tampered)
			if($this->apiConf != Security::hash($this->apiKey . $this->apiSalt . $listen, 20))
			{
				$this->alert = "Error: #3041";
				return false;
			}
			
			$this->data = json_decode($listen, true);
			
			// Check if we passed the timestamp check (i.e. it was sent recently, using cross-server swatch time)
			if(!Time::unique($this->data['_enc']))
			{
				$this->alert = "Error: #343";
				return false;
			}
			
			unset($this->data['_enc']);
			
			// If the data contains a "_orig" array, it means it was originally a string, number, or other non-array.
			if(isset($this->data['_orig']))
			{
				$this->data = $this->data['_orig'];
			}
			
			// Run API Tracking (unless your clearance enables you to bypass it)
			if($this->apiClearance < 6)
			{
				// Run the tracking operations
				APITrack::track($this->apiHandle, get_called_class());
			}
		}
		
		// Public APIs are simple: just json_decode the value that was sent
		else
		{
			$this->data = json_decode($this->data, true);
		}
		
		// Check if this API has already been run once before
		// Only run this check if the API is private
		if($this->apiSafeguard and $this->isPrivate)
		{
			if(!$this->confirmAPISafe())
			{
				$this->alert = "Error: #291";
				return false;
			}
		}
		
		// Get the API response
		$response = $this->runAPI();
		
		// Prepare the return packet
		$packet = array();
		
		// If encryption is set, return the encrypted response
		if($this->encryptType != "" and $this->isPrivate)
		{
			$packet['resp'] = Encrypt::run($this->apiKey . $this->apiSalt, json_encode($response), $this->encryptType);
			$packet['enc'] = true;
		}
		else
		{
			$packet['resp'] = $response;
		}
		
		// If there was an alert set, include that in the response
		if($this->alert) { $packet['alert'] = $this->alert; }
		if($this->meta) { $packet['meta'] = $this->meta; }
		
		// Set this API so that it cannot be run again
		if($this->apiSafeguard and $this->isPrivate and ENVIRONMENT == "production")
		{
			$this->consumeAPI();
		}
		
		// Return the standard packet
		echo json_encode($packet); exit;
	}
	
	
/****** Confirm that this API is safe to use (hasn't already been activated) ******/
	public function confirmAPISafe (
	): bool					// RETURNS <bool> TRUE if the API is safe to use, FALSE if it's already been used.
	
	// $runAPI = $this->confirmAPISafe();
	{
		// Check if this API has already been used
		return Database::selectValue("SELECT conf FROM api_conf_hash WHERE conf=? LIMIT 1", array($this->apiConf)) ? false : true;
	}
	
	
/****** Consume the API's activation (prevent it from being reused) ******/
	public function consumeAPI (
	): void					// RETURNS <void>
	
	// $this->consumeAPI();
	{
		$timestamp = time();
		
		// Consume the confirmation value that this API uses
		Database::query("INSERT INTO api_conf_hash (conf, date_run) VALUES (?, ?)", array($this->apiConf, $timestamp));
		
		// Prune any old values every few API calls
		if(mt_rand(0, 30) == 22)
		{
			Database::query("DELETE FROM api_conf_hash WHERE date_run < ?", array($timestamp - 300));
		}
	}
}