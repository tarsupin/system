<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Connect Plugin ------
--------------------------------------

This plugin is used to connect with other UniFaction APIs. It treats APIs like they're a function and will automatically encrypt and decrypt the APIs so that no additional work is necessary.

To set up an API, you will need to review the API plugin.

--------------------------------
------ How to call an API ------
--------------------------------

There are two ways to call APIs. If you have a connection established with that site (including a shared API key), you can call that site's private API's. To call a private API you only need to run one line:
	
	$response = Connect::to($siteHandle, $apiName, $dataToSend);
	
	
For example, your API may look like this:
	
	// Check if you are connected to "auth"
	$response = Connect::to("auth", "IsSiteConnected", "hello!");
	
	
The parameters do the following:

	$siteHandle		// The handle of the site you're trying to call (such as "auth" or "social")
	$apiName		// The name of the API that you're connecting to
	$dataToSend		// The variable (array, string, integer, etc) that you're passing to the API
	$response		// Captures the API's response
	
	
You can only send one variable to the API (with $dataToSend), but it can be an array of data (which is the most common type of variable to send).

The API will provide a response, such as to indicate true or return the values you were expecting.


---------------------------------
------ Calling Public APIs ------
---------------------------------

Calling public APIs requires the use of the Connect::call() method. You must enter the full URL path to the public API. For example:
	
	$packet = "ping";
	$response = Connect::call(URL::auth_unifaction_com() . "/api/PingPong", $packet);
	
You will have to know the URL public API, but the standard convention is to use the /api segment followed by the name of the API you are attempting to call.
	
	
------------------------------
------ Receiving Alerts ------
------------------------------

Sometimes an API needs to give you more of a response than just TRUE or FALSE. API's can also send alerts, such as error codes. This will provide additional information to the response in case there is something that needs to be learned about why the API failed (or, in rare cases, why it succeeded).

To access the alerts that are sent by the API, just refer to the Connect::$alert value after the response has been gathered. For example:

	$response = Connect::to($siteHandle, $apiName, $dataTosend)
	
	if($response === false)
	{
		if(Connect::$alert)
		{
			echo "The API encountered the following error: " . Connect::$alert;
		}
	}
	
	
--------------------------------------------------
------ Additional Settings and Instructions ------
--------------------------------------------------

You can provide additional settings and instructions with your API call to adjust it's behavior. For example:

	$settings = array(
		"post"			=> true					// Use $_POST instead of $_GET
	,	"encryption"	=> "fast"				// Use "fast" encryption algorithm rather than default
	,	"filepath"		=> "./path/image"		// Send a file with the API call
	);

The following settings are recognized:
	
	"post"			// A boolean. TRUE will set the connection to use $_POST rather than $_GET
	"encryption"	// A string. You can set the "Encrypt" plugin algorithm you want to use. Default is "default".
	"filepath"		// A string. The filepath to the file you want to send with the API call.

	
For example, if you want to pass a file:

	$settings = array("filepath" => $_FILES['image']['tmp_name']);
	$response = Connect::to($siteHandle, $apiName, $dataTosend, $settings);
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Connects to an API (and returns the response)
$response = Connect::to($siteHandle, $apiName, $apiData, [$settings]);

// This method grants more control than Connect::to, but is more complicated to use
$response = Connect::call($apiFullURL, $apiData, $apiKey, [$settings]);

*/

abstract class Connect {
	
	
/****** Plugin Variables ******/
	public static string $alert = "";		// <str> The alert received by the last API connection, if applicable.
	public static array <str, mixed> $meta = array();	// <str:mixed> Additional meta data that was sent. Will be non-encrypted.
	public static string $url = "";		// <str> The exact URL that was used for the API call
	
	
/****** Connect to an API ******/
	public static function to
	(
		string $siteHandle			// <str> The handle of the site to connect to
	,	string $apiName			// <str> The name of the API
	,	mixed $apiData = ""		// <mixed> Any data to pass to the API call
	,	array <str, mixed> $settings = array()	// <str:mixed> Additional settings or instructions to provide.
	): mixed						// RETURNS <mixed> the response of the API call
	
	// $response = Connect::to($siteHandle, $apiName, [$apiData], [$settings]);
	{
		// Get the network data
		if(!$siteData = Network::get($siteHandle))
		{
			// Attempt to sync the network connection automatically if the connection doesn't exist yet
			if(!Network::syncConnection($siteHandle, true, true))
			{
				Alert::saveError("Not Connected", "Cannot retrieve a valid connection with `" . $siteHandle . "`", 7);
				return false;
			}
			
			// Retrieve the updated information about the network connection (should have working key now)
			$siteData = Network::get($siteHandle);
		}
		
		// Run the API Call
		return self::call($siteData['site_url'] . "/api/" . $apiName, $apiData, $siteData['site_key'], $settings);
	}
	
	
/****** An ALL-IN-ONE call handler of an API ******/
	public static function call
	(
		string $apiFullURL			// <str> The Full API URL that you're calling, including the host
	,	mixed $apiData = ""		// <mixed> Any data to pass to the API call
	,	string $apiKey = ""		// <str> The API Key that corresponds to the API you're calling
	,	array <str, mixed> $settings = array()	// <str:mixed> Additional settings or instructions to provide.
	): mixed						// RETURNS <mixed> the response of the API call
	
	// $response = Connect::call("http://example.com/api/this-api", $dataToSend, [$apiKey], [$settings]);
	{
		// If the user is calling a private API (that requires a key)
		if($apiKey != "")
		{
			// Make sure we're sending an array (so that we can pass additional encryption)
			if(!is_array($apiData))
			{
				$apiData = array('_orig' => $apiData);
			}
			
			$apiData['_enc'] = Time::unique();
			
			// Prepare API
			$apiSalt = Security::randHash(15, 62);
		}
		
		// Get important information about the address of the API
		$api = parse_url($apiFullURL);
		
		// Make sure the API is valid
		if(!isset($api['host']))
		{
			return false;
		}
		
		// Prepare Values
		$apiPost = "";
		$apiData = json_encode($apiData);
		$apiPath = ($api['path'] ? $api['path'] : '/');
		
		// Prepare a public API
		if($apiKey == "")
		{
			$apiPath .= (isset($api['query']) && $api['query'] != "" ? "?" . $api['query'] . '&' : "?")
					. "api=" . urlencode($apiData);
		}
		
		// Prepare a Private API
		else
		{
			// Prepare the "Encrypt" plugin algorithm to use for this encryption
			$algo = (isset($settings['encryption']) ? Sanitize::word($settings['encryption']) : "default");
			
			// If we're running in POST mode
			if(isset($settings['post']))
			{
				$apiPost = Encrypt::run($apiKey . $apiSalt, $apiData, $algo);
			}
			
			// Prepare the URL String
			$apiPath .= (isset($api['query']) && $api['query'] != "" ? "?" . $api['query'] . "&" : "?")
					. "site=" . SITE_HANDLE
					. "&api=" . urlencode(Encrypt::run($apiKey . $apiSalt, ($apiPost ? "" : $apiData), $algo))
					. "&salt=" . $apiSalt
					. "&conf=" . urlencode(Security::hash($apiKey . $apiSalt . $apiData, 20));
		}
		
		// Set the URL that was most recently connected to
		self::$url = $api['scheme'] . "://" . $api['host'] . $apiPath;
		
		// Process the API and get the response
		$respPacket = self::processCall($api['host'], $apiPath, $apiPost, $settings);
		
		// Capture the data that was received by the API call
		$respPacket = json_decode($respPacket, true);
		
		// Retrieve the standard response from the packet returned
		if(isset($respPacket['enc']))
		{
			$response = json_decode(Decrypt::run($apiKey . $apiSalt, $respPacket['resp']), true);
		}
		else
		{
			$response = $respPacket['resp'];
		}
		
		// Get the alert that was returned, if applicable
		self::$alert = isset($respPacket['alert']) ? $respPacket['alert'] : "";
		self::$meta = isset($respPacket['meta']) ? $respPacket['meta'] : array();
		
		return $response;
	}
	
	
/****** Process the Call ******/
	private static function processCall
	(
		string $apiHost			// <str> The API Host that you're connecting to.
	,	string $apiPath			// <str> The API Path that you're connecting to.
	,	string $apiPost			// <str> The API Data that you're using (in JSON form).
	,	array <str, mixed> $settings = array()	// <str:mixed> Additional settings or instructions to provide.
	): mixed						// RETURNS <mixed> the response of the API call
	
	// $response = self::processCall($apiHost, $apiPath, $apiPost, [$settings]);
	{
		// Get cURL resource
		$curl = curl_init();
		
		// If we're running in POST mode
		if(isset($settings['post']))
		{
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1
			,	CURLOPT_URL => "http://" . $apiHost . $apiPath
			,	CURLOPT_POST => true
			,	CURLOPT_POSTFIELDS => array("postData" => $apiPost)
			));
		}
		
		// If we're running in GET mode
		else
		{
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1
			,	CURLOPT_URL => "http://" . $apiHost . $apiPath
			));
		}
		
		// If we're sending a file (such as an image)
		if(isset($settings['filepath']))
		{
			// This section should work according to php.net, but appears broken. Using deprecated option for now.
			if(false and function_exists('curl_file_create'))
			{
				$cfile = curl_file_create($settings['filepath']);
				
				$postData = array("filename" => $cfile);
			}
			else
			{
				// The same as using <input type="file" name="fileName" />
				$postData = array(
					"fileName"	=>	"@" . $settings['filepath']		// Requires the @ to send it as a file
				);
			}
			
			// Set the post sending options
			curl_setopt($curl, CURLOPT_POST, true);
			@curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		}
		
		// Send the request and save the response
		$response = curl_exec($curl);
		
		// Close request to clear up some resources
		curl_close($curl);
		
		return $response;
	}
	
}