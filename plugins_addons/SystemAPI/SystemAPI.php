<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API receives instructions from the System Panel and executes them.

	
------------------------------
------ Calling this API ------
------------------------------
	
	// Choose the necessary settings
	$settings = array();
	// $settings['encryption'] = "mythril";
	
	// Prepare the SystemAPI Packet
	$packet = array(
	);
	
	$response = Connect::call($urlToSite . "/api/SystemAPI", $packet, $apiKey, $settings);
	
	
[ Possible Responses ]
	{UNIQUE}

*/

class SystemAPI extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "default";	// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array("syspanel");		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 100000;		// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 8;			// <int> The minimum clearance level required to use this API.
	public $apiSafeguard = true;		// <bool> TRUE to run safeguarding measures with this API (prevent re-use).
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <str:mixed> site info on success, array() on failure.
	
	// $this->runAPI()
	{
		// Make sure that all of the necessary data was received
		if(ENVIRONMENT == "production")
		{
			if(!isset($this->data['salt']) or !isset($this->data['cipher_next']))
			{
				Alert::error("System API", "The system API was activated with incomplete data.", 10);
				
				return array("error" => "Not all of the necessary data was sent.");
			}
		}
		
		if(!isset($this->data['commands']) or !isset($this->data['scripts']) or !isset($this->data['responses']))
		{
			Alert::error("System API", "The system API was activated with incomplete data.", 10);
			
			return array("error" => "Not all of the necessary data was sent.");
		}
		
		// Get site variables for this API
		$sysVals = SiteVariable::load("system-api");
		
		// Make sure that the current cipher stored in the database matches up with what was sent
		if(!isset($sysVals['cipher']))
		{
			$sysVals['cipher'] = "";
		}
		
		// Check if the cipher can be deciphered with the salt sent
		else if($sysVals['cipher'] != Security::hash($this->data['salt'] . $this->apiKey, 100, 62))
		{
			Alert::error("System API", "The system API was activated with an inaccurate salt.", 10);
			
			return array("error" => "The API cipher needs to be reset.");
		}
		
		// Create scripts
		foreach($this->data['scripts'] as $scriptData)
		{
			// Get the base directory path to use for where this script should be saved
			$basePath = "";
			
			switch($scriptData[2])
			{
				case "CONF":
				case "CONF_PATH":
					$basePath = CONF_PATH;
					break;
				
				case "APP":
				case "APP_PATH":
					$basePath = APP_PATH;
					break;
				
				case "SYS":
				case "SYSTEM":
				case "SYS_PATH";
					$basepath = SYS_PATH;
					break;
			}
			
			if($basePath == "")
			{
				return array("error" => "The base directory used for one of the scripts was broken.");
			}
			
			// Sanitize the filepath
			$scriptData[0] = Sanitize::filepath($scriptData[0]);
			
			// Save the script
			File::write($basePath . "/" . ltrim($scriptData[0], '/'), $scriptData[1]);
		}
		
		// Update plugins
		foreach($this->data['plugins'] as $pluginData)
		{
			// Get the base directory path to use for where this script should be saved
			$pluginDir = "";
			
			switch($pluginData[2])
			{
				case "APP":
				case "CONF":
					$pluginDir = APP_PATH . "/plugins";
					break;
				
				case "ADDON":
					$pluginDir = ADDON_PLUGIN_PATH;
					break;
				
				case "SYS":
				case "SYSTEM":
				case "CORE";
					$pluginDir = CORE_PLUGIN_PATH;
					break;
			}
			
			if($pluginDir == "")
			{
				return array("error" => "The base directory used for the " . $pluginData[0] . " plugin was broken.");
			}
			
			// Get the config file of the plugin
			$pluginConfig = Plugin::getConfig($pluginData[0], $pluginDir);
			
			// Save the script
			File::write($pluginConfig->data['path'] . '/' . $pluginConfig->pluginName . '.php', $pluginData[1]);
			
			// Update the HHVM copy of this plugin
			HHVMConvert::convert($pluginData[0], $pluginDir);
		}
		
		// Run commands
		foreach($this->data['commands'] as $command)
		{
			call_user_func_array(array($command[0], $command[1]), $command[2]);
		}
		
		// Save the new data
		if(ENVIRONMENT == "production")
		{
			SiteVariable::save("system-api", "cipher", $this->data['cipher_next']);
		}
		
		// Prepare the response packet
		$responsePacket = array(
			"pass"					=> true
		,	"responses"				=> array()
		);
		
		// Run any "response" requests
		foreach($this->data['responses'] as $refVar => $resp)
		{
			$responsePacket['responses'][$refVar] = call_user_func_array(array($resp[0], $resp[1]), $resp[2]);
		}
		
		return $responsePacket;
	}
	
}
