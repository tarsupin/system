<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API allows UniFaction and Auth to instruct the site to perform an important function. The site can be instructed to run a system-wide script (stored in the {SYS_PATH}/system-script.php file), or to run it's plugin's actions at a higher clearance level than than the public can.

The site may be prompted with instructions such as automatically updating plugins (optimizations / security updates, etc), to sync content, migrate, or otherwise perform some function on the site that it should be activating at this time.

This API will enable the clearance of the plugin action to be run at clearance level 8.


------------------------------------------------------------------
------ Calling this API to trigger the site's system script ------
------------------------------------------------------------------

To trigger the site's system script (saved at {SYS_PATH}/system-script.php), the only data that needs to be sent is the line "system-script".
	
	// Run the command
	$response = Connect::to($siteHandle, "AuthCommand", "system-script");
	
	
-----------------------------------------------------------------
------ Calling this API to authorize multiple instructions ------
-----------------------------------------------------------------

	// Create an instruction (or multiple instructions, if desired)
	$instruction = array(
		"plugin"		// The name of the plugin to provide an instruction to.
	,	"action"		// The name of the action ("action_" + method) to run.
	,	"arg"			// The argument to pass to the action being run.
	);
	
	// Add each instruction to the packet
	$packet = array($instruction, [..$instruction]);
	
	// Run the command
	$response = Connect::to($siteHandle, "AuthCommand", $packet);
	
	
[ Possible Responses ]
	TRUE if the site was properly updated.
	FALSE if the site failed to update properly.

*/

class AuthCommand extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array("auth", "unifaction");		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 50000;		// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 8;			// <int> The minimum clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $this->runAPI()
	{
		// Check if this was designed to run the global script
		if($this->data == "system-script")
		{
			// Check if the global script is available
			if(File::exists(SYS_PATH . "/system-script.php"))
			{
				define("PROTECTED", true);
				
				require(SYS_PATH . "/system-script.php");
				
				return true;
			}
			
			return false;
		}
		
		// Make sure the proper data was provided
		if(!is_array($this->data) or count($this->data) == 0)
		{
			return false;
		}
		
		// Prepare Values
		$success = true;
		
		// Run the instructions provided
		foreach($this->data as $instruction)
		{
			// The name of the Plugin to run
			$plugin = Sanitize::variable($instruction['plugin']);
			
			// The name of the Plugin's Action to run
			$method = Sanitize::variable($instruction['action']);
			
			// Make sure the runAPI method exists
			if(method_exists($plugin, "action_" . $method))
			{
				// Run the plugin's action
				$success = call_user_func(array($plugin, "action_" . $method), 8, $instruction['arg']) ? $success : false;
			}
		}
		
		return $success;
	}
	
}
