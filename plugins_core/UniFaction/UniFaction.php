<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the UniFaction Plugin ------
-----------------------------------------

This plugin allows the site to login through UniFaction and return the appropriate user data to your site's "/login" page if it was successful.

Once a user is logged into the site, they can access many of UniFaction's features.


-------------------------------
------ Methods Available ------
-------------------------------

// Get the login response from UniFaction
$loginResponse = UniFaction::login(SITE_URL . "/login", [$logMode], [$logAct]);

*/

abstract class UniFaction {
	
	
/****** Universal Login with UniFaction User ******/
	public static function login
	(
		$linkback		// <str> The URL link back to the page (used to return after logging in).
	,	$logMode = ""	// <str> The mode to use for logging in.
	,	$logAct = ""	// <str> The action to use for logging in.
	,	$chosenID = 0	// <int> The chosen UniID to log in with.
	)					// RETURNS <str:mixed> response from UniFaction, or array() if not available.
	
	// $loginResponse = UniFaction::login(SITE_URL . "/login", [$logMode], [$logAct], [$chosenID]);
	{
		// Get the site data
		if(!$siteData = Network::get("unifaction"))
		{
			return array();
		}
		
		// Prepare a random hash to validate handshake
		if(!isset($_SESSION[SITE_HANDLE]['unilogin_handshake']))
		{
			$_SESSION[SITE_HANDLE]['unilogin_handshake'] = Security::randHash(15, 62);
		}
		
		// Check if the data sent was valid
		if(isset($_GET['enc']) && $_SESSION[SITE_HANDLE]['unilogin_handshake'])
		{
			// Get Login Response
			$loginResponse = Decrypt::run($siteData['site_key'], $_GET['enc']);
			$loginResponse = json_decode($loginResponse, true);
			
			if(isset($loginResponse['handshake']) && $loginResponse['handshake'] == $_SESSION[SITE_HANDLE]['unilogin_handshake'])
			{
				// Unset Handshake Value
				unset($_SESSION[SITE_HANDLE]['unilogin_handshake']);
				
				// Return the Authorization API response
				return $loginResponse;
			}
		}
		else
		{
			// Redirect to the Universal Login Page - get credentials and return
			header("Location: " . $siteData['site_url'] . "/login?site=" . SITE_HANDLE . "&shk=" . $_SESSION[SITE_HANDLE]['unilogin_handshake'] . "&conf=" . Security::hash(SITE_HANDLE . $_SESSION[SITE_HANDLE]['unilogin_handshake'] . $siteData['site_key'], 20, 62) . "&ret=" . rawurlencode($linkback) . ($logMode != "" ? '&logMode=' . Sanitize::variable($logMode) : '') . '&logAct=' . ($logAct != "" ? Sanitize::variable($logAct) : "") . ($chosenID != 0 ? "&chooseID=" . $chosenID : "")); exit;
		}
		
		return array();
	}
}


