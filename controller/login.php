<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------------
------ About the UniFaction Login Script ------
-----------------------------------------------

If the user is already logged into UniFaction Auth server, then this page will automatically log them in to the current site.

Otherwise, the user will be redirected to UniFaction's "Auth" login page and instructed to log in. When they log in, they will be sent back to your site and logged in automatically.


-----------------------------
------ Response Values ------
-----------------------------

When a user logs into UniFaction after visiting your site, they will be redirected to your "/login" page with an array
of response data.

The values returned include:

	$loginResponse['uni_id']			// The UniID of the user
	$loginResponse['handle']			// The user's handle (e.g. "JoeSmith10")
	$loginResponse['display_name']		// The user's display name, (e.g. "Joe Smith")

*/
echo '2';
// Make sure you're not already logged in
if(Me::$loggedIn)
{
	Me::logout();
}
echo '3';
// Log into UniFaction
if(!$loginResponse = UniFaction::login())
{
	header("Location: /"); exit;
}
echo '4';
if($uniID = User::authLogin($loginResponse))
{
	echo '5';
	// Successfully Logged In
	Me::login($uniID);
}
echo '6';
// Update the display name, if applicable
if(Me::$loggedIn and $loginResponse['display_name'] != Me::$vals['display_name'])
{
	echo '7';
	Database::query("UPDATE users SET display_name=? WHERE uni_id=? LIMIT 1", array($loginResponse['display_name'], Me::$id));
}
echo '8';
// Check for custom login handling
if(function_exists("custom_login"))
{
	custom_login($loginResponse);
}

// Return to any page requested by the force-login mechanism
if(isset($_SESSION[SITE_HANDLE]['return_url']))
{
	$redirectTo = $_SESSION[SITE_HANDLE]['return_url'];
	echo '9';
	unset($_SESSION[SITE_HANDLE]['return_url']);
	
	header("Location: " . $redirectTo); exit;
}
echo '10';
header("Location: /"); exit;