<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------------
------ About the UniFaction Login Script ------
-----------------------------------------------

This login script is used to log into UniFaction's core system. When this page is loaded by an unlogged user, they will
be redirected to UniFaction's "Auth" page (login page), and instructed to log in. When they log in, they will be sent
back to your site and logged in automatically.

If the user is already logged into UniFaction, then it will automatically log them in.

---------------------------
------ Login Actions ------
---------------------------

There are different "actions" that are taken when logging in, and each has unique effects. In most cases, you can
ignore this behaviour.

1. The "Standard" login action is used by default. This activates when the user loads this page.

2. The "Soft-Login" is used if the user is visiting this site from another UniFaction site, and it triggers the ?slg=1
query string. What this means is that the user is already logged in and it should log them in without disturbing them
from their page visit.

3. The "Switch" action is used if someone is attempting to switch from their current profile to a different one on your
site. This will return them to UniFaction with the option to load one of their other profiles. Once they choose a
different profile to load, it will redirect back to your site and load them automatically.

-------------------------
------ Login Modes ------
-------------------------

There are also different "modes" of logins for sites. In most cases, you can ignore this behaviour completely.

1. The "Standard" mode is used by default, and has no unusual functionality.

2. The "1 Profile Recommendation" mode, or "1rec", means that this site prefers that you only use one profile, rather
than multiple accounts. This does not pertain to most sites, and it is only a recommendation.

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

// Prepare Values
if(!isset($_GET['logMode'])) { $_GET['logMode'] = ""; }
if(!isset($_GET['logAct'])) { $_GET['logAct'] = ""; }

// Get Login Properties
$logMode = (in_array($_GET['logMode'], array("1rec")) ? $_GET['logMode'] : "");
$logAct = (in_array($_GET['logAct'], array("soft", "switch")) ? $_GET['logAct'] : "");

// Make sure you're not already logged in
if(Me::$loggedIn)
{
	Me::logout();
}

// Log into UniFaction
if(!$loginResponse = UniFaction::login(SITE_URL . "/login", $logMode, $logAct))
{
	header("Location: /"); exit;
}

$uniID = User::authLogin($loginResponse);

if($uniID)
{
	// Successfully Logged In
	Me::login($uniID);
	
	// Check if the site needs to sync
	Sync::run();
}

// Update the display name, if applicable
if(Me::$loggedIn and $loginResponse['display_name'] != Me::$vals['display_name'])
{
	Database::query("UPDATE users SET display_name=? WHERE uni_id=? LIMIT 1", array(Me::$vals['display_name'], Me::$id));
}

// Check for custom login handling
if(function_exists("custom_login"))
{
	custom_login($loginResponse);
}

// Return to any page requested by the force-login mechanism
if(isset($_SESSION[SITE_HANDLE]['return_to']))
{
	$redirectTo = $_SESSION[SITE_HANDLE]['return_to'];
	
	unset($_SESSION[SITE_HANDLE]['return_to']);
	
	header("Location: " . $redirectTo); exit;
}