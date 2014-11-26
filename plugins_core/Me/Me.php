<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------
------ About the Me Plugin ------
---------------------------------

This plugin stores information regarding the "active user", or the user currently browsing the site. For example, if you log into a site with the handle "JoeSmith", Me::$vals['handle'] would be set to "JoeSmith", and Me::$id would be set to that user's UniID.

This plugin is based around the session variable $_SESSION[SITE_HANDLE], which tracks the user's UniID. On every page load, it pulls information the `users` table about that user.
	
	// An example of what information is gathered
	Me::$vals = Database::selectOne("SELECT * FROM users WHERE id=?", array($_SESSION[SITE_HANDLE]['id']));


The Me:: plugin is called on the index.php file in every site using the Me::initialize() and Me::runBehavior() methods. These will prepare the following values to use on the page:
	
	Me::$id				// The user's UniID, or 0 if the user is not logged in.
	Me::$clearance		// Clearance level of the user. 0 = guest, 6+ = mod permissions, 8+ = admin permissions.
	Me::$device			// Value of the device (1 = mobile, 2 = tablet, 3 = desktop).
	Me::$vals[]			// Contains an array of the user's data.
	Me::$loggedIn		// TRUE if the user is logged in, FALSE if not. (Could alternatively use Me::$id)
	Me::$slg			// Set to "" or "?slg=X" if logged in. Added to links to allow soft logins.
	
	
---------------------------------------
------ Understanding Soft Logins ------
---------------------------------------

"Soft Logins" refer to the users being logged onto our sites automatically, as long as they are logged into the Authentication site (or "Auth").

When visiting another site, you may have a value like "?slg=10" that appears. The "slg" is the "soft login" variable that is telling the site that it wants to silently log into the site you're visiting using that UniID.

For example, if you're currently logged into FastChat under the UniID of 10, you may see a link to Social that looks like this:
	
	http://unifaction.social?slg=10
	
In the phpTesla.php file, it will check to see if the "slg" value is present. If it is, it will then check if you're already logged in under that UniID. If you're not logged in with that UniID, it will communicate with the Auth server and confirm that you own the UniID and have permissions to log in with it. Then, it will log you in. This happens automatically without the user knowing it (hence the terminology "soft login").


-------------------------------------
------ User Login Redirections ------
-------------------------------------

A common requirement for pages is to make sure that the user is logged in. For example, you cannot view your "friends" page unless you're logged into it. There are two ways to handle a page that requires a login. You could either say that a login is required, or you could redirect the user to the opportunity to log in.

To redirect a user to login, use the Me::redirectLogin() method. Here's an example of how it looks:

	Me::redirectLogin("/friends?info=RequestsOnly", "/");

In this example, the user would first be asked to log in (or automatically logged in), and then redirected to the page "/friends?info=RequestsOnly". If something goes wrong during this process (such as an infinite loop), the fallback will cause the user to be redirected to "/" (the home page).


-------------------------------
------ Methods Available ------
-------------------------------

Me::initialize();				// Initializes the user for the page view.
Me::runBehavior();				// Runs behavior tests and checks if there are any special instructions.

Me::softLog([$chooseID]);		// Attempts to log in the user silently to a specific UniID.
Me::login($uniID)				// Login as a particular user (sets session variable).
Me::logout()					// Logs the current user out.
Me::load($uniID)				// Loads your personal user data.
Me::setCookie()					// Set cookies to remember the user.
Me::rememberMe()				// Auto-log user if they have authentic cookies.
Me::resetToken()				// Resets the user's token.

Me::redirectLogin($returnTo)	// Redirects to login, but returns you to $returnTo page after login

*/

abstract class Me {
	
	
/****** Prepare Variables ******/
	public static $id = 0;					// <int> The active user's UniID
	public static $clearance = 0;			// <int> The clearance level of the user (0 is guest)
	public static $device = 3;				// <int> Value of the device (1 = mobile, 2 = tablet, 3 = desktop).
	public static $slg = "";				// <str> The soft-login value to append if you're logged in.
	public static $vals = array();			// <str:str> The active user's data (from their database row)
	public static $loggedIn = false;		// <bool> TRUE if the user is logged in, FALSE if not.
	public static $getColumns = "";			// <str> The database columns to retrieve when loading the user.
	
	
/****** Initialize "Me" (the active user) ******/
	public static function initialize (
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Me::initialize();
	{
		if(!Database::$database) { return false; }
		
		// If you are logged in
		if(isset($_SESSION[SITE_HANDLE]['id']))
		{
			return self::load($_SESSION[SITE_HANDLE]['id']);
		}
		
		// If you're not logged in, run a "remember me" check
		return self::rememberMe();
	}
	
	
/****** Run a soft login (try to login without disrupting the user) ******/
	public static function softLog
	(
		$chooseID = 0	// <int> The UniID that is being set as active (0 if whatever Auth is logged as)
	,	$returnTo = ""	// <str> The URL to return to after logging in.
	)					// RETURNS <void>
	
	// Me::softLog([$chooseID], [$returnTo]);
	{
		// If a Chosen ID is set and is already active, ignore this process
		if($chooseID and isset($_SESSION[SITE_HANDLE]['id']))
		{
			if($_SESSION[SITE_HANDLE]['id'] == $chooseID) { return; }
		}
		
		// Create a soft logout
		unset($_SESSION[SITE_HANDLE]);
		
		// Assign the return URL
		if($returnTo)
		{
			$_SESSION[SITE_HANDLE]['return_url'] = $returnTo;
		}
		
		// Login with Auth
		UniFaction::login($chooseID); exit;
	}
	
	
/****** Login ******/
	public static function login
	(
		$uniID				// <int> The Uni-ID to log in as.
	,	$remember = false	// <bool> If set to true, add a remember me cookie.
	)						// RETURNS <bool> TRUE if login validation was successful, FALSE if not.
	
	// Me::login($uniID, true)
	{
		// Load Your Data
		self::$getColumns = "*";	// Retrieve all of your data during login
		self::load($uniID);
		
		if(self::$id == 0) { return false; }
		
		// Prepare User Session
		if(isset($_SESSION[SITE_HANDLE]['site_login']))
		{
			// This retains the site login redirection for UniFaction (Auth)
			$_SESSION[SITE_HANDLE] = array("site_login" => $_SESSION[SITE_HANDLE]['site_login']);
		}
		else
		{
			$_SESSION[SITE_HANDLE] = array();
		}
		
		$_SESSION[SITE_HANDLE]['id'] = self::$id;		// Required to load user each page.
		
		// Update the last login time (to right now)
		Database::query("UPDATE users SET date_lastLogin=? WHERE uni_id=? LIMIT 1", array(time(), self::$id));
		
		// Set "Remember Me" cookie if applicable
		if($remember)
		{
			self::setCookie();
		}
		
		return true;
	}
	
	
/****** Log Out ******/
	public static function logout(
	)			// RETURNS <bool> TRUE after removing all login sessions and cookies.
	
	// Me::logout()
	{
		Cookie::delete('userID_' . SITE_HANDLE);
		Cookie::deleteAll();
		
		$returnTo = (isset($_SESSION[SITE_HANDLE]['return_url']) ? $_SESSION[SITE_HANDLE]['return_url'] : false);
		
		// Unset All Session Values
		foreach($_SESSION[SITE_HANDLE] as $key => $val)
		{
			unset($_SESSION[SITE_HANDLE][$key]);
		}
		
		unset($_SESSION[SITE_HANDLE]);
		
		// Return to a specific address if required
		if($returnTo)
		{
			header("Location: " . $returnTo); exit;
		}
		
		return true;
	}
	
	
/****** Load My Data ******/
	public static function load
	(
		$uniID		// <int> The ID of the user you would like to load.
	)				// RETURNS <bool> TRUE if the user's data was loaded, FALSE if failed.
	
	// Me::load($uniID);
	{
		// Set your login tracker
		self::$loggedIn = true;
		
		// Get information from the database
		if(self::$getColumns)
		{
			self::$vals = Database::selectOne("SELECT " . self::$getColumns . " FROM users WHERE uni_id=? LIMIT 1", array($uniID));
		}
		else
		{
			self::$vals = Database::selectOne("SELECT uni_id, role, clearance, handle, display_name, date_joined FROM users WHERE uni_id=? LIMIT 1", array($uniID));
		}
		
		// Set your device value (1 = mobile, 2 = tablet, 3 = device)
		if(!isset($_SESSION[SITE_HANDLE]['device']))
		{
			$device = new DetectDevice();
			
			if($device->isMobile())
			{
				$_SESSION[SITE_HANDLE]['device'] = 1;
			}
			else if($device->isTablet())
			{
				$_SESSION[SITE_HANDLE]['device'] = 2;
			}
			else
			{
				$_SESSION[SITE_HANDLE]['device'] = 3;
			}
		}
		
		// Prepare the Device
		self::$device = (int) $_SESSION[SITE_HANDLE]['device'];
		
		// Make sure the user exists
		if(isset(self::$vals['uni_id']))
		{
			// Set your session ID, which corresponds to your database user ID
			self::$id = (int) self::$vals['uni_id'];
			
			// Set the user's soft login variable
			self::$slg = "?slg=" . self::$id;
			
			// Save your Clearance Level
			self::$clearance = (int) self::$vals['clearance'];
			
			// Handle Banned Accounts
			if(self::$clearance <= -3)
			{
				header("Location: /banned"); exit;
			}
			
			// Occasionally log activity (handles auro allotment)
			if(mt_rand(0, 25) == 22)
			{
				self::logActivity();
			}
			
			return true;
		}
		
		return false;
	}
	
	
/****** Run the karma, auro, and general activity log ******/
	public static function logActivity (
	)					// RETURNS <void>
	
	// Me::logActivity();
	{
		// Not ready yet
		Connect::to("karma", "KarmaActivityAPI", array("uni_id" => Me::$id, "site_handle" => SITE_HANDLE, "action" => "view"));
	}
	
	
/****** Set Cookie ******/
	private static function setCookie (
	)				// RETURNS <void>
	
	// self::setCookie($uniID)
	{
		self::resetToken(self::$id);
		
		$authData = User::get(self::$id, "auth_token");
		
		Cookie::set('userID_' . SITE_HANDLE, self::$id, $authData['auth_token']);
	}
	
	
/****** "Remember Me" Setting ******/
# Run this if you're not logged in. This will check for auto-login authentication, and self-log if it's valid.
	private static function rememberMe(
	)		// RETURNS <bool> TRUE on success, FALSE if failed.
	
	// self::rememberMe()
	{
		$varName = Cookie::varName('userID_' . SITE_HANDLE);
		
		// Make sure the user is using the remember me cookie
		if(!isset($_COOKIE[$varName]))
		{
			return false;
		}
		
		// Try to get the user data from the cookie
		if(!$authData = User::get((int) $_COOKIE[$varName], "auth_token"))
		{
			return false;
		}
		
		// Check the cookie authentication
		if(!$userID = Cookie::get('userID_' . SITE_HANDLE, $authData['auth_token']))
		{
			return false;
		}
		
		// Cookie checks have passed, log in
		return self::login((int) $userID, true);
	}
	
	
/****** Resets the User Token ******/
	private static function resetToken(
	)				// RETURNS <bool> TRUE if the token was updated, FALSE if not.
	
	// self::resetToken();
	{
		// Run the authentication token change
		$randToken = Security::randHash(22, 80);
		
		return Database::query("UPDATE users SET auth_token=? WHERE uni_id=? LIMIT 1", array($randToken, self::$id));
	}
	
	
/****** Forces user to login, then redirects back to page ******/
	public static function redirectLogin
	(
		$returnTo		// <str> The page to return to after login.
	,	$fallback = "/"	// <str> The URL to fall back to if the redirect fails.
	)					// RETURNS <void> REDIRECTS to login.
	
	// Me::redirectLogin("/page-to-return-to?extraVal=yep");
	{
		if(isset($_SESSION[SITE_HANDLE]['return_url']))
		{
			unset($_SESSION[SITE_HANDLE]['return_url']);
			
			header("Location: " . Sanitize::url($fallback)); exit;
		}
		
		$_SESSION[SITE_HANDLE]['return_url'] = $returnTo;
		
		header("Location: /login"); exit;
	}
}
