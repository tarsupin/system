<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------
------ About the Me Plugin ------
---------------------------------

This plugin sets up and handles the active user - that is, the user currently browsing the site (that is interacting with the server).

This plugin is based around the session variable $_SESSION[SITE_HANDLE], which tracks your personal information.


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Me::$getColumns = "*";	// Uncomment to retrieve all rows

// Initialize and Test Active User's Behavior
if(Me::initialize())
{
	Me::runBehavior($url);
}

// See the results of what was returned
var_dump(Me::$id);
var_dump(Me::$clearance);
var_dump(Me::$vals);


-----------------------------------------------------------
------ Important Variables for Feeds and Advertising ------
-----------------------------------------------------------

While browsing UniFaction, the user may follow sites (or certain sections within those sites). This information is recorded in the Me:: class so that it can be reused in other plugin, such as to assist with advertising or preferential data that could be loaded.

Me::$vals['follow'][$referenceID] is a (str:bool) array that contains any page-relevant referenceID as a key, indicating that it has been followed by the user.

Me::$vals['follow_site'] is a boolean (generally only set to true). If set, it means that the user is following the site.

-------------------------------
------ Methods Available ------
-------------------------------

Me::initialize();				// Initializes the user for the page view.
Me::runBehavior();				// Runs behavior tests and checks if there are any special instructions.

Me::update($userID, $colVals)	// Returns your current role, or sets it if a new role is passed.
Me::login($uniID)				// Login as a particular user (sets session variable).
Me::load($uniID)				// Loads your personal user data.
Me::logout()					// Logs the current user out.
Me::resetToken()				// Resets the user's token.
Me::setCookie()					// Set cookies to remember the user.
Me::rememberMe()				// Auto-log user if they have authentic cookies.

Me::redirectLogin($returnTo)	// Redirects to login, but returns you to $returnTo page after login

*/

abstract class Me {
	
	
/****** Prepare Variables ******/
	public static int $id = 0;					// <int> The active user's UniID
	public static int $clearance = 0;			// <int> The clearance level of the user (0 is guest)
	public static int $device = 3;				// <int> Value of the device (1 = mobile, 2 = tablet, 3 = desktop).
	public static array <str, str> $vals = array();			// <str:str> The active user's data (from their database row)
	public static bool $loggedIn = false;		// <bool> TRUE if the user is logged in, FALSE if not.
	public static string $getColumns = "";			// <str> The database columns to retrieve when loading the user.
	
	
/****** Initialize "Me" (the active user) ******/
	public static function initialize (
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
	
	
/****** Run Behaviors on Active User ******/
	public static function runBehavior
	(
		array <int, str> $url	// <int:str> The URL data that was recorded by the page.
	): void			// RETURNS <void> REDIRECT when instruction requires redirect.
	
	// if(Me::initialize()) { Me::runBehavior($url); }
	{
		// Handle Banned Accounts
		if(Me::$clearance <= -3)
		{
			if($url[0] != "user-panel" or $url[1] != "banned")
			{
				header("Location: /user-panel/banned"); exit;
			}
		}
		
		// Check if there are any further instructions to follow
		if(Me::$vals['has_instructions'] != 1) { return; }
		
		// Run User Instructions
		UserInstruct::runInstructions(Me::$id);
	}
	
	
/****** Run a soft login (try to login without disrupting the user) ******/
	public static function softLog
	(
		int $chooseID = 0	// <int> The UniID that is being set as active (0 if whatever Auth is logged as)
	): void					// RETURNS <void>
	
	// Me::softLog([$chooseID]);
	{
		// If a Chosen ID is set and is already active, ignore this process
		if($chooseID and isset($_SESSION[SITE_HANDLE]['id']))
		{
			if($_SESSION[SITE_HANDLE]['id'] == $chooseID) { return; }
		}
		
		global $url_relative;
		
		// Create a soft logout
		Cookie::delete('userID_' . SITE_HANDLE);
		unset($_SESSION[SITE_HANDLE]);
		
		// Prepare the return value
		$_SESSION[SITE_HANDLE]['return_to'] = '/' . ($url_relative != "" ? $url_relative : "login");
		
		// Login with Auth
		UniFaction::login(SITE_URL . "/login", "", "", $chooseID); exit;
	}
	
	
/****** Login ******/
	public static function login
	(
		int $uniID				// <int> The Uni-ID to log in as.
	,	bool $remember = false	// <bool> If set to true, add a remember me cookie.
	): bool						// RETURNS <bool> TRUE if login validation was successful, FALSE if not.
	
	// Me::login($uniID, true)
	{
		// Load Your Data
		self::$getColumns = "*";	// Retrieve all of your data during login
		self::load($uniID);
		
		if(self::$id == 0) { return false; }
		
		// Prepare User Session
		if(isset($_SESSION[SITE_HANDLE]['return_to']))
		{
			// This section prevents the "return_to" variable from being lost during login
			// This allows us to return back to the URL that the user actually wants to load up
			$_SESSION[SITE_HANDLE] = array("return_to" => $_SESSION[SITE_HANDLE]['return_to']);
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
	): bool			// RETURNS <bool> TRUE after removing all login sessions and cookies.
	
	// Me::logout()
	{
		Cookie::delete('userID_' . SITE_HANDLE);
		Cookie::deleteAll();
		
		$returnTo = (isset($_SESSION[SITE_HANDLE]['return_to']) ? $_SESSION[SITE_HANDLE]['return_to'] : false);
		
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
		int $uniID		// <int> The ID of the user you would like to load.
	): bool				// RETURNS <bool> TRUE if the user's data was loaded, FALSE if failed.
	
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
			self::$vals = Database::selectOne("SELECT uni_id, role, clearance, handle, display_name, has_instructions, has_notifications FROM users WHERE uni_id=? LIMIT 1", array($uniID));
		}
		
		// Set your device value (1 = mobile, 2 = tablet, 3 = device)
		if(!isset($_SESSION[SITE_HANDLE]['device']))
		{
			$device = new Device();
			
			$_SESSION[SITE_HANDLE]['device'] = (int) ($device->isMobilePhone ? 1 : self::$device);
		}
		
		self::$device = (int) $_SESSION[SITE_HANDLE]['device'];
		
		// Make sure the user exists
		if(isset(self::$vals['uni_id']))
		{
			// Set your session ID, which corresponds to your database user ID
			self::$id = (int) self::$vals['uni_id'];
			
			// Save your Clearance Level
			self::$clearance = (int) self::$vals['clearance'];
			
			// Recognize Integers
			if(isset(self::$vals['auth_id']))
			{
				self::$vals['auth_id'] = (int) self::$vals['auth_id'];
			}
			
			return true;
		}
		
		return false;
	}
	
	
/****** Set Cookie ******/
	private static function setCookie (
	): void				// RETURNS <void>
	
	// self::setCookie($uniID)
	{
		self::resetToken(self::$id);
		
		$authData = User::get(self::$id, "auth_token");
		
		Cookie::set('userID_' . SITE_HANDLE, self::$id, $authData['auth_token']);
	}
	
	
/****** "Remember Me" Setting ******/
# Run this if you're not logged in. This will check for auto-login authentication, and self-log if it's valid.
	private static function rememberMe(
	): bool		// RETURNS <bool> TRUE on success, FALSE if failed.
	
	// self::rememberMe()
	{
		$varName = Cookie::varName('userID_' . SITE_HANDLE);
		
		// Make sure the user is using the remember me cookie
		if(!isset($_COOKIE[$varName]))
		{
			return false;
		}
		
		$authData = User::get($_COOKIE[$varName], "auth_token");
		
		// Check the cookie authentication
		if(!$userID = Cookie::get('userID_' . SITE_HANDLE, $authData['auth_token']))
		{
			return false;
		}
		
		// Cookie checks have passed, log in
		return self::login($userID, true);
	}
	
	
/****** Resets the User Token ******/
	private static function resetToken(
	): bool				// RETURNS <bool> TRUE if the token was updated, FALSE if not.
	
	// self::resetToken();
	{
		// Run the authentication token change
		$randToken = Security::randHash(22, 80);
		
		return Database::query("UPDATE users SET auth_token=? WHERE uni_id=? LIMIT 1", array($randToken, self::$id));
	}
	
	
/****** Forces user to login, then redirects back to page ******/
	public static function redirectLogin
	(
		string $returnTo		// <str> The page to return to after login.
	,	string $fallback = ""	// <str> The URL to fall back to if the redirect fails.
	): void					// RETURNS <void> REDIRECTS to login.
	
	// Me::redirectLogin("/page-to-return-to?extraVal=yep");
	{
		if(isset($_SESSION[SITE_HANDLE]['return_to']))
		{
			unset($_SESSION[SITE_HANDLE]['return_to']);
			
			$fallback = $fallback == "" ? URL::auth_unifaction_com() : Sanitize::url($fallback);
			
			header("Location: " . $fallback); exit;
		}
		
		$_SESSION[SITE_HANDLE]['return_to'] = $returnTo;
		
		header("Location: /login"); exit;
	}
}