<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Cookie Plugin ------
-------------------------------------

This plugin allows you to create secure cookies. This is particularly helpful for remembering users that are returning to your site, but can also be used for other purposes.


-----------------------------------------
------ Example of using this class ------
-----------------------------------------

// To set the cookies, run Cookie::set(...)
if($justLoggedIn == true OR $regenerateCookies == true)
{
	Cookie::set("uniID", $user['uni_id'], $user['password'] . $user['auth_token']);
}

// Every page view, you'll want to check if your session forgets. If it does, use the reminder.
if(!isset($_SESSION[SITE_HANDLE]['id']))
{
	$retVal = Cookie::get("uniID", $user['password'] . $user['auth_token']);
	
	if($retVal !== false)
	{
		$_SESSION[SITE_HANDLE]['id'] = $retVal;
	}
}


-------------------------------
------ Methods Available ------
-------------------------------

// Retrieve a cookie's value
$value = Cookie::get($cookieName, [$salt])

// Create a cookie
Cookie::set($cookieName, $valueToRemember, [$salt], [$expiresInDays = 90])

// Deletes a cookie
Cookie::delete($cookieName);

*/

abstract class Cookie {
	
	
/****** Get the variable name of the cookie ******/
	public static function varName
	(
		$cookieName		// <str> The name of the cookie you're returning.
	)					// RETURNS <str> variable name of the cookie.
	
	// $cookieVarName = Cookie::varName("userID-auth");
	{
		return Security::hash($cookieName, 5, 62) . '-' . $cookieName;
	}
	
	
/****** Get Cookie Value ******/
	public static function get
	(
		$cookieName		// <str> The name of the cookie you're trying to remember.
	,	$salt = ""		// <str> The unique identifier for the validation.
	)					// RETURNS <str> value to remember if successful, or "" if validation failed.
	
	// Cookie::get("uniID", $user['password'] . "extraSalt");
	// Cookie::get($cookieName, [$salt]);
	{
		// Prepare Values
		$cookieName = Security::hash($cookieName, 5, 62) . '-' . $cookieName;
		
		// Check if Cookie is Valid
		if(isset($_COOKIE[$cookieName]) and isset($_COOKIE[$cookieName . "_key"]))
		{
			// Prepare Token
			$token = Security::hash($_COOKIE[$cookieName] . SITE_SALT . $salt, 50, 62);
			
			/*
			echo "<br /><br /> Token String: " . $_COOKIE[$cookieName] . SITE_SALT . $salt;
			echo "<br /><br /> COOKIE: " . json_encode($_COOKIE);
			echo "<br /><br /> Cookie Name: " . $cookieName;
			echo "<br /><br /> Token: " . $token;
			echo "<br /><br /> SITE SALT: " . SITE_SALT;
			echo "<br /><br /> Value: " . $_COOKIE[$cookieName];
			echo "<br /><br /> Key: " . $_COOKIE[$cookieName . "_key"];
			echo "<br /><br /> Salt: " . $salt;
			//*/
			
			if($_COOKIE[$cookieName . "_key"] === $token)
			{
				return $_COOKIE[$cookieName];
			}
		}
		
		return "";
	}
	
	
/****** Create Cookies w/ Authentication ******/
	public static function set
	(
		$cookieName				// <str> The name of the cookie you're trying to remember.
	,	$valueToRemember		// <str> The value you want to remember.
	,	$salt = ""				// <str> The unique salt you want to use to keep the cookie safe.
	,	$expiresInDays = 30		// <int> The amount of time the cookie should last, in days.
	)							// RETURNS <void>
	
	// Cookie::set('uniID', $user['id'], $user['password'] . "extraSalt");
	// Cookie::set($cookieName, $cookieValue, [$cookieSalt], [$expiresInDays]);
	{
		// Prepare Values
		$cookieName = Security::hash($cookieName, 5, 62) . '-' . $cookieName;
		
		// Prepare Values
		$timestamp = time();
		$token = Security::hash($valueToRemember . SITE_SALT . $salt, 50, 62);
		
		$parsedURL = URL::parse($_SERVER['SERVER_NAME']);
		
		self::delete($cookieName);
		
		// Set the cookie
		setcookie($cookieName, $valueToRemember, $timestamp + (86400 * $expiresInDays), "/", $parsedURL['host']);
		setcookie($cookieName . "_key", $token, $timestamp + (86400 * $expiresInDays), "/", $parsedURL['host']);
		
		/*
		echo "<br /><br /> Token String: " . $valueToRemember . SITE_SALT . $salt;
		echo "<br /><br /> COOKIE: " . json_encode($_COOKIE);
		echo "<br /><br /> Cookie Name: " . $cookieName;
		echo "<br /><br /> Token: " . $token;
		echo "<br /><br /> Parsed URL: " . json_encode($parsedURL);
		echo "<br /><br /> Value to Remember: " . $valueToRemember;
		echo "<br /><br /> SITE SALT: " . SITE_SALT;
		echo "<br /><br /> Salt: " . $salt;
		*/
	}
	
	
/****** Delete Cookie ******/
	public static function delete
	(
		$cookieName		// <str> The name of the cookie you're trying to delete.
	)					// RETURNS <void>
	
	// Cookie::delete("uniID");
	{
		// Prepare Values
		$cookieName = Security::hash($cookieName, 5, 62) . '-' . $cookieName;
		$timestamp = time();
		
		// Remove Global Cookie Values
		if(isset($_COOKIE[$cookieName]))
		{
			unset($_COOKIE[$cookieName]);
		}
		
		if(isset($_COOKIE[$cookieName . "_key"]))
		{
			unset($_COOKIE[$cookieName . "_key"]);
		}
		
		$parsedURL = URL::parse($_SERVER['SERVER_NAME']);
		
		// Remove desired Cookie and its associated key
		setcookie($cookieName, "", $timestamp - 360000, "/", $parsedURL['host']);
		setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $parsedURL['host']);
		
		// Remove desired Cookie and its associated key
		setcookie($cookieName, "", $timestamp - 360000, "/", $parsedURL['baseDomain']);
		setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $parsedURL['baseDomain']);
	}
	
	
/****** Delete All Cookies ******/
	public static function deleteAll (
	)					// RETURNS <void>
	
	// Cookie::deleteAll();
	{
		$timestamp = time();
		$parsedURL = URL::parse($_SERVER['SERVER_NAME']);
		
		if(!$_COOKIE) { return; }
		
		// Loop through each cookie
		foreach($_COOKIE as $cookieName => $value)
		{
			// Remove Global Cookie Values
			if(isset($_COOKIE[$cookieName]))
			{
				unset($_COOKIE[$cookieName]);
			}
			
			if(isset($_COOKIE[$cookieName . "_key"]))
			{
				unset($_COOKIE[$cookieName . "_key"]);
			}
			
			// Remove desired Cookie and its associated key
			setcookie($cookieName, "", $timestamp - 360000, "/", $parsedURL['host']);
			setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $parsedURL['host']);
			
			// Remove desired Cookie and its associated key
			setcookie($cookieName, "", $timestamp - 360000, "/", $parsedURL['baseDomain']);
			setcookie($cookieName . "_key", "", $timestamp - 360000, "/", $parsedURL['baseDomain']);
		}
	}
}

