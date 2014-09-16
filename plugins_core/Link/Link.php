<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Link Plugin ------
-----------------------------------

This plugin provides simple functionality to links to protect them against cross-site forgery requests, as well as to prevent people from accidentally reusing the same link by refreshing.

For example, a user clicks on a link to send twenty gold pieces to a friend. The page loads the URL that was linked and says "Gold Sent!". Now, if the page is reloaded (such as an accidental refresh), the page SHOULD NOT re-send another 20 gold, even though it was the same link. There are multiple ways to prevent this, but not running the process in the first place is a good way to start.

This sort of incident is easily prevented with this plugin.


-------------------------------------------
------ Examples of using ::prepare() ------
-------------------------------------------

// Check if the form was submitted successfully (with Form::submitted())
if($link = Link::clicked() and $link == "tip")
{
	echo "You have successfully tipped someone 10 coins!";
}

// Display the link (with Link::prepare())
echo '<a href="/page?tip=10&' . Link::prepare("tip") . '">Tip 10 Coins</a>';



-----------------------------------------------
------ Examples of using ::prepareData() ------
-----------------------------------------------

// Check if the data was sent
if($getData = Link::getData("some-data"))
{
	var_dump($getData);
}

// Display the link (with Link::prepare())
echo '<a href="/page?' . Link::prepareData("some-data", "value1", "value2", "value3") . '">Create a link for some data</a>';


-------------------------------
------ Methods Available ------
-------------------------------

// Prepare hidden tags to protect a link.
Link::prepare($clickVal = "1")

// Returns a prepared value if the link is valid.
$clickVal = Link::clicked()

*/

abstract class Link {
	
	
/****** Class Variables ******/
	public static $linkList = array();
	
	
/****** Prepare special tags and protection for a Link ******/
	public static function prepare
	(
		$clickVal = "1"		// <str> A secret click value to pass.
	)						// RETURNS <str> encryption code to append to the link.
	
	// echo '<a href="/page?tip=50&' . Link::prepare("tip") . '">Give Tip</a>";
	{
		// If you've already used the unique identifier, reuse it for optimization purposes
		if(isset(self::$linkList[$clickVal]))
		{
			return self::$linkList[$clickVal];
		}
		
		// Prepare Values
		$salt = Security::randHash(mt_rand(6, 9), 62);
		
		// Test the identifier that makes forms unique to each user
		$siteSalt = SITE_SALT;
		
		// Add User Agent
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
		
		// Add Auth Token
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
		
		// Add CSRF Token
		//$siteSalt .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
		
		$hash = Security::hash($siteSalt . $salt . $clickVal, 10, 62);
		
		$encString = 'lslt=' . $salt . '&lhsh=' . urlencode($hash) . '&lcv=' . urlencode(base64_encode($clickVal));
		
		// Set this value to remember it for later on the page
		self::$linkList[$clickVal] = $encString;
		
		// Return the encryption code to insert into the link
		return $encString;
	}
	
	
/****** Validate a Link Click using Special Protection ******/
	public static function clicked (
	)					// RETURNS <str> a string identifier for which click was made, "" on failure.
	
	// if($val = Link::clicked() && $val == "tip") { echo "The link has been clicked successfully!"; }
	{
		// Make sure all of the right data was sent
		if(isset($_GET['lslt']) && isset($_GET['lhsh']) && isset($_GET['lcv']))
		{
			/// Decode the prepared click value
			if(!$clickVal = base64_decode($_GET['lcv']))
			{
				return "";
			}
			
			// Prepare identifier that will make forms unique to each user
			$siteSalt = SITE_SALT;
			
			// Add User Agent
			$siteSalt .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
			
			// Add Auth Token
			$siteSalt .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
			
			// Add CSRF Token
			//$siteSalt .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
			
			// Generate the Hash
			$hash = Security::hash($siteSalt . $_GET['lslt'] . $clickVal, 10, 62);
			
			// Make sure the hash was valid
			if($_GET['lhsh'] == $hash)
			{
				// Prevent Page Refreshes
				if(!isset($_SESSION[SITE_HANDLE]['trackLink']))
				{
					$_SESSION[SITE_HANDLE]['trackLink'] = '';
				}
				
				if(strpos($_SESSION[SITE_HANDLE]['trackLink'], "~" . $hash) !== false)
				{
					return "";
				}
				
				$_SESSION[SITE_HANDLE]['trackLink'] = "~" . $hash . substr($_SESSION[SITE_HANDLE]['trackLink'], 0, 110);
				
				// If the submission wasn't a resubmit, post it
				return $clickVal;
			}
		}
		
		return "";
	}
	
	
/****** Safely send uneditable data through a link ******/
	public static function prepareData
	(
		$clickVal = "1"		// <str> A secret click value to pass.
	)						// RETURNS <str> encryption code to append to the link.
	
	// Link::prepareData($clickVal, $data, $data, $data, ...)
	// echo '<a href="/page?' . Link::prepareData("tip", $data1, $data2, ...) . '">Run Data</a>";
	{
		// Prepare Values
		$linkData = array();
		$args = func_get_args();
		
		for($a = 1;$a < count($args);$a++)
		{
			$linkData[] = $args[$a];
		}
		
		// Prepare Values
		$salt = Security::randHash(10, 62);
		
		// Test the identifier that makes forms unique to each user
		$siteSalt = SITE_SALT;
		
		// Add User Agent
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
		
		// Add Auth Token
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
		
		// Add CSRF Token
		//$siteSalt .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
		
		$hash = Security::hash($siteSalt . $salt . $clickVal, 15, 62);
		
		$lData = Encrypt::run($hash, json_encode($linkData), "fast");
		
		$encString = 'lslt=' . $salt . '&lhsh=' . urlencode($hash) . '&lcv=' . urlencode(base64_encode($clickVal)) . '&ldata=' . urlencode($lData);
		
		// Return the encryption code to insert into the link
		return $encString;
	}
	
	
/****** Get the data from a link ******/
	public static function getData
	(
		$origClickVal	// <str> The click value that was originally used.
	)					// RETURNS <int:mixed> an array of data sent through the link, array() on failure.
	
	// $getData = Link::getData($origClickVal);
	{
		// Make sure all of the right data was sent
		if(!isset($_GET['lslt']) or !isset($_GET['lhsh']) or !isset($_GET['lcv']) or !isset($_GET['ldata']))
		{
			return array();
		}
		
		/// Decode the prepared click value and confirm it matches
		if(!$clickVal = base64_decode($_GET['lcv']) or $origClickVal != $clickVal)
		{
			return array();
		}
		
		// Prepare identifier that will make forms unique to each user
		$siteSalt = SITE_SALT;
		
		// Add User Agent
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
		
		// Add Auth Token
		$siteSalt .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
		
		// Add CSRF Token
		//$siteSalt .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
		
		// Generate the Hash
		$hash = Security::hash($siteSalt . $_GET['lslt'] . $clickVal, 15, 62);
		
		// Make sure the hash was valid
		if($_GET['lhsh'] == $hash)
		{
			// Prevent Page Refreshes
			if(!isset($_SESSION[SITE_HANDLE]['trackLink']))
			{
				$_SESSION[SITE_HANDLE]['trackLink'] = '';
			}
			
			if(strpos($_SESSION[SITE_HANDLE]['trackLink'], "~" . $hash) !== false)
			{
				return array();
			}
			
			$_SESSION[SITE_HANDLE]['trackLink'] = "~" . $hash . substr($_SESSION[SITE_HANDLE]['trackLink'], 0, 110);
			
			$someData = Decrypt::run($hash, $_GET['ldata']);
			
			// If the submission wasn't a resubmit, post it
			return json_decode($someData, true);
		}
		
		return array();
	}
	
	
/****** Maintain associations in a query string ******/
	public static function queryHold
	(
			// ([args] list of GET keys to hold for this query strings)
	)		// RETURNS <str> the query string that results.
	
	// $queryString = Link::queryHold("filter", "sort") . "page=" . $currentPage;
	{
		$queryString = "";
		$args = func_get_args();
		
		// Cycle through the keys you provide
		foreach($args as $arg)
		{
			// Make sure the GET value exists
			if(isset($_GET[$arg]))
			{
				if(is_array($_GET[$arg]))
				{
					$a = 0;
					
					foreach($_GET[$arg] as $getArg)
					{
						$queryString .= "&" . $arg . "[" . $a . "]=" . urlencode($getArg);
						$a++;
					}
				}
				else
				{
					$queryString .= "&" . $arg . "=" . urlencode($_GET[$arg]);
				}
			}
		}
		
		return $queryString;
	}
}
