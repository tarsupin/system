<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/***********************************************
******* DO NOT EDIT ANYTHING IN THIS FILE ******
***********************************************/

/****** Important Settings ******/
date_default_timezone_set('America/Los_Angeles');	// Required

/****** Prepare the Auto-Loader ******/
spl_autoload_register(null, false);
spl_autoload_extensions('.php');

// Create our custom Auto-Loader Function
function autoLoader($class)
{
	$special = (USE_HHVM ? array("hhvm/", "") : array(""));
	
	foreach($special as $opt)
	{
		// First look through the application classes to see if the class is located there
		$classFile = realpath(APP_PATH . "/plugins/$class/" . $opt . $class . ".php");
		
		if(is_file($classFile))
		{
			require($classFile); return true;
		}
		
		// Look through the system classes if the class was not found in the application
		$classFile = realpath(CORE_PLUGIN_PATH . "/$class/" . $opt . $class . ".php");
		
		if(is_file($classFile))
		{
			require($classFile); return true;
		}
		
		// Finally, look through the plugin directory to see if the class is located there
		$classFile = realpath(ADDON_PLUGIN_PATH . "/$class/" . $opt . $class . ".php");
		
		if(is_file($classFile))
		{
			require($classFile); return true;
		}
	}
	
	// The class was not located. Return false.
	return false;
}

// Register our custom Auto-Loader
spl_autoload_register('autoLoader');


/****** Load Configuration Files ******/
require(dirname(SYS_PATH) . "/global-config.php");	// Loads the Global Configuration File
require(CONF_PATH . "/config.php");					// Loads the Application Configuration File


/****** Session Preparation ******/
session_start();

// Make sure the base session value used is available
if(!isset($_SESSION[SITE_HANDLE]))
{
	$_SESSION[SITE_HANDLE] = array();
}

// Check if the user is logged in
// If the user isn't logged in, try to handle automatic logins as effectively as possible
if(!isset($_SESSION[SITE_HANDLE]['id']))
{
	if($cookieData = Cookie::get("last_slg", SITE_HANDLE))
	{
		if((int) $cookieData < time() - 900)
		{
			$_GET['slg'] = 1;
			
			Cookie::set('last_slg', time(), SITE_HANDLE, 365);
		}
	}
	else
	{
		Cookie::set('last_slg', time() - 36000, SITE_HANDLE, 365);
	}
}

/****** Process Security Functions ******/
Security::fingerprint();


/****** Prepare the Database Connection ******/
Database::initialize(
	$config['database']['name'],
	$config['database']['user'],
	$config['database']['pass'],
	$config['database']['host'],
	$config['database']['type']
);


/****** Create Custom Error Handling ******/
function customErrorHandler($errorNumber, $errorString, $errorFile, $errorLine)
{
	// Prepare Values
	$errorType = "Error";
	
	switch($errorNumber)
	{
		case E_USER_NOTICE:		$errorType = "Notice";			$importance = 0;	break;
		case E_USER_WARNING:	$errorType = "Warning";			$importance = 2;	break;
		case E_USER_ERROR:		$errorType = "Fatal Error";		$importance = 4;	break;
		default:				$errorType = "Unknown Error";	$importance = 8;	break;
	}
	
	// Run the Backtrace
	$backtrace = debug_backtrace();
	
	if(isset($backtrace[1]))
	{
		$origin = $backtrace[1];
		$behind = $backtrace[0];
		
		$class = isset($origin['class']) ? $origin['class'] : "";
		$function  = isset($origin['function']) ? $origin['function'] : "";
		$argString = isset($origin['args']) ? StringUtils::convertArrayToArgumentString($origin['args']) : "";
		$filePath = (isset($behind['file']) ? str_replace(dirname(SYS_PATH), "", $behind['file']) : '');
		$fileLine = (isset($behind['line']) ? $behind['line'] : 0);
		
		$filePathNext = (isset($origin['file']) ? str_replace(dirname(SYS_PATH), "", $origin['file']) : '');
		$fileLineNext = (isset($origin['line']) ? $origin['line'] : 0);
		
		$fullFunction = (isset($origin['class']) ? $origin['class'] . $origin['type'] : "") . (isset($origin['function']) ? $origin['function'] : "") . "(" . $argString . ")";
		
		// Skip instances of the autoloader
		if($errorType == "Unknown Error" and strpos($function, "spl_autoload") !== false)
		{
			return false;
		}
		
		// Get the URL information for this page
		$urlData = array("full" => "");
		//$urlData = URL::parse($_SERVER['SERVER_NAME'] . "/" . $_SERVER['REQUEST_URI']);
		
		// Log this error in the database
		Debug::logError($importance, $errorType, $class, $function, $argString, $filePath, $fileLine, $urlData['full'], Me::$id, $filePathNext, $fileLineNext);
		
		//if(ENVIRONMENT != "production")
		{
			Debug::$verbose = true;
			
			Debug::scriptError($errorString, $class, $function, $argString, $filePath, $fileLine, $filePathNext, $fileLineNext);
		}
		//else
		{
			return false;
		}
	}
	
	// Returning FALSE will activate the default PHP Handler after ours runs.
	// Returning TRUE will prevent the default PHP Handler from running.
	return true;
}

// Register our custom error handler
set_error_handler("customErrorHandler");

/****** Set up System Configurations & Data ******/

// Get URL Segments
list($url, $url_relative) = URL::getSegments();

// Quick-load certain files
switch($url[0])
{
	case "api":
	case "logout":
		require(SYS_PATH . "/controller/" . $url[0] . ".php"); exit;
}

/****** Attempt a soft login ******/
if(isset($_GET['slg']))
{
	Me::softLog((int) $_GET['slg']);
}
