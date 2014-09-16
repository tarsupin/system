<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the "Theme" Routes ------
--------------------------------------

The "Theme" Routes are essentially identical to the standard routes, but are used on sites that want multiple themes (particularly those that enable you to switch between themes).

Instead of instantly using the default theme, the "Theme" routing system will check to see if there is an alternative theme available and attempt to use that one instead.

*/

// If there is more than one route used in the URL, we need to look for the page to load in /controller/
$home = !(count($url) > 0 && $url[0] != "");

// Check if a non-default theme is active
if(Theme::$theme != "default")
{
	// Cycle Through Theme Controllers
	$checkPath = ($home ? "home" : $url_relative);
	
	while(true)
	{
		// Check if the appropriate path exists. Load it if it does.
		if(File::exists(Theme::$dir . "/controller/" . $checkPath . ".php"))
		{
			require(Theme::$dir . "/controller/" . $checkPath . ".php"); exit;
		}
		
		// Make sure there's still paths to check
		if(strpos($checkPath, "/") === false) { break; }
		
		$checkPath = substr($checkPath, 0, strrpos($checkPath, '/'));
	}
}

// Cycle Through Default Theme Controllers
$checkPath = ($home ? "home" : $url_relative);

while(true)
{
	// Check if the appropriate path exists. Load it if it does.
	if(File::exists(APP_PATH . "/themes/default/controller/" . $checkPath . ".php"))
	{
		require(APP_PATH . "/themes/default/controller/" . $checkPath . ".php"); exit;
	}
	
	// Make sure there's still paths to check
	if(strpos($checkPath, "/") === false) { break; }
	
	$checkPath = substr($checkPath, 0, strrpos($checkPath, '/'));
}

/****** Check for Admin Operations ******/
// This section tracks any reusable controller and loads those system pages
$checkPath = $url_relative;

while(true)
{
	// Check if the appropriate path exists. Load it if it does.
	if(File::exists(SYS_PATH . "/controller/" . $checkPath . ".php"))
	{
		require(SYS_PATH . "/controller/" . $checkPath . ".php"); exit;
	}
	
	// Make sure there's still paths to check
	if(strpos($checkPath, "/") === false) { break; }
	
	$checkPath = substr($checkPath, 0, strrpos($checkPath, '/'));
}