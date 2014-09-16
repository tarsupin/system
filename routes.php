<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/****** Prepare Navigation ******
URL segments are delimited by forward brackets (i.e. "/") and are saved as the $url array.

	For example, in the URL "http://domain.com/user/profile/edit", the following $url would be set:
	
		* $url[0]		would equal "user"
		* $url[1]		would equal "profile"
		* $url[2]		would equal "edit"
	
The $url array is used by the system to determine what pages will load.

Every URL points to a straightforward filepath within the {APP_PATH}/controller directory. For example, the URL
"domain.com/account/login" would point to "{APP_PATH}/controller/account/login.php"

	*****************************************************
	**** Example #1 - the URL uses only one segment. ****
	*****************************************************
			  http://domain.com/profile				<-- if you enter this URL
					/controller/profile.php			<-- it looks for this base file in the application directory
	
	***********************************************
	**** Example #2 - the URL uses two routes: ****
	***********************************************
			  http://domain.com/account/login			<-- if you enter this URL
					/controller/account/login.php		<-- it looks for this file FIRST
					/controller/account.php				<-- tries to load this page if it can't find the previous
	
	*************************************************
	**** Example #3 - the URL uses three routes: ****
	*************************************************
			  http://domain.com/user/profile/edit		<-- if you enter this URL
					/controller/user/profile/edit.php	<-- it looks for this file FIRST
					/controller/user/profile.php		<-- tries to load this page if it can't find the previous
					/controller/user.php				<-- tries to load the base file
	
	**********************************************************
	**** Example #4 - You want to use URL's as variables: ****
	**********************************************************
	http://domain.com/profile/joe			<-- this is the url that we load
		  /controller/profile/joe.php		<-- this page doesn't exist, so we look for the next page
		  /controller/profile.php			<-- $url[1] is set to "joe", which we can use on the profile page
*/

// If there is more than one route used in the URL, we need to look for the page to load in /controller/
if(count($url) > 0 && $url[0] != "")
{
	// Cycle Through Standard Route List
	$checkPath = $url_relative;
	
	while(true)
	{
		// Check if the appropriate path exists. Load it if it does.
		if(File::exists(APP_PATH . "/controller/" . $checkPath . ".php"))
		{
			require(APP_PATH . "/controller/" . $checkPath . ".php"); exit;
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
}
else
{
	// Load the home page
	require(APP_PATH . '/controller/home.php'); exit;
}