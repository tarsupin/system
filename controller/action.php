<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the "Action" page ------
-------------------------------------

This page can run methods that plugins have specifically created for running publicly accessible scripts.

For example, the URL "/action/MyPlugin/Jump?param[0]=value&param[1]=value2" would run:
	
	MyPlugin::Jump_TeslaAction($_GET['param'][0], $_GET['param'][1], Me::$clearance);
	
	
Note that every action accepts $clearance as a parameter, allowing the action to decide whether or not someone can perform all of the desired actions on it. The $clearance value is always set as the LAST parameter that is passed, and it is always passed.
	
	
----------------------------------------------
------ Creating an "Action" on a plugin ------
----------------------------------------------

In order to have a public "action", plugins must use a naming convention of {NAME OF ACTION} + "_TeslaAction". They must also be static methods.

The corresponding URL to activate the plugin's action always follows this naming convention:
	
	Segment #1: "action"
	Segment #2: The name of the plugin
	Segment #3: The name of the action
	Query Value "param[#]": The value(s) that will be passed into the method, if applicable.
	Query Value "return": The url path to return to once this action has completed.
	
For example, a typical action call will look similar to this:
	
	/action/PluginName/actionName?param[0]=firstParameter&param[1]=secondParameter
	
	
----------------------------------------
------ Example of a Plugin Action ------
----------------------------------------
	
	// Run with: /action/Plugin/MyTest?param[0]=something
	public static function MyTest_TeslaAction
	(
		$arg			// <mixed> Allows a single argument to be passed.
	,	$clearance = 0	// <int> The level of clearance provided to this action.
	)					// RETURNS <void>
	{
		// The user activating this action must have a clearance of 3 or higher
		if($clearance >= 3)
		{
			Database::query("INSERT INTO someTable (column) VALUES (?)", array($arg));
		}
	}
	
-----------------------------------------------
------ Redirection after a Plugin Action ------
-----------------------------------------------

Once a plugin has been run, the "return" value will be used to determine which page to return back to. If none was provided, it will return to the home page of the site.

A typical action URL therefore looks like this:
	
	/action/PluginName/actionName?param[0]=dataToSend&return=/page/to/return?to=andArgsIfYouWantThem

*/

// Run the Plugin's Action
Plugin::runAction($url[1], $url[2], (isset($_GET['param']) ? $_GET['param'] : array()));

// Run the URL Redirection
if(isset($_GET['return']))
{
	header("Location: " . $_GET['return']); exit;
}

header("Location: /"); exit;