<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }/*

---------------------------------------
------ About the LocalDev Plugin ------
---------------------------------------

This plugin allows the localhost developer to quickly update the development and production environments.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------

*/

abstract class LocalDev {
	
	
/****** Plugin Variables ******/
	private static $envType = "";				// <str> Gets set to "development" or "production"
	private static $instructions = array();	// <int:mixed> A list of instructions to submit.
	
	
/****** Display the LocalDev Control Panel, when appropriate ******/
	public static function showPanel (
	)				// RETURNS <void>
	
	// LocalDev::showPanel();
	{
		// The user must be in the local environment to use this
		if(ENVIRONMENT != "local") { return; }
		
		// The user must be logged in
		if(!Me::$loggedIn or !isset(Me::$vals['handle'])) { return; }
		
		// The user's handle must be "development" or "production"
		if(Me::$vals['handle'] != "development" and Me::$vals['handle'] != "production")
		{
			return;
		}
		
		// Prepare Values
		self::$envType = (Me::$vals['handle'] == "production" ? "production" : "development");
		
		// Run the form associated with this panel
		$formResponse = self::runForm();
		
		// The checks have passed, we can show the local development display
		echo '
		<div style="height:35%;width:100%;">&nbsp;</div>
		<div style="position:relative; text-align:left; background-color:white; font-family:courier; z-index:9999999; padding:8px; border:solid black 2px; max-height:35%; overflow:auto; width:95%;">';
		
		// Display form response, if applicable
		if($formResponse)
		{
			if(!is_string($formResponse))
			{
				$formResponse = json_encode($formResponse);
			}
			
			echo '
			<div style="padding:8px;"><textarea style="width:100%;min-height:100px;">' . htmlspecialchars($formResponse) . '</textarea></div>';
		}
		
		// Create Form
		echo '
		<form action="' . Sanitize::url($_SERVER['REQUEST_URI']) . '" method="post">' . Form::prepare("local-dev-sys-uni");
		
		// Display Core Plugins
		echo '
		<div>
			<select name="environment">' . str_replace('value="' . self::$envType . '"', 'value="' . self::$envType . '" selected', '
				<option value="" style="font-weight:bold">-- Environment --</option>
				<option value="local">local</option>
				<option value="development">development</option>
				<option value="production">production</option>') . '
			</select>
		</div>';
		
		// Display Core Plugins
		echo '
		<div>
		<select name="plugin_core">
			<option value="" style="font-weight:bold">-- Core Plugins --</option>';
		
		$pluginList = Plugin::getPluginList(CORE_PLUGIN_PATH);
		
		foreach($pluginList as $plug)
		{
			echo '
			<option value="' . $plug . '">' . $plug . '</option>';
		}
		
		echo '
		</select>';
		
		// Display Addon Plugins
		echo '
		<select name="plugin_addon">
			<option value="" style="font-weight:bold">-- Addon Plugins --</option>';
		
		$pluginList = Plugin::getPluginList(ADDON_PLUGIN_PATH);
		
		foreach($pluginList as $plug)
		{
			echo '
			<option value="' . $plug . '">' . $plug . '</option>';
		}
		
		echo '
		</select>';
		
		// Display Application Plugins
		echo '
		<select name="plugin_app">
			<option value="" style="font-weight:bold">-- App Plugins --</option>';
		
		$pluginList = Plugin::getPluginList(APP_PATH . "/plugins");
		
		foreach($pluginList as $plug)
		{
			echo '
			<option value="' . $plug . '">' . $plug . '</option>';
		}
		
		echo '
		</select>
		</div>';
		
		// Display the folders
		echo '
		<div>';
		
		// List all folders and files in the app path
		$appList = Dir::getFiles(APP_PATH, true);
		
		echo '
		<select name="app_script">
				<option value="" style="font-weight:bold;">-- App Files --</option>';
		
		sort($appList);
		
		foreach($appList as $list)
		{
			if(strpos($list, "assets") !== false or strpos($list, "plugins") !== false) { continue; }
			
			if(!is_dir(APP_PATH . '/' . $list))
			{
				echo '
				<option value="' . $list . '">' . $list . '</option>';
			}
		}
		
		echo '
		</select>';
		
		// List all folders and files in the conf path, if different than the app path
		if(CONF_PATH != APP_PATH)
		{
			// List all folders and files in the conf path
			$confList = Dir::getFiles(CONF_PATH, true);
			
			echo '
			<select name="conf_script">
					<option value="" style="font-weight:bold;">-- CONF Files --</option>';
			
			sort($confList);
			
			foreach($confList as $list)
			{
				if(strpos($list, "assets") !== false or strpos($list, "plugins") !== false) { continue; }
				
				if(!is_dir(APP_PATH . '/' . $list))
				{
					echo '
					<option value="' . $list . '">' . $list . '</option>';
				}
			}
			
			echo '
			</select>';
		}
		
		// List all folders and files in the system path
		$sysList = Dir::getFiles(SYS_PATH, true);
		
		echo '
		<select name="sys_script">
				<option value="" style="font-weight:bold;">-- Core Files --</option>';
		
		sort($sysList);
		
		foreach($sysList as $list)
		{
			if(strpos($list, "assets") !== false or strpos($list, "plugins") !== false or strpos($list, "libraries") !== false) { continue; }
			
			if(!is_dir(SYS_PATH . '/' . $list))
			{
				echo '
				<option value="' . $list . '">' . $list . '</option>';
			}
		}
		
		echo '
		</select>';
		
		echo '
		<input type="submit" name="submit" value="Update" />
		</div>';
		
		echo '
		</form>';
		
		echo '
		</div>';
	}
	
	
/****** Run the form for this panel ******/
	private static function runForm (
	)				// RETURNS <mixed>
	
	// self::runForm();
	{
		// Prepare Values
		$postList = array("environment", "plugin_app", "plugin_addon", "plugin_core", "conf_script", "app_script", "sys_script");
		
		foreach($postList as $pl)
		{
			if(!isset($_POST[$pl]))
			{
				$_POST[$pl] = "";
			}
		}
		
		if(in_array($_POST['environment'], array("local", "development", "production")))
		{
			self::$envType = $_POST['environment'];
		}
		
		// Check if there are any app plugins to update
		if($_POST['plugin_app'])
		{
			$plug = Sanitize::variable($_POST['plugin_app']);
			$pluginConfig = Plugin::getConfig($plug, APP_PATH . "/plugins");
			
			$filePath = $pluginConfig->data['path'] . "/" . $plug . '.php';
			$fileContents = file_get_contents($filePath);
			
			self::$instructions[] = array("plugin_app" => array($plug, $fileContents, "APP"));
		}
		
		// Check if there are any addon plugins to update
		if($_POST['plugin_addon'])
		{
			$plug = Sanitize::variable($_POST['plugin_addon']);
			$pluginConfig = Plugin::getConfig($plug, ADDON_PLUGIN_PATH);
			
			$filePath = $pluginConfig->data['path'] . "/" . $plug . '.php';
			$fileContents = file_get_contents($filePath);
			
			self::$instructions[] = array("plugin_addon" => array($plug, $fileContents, "ADDON"));
		}
		
		// Check if there are any core plugins to update
		if($_POST['plugin_core'])
		{
			$plug = Sanitize::variable($_POST['plugin_core']);
			$pluginConfig = Plugin::getConfig($plug, CORE_PLUGIN_PATH);
			
			$filePath = $pluginConfig->data['path'] . "/" . $plug . '.php';
			$fileContents = file_get_contents($filePath);
			
			self::$instructions[] = array("plugin_core" => array($plug, $fileContents, "CORE"));
		}
		
		// Check if there are any conf files to update
		if($_POST['conf_script'])
		{
			$filePath = trim(Sanitize::filepath($_POST['conf_script']), "/");
			$fileContents = file_get_contents(CONF_PATH . "/" . $filePath);
			
			self::$instructions[] = array("conf_script" => array($filePath, $fileContents));
		}
		
		// Check if there are any app files to update
		if($_POST['app_script'])
		{
			$filePath = trim(Sanitize::filepath($_POST['app_script']), "/");
			$fileContents = file_get_contents(APP_PATH . "/" . $filePath);
			
			self::$instructions[] = array("app_script" => array($filePath, $fileContents));
		}
		
		// Check if there are any sys files to update
		if($_POST['sys_script'])
		{
			$filePath = trim(Sanitize::filepath($_POST['sys_script']), "/");
			$fileContents = file_get_contents(SYS_PATH . "/" . $filePath);
			
			self::$instructions[] = array("sys_script" => array($filePath, $fileContents));
		}
		
		// Send instructions
		return self::sendInstructions();
	}
	
	
/****** Send instructions to be updated ******/
	private static function sendInstructions (
	)				// RETURNS <str>
	
	// self::sendInstructions();
	{
		// If there are no instructions set, don't send instructions
		if(!self::$instructions)
		{
			return "";
		}
		
		// Prepare the packet
		$packet = array(
			"site"			=> SITE_HANDLE
		,	"env"			=> self::$envType
		,	"instructions"	=> self::$instructions
		);
		
		// Prepare Settings
		$settings = array("post" => true);
		
		// Run the LocalDev Instructions through the System Control Panel
		$response = Connect::to("syspanel", "LocalDevAPI", $packet, $settings);
		return Connect::$url;
		return $response;
	}
	
}
