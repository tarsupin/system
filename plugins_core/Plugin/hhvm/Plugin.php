<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Plugin Class ------
------------------------------------

This class is used to access the configurations and installation settings for other plugins in the system.

If you look through the plugins, you will generally notice two files in the main directory that look similar to:

	/Plugin/Plugin.php
	/Plugin/Plugin.config.php
	
The file that ends in config.php is referred to as the "Plugin Configuration File". This file identifies information that is valuable to the admin of the site, but which doesn't provide any functionality to the users.

The Plugin Configuration File performs several important tasks:

	1. Provides details about the plugin, such as the author and it's version (important for updating).

	2. Automatically installs and sets up the plugin on the site.
	
	3. Prepares links that are used in the administration panel.
	
Many of these functions are performed by accessing the "Plugin" plugin, which acts as a handler for other plugins and their configurations.


----------------------------
------ Plugin Actions ------
----------------------------

Some plugins have "actions", which is a term that is unique to phpTelsa. "Actions" describe a plugin method that anyone can activate directly through a URL string. For security purposes, all actions must follow the naming convention of: {NAME_OF_ACTION} + "_TeslaAction".

For example, the action "Jump" would be created like this:
	
	public static function Jump_TeslaAction()
	
And when somebody is activating the action, they do not include "_TeslaAction" in the string (which would accomplish nothing). Instead, the function running the action would automatically append "_TeslaAction" to the action being called.

For example, if someone wanted to activate the "Jump" action on the Plugin "MyPlugin", they would use the following URL:
	
	/action/MyPlugin/Jump
	
If they wanted to include any parameters with the action, they can include it using the "param" parameters:
	
	/action/MyPlugin/Jump?param[0]=Ten Feet&param[1]=Horizontal
	
If you are creating an action for a plugin, it is essential that you sanitize any user input that is sent to the method.

The action controller will sanitize the plugin and action name automatically, such as to eliminate any null byte attacks.


------------------------------
------ Plugin Behaviors ------
------------------------------

Plugin behaviors are nearly identical to "Actions" (see "Plugin Actions" above), except that they are designed for private use by the server (or database), and are NOT intended to be accessible by the public in any way.

However, even though these behaviors are not accessible to the public, they are often methods that need to be heavily secured. Therefore, they are protected by the same means.

Plugin Behaviors use the naming convention of: {NAME OF BEHAVIOR} + "_TeslaBehavior". For example, the "RunThis" behavior would be created like this:
	
	public static function RunThis_TeslaBehavior()
	
When the system is attempting to launch the behavior, it will sanitize the plugin and behavior name automatically, such as to eliminate any null byte attacks.


-------------------------------
------ Methods Available ------
-------------------------------

// Install a plugin
Plugin::install($plugin);

// Return the full list of plugins available
$pluginList = Plugin::getPluginList();

// Get the configuration class for the plugin
$pluginConfig = Plugin::getConfig($plugin);

// Get the admin controllers of the plugin
$controllerList = Plugin::getAdminPages($pluginPath);

// Run a plugin "action" (unique to phpTesla)
Plugin::runAction($plugin, $action, $parameters, [$clearance]);

// Run a plugin "behavior" (unique to phpTesla)
Plugin::runBehavior($plugin, $behavior, $params);

// Check if the plugin has an installer
Plugin::hasInstaller($plugin, [$pluginConfig]);

*/

abstract class Plugin {
	
	
/****** Plugin Variables ******/
	public static array <str, array<str, mixed>> $pluginPages = array();		// <str:[str:mixed]>
	
	// Installation Constants
	const ADMIN_NOT_FOUND = -3;
	const DEPENDENCIES_MISSING = -2;
	const INSTALL_FAILED = -1;
	const NO_INSTALL_NEEDED = 0;
	const INSTALL_SUCCEEDED = 1;
	const ALREADY_INSTALLED = 2;
	
	
/****** Check if a Plugin Exists ******/
	public static function install
	(
		string $plugin			// <str> The dependency plugin that you need to prepare
	): int					// RETURNS <int> The value of the plugin's status
	
	// Plugin::install($plugin);
	{
		// Get the admin class for the dependency
		if(!$pluginConfig = self::getConfig($plugin))
		{
			return self::ADMIN_NOT_FOUND;
		}
		
		// Install all dependencies first
		if(isset($pluginConfig->dependencies))
		{
			foreach($pluginConfig->dependencies as $dep)
			{
				if(self::install($dep) < 0)
				{
					return self::DEPENDENCIES_MISSING;
				}
			}
		}
		
		// Run the installation for the dependency
		if(method_exists($pluginConfig, "install"))
		{
			$success = $pluginConfig->install() ? $success : false;
			
			return ($success == true ? self::INSTALL_SUCCEEDED : self::INSTALL_FAILED);
		}
		
		return self::NO_INSTALL_NEEDED;
	}
	
	
/****** Load the full list of Plugins available ******/
	public static function getPluginList
	(
		string $dir = ""		// <str> A strict directory to look it for plugins; disallows any other paths if set
	): array <int, str>					// RETURNS <int:str> a list of plugins located.
	
	// $pluginList = Plugin::getPluginList([$dir]);
	{
		// If a directory is provided, ONLY look through that directory for plugins
		if($dir !== "")
		{
			$dir = rtrim(Sanitize::filepath($dir), "/");
			return Dir::getFolders($dir);
		}
		
		// Get the full list of available plugins
		$plugins = Dir::getFolders(APP_PATH . "/plugins");
		$plugins = array_merge(Dir::getFolders(CORE_PLUGIN_PATH), $plugins);
		$plugins = array_merge(Dir::getFolders(ADDON_PLUGIN_PATH), $plugins);
		
		return $plugins;
	}
	
	
/****** Load a Plugin Config ******/
	public static function getConfig
	(
		string $plugin			// <str> The name of the plugin whose admin class needs to be loaded.
	,	string $dir = ""		// <str> A strict directory to look it for plugins; disallows any other paths if set
	): mixed					// RETURNS <mixed> PLUGIN class if it was found, FALSE on failure
	
	// $pluginConfig = Plugin::getConfig($plugin, [$dir]);
	{
		$plugin = Sanitize::variable($plugin);
		$pluginConfig = $plugin . "_config";
		
		// If a directory is provided, ONLY look through that directory for plugins
		if($dir !== "")
		{
			$dir = rtrim(Sanitize::filepath($dir), "/");
			$fullDir = $dir . "/" . $plugin . "/" . $plugin . ".config.php";
			
			if(is_file($fullDir))
			{
				if(!class_exists($pluginConfig)) { include($fullDir); }
				
				$pluginConfig = new $pluginConfig();
				$pluginConfig->data['path'] = $dir . "/" . $plugin;
				$pluginConfig->data['type'] = "???";
				return $pluginConfig;
			}
			
			return false;
		}
		
		// Attempt to load an application plugin
		$dir = APP_PATH . "/plugins/" . $plugin . "/" . $plugin . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($pluginConfig)) { include($dir); }
			
			$pluginConfig = new $pluginConfig();
			$pluginConfig->data['path'] = APP_PATH . "/plugins/" . $plugin;
			$pluginConfig->data['type'] = "app";
			return $pluginConfig;
		}
		
		// Attempt to load a core plugin
		$dir = CORE_PLUGIN_PATH . "/" . $plugin . "/" . $plugin . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($pluginConfig)) { include($dir); }
			
			$pluginConfig = new $pluginConfig();
			$pluginConfig->data['path'] = CORE_PLUGIN_PATH . "/" . $plugin;
			$pluginConfig->data['type'] = "core";
			return $pluginConfig;
		}
		
		// Attempt to load an addon plugin
		$dir = ADDON_PLUGIN_PATH . "/" . $plugin . "/" . $plugin . ".config.php";
		
		if(is_file($dir))
		{
			if(!class_exists($pluginConfig)) { include($dir); }
			
			$pluginConfig = new $pluginConfig();
			$pluginConfig->data['path'] = ADDON_PLUGIN_PATH . "/" . $plugin;
			$pluginConfig->data['type'] = "addon";
			return $pluginConfig;
		}
		
		return false;
	}
	
	
/****** Get a Plugin Directory ******/
	public static function getPath
	(
		string $plugin			// <str> The name of the plugin to retrieve the path of
	): string					// RETURNS <str> Path to the plugin if found, or "" on failure.
	
	// $pluginPath = Plugin::getPath($plugin);
	{
		$plugin = Sanitize::variable($plugin);
		
		// Attempt to load an application plugin
		if(is_file(APP_PATH . "/plugins/" . $plugin . "/" . $plugin . ".config.php"))
		{
			return APP_PATH . "/plugins/" . $plugin;
		}
		
		// Attempt to load a core plugin
		if(is_file(CORE_PLUGIN_PATH . "/" . $plugin . "/" . $plugin . ".config.php"))
		{
			return CORE_PLUGIN_PATH . "/" . $plugin;
		}
		
		// Attempt to load an addon plugin
		if(is_file(ADDON_PLUGIN_PATH . "/" . $plugin . "/" . $plugin . ".config.php"))
		{
			return ADDON_PLUGIN_PATH . "/" . $plugin;
		}
		
		return "";
	}
	
	
/****** Retrieve a list of the Plugin's Admin Pages ******/
	public static function getAdminPages
	(
		string $pluginPath = ""	// <str> The base plugin directory to retrieve admin pages from.
	): array <int, str>						// RETURNS <int:str> an array of controller pages contained in the directory provided.
	
	// $controllerList = Plugin::getAdminPages($pluginPath);
	{
		$controllerList = array();
		
		// Get the admin pages for this plugin, if availble
		if(is_dir($pluginPath . '/admin'))
		{
			$contFiles = Dir::getFiles($pluginPath . '/admin');
			
			foreach($contFiles as $filename)
			{
				if(strpos($filename, ".php") === false)
				{
					continue;
				}
				
				$fileName = Sanitize::variable(str_replace(".php", "", $filename), " -");
				$controllerList[] = $fileName;
			}
		}
		
		return $controllerList;
	}
	
	
/****** Run a plugin's action (if applicable) ******/
	public static function runAction
	(
		string $plugin			// <str> The plugin to run the action for
	,	string $action			// <str> The name of the plugin's action to run.
	,	array $params			// <array> The parameters passed for this action.
	,	int $clearance = 0	// <int> The level of clearance to activate the action with.
	): mixed					// RETURNS <mixed> the response of the function, or void on failure.
	
	// Plugin::runAction($plugin, $action, $parameters, [$clearance]);
	{
		// The name of the Plugin to run
		$plugin = Sanitize::variable($plugin);
		
		// The name of the Plugin's Action to run
		$action = Sanitize::variable($action);
		
		// Set clearance to user, if applicable
		if($clearance == 0)
		{
			$clearance = Me::$clearance;
		}
		
		// Set the parameters
		array_push($params, $clearance);
		
		// Make sure the action exists
		if(method_exists($plugin, $action . "_TeslaAction"))
		{
			// Run the plugin's action
			return call_user_func(array($plugin, $action . "_TeslaAction"), $params);
		}
	}
	
	
/****** Run a plugin's behavior (if applicable) ******/
	public static function runBehavior
	(
		string $plugin			// <str> The plugin to run the behavior for
	,	string $behavior		// <str> The name of the plugin's behavior to run.
	,	array $params			// <array> The parameters passed for this behavior.
	): mixed					// RETURNS <mixed> the response of the function, or void on failure.
	
	// Plugin::runBehavior($plugin, $behavior, $params);
	{
		// The name of the Plugin to run
		$plugin = Sanitize::variable($plugin);
		
		// The name of the Plugin's Behavior to run
		$behavior = Sanitize::variable($behavior);
		
		// Make sure the behavior exists
		if(method_exists($plugin, $behavior . "_TeslaBehavior"))
		{
			// Run the plugin's behavior
			return call_user_func_array(array($plugin, $behavior . "_TeslaBehavior"), $params);
		}
	}
	
	
/****** Check if a Plugin has installations to run ******/
	public static function hasInstaller
	(
		string $plugin					// <str> The plugin that you need to check if it has an installation process.
	,	mixed $pluginConfig = null	// <mixed> The plugin config object, if already active.
	): bool							// RETURNS <bool> TRUE if it has an installer, FALSE if not.
	
	// Plugin::hasInstaller($plugin, [$pluginConfig]);
	{
		// Make sure the config class for the plugin exists
		if($pluginConfig == null)
		{
			if(!$pluginConfig = self::getConfig($plugin))
			{
				return false;
			}
		}
		
		// Check if there is an install method
		return method_exists($pluginConfig, "install") ? true : false;
	}
}
