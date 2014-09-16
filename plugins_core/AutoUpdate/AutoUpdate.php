<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*






			NOTE: The "AutoUpdateAPI" is currently only on UniFaction.com.
			
			We need to set this into an addon plugin (or something) to get it working.






-----------------------------------------
------ About the AutoUpdate Plugin ------
-----------------------------------------

This plugin will identify when there are new updates that the site should be informed about and update.

This plugin will currently search for:
	
	1. Updates to plugins.
	
	
-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class AutoUpdate {
	
	
/****** Plugin Variables ******/
	public static $updateSite = "unifaction";	// <str> The site to get updates from.
	public static $updateDuration = 172800;		// <int> Time (in seconds) to pass before checking updates.
	
	const PACKAGE_PLUGINS = "/assets/packages/plugins";	// The plugin package directory.
	const PACKAGE_BACKUP = "/assets/packages/backup";	// The plugin backup directory;
	
	
/****** Check the last time the site updated ******/
	public static function checkLastUpdate (
	)				// RETURNS <int> the timestamp of the last update, or 0 on failure.
	
	// $lastUpdate = AutoUpdate::checkLastUpdate();
	{
		// Retrieve the timestamp of the last auto-update
		$lastUpdate = SiteVariable::load("auto-update", "last-update");
		
		return $lastUpdate ? (int) $lastUpdate : 0;
	}
	
	
/****** Check if there are any updates ******/
	public static function checkForUpdates (
	)				// RETURNS <str:str> the list of updates that need to be done, or array() if none.
	
	// $updateList = AutoUpdate::checkForUpdates();
	{
		// Make sure you haven't checked for updates too recently
		$lastUpdate = self::checkLastUpdate();
		
		if($lastUpdate >= time() - self::$updateDuration)
		{
			return array();
		}
		
		// Prepare Variables
		$pluginData = array();
		
		// Prepare the core plugins for updating
		$pluginList = Plugin::getPluginList(CORE_PLUGIN_PATH);
		
		foreach($pluginList as $pluginName)
		{
			if($pluginConfig = Plugin::getConfig($pluginName))
			{
				$pluginData[$pluginName] = $pluginConfig->version;
			}
		}
		
		// Prepare the addon plugins for updating
		$pluginList = Plugin::getPluginList(ADDON_PLUGIN_PATH);
		
		foreach($pluginList as $pluginName)
		{
			if($pluginConfig = Plugin::getConfig($pluginName))
			{
				$pluginData[$pluginName] = $pluginConfig->version;
			}
		}
		
		// Connect to the site's AutoUpdateAPI plugin and get a list of necessary updates
		if($updateInfo = Connect::to(self::$updateSite, "AutoUpdateAPI", $pluginData))
		{
			return array();
		}
		
		// Cycle through the list of plugins to update
		Database::startTransaction();
		
		foreach($updateInfo as $plugin => $version)
		{
			// Save this plugin as one that needs to be updated
			SiteVariable::save("plugin-update", $plugin, json_encode($version));
		}
		
		Database::endTransaction();
		
		// Update the last check time to now
		SiteVariable::load("auto-update", "last-update", time());
		
		return $updateInfo;
	}
	
	
/****** Run any updates that are necessary ******/
	public static function runUpdates (
	)				// RETURNS <void>
	
	// AutoUpdate::runUpdates();
	{
		// Load all of the plugins that need to be updated
		$pluginsToUpdate = SiteVariable::load("plugin-update");
		
		// Get the site URL to download from
		$siteData = Network::get(self::$updateSite);
		
		// Cycle through each plugin and update it
		foreach($pluginsToUpdate as $plugin => $pluginInfo)
		{
			// Prepare the plugin info
			$pluginInfo = json_decode($pluginInfo, true);
			
			// Prepare File Names
			$packageName = $plugin . "_v" . number_format($pluginInfo['version'], 2) . ".zip";
			
			$localPackage = SYS_PATH . self::PACKAGE_PLUGINS . "/" . $packageName;
			$localExtract = SYS_PATH . self::PACKAGE_PLUGINS . "/" . $plugin;
			
			// Check if you already have the local copy (may have already downloaded, but had errors)
			$haveLocal = false;
			
			if(is_file($localPackage))
			{
				// Make sure the local hash is correct
				$filehash = Security::filehash($localPackage);
				
				$haveLocal = $filehash == $pluginInfo['hash'] ? true : false;
			}
			
			// If you don't have a local copy, download it and confirm the downloaded package
			if(!$haveLocal)
			{
				// Download the plugin and save it locally
				Download::get($siteData['site_url'] . self::PACKAGE_PLUGINS . "/" . $packageName, "/system" . self::PACKAGE_PLUGINS . "/" . $packageName, true);
				
				// Confirm that the plugin is downloaded properly
				if(!is_file($localPackage))
				{
					Alert::error($plugin . ' Failed', 'The plugin `' . $plugin . '` was unable to download properly.');
					continue;
				}
				
				// Confirm the HASH value is identical
				$filehash = Security::filehash($localPackage);
				
				if($filehash != $pluginInfo['hash'])
				{
					Alert::error($plugin . ' Invalid', 'The plugin `' . $plugin . '` was unable to verify the appropriate confirmation hash.', 7);
					continue;
				}
			}
			
			// Unzip the downloaded file
			if(!Zip::unpackage($localPackage, $localExtract))
			{
				Alert::error($plugin . ' Unzip Error', 'The plugin `' . $plugin . '` could not be unzipped properly.', 7);
				continue;
			}
			
			// Confirm the plugin folder was properly extracted
			if(!is_dir($localExtract))
			{
				Alert::error($plugin . ' Extraction', 'The plugin `' . $plugin . '` was not extracted properly.', 7);
				continue;
			}
			
			// Determine the plugin type (addon, core, app) and use that to affect installation directory
			$baseDir = "";
			
			if($pluginInfo['type'] == "core")
			{
				$baseDir = CORE_PLUGIN_PATH;
			}
			else if($pluginInfo['type'] == "addon")
			{
				$baseDir = ADDON_PLUGIN_PATH;
			}
			
			if($baseDir == "") { continue; }
			
			// Determine the path of the current plugin, and make sure it matches expectations
			$pluginConfig = Plugin::getConfig($plugin);
			
			if($pluginConfig->data['path'] != $baseDir . "/" . $plugin)
			{
				Alert::error($plugin . " Directory Conflict", "The plugin `" . $plugin . "` is expected in a different directory.", 5);
				continue;
			}
			
			// Prepare Backup Conventions
			$backupDir = SYS_PATH . self::PACKAGE_BACKUP . "/" . $plugin;
			
			// Make sure the backup directory exists
			Dir::create($backupDir);
			
			// Move the current plugin to another file
			if(!Dir::move($baseDir . '/' . $plugin, $backupDir))
			{
				Alert::error($plugin . " Move", "The plugin `" . $plugin . "` could not move directories properly while updating.", 7);
				continue;
			}
			
			// Move the downloaded file to plugin directory
			Dir::move($localExtract, $baseDir . '/' . $plugin);
			
			// Set this plugin as being updated successfully
			SiteVariable::delete("plugin-update", $plugin);
			
			// Announce that this plugin was updated
			Alert::success($plugin . " Updated!", "The plugin `" . $plugin . "` has successfully been updated to version " . number_format($pluginInfo['version'], 2) . "!");
		}
	}
	
}

