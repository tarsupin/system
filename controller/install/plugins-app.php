<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display the Page
echo '
<div id="content" class="content-open">
' . Alert::display() . '

<h1>Installation: Plugins</h1>

<h3>Step #1 - Install Application Plugins</h3>

<p>These plugins will ONLY be used within the `' . $config['site-name'] . '` application you are setting up - NOT through the entire system. By default, they must be saved in your application in the /plugins directory.</p>

<p>Note: Application plugins will overwrite the functionality of core and addon plugins if you use the same plugin name.</p>';

// Loop through each plugin and install it
$plugins = Dir::getFolders(APP_PATH . "/plugins");

foreach($plugins as $plugin)
{
	// Load the Plugin's Config Class
	if(!$pluginConfig = Plugin::getConfig($plugin, APP_PATH . "/plugins"))
	{
		echo '<h4 style="color:red;">' . $plugin . '</h4>
		<p><span style="color:red;">The plugin\'s config class was inaccessible.</span></p>';
		continue;
	}
	
	// Bypass installation plugins
	if($plugin == "Install")
	{
		$installed = Install::setup() ? 1 : -1;
	}
	else
	{
		// Install Standard Plugin Types  (not installation)
		$installed = Plugin::install($plugin);
	}
	
	switch($installed)
	{
		case Plugin::DEPENDENCIES_MISSING:
			$details = '<span style="color:red; font-weight:700;">This installation requires dependencies that were not installed properly.</span>';
			break;
		
		case Plugin::INSTALL_FAILED:
			$details = '<span style="color:red; font-weight:700;">Installation failed. Core functionality may be broken.</span>';
			break;
		
		case Plugin::INSTALL_SUCCEEDED:
			$details = '<span style="color:green; font-weight:700;">Installation was completed successfully.</span>';
			break;
		
		case Plugin::NO_INSTALL_NEEDED:
			$details = '<span style="color:blue;">No installation was necessary for this plugin.</span>';
			break;
	}
	
	// Display the Plugin
	echo '<h4>' . $plugin . ' - v' . number_format($pluginConfig->version, 2) . '</h4>
	<p>
		Author: ' . $pluginConfig->author . '
		<br />Description: ' . $pluginConfig->description . '
		<br />' . $details . '
	</p>';
	
	// Display the Database Tables
	if($installed == Plugin::INSTALL_SUCCEEDED and isset($pluginConfig->data['schema']))
	{
		foreach($pluginConfig->data['schema'] as $tableName)
		{
			echo '
			<p>' . DatabaseAdmin::showTable($tableName) . '</p>';
		}
	}
}

echo '
<a class="button" href="/install/connect-auth">Continue with Installation</a>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");