<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Prepare Values
$madeSelection = (Form::submitted("install-addon-plugins") ? true : false);

set_time_limit(300); 	// Set the maximum execution time to 5 minutes

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

<h3>Step #1 - Install Addon Plugins</h3>

<p>The addon plugins are not required plugins, but are added to phpTesla system (as opposed to the phpTesla application) so that they can be used across all phpTesla applications within your environment. This step will allow you to install them.</p>

<p>Note: Not all plugins require installation. For plugins that do require installation, you can choose whether or not you wish to install them for this application.</p>';

// Loop through each addon plugin
$plugins = Dir::getFolders(ADDON_PLUGIN_PATH);

if($madeSelection)
{
	foreach($plugins as $plugin)
	{
		// Load the Plugin's Config Class
		if(!$pluginConfig = Plugin::getConfig($plugin, ADDON_PLUGIN_PATH))
		{
			echo '<h4 style="color:red;">' . $plugin . '</h4>
			<p><span style="color:red;">The plugin\'s config class was inaccessible.</span></p>';
			
			continue;
		}
		
		$details = "";
		
		// If the plugin was intentionally installed
		if(isset($_POST['addon'][$plugin]))
		{
			// Install the Plugin
			$installed = Plugin::install($plugin);
			
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
		}
		
		// If the plugin doesn't require installation
		else if(!Plugin::hasInstaller($plugin, $pluginConfig))
		{
			$details = '<span style="color:blue;">No installation was necessary for this plugin.</span>';
		}
		else
		{
			$details = '<span style="color:blue; font-weight:700;">Was not installed.</span>';
		}
		
		// Display the Plugin
		echo '<h4>' . $plugin . ' - v' . number_format($pluginConfig->version, 2) . '</h4>
		<p>
			Author: ' . $pluginConfig->author . '
			<br />Description: ' . $pluginConfig->description . '
			<br />' . $details . '
		</p>';
	}
	
	echo '
	<a class="button" href="/install/plugins-app">Continue with Installation</a>';
}
else
{
	echo '
	<style>
		.plugin-table tr:nth-child(2n-1) { background-color:#cceeff; }
		.plugin-table tr:hover { background-color:#aaddaa; }
		.plugin-table td { padding:3px; border:solid black 1px; }
	</style>
	
	<form class="uniform" action="/install/plugins-addon" method="post">' . Form::prepare("install-addon-plugins") . '
	<table class="plugin-table">';
	
	foreach($plugins as $plugin)
	{
		// Load the Plugin's Admin Class
		if($pluginConfig = Plugin::getConfig($plugin, ADDON_PLUGIN_PATH))
		{
			// Display the Plugins
			echo '
			<tr>
				<td>';
			
			if(Plugin::hasInstaller($plugin, $pluginConfig))
			{
				echo '<input type="checkbox" name="addon[' . $plugin . ']" ' . (isset(Install::$addonPlugins[$plugin]) ? 'checked onchange="this.checked=true"' : '') . ' />';
			}
			else
			{
				echo '&nbsp;';
			}
			
			echo '</td>
				<td style="max-width:100px; overflow:hidden;"><a href="/admin/plugins/' . $plugin . '">' . $plugin . '</a></td>
				<td>' . $pluginConfig->description . '</td>
			</tr>';
		}
	}
	
	echo '
	</table>
	
	<br />
	<input type="submit" name="submit" value="Install Selected Plugins" />
	</form>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");