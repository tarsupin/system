<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Admin Panel ------
-----------------------------------

This control panel is for administrators of the site, which include all staff members.

This panel will list all of the available functionality and administrative pages available to the system, but some of them may be locked to staff members that do not have high enough clearance levels.

This page will pull all of the functionality from the plugins available to the site in two ways:

	1. Any .php file saved in the /admin directory of a plugin will be loaded as an admin page here.
	
	2. Any .schema.php file saved in the /schema directory of a plugin will be loaded as a schema class / handler.
	
		* Schema handlers must be structured like a proper schema plugin in order to function properly.
	
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Check if the site needs to sync
Sync::run();

// Retrieve the URL segments to determine what to load
$plugin = isset($url[1]) ? Sanitize::variable($url[1]) : '';
$page = isset($url[2]) ? Sanitize::variable($url[2], " -") : '';

// Attempt to load the Schema Class
if($plugin and $page)
{
	// Load the Plugin Config and retrieve the schema
	$pluginConfig = Plugin::getConfig($plugin);
	
	// Attempt to load an admin file
	$adminFile = $pluginConfig->data['path'] . "/admin/" . $page . ".php";
	
	if(is_file($adminFile))
	{
		require($adminFile); exit;
	}
	
	// Attempt to load the schema file
	$schemaFile = $pluginConfig->data['path'] . "/schema/" . $page . ".schema.php";
	
	if(is_file($schemaFile))
	{
		// Prepare Schema Values
		define("SCHEMA_HANDLER", true);
		$actionType = isset($url[3]) ? Sanitize::word($url[3]) : 'view';
		$table = $page;
		$schemaClass = "";
		
		// Load the file
		require($schemaFile);
		
		$schemaClass = $table . "_schema";
		
		if(class_exists($schemaClass))
		{
			$schema = new $schemaClass();
			
			// Show the Schema Form
			if(in_array($actionType, array("view", "create", "edit", "search")))
			{
				require(SYS_PATH . "/controller/includes/schema/" . $actionType . ".php"); exit;
			}
		}
	}
}

// Scan through the plugins directory
$pluginList = Plugin::getPluginList();

// Prepare Values
$linkList = array();

// Cycle through the plugins to find any schema pages available.
// If a plugin has a schema page, add it to the admin list.
foreach($pluginList as $plugin)
{
	// Reject class names that aren't valid
	if(!ctype_alnum($plugin)) { continue; }
	
	if($pluginConfig = Plugin::getConfig($plugin))
	{
		// If there is no "isInstalled" method, don't show the entry
		if(!method_exists($pluginConfig->pluginName . "_config", "isInstalled"))
		{
			continue;
		}
		
		// If the plugin isn't installed, don't show it
		if(!$installed = call_user_func(array($pluginConfig->pluginName . "_config", "isInstalled")))
		{
			continue;
		}
		
		// Get list of controllers
		if($controllerList = Plugin::getAdminPages($pluginConfig->data['path']))
		{
			foreach($controllerList as $controller)
			{
				$linkList[$pluginConfig->pluginName][$controller] = $controller;
			}
		}
		
		// Get list of schemas
		if($getSchemas = Plugin::getSchemas($pluginConfig->data['path']))
		{
			foreach($getSchemas as $schema)
			{
				require_once($pluginConfig->data['path'] . '/schema/' . $schema . '.schema.php');
				
				$schemaClass = $schema . "_schema";
				$getSchema = new $schemaClass();
				
				$linkList[$pluginConfig->pluginName][$getSchema->title] = $schema;
			}
		}
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Cycle through all of the available admin components
ksort($linkList);

echo '
<style>
	.admin-table tr:nth-child(2n-1) { background-color:#cceeff; }
	.admin-table td { padding:3px; border:solid black 1px; }
</style>

<table class="admin-table">';

foreach($linkList as $plugin => $linkData)
{
	echo '
	<tr>	
		<td>' . $plugin . '</td>
		<td>';
	
	$comma = "";
	
	foreach($linkData as $title => $link)
	{
		echo $comma . '<a href="/admin/' . $plugin . '/' . $link . '">' . $title . '</a>';
		$comma = "<br />";
	}
	
	echo '
		</td>
	</tr>';
}

echo '
</table>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
