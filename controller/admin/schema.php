<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*
	/admin/schema
	
	This page is used to view, create, edit, and delete entries on tables / schemas / plugins.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Retrieve the URL segments to determine what to load
$plugin = isset($url[2]) ? Sanitize::variable($url[2]) : '';
$page = isset($url[3]) ? Sanitize::variable($url[3], " -") : '';

// Attempt to load the Schema Class
if($plugin and $page)
{
	// Load the Plugin Config and retrieve the schema
	$pluginConfig = Plugin::getConfig($plugin);
	
	// Attempt to load an admin file
	$adminFile = $pluginConfig->data['path'] . "/admin/" . $page . ".php";
	
	if(is_file($adminFile))
	{
		// Run Header
		require(SYS_PATH . "/controller/includes/admin_header.php");
		
		require($adminFile);
		
		// Display the Footer
		require(SYS_PATH . "/controller/includes/admin_footer.php");
		
		exit;
	}
	
	// Attempt to load the schema file
	$schemaFile = $pluginConfig->data['path'] . "/schema/" . $page . ".schema.php";
	
	if(is_file($schemaFile))
	{
		// Prepare Schema Values
		define("SCHEMA_HANDLER", true);
		$actionType = isset($url[4]) ? Sanitize::word($url[4]) : 'view';
		$schemaClass = "";
		
		// Load the file
		require($schemaFile);
		
		$schemaClass = $page . "_schema";
		
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
<table class="mod-table">';

foreach($linkList as $plugin => $linkData)
{
	echo '
	<tr>	
		<td>' . $plugin . '</td>
		<td>';
	
	$comma = "";
	
	foreach($linkData as $title => $link)
	{
		echo $comma . '<a href="/admin/schema/' . $plugin . '/' . $link . '">' . $title . '</a>';
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