<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// If you're loading a specific plugin
if(isset($url[3]))
{
	$plugin = Sanitize::variable($url[3]);
	$pluginAdmin = Plugin::getConfig($plugin);
	
	// If this plugin doesn't exist, return to the standard admin plugin page
	if($pluginAdmin === false)
	{
		header("Location: /admin/Plugin"); exit;
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

echo '
<style>
.tesla-documentation pre { font-family:Courier; -moz-tab-size:4; -o-tab-size:4; tab-size:4; color:blue; margin-top:22px; margin-bottom:22px; white-space: pre-wrap; }
.tesla-documentation textarea { width:100%; min-height:300px; -moz-tab-size:4; -o-tab-size:4; tab-size:4; font-size:1.0em; color:green; margin-bottom:12px; }
.tesla-documentation ol li { margin-bottom:22px; }
.describe-plugin { margin-bottom:22px; }
.describe-plugin p { margin-bottom:0px; }
</style>';

// Load a plugin page
if(isset($pluginAdmin))
{
	// Display details about the plugins
	echo '
	<h3>' . $pluginAdmin->title . ' (v ' . $pluginAdmin->version . ')</h3>
	<div class="describe-plugin">
		<p>Author: ' . $pluginAdmin->author . '</p>
		<p>License: ' . $pluginAdmin->license . '</p>
		<p>Website: <a href="' . $pluginAdmin->website . '">' . $pluginAdmin->website . '</a></p>
		<p>Description: ' . $pluginAdmin->description . '</p>
	</div>';
	
	if(isset($pluginAdmin->dependencies))
	{
		echo "
		<p>Dependencies: ";
		
		$comma = "";
		foreach($pluginAdmin->dependencies as $dep)
		{
			echo $comma . '<a href="/admin/Plugin/Plugin Details/' . $dep . '">' . $dep . '</a>';
			$comma = ", ";
		}
		
		echo '
		</p>';
	}
	
	// Scour the list of methods from the plugin through the file itself
	echo '
	<h3>`' . $pluginAdmin->pluginName . '` Methods (Extended)</h3>
	<pre>';
	
	if($methodList = File::getLines($pluginAdmin->data['path'] . "/" . $plugin . ".php"))
	{
		$collect = false;
		$html = "";
		
		for($a = 0, $len = count($methodList);$a < $len;$a++)
		{
			if(strpos($methodList[$a], "public ") !== false)
			{
				$collect = true;
				$html = $methodList[$a - 1] . "<br />" . $methodList[$a];
			}
			else if($collect == true)
			{
				$html .= $methodList[$a] . "<br />";
			}
			
			if(strpos($methodList[$a], "RETURN") !== false)
			{
				if($collect == true)
				{
					$collect = false;
					
					echo $html . '<br /><br />';
				}
			}
		}
	}
	
	echo '</pre>';
	
	// Get the list of methods for this plugin
	/*
	echo '
	<h3>`' . $pluginAdmin->pluginName . '`  Methods</h3>
	<pre>';
	
	$methods = get_class_methods($pluginAdmin->pluginName);
	
	foreach($methods as $method)
	{
		$methodData = new ReflectionMethod($pluginAdmin->pluginName, $method);
		
		// Display the Method
		echo '
<strong>';
		
		// Identify method scope
		if($methodData->isPublic())
		{
			echo "public ";
		}
		else if($methodData->isPrivate())
		{
			echo "private ";
		}
		else if($methodData->isProtected())
		{
			echo "protected ";
		}
		
		// Identify if the method is statically callable or not
		if($methodData->isStatic())
		{
			echo "static ";
		}
		
		echo $method . '</strong> (';
		
		$params = $methodData->getParameters();
		$comma = "";
		
		// Cycle through each method parameter and display it
		foreach($params as $param)
		{
			echo $comma;
			
			$comma = ", ";
			
			if($param->isPassedByReference())
			{
				echo "&";
			}
			
			echo "$" . $param->getName();
			
			if($param->isDefaultValueAvailable())
			{
				$defVal = $param->getDefaultValue();
				
				if(is_numeric($defVal))
				{
					echo ' = ' . $defVal;
				}
				else
				{
					echo ' = "' . $defVal . '"';
				}
			}
		}
		
		echo ')';
	}
	
	echo '
	</pre>';
	*/
	
	// Load the Documentation
	/*
	echo '
	<h3>`' . $pluginAdmin->pluginName . '` Documentation</h3>';
	
	if($documentation = File::getLines($pluginAdmin->data['path'] . "/" . $pluginAdmin->pluginName . ".php"))
	{
		echo '<pre style="tab-size:1; -moz-tab-size:4; white-space: pre-wrap; white-space: -moz-pre-wrap;">';
		
		foreach($documentation as $docLine)
		{
			echo '
' . $docLine;
		}
		
		echo '</pre>';
	}
	*/
}

// Load the list of plugins
else
{
	// Scan through the plugins directory
	$plugins = Dir::getFolders(APP_PATH . "/plugins");
	
	echo '
	<h3>Application Plugins</h3>
	<table class="mod-table">';
	
	foreach($plugins as $plugin)
	{
		// Reject class names that aren't valid
		if(!ctype_alnum($plugin)) { continue; }
		
		if($pluginConfig = Plugin::getConfig($plugin))
		{
			echo '
			<tr>
				<td style="max-width:100px; overflow:hidden;"><a href="/admin/Plugin/Plugin Details/' . $plugin . '">' . $plugin . '</a></td>
				<td>' . $pluginConfig->description . '</td>
			</tr>';
		}
	}
	
	echo '
	</table>';
	
	// Scan through the plugins directory
	$plugins = Dir::getFolders(ADDON_PLUGIN_PATH);
	
	echo '
	<h3 style="margin-top:22px;">Addon Plugins</h3>
	<table class="mod-table">';
	
	foreach($plugins as $plugin)
	{
		// Reject class names that aren't valid
		if(!ctype_alnum($plugin)) { continue; }
		
		if($pluginConfig = Plugin::getConfig($plugin))
		{
			echo '
			<tr>
				<td style="max-width:100px; overflow:hidden;"><a href="/admin/Plugin/Plugin Details/' . $plugin . '">' . $plugin . '</a></td>
				<td>' . $pluginConfig->description . '</td>
			</tr>';
		}
	}
	
	echo '
	</table>';
	
	// Scan through the plugins directory
	$plugins = Dir::getFolders(CORE_PLUGIN_PATH);
	
	echo '
	<h3 style="margin-top:22px;">Core Plugins</h3>
	<table class="mod-table">';
	
	foreach($plugins as $plugin)
	{
		// Reject class names that aren't valid
		if(!ctype_alnum($plugin)) { continue; }
		
		if($pluginConfig = Plugin::getConfig($plugin))
		{
			echo '
			<tr>
				<td style="max-width:100px; overflow:hidden;"><a href="/admin/Plugin/Plugin Details/' . $plugin . '">' . $plugin . '</a></td>
				<td>' . $pluginConfig->description . '</td>
			</tr>';
		}
	}
	
	echo '
	</table>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
