<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }/*

------------------------------------------
------ About the HHVMConvert Plugin ------
------------------------------------------

This plugin, when run, will automatically convert a plugins from standard PHP to an HHVM equivalent (Hack Language) and insert it into the appropriate /hhvm directory.

------------------------------------------
------ Example of using this plugin ------
------------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------

// Converts a single, designated plugin to work on HHVM
HHVMConvert::convert($plugin);


// Converts all plugins to work on HHVM (within desired directories)
// HHVMConvert::massConversion($core = false, $addon = false, $app = false);

*/

abstract class HHVMConvert {
	
	
/****** Plugin Variables ******/
	public static $typeList = array(
			"str"		=> "string"
		,	"int"		=> "int"
		,	"float"		=> "float"
		,	"bool"		=> "bool"
		,	"void"		=> "void"
		,	"array"		=> "array"
		,	"mixed"		=> "mixed"
		,	"gen"		=> "T"
		,	"T"			=> "T"
		);
	
	
/****** Convert a plugin from PHP to Hack Language ******/
	public static function convert
	(
		$plugin		// <str> The name of the plugin to convert.
	,	$dir = ""	// <str> The directory to search for the plugin at.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// HHVMConvert::convert($plugin, [$dir]);
	{
		// Do not allow the HHVMConvert plugin to be converted
		if($plugin == "HHVMConvert") { return false; }
		
		// Get the plugin configurations, so that we can identify the file's directory
		if(!$pluginConfig = Plugin::getConfig($plugin, $dir))
		{
			return false;
		}
		
		// Get the file contents
		if(!$contents = File::getLines($pluginConfig->data['path'] . '/' . $pluginConfig->pluginName . '.php'))
		{
			return false;
		}
		
		// Simple Conversions
		$contents = str_replace('<?php', '<?hh', $contents);
		
		// Line by Line Conversions
		foreach($contents as $key => $line)
		{
			// Method Parameters
			$param = "";
			
			if(strpos($line, '	$') !== false)
			{
				$param = '$';
			}
			else if(strpos($line, '	&$') !== false)
			{
				$param = '&$';
			}
			
			if($param !== "")
			{
				$type = HHVMConvert::varType($line, "// ");
				
				// Perform the update, if possible
				if($type != "")
				{
					$contents[$key] = str_replace("	" . $param, "	" . $type . " " . $param, $line);
					continue;
				}
			}
			
			// Class Variables
			if(strpos($line, '$') !== false)
			{
				$type = HHVMConvert::varType($line, "// ");
				// and (strpos($line, "public") !== false or strpos($line, "private") !== false or strpos($line, "protected") !== false)
				
				if($type != "")
				{
					$contents[$key] = str_replace('$', $type . ' $', $line);
					continue;
				}
			}
			
			// If there is a RETURN value
			if(strpos($line, "	)") !== false)
			{
				$type = HHVMConvert::varType($line, "RETURNS ");
				
				// Perform the update, if possible
				if($type != "")
				{
					$contents[$key] = str_replace("	)", "	): " . $type, $line);
					continue;
				}
			}
			
			// If the method line itself has a comment (for generic types)
			if(strpos($line, "function") !== false)
			{
				$type = HHVMConvert::varType($line, "// ");
				
				// Perform the update, if possible
				if($type == "T")
				{
					$contents[$key] = str_replace("// <T>", "<T> // <T>", $line);
					continue;
				}
			}
		}
		
		// Convert to String
		$fullContent = "";
		
		foreach($contents as $key => $line)
		{
			$fullContent .= ($key == 0 ? "" : "\n") . $line;
		}
		
		// Remove Documentation
		//$between = Parse::through($fullContent, "/*", "*/");
		//$fullContent = str_replace($between, "", $fullContent);
		
		// Display Result
		// echo "<br /><pre>" . htmlspecialchars($fullContent) . "</pre>";
		
		// Save the File
		return File::write($pluginConfig->data['path'] . '/hhvm/' . $pluginConfig->pluginName . '.php', $fullContent);
	}
	
	
/****** Convert a group of plugins from PHP to Hack Language ******/
	public static function massConversion
	(
		$core = false	// <bool> TRUE to convert core plugins.
	,	$addon = false	// <bool> TRUE to convert addon plugins.
	,	$app = false	// <bool> TRUE to convert app plugins.
	)					// RETURNS <bool> TRUE on success, FALSE on failure
	
	// HHVMConvert::massConversion([$core], [$addon], [$app]);
	{
		// Convert Core Plugins
		if($core)
		{
			echo '<h2>Core Plugin HHVM Conversions</h2>';
			
			$pluginList = Plugin::getPluginList(CORE_PLUGIN_PATH);
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Conversion Complete.<br />';
				
				HHVMConvert::convert($plugin, CORE_PLUGIN_PATH);
			}
		}
		
		// Convert Addon Plugins
		if($addon)
		{
			echo '<h2>Addon Plugin HHVM Conversions</h2>';
			
			$pluginList = Plugin::getPluginList(ADDON_PLUGIN_PATH);
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Conversion Complete.<br />';
				
				HHVMConvert::convert($plugin, ADDON_PLUGIN_PATH);
			}
		}
		
		// Convert App Plugins
		if($app)
		{
			echo '<h2>App Plugin HHVM Conversions</h2>';
			
			$pluginList = Plugin::getPluginList(APP_PATH . "/plugins");
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Conversion Complete.<br />';
				
				HHVMConvert::convert($plugin, APP_PATH . "/plugins");
			}
		}
	}
	
	
/****** Delete an HHVM version of a plugin ******/
	public static function delete
	(
		$plugin		// <str> The name of the plugin to convert.
	,	$dir = ""	// <str> The directory that the plugin needs to be deleted from.
	)				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// HHVMConvert::delete($plugin, [$dir]);
	{
		// Get the plugin configurations, so that we can identify the file's directory
		if(!$pluginConfig = Plugin::getConfig($plugin, $dir))
		{
			return false;
		}
		
		// Make sure the file exists
		if(!File::exists($pluginConfig->data['path'] . '/hhvm/' . $pluginConfig->pluginName . '.php'))
		{
			return false;
		}
		
		// Delete the file
		return File::delete($pluginConfig->data['path'] . '/hhvm/' . $pluginConfig->pluginName . '.php');
	}
	
	
/****** Delete a group of HHVM plugins ******/
	public static function massDeletion
	(
		$core = false	// <bool> TRUE to convert core plugins.
	,	$addon = false	// <bool> TRUE to convert addon plugins.
	,	$app = false	// <bool> TRUE to convert app plugins.
	)					// RETURNS <void>
	
	// HHVMConvert::massDeletion([$core], [$addon], [$app]);
	{
		// Convert Core Plugins
		if($core)
		{
			echo '<h2>Core Plugin HHVM Deletions</h2>';
			
			$pluginList = Plugin::getPluginList(CORE_PLUGIN_PATH);
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Deletion Complete.<br />';
				
				HHVMConvert::delete($plugin, CORE_PLUGIN_PATH);
			}
		}
		
		// Convert Addon Plugins
		if($addon)
		{
			echo '<h2>Addon Plugin HHVM Deletions</h2>';
			
			$pluginList = Plugin::getPluginList(ADDON_PLUGIN_PATH);
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Deletion Complete.<br />';
				
				HHVMConvert::delete($plugin, ADDON_PLUGIN_PATH);
			}
		}
		
		// Convert App Plugins
		if($app)
		{
			echo '<h2>App Plugin HHVM Deletions</h2>';
			
			$pluginList = Plugin::getPluginList(APP_PATH . "/plugins");
			
			foreach($pluginList as $plugin)
			{
				echo '
				<span style="font-weight:bold;">' . $plugin . '</span>: HHVM Deletion Complete.<br />';
				
				HHVMConvert::delete($plugin, APP_PATH . "/plugins");
			}
		}
	}
	
	
/****** Check the variable type ******/
	public static function varType
	(
		$line			// <str> The line to retrieve the type from.
	,	$before = ""	// <str> The content prior to the variable type.
	)					// RETURNS <str>
	
	// $type = HHVMConvert::varType($line, [$before]);
	{
		// Prepare the content that should be found before and after the matching type key
		$before .= "<";
		
		// Cycle through the list of possible variables, and return the appropriate type
		// For example, if the line finds <str> in it, return "string"
		foreach(self::$typeList as $tKey => $tType)
		{
			if(strpos($line, $before . $tKey . ">") !== false)
			{
				return $tType;
			}
		}
		
		// If there is a more advanced situation, such as an array, we need to parse it differently
		if($check = Parse::between($line, $before, ">"))
		{
			// Split the hypothetical array into first and second parts
			$exp = explode(":", $check, 2);
			
			// If the second part exists, we found a proper array
			if(isset($exp[1]))
			{
				// Check if the first section of the array is a proper type (will be int, str, or mixed)
				if(isset(self::$typeList[$exp[0]]))
				{
					// The second part may be a standard type. If so, we can return the type
					if(isset(self::$typeList[$exp[1]]))
					{
						return "array <" . $exp[0] . ", " . $exp[1] . ">";
					}
					
					// If we haven't returned, the second part is probably a nest (another array)
					// No more nests allowed beyond this.
					if(strpos($exp[1], "[") !== false)
					{
						// Repeat the same test again
						$nest = Parse::between($exp[1], "[", "]");
						
						$nestExp = explode(":", $nest, 2);
						
						if(isset($nestExp[1]))
						{
							if(self::$typeList[$nestExp[0]] and self::$typeList[$nestExp[1]])
							{
								return "array <" . $exp[0] . ", " . "array<" . $nestExp[0] . ", " . $nestExp[1] . ">>";
							}
						}
					}
				}
			}
		}
		
		// If no variable was found, return no type found (empty string)
		return "";
	}
}
