<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Scripts {

/****** Scripts Class ******
* This class is just used as generic functions or scripts that are likely used between sites.
*
****** Methods Available ******
* $confDirs = Scripts::getConfigDirs();		// Get a list of the CONF directories
* $appDirs = Scripts::getAppDirs();			// Get a list of the APP directories
* 
* Scripts::defaultSearch()					// Automatically generates the default search options
* Scripts::defaultCategorySearch()			// Automatically generate the default category search options
*/
	
	
/****** Get a list of all of the site CONF directories ******/
	public static function getConfigDirs (
	)						// RETURNS <int:str> list of CONF directories.
	
	// $confDirs = Scripts::getConfigDirs();
	{
		$baseDir = dirname(SYS_PATH);
		$files = array();
		
		// Get Sites with no sub-sites
		foreach(glob($baseDir . "/*/config.php") as $filename)
		{
			$files[] = str_replace("/config.php", "", $filename);
		}
		
		// Get Sites with sub-sites
		foreach(glob($baseDir . "/*/*/config.php") as $filename)
		{
			$files[] = str_replace("/config.php", "", $filename);
		}
		
		return $files;
	}
	
	
/****** Get a list of all of the site APP directories ******/
	public static function getAppDirs (
	)						// RETURNS <int:str> list of APP directories.
	
	// $appDirs = Scripts::getAppDirs();
	{
		$baseDir = dirname(SYS_PATH);
		$files = array();
		
		// Get Sites with no sub-sites
		foreach(glob($baseDir . "/*/controller/home.php") as $filename)
		{
			$files[] = str_replace("/controller/home.php", "", $filename);
		}
		
		// Get Sites with sub-sites
		foreach(glob($baseDir . "/*/*/controller/home.php") as $filename)
		{
			$files[] = str_replace("/controller/home.php", "", $filename);
		}
		
		return $files;
	}
}

