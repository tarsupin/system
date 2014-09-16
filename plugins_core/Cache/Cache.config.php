<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Cache_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Cache";
	public $title = "Data Caching System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Caches data in RAM (or database as a fallback) to reduce the number of calls to expensive operations.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return Cache::sql();
	}
	
}