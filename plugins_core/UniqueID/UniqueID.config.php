<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UniqueID_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "UniqueID";
	public $title = "Unique Numerical ID Generator";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Creates numerically unique ID counters that generate new, unique IDs when called.";
	public $dependencies = array("SiteVariable");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		// Create an UniqueID Tracker
		return UniqueID::newCounter("unique");
	}
	
}