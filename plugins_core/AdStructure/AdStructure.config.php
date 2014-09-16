<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AdStructure_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "AdStructure";
	public $title = "Ad Structure";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides rules and data for ads based on their designated structure.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if installed, FALSE if not
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return true;
	}
	
}
