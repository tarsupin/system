<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Avatar_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Avatar";
	public $title = "Avatar Image System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows your site to utilize the avatar images.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		DatabaseAdmin::addColumn("users", "avatar_opt", "tinyint(1) unsigned NOT NULL", 0);
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("users", array("avatar_opt"));
	}
	
}
