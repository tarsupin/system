<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Network_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Network";
	public $title = "Network System";
	public $version = 1.00;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the site to connect to APIs, phpTesla sites, and the UniFaction sites.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `network_data`
		(
			`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
			`site_name`				varchar(48)					NOT NULL	DEFAULT '',
			`site_clearance`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`site_url`				varchar(48)					NOT NULL	DEFAULT '',
			`site_key`				varchar(100)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`site_handle`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("network_data", array("site_handle", "site_name"));
	}
	
}