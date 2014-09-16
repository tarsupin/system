<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Analytics_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Analytics";
	public $title = "Analytics System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides simple tools to track traffic on your site (not a comprehensive analytics solution).";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `analytics_page_views` (
			
			`timestamp_interval`	int(10)			unsigned	NOT NULL	DEFAULT '0',
			`url_path`				varchar(45)					NOT NULL	DEFAULT '',
			`visits`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`timestamp_interval`, `url_path`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 PARTITION BY KEY(timestamp_interval) PARTITIONS 13;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `analytics_custom_trackers` (
			
			`timestamp_interval`	int(10)			unsigned	NOT NULL	DEFAULT '0',
			`custom_tracker`		varchar(45)					NOT NULL	DEFAULT '',
			`visits`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`timestamp_interval`, `custom_tracker`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 PARTITION BY KEY(timestamp_interval) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass = DatabaseAdmin::columnsExist("analytics_page_views", array("timestamp_interval", "url_path", "visits"));
		$pass2 = DatabaseAdmin::columnsExist("analytics_custom_trackers", array("timestamp_interval", "custom_tracker", "visits"));
		
		return ($pass and $pass2);
	}
	
}