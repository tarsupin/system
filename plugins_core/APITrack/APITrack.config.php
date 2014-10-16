<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class APITrack_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "APITrack";
	public $title = "API Tracking System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Tracks the API usage for every API on the site.";
	public $dependencies = array("SchemaDefine");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `api_tracker`
		(
			`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
			`cycle`					mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`api_name`				varchar(22)					NOT NULL	DEFAULT '',
			`times_accessed`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`site_handle`, `cycle`, `api_name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(site_handle) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("api_tracker", array("site_handle", "cycle"));
	}
	
}