<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Sync_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Sync";
	public $title = "Synchronization System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides critical tools that are necessary for synchronizing.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `sync_tracker`
		(
			`plugin`				varchar(22)					NOT NULL	DEFAULT '',
			`tracker_time`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sync_time`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`delay`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`plugin`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Make sure the newly installed tables exist
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin has been successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		return DatabaseAdmin::columnsExist("sync_tracker", array("plugin", "sync_time"));
	}
	
}
