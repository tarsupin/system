<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Search_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Search";
	public $title = "Search System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A very simple searching system that provides tools for adding search options to a list.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `search_entries`
		(
			`entry_id`				int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`entry`					varchar(72)					NOT NULL	DEFAULT '',
			`extra_keywords`		varchar(150)				NOT NULL	DEFAULT '',
			
			`url_path`				varchar(72)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`entry_id`),
			FULLTEXT (`entry`, `extra_keywords`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("search_entries", array("entry_id", "entry"));
	}
	
}