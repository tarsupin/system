<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ModuleText_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ModuleText";
	public $title = "Standard Text Module";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the standard text module for the content system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_block_text`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`class`					varchar(22)					NOT NULL	DEFAULT '',
			
			`title`					varchar(120)				NOT NULL	DEFAULT '',
			`body`					text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 11;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("content_block_text", array("id", "title", "body"));
	}
	
}