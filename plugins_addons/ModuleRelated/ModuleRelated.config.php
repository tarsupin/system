<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ModuleRelated_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ModuleRelated";
	public $title = "Related Content Module";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a module that shows related content to the entry being viewed.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_related`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`related_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`, `related_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 5;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("content_related", array("content_id", "related_id"));
	}
	
}