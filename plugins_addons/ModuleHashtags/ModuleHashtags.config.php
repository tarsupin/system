<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ModuleHashtags_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ModuleHashtags";
	public $title = "Standard Hashtag Module";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the standard hashtag module for the content system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_hashtags`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`submitted`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`, `hashtag`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 3;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("content_hashtags", array("content_id", "hashtag"));
	}
	
}