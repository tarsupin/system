<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ModuleImage_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ModuleImage";
	public $title = "Standard Image Module";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the standard image module for the content system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_block_image`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`img_class`				varchar(22)					NOT NULL	DEFAULT '',
			`caption`				varchar(64)					NOT NULL	DEFAULT '',
			`credits`				varchar(180)				NOT NULL	DEFAULT '',
			
			`image_url`				varchar(72)					NOT NULL	DEFAULT '',
			`mobile_url`			varchar(72)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("content_block_image", array("id", "image_url"));
	}
	
}