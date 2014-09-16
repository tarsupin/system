<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ContentHashtags_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ContentHashtags";
	public $title = "Content Hashtag System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the content system to be sorted by hashtags.";
	public $dependencies = array("Content");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_by_hashtag`
		(
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`hashtag`, `content_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(hashtag) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_site_hashtags`
		(
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			
			UNIQUE (`hashtag`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("content_by_hashtag", array("hashtag", "content_id"));
		$pass2 = DatabaseAdmin::columnsExist("content_hashtags", array("hashtag"));
		
		return ($pass1 and $pass2);
	}
	
}