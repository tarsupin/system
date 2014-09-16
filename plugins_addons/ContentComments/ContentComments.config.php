<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ContentComments_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ContentComments";
	public $title = "Content Comment System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows users to post comments on articles, blogs, and other content entries.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_comments`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`comment`				text						NOT NULL	DEFAULT '',
			
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 13;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_comments_by_entry`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`comment_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`, `comment_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("content_comments", array("id", "uni_id", "comment"));
		$pass2 = DatabaseAdmin::columnsExist("content_comments_by_entry", array("content_id", "comment_id"));
		
		return ($pass1 and $pass2);
	}
	
}