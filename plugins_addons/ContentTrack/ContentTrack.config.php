<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ContentTrack_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ContentTrack";
	public $title = "Content Tracking Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a system for handling content tracking.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_tracking`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`rating`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`views`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			`shared`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`comments`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`votes_up`				mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`votes_down`			mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			
			`tipped_times`			smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			`tipped_amount`			float(8,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`flagged`				tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_tracking_users`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`shared`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`vote`					tinyint(1)					NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `content_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("content_tracking", array("content_id", "rating", "views"));
		$pass2 = DatabaseAdmin::columnsExist("content_tracking_users", array("uni_id", "content_id", "vote"));
		
		return ($pass1 and $pass2);
	}
	
}