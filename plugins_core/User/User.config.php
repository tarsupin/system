<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class User_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "User";
	public $title = "User and Account Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Handles several important functions that deal with users, including registration and login.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`role`					varchar(12)					NOT NULL	DEFAULT '',
			`flair`					varchar(22)					NOT NULL	DEFAULT '',
			`clearance`				tinyint(1)					NOT NULL	DEFAULT '0',
			
			`handle`				varchar(22)					NOT NULL	DEFAULT '',
			`display_name`			varchar(22)					NOT NULL	DEFAULT '',
			
			`timezone`				varchar(22)					NOT NULL	DEFAULT '',
			
			`date_joined`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_lastLogin`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`auth_token`			varchar(22)					NOT NULL	DEFAULT '',
			
			UNIQUE (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 7;
		");
		
		// Vertical partitioning of the user's table. This creates duplicate content, but will greatly speed up
		// lookup times for handles. There is overhead for two tables, but its a negligible impact compared to the
		// horizontal partitioning we gain on the user's table.
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_handles`
		(
			`handle`				varchar(22)					NOT NULL	DEFAULT '',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`handle`, `uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8
		
		PARTITION BY RANGE COLUMNS(handle) (
			PARTITION p0 VALUES LESS THAN ('a'),
			PARTITION p1 VALUES LESS THAN ('e'),
			PARTITION p2 VALUES LESS THAN ('i'),
			PARTITION p3 VALUES LESS THAN ('m'),
			PARTITION p4 VALUES LESS THAN ('q'),
			PARTITION p5 VALUES LESS THAN ('u'),
			PARTITION p6 VALUES LESS THAN MAXVALUE
		);
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("users", array("handle", "uni_id"));
		$pass2 = DatabaseAdmin::columnsExist("users_handles", array("handle", "uni_id"));
		
		return ($pass1 and $pass2);
	}
	
}