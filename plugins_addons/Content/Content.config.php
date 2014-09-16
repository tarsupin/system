<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Content_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Content";
	public $title = "Content System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the creation, modification, and interpretation of content entries.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`primary_hashtag` is the hashtag that is most closely associated with this entry.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_entries`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`url_slug`				varchar(46)					NOT NULL	DEFAULT '',
			`title`					varchar(72)					NOT NULL	DEFAULT '',
			
			`primary_hashtag`		varchar(22)					NOT NULL	DEFAULT '',
			
			`thumbnail`				varchar(72)					NOT NULL	DEFAULT '',
			
			`status`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`clearance_view`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`clearance_edit`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`comments`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`voting`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`date_posted`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
		");
		
		/*
			This table is identifying the individual segments to load for an entry, and the order they load in.
			
			`content_id` is the ID of the content to load.
			`sort_order` is the order that the block is being sorted in.
			`type` is the block type to load (e.g. "Text", "Image", etc.)
			`block_id` is the ID of the content block to load.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_block_segment`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sort_order`			tinyint(255)	unsigned	NOT NULL	DEFAULT '0',
			
			`type`					varchar(22)					NOT NULL	DEFAULT '',
			`block_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`, `sort_order`, `block_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 13;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_by_url`
		(
			`url_slug`				varchar(42)					NOT NULL	DEFAULT '',
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`url_slug`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(url_slug) PARTITIONS 3;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_by_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `content_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 3;
		");
		
		// This table allows us to track entries listed as guest posts that are waiting for official approval
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_queue`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`last_update`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`content_id`),
			INDEX (`last_update`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// This table caches data in it
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_cache`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`body`					text						NOT NULL	DEFAULT '',
			
			UNIQUE (`content_id`)
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
		$pass1 = DatabaseAdmin::columnsExist("content_entries", array("id", "title", "status"));
		$pass2 = DatabaseAdmin::columnsExist("content_block_segment", array("content_id", "block_id"));
		$pass3 = DatabaseAdmin::columnsExist("content_by_url", array("url_slug", "content_id"));
		$pass4 = DatabaseAdmin::columnsExist("content_by_user", array("uni_id", "content_id"));
		$pass5 = DatabaseAdmin::columnsExist("content_queue", array("content_id", "last_update"));
		$pass6 = DatabaseAdmin::columnsExist("content_cache", array("content_id", "body"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4 and $pass5 and $pass6);
	}
	
}