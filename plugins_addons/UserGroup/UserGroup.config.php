<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UserGroup_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "UserGroup";
	public $title = "Group Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Identifies groups that the user belongs to and provides tools of interaction with them.";
	public $dependencies = array("User");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `groups`
		(
			`group_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`type`					varchar(16)					NOT NULL	DEFAULT '',
			`title`					varchar(22)					NOT NULL	DEFAULT '',
			
			UNIQUE (`group_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(group_id) PARTITIONS 3;
		");
		
		// Create a table that stores the UniIDs associated with a group
		Database::exec("
		CREATE TABLE IF NOT EXISTS `groups_users`
		(
			`group_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`clearance`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`group_id`, `uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(group_id) PARTITIONS 13;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `groups_users_join`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`group_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `group_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 13;
		");
	}
	
	
/****** Check if this plugin was successfuly installed ******/
	public static function isInstalled (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("groups", array("type", "title"));
		$pass2 = DatabaseAdmin::columnsExist("groups_users", array("group_id", "uni_id"));
		$pass3 = DatabaseAdmin::columnsExist("groups_users_join", array("uni_id", "group_id"));
		
		return ($pass1 and $pass2 and $pass3);
	}
	
}