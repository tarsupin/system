<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UserAuth_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "UserAuth";
	public $title = "Auth-User Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Identifies users with owner IDs (own multiple uniIDs), and provides tools of interaction with them.";
	public $dependencies = array("User");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		// Update the User's Table
		DatabaseAdmin::addColumn("users", "auth_id", "int(10) unsigned not null", 0);
		
		// Create a table that stores auth_id ownership
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_auth_join`
		(
			`auth_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`handle`				varchar(22)					NOT NULL	DEFAULT '',
			
			UNIQUE (`auth_id`, `uni_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PARTITION BY KEY(auth_id) PARTITIONS 5;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass = DatabaseAdmin::columnsExist("users", array("auth_id"));
		$pass2 = DatabaseAdmin::columnsExist("users_auth_join", array("auth_id", "uni_id", "handle"));
		
		return ($pass and $pass2);
	}
	
}