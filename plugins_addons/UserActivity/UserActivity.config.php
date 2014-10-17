<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UserActivity_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "UserActivity";
	public $title = "Activity Measuring System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to keep track of and count online users and guests.";
	public $dependencies = array("Cache");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `activity_guests` (
			
			`guest_ip`				varchar(45)					NOT NULL	DEFAULT '',
			`date_lastVisit`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`guest_ip`),
			INDEX (`date_lastVisit`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `activity_users` (
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_lastVisit`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`),
			INDEX (`date_lastVisit`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("activity_guests", array("guest_ip", "date_lastVisit"));
		$pass2 = DatabaseAdmin::columnsExist("activity_users", array("uni_id", "date_lastVisit"));
		
		return ($pass1 and $pass2);
	}
	
}