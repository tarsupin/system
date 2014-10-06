<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Notifications_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Notifications";
	public $title = "Notification System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a system to create and show notifications, as well as connect with UniFaction's Notification system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`sender_id`			the uni_id that was responsible for sending the notification (usually 0, for server)
			`note_type`			the notification type
			`url`				the url to go to if the notification is clicked
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `notifications`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sender_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`note_type`				varchar(22)					NOT NULL	DEFAULT '',
			`message`				varchar(150)				NOT NULL	DEFAULT '',
			`url`					varchar(72)					NOT NULL	DEFAULT '',
			
			`sync_unifaction`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`, `note_type`, `date_created`),
			INDEX (`date_created`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 13;
		");
		
		/*
			Global Notifictions aren't provided to the user until they log in (that way inactive users don't get them).
			Use the user's last login time to check what notifications to receive.
			
			Everyone's notifications are set to 2 when a global notification is posted (rather than 1)
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `notifications_global`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`message`				varchar(150)				NOT NULL	DEFAULT '',
			`url`					varchar(72)					NOT NULL	DEFAULT '',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`date_created`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		// Add a notification tracker
		DatabaseAdmin::addColumn("users", "date_notes", "int(10) unsigned NOT NULL", 0);
		
		// Set the Notification Sync Tracker
		Sync::setTracker("Notifications", time(), 1, 120);
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("notifications", array("uni_id", "sender_id", "message"));
		$pass2 = DatabaseAdmin::columnsExist("notifications_global", array("message", "url"));
		$pass3 = DatabaseAdmin::columnsExist("users", array("date_notes"));
		
		return ($pass1 and $pass2 and $pass3);
	}
	
}