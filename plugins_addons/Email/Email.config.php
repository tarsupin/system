<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Email_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Email";
	public $title = "Email System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "If your server can send emails, this allows you to send emails, email attachments, etc.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_email`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`recipient`				varchar(72)					NOT NULL	DEFAULT '',
			`subject`				varchar(42)					NOT NULL	DEFAULT '',
			`message`				text						NOT NULL	DEFAULT '',
			
			`details`				text						NOT NULL	DEFAULT '',
			
			`date_sent`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`id`) PARTITIONS 5;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("log_email", array("id", "subject"));
	}
	
}