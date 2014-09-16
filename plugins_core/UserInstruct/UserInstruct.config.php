<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UserInstruct_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "UserInstruct";
	public $title = "User Instructions";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allow settings for user instructions.";
	public $dependencies = array("User");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_instructions`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`plugin`				varchar(22)					NOT NULL	DEFAULT '',
			`behavior`				varchar(22)					NOT NULL	DEFAULT '',
			`params`				varchar(250)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			INDEX (`uni_id`, `plugin`, `behavior`, `params`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("users_instructions", array("id", "uni_id", "plugin", "behavior"));
	}
	
}