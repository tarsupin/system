<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Friends_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Friends";
	public $title = "Friends System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to identify and interact with friends and friend lists.";
	public $dependencies = array("Sync");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`view_clearance` is the level of viewing privileges the friend has granted
			`interact_clearance` is the level of interaction privileges the friend has granted
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users_friends`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`friend_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`view_clearance`		tinyint(1)					NOT NULL	DEFAULT '0',
			`interact_clearance`	tinyint(1)					NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `friend_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 23;
		");
		
		// Create a sync tracker for the Friends plugin
		Sync::setTracker("Friends", time(), 1, 600, true);
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("users_friends", array("uni_id", "friend_id"));
	}
	
}