<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Confirm_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Confirm";
	public $title = "Confirmation Checker";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Stores confirmation data (for email, coupon codes, etc) and validates when confirmation is valid.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `confirm_values`
		(
			`confirm_val`			varchar(22)					NOT NULL	DEFAULT '',
			`confirm_data`			varchar(250)				NOT NULL	DEFAULT '',
			`date_expires`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`confirm_val`),
			INDEX (`date_expires`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (confirm_val) PARTITIONS 7;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("confirm_values", array("confirm_val"));
	}
	
}