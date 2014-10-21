<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Paypal_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "Paypal";
	public $title = "Paypal System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Handles Paypal transactions and APIs.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `paypal_transactions`
		(
			`txn_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`auth_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`amount_paid`			float(7,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`user_received`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`status`				varchar(20)					NOT NULL	DEFAULT '',
			`email`					varchar(64)					NOT NULL	DEFAULT '',
			
			`date_paid`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`txn_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("paypal_transactions", array("auth_id", "uni_id"));
	}
	
}