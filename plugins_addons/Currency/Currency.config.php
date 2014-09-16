<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Currency_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Currency";
	public $title = "Virtual Currency Handler";
	public $version = 0.8;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to create and track virtual currency on your site.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `currency`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`amount`				float(10,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			UNIQUE (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 3;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `currency_records`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`other_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`amount`				float(10,2)					NOT NULL	DEFAULT '0.00',
			`running_total`			float(10,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`date_exchange`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`description`			varchar(64)					NOT NULL	DEFAULT '',
			
			INDEX (`uni_id`, `date_exchange`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 11;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass = DatabaseAdmin::columnsExist("currency", array("uni_id", "amount"));
		$pass2 = DatabaseAdmin::columnsExist("currency_records", array("uni_id", "other_id", "amount", "running_total", "date_exchange", "description"));
		
		return ($pass and $pass2);
	}
	
}