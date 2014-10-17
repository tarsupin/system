<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Transaction_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Transaction";
	public $title = "Transaction Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows multiple participants to safely create transactions and trades between each other.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions_users`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`transaction_id`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			`has_agreed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `transaction_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/****** `transactions_entries` Table Clarifications ******
		`uni_id`				// Which user is responsible for listing this transaction entry.
		
		`class`					// The class (or plugin) to run for this transaction entry.
		`process_method`		// The method that will be used to run this transaction entry.
		`process_parameters`	// A JSON encoded array to use as the parameters for this transaction.
		
		`display`				// A JSON encoded array to pass information on how to display the transaction.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions_entries`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`transaction_id`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`class`					varchar(24)					NOT NULL	DEFAULT '',
			`process_method`		varchar(32)					NOT NULL	DEFAULT '',
			`process_parameters`	varchar(255)				NOT NULL	DEFAULT '',
			
			`display`				text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			INDEX (`transaction_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("transactions", array("id", "title"));
		$pass2 = DatabaseAdmin::columnsExist("transactions_users", array("uni_id", "transaction_id"));
		$pass3 = DatabaseAdmin::columnsExist("transactions_entries", array("uni_id", "class"));
		
		return ($pass1 and $pass2 and $pass3);
	}
	
}