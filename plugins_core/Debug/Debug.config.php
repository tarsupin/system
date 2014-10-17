<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Debug_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Debug";
	public $title = "Debugging System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the webmaster with debugging tools.";
	
	public $data = array();
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_errors`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`date_logged`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`importance`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`error_type`			varchar(12)					NOT NULL	DEFAULT '',
			
			`class`					varchar(22)					NOT NULL	DEFAULT '',
			`function`				varchar(32)					NOT NULL	DEFAULT '',
			`arg_string`			varchar(200)				NOT NULL	DEFAULT '',
			
			`file_path`				varchar(32)					NOT NULL	DEFAULT '',
			`file_line`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`url`					varchar(200)				NOT NULL	DEFAULT '',
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`date_logged`),
			INDEX (`class`, `function`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_debug` (
			
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_logged`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`function_call`			varchar(100)				NOT NULL	DEFAULT '',
			`file_path`				varchar(32)					NOT NULL	DEFAULT '',
			`file_line`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`url_path`				varchar(64)					NOT NULL	DEFAULT '',
			
			`content`				text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			INDEX (`uni_id`, `date_logged`),
			INDEX (`date_logged`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
		
		return self::isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $plugin->isInstalled();
	{
		$pass1 = DatabaseAdmin::tableExists("log_errors");
		$pass2 = DatabaseAdmin::tableExists("log_debug");
		
		return ($pass1 and $pass2);
	}
	
}