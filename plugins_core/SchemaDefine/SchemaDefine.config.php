<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SchemaDefine_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "SchemaDefine";
	public $title = "Schema Building System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows users to create, update, and work with their own tables and forms.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `schema_fields`
		(
			`table_key`				varchar(22)					NOT NULL	DEFAULT '',
			`field_key`				varchar(22)					NOT NULL	DEFAULT '',
			
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			`description`			varchar(250)				NOT NULL	DEFAULT '',
			
			`field_type`			varchar(12)					NOT NULL	DEFAULT '',
			`pull_type`				varchar(16)					NOT NULL	DEFAULT '',
			`pull_from`				varchar(22)					NOT NULL	DEFAULT '',
			
			`extra_chars`			varchar(22)					NOT NULL	DEFAULT '',
			`min_value`				int(10)						NOT NULL	DEFAULT '0',
			`max_value`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`decimals`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`is_unique`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`is_readonly`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`default_value`			varchar(32)					NOT NULL	DEFAULT '',
			`special_instructions`	text						NOT NULL	DEFAULT '',
			
			UNIQUE (`table_key`, `field_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `schema_selections`
		(
			`selection_name`		varchar(22)					NOT NULL	DEFAULT '',
			`arg_key`				varchar(72)					NOT NULL	DEFAULT '',
			`arg_value`				varchar(72)					NOT NULL	DEFAULT '',
			
			UNIQUE (`selection_name`, `arg_key`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::startTransaction();
		
		// Boolean Options
		SchemaDefine::addSelectOption("boolean", 0, "False");
		SchemaDefine::addSelectOption("boolean", 1, "True");
		
		// Agreement (Yes or No)
		SchemaDefine::addSelectOption("yes-no", 0, "No");
		SchemaDefine::addSelectOption("yes-no", 1, "Yes");
		
		// Priority Level
		SchemaDefine::addSelectOption("priority", 9, "9: Emergency / Top Priority");
		SchemaDefine::addSelectOption("priority", 8, "8: Urgent Priority");
		SchemaDefine::addSelectOption("priority", 7, "7: Critical Priority");
		SchemaDefine::addSelectOption("priority", 6, "6: Very High Priority");
		SchemaDefine::addSelectOption("priority", 5, "5: High Priority");
		SchemaDefine::addSelectOption("priority", 4, "4: Moderate Priority");
		SchemaDefine::addSelectOption("priority", 3, "3: Average Priority");
		SchemaDefine::addSelectOption("priority", 2, "2: Low Priority");
		SchemaDefine::addSelectOption("priority", 1, "1: Very Low Priority");
		SchemaDefine::addSelectOption("priority", 0, "0: Not a Priority");
		
		Database::endTransaction();
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass = DatabaseAdmin::columnsExist("schema_fields", array("table_key", "field_key", "title", "description", "field_type"));
		$pass2 = DatabaseAdmin::columnsExist("schema_selections", array("selection_name", "arg_key", "arg_value"));
		
		return ($pass and $pass2);
	}
	
}