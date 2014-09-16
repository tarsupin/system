<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class api_tracker_schema {
	
	
/****** Plugin Variables ******/
	public $title = "API Tracker";		// <str> The title for this table.
	public $description = "Tracks the sites using the site APIs and the number of times accessed.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "api_tracker";	// <str> The name of the table.
	public $fieldIndex = array("site_handle", "cycle", "api_name");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 5;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 9;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 8;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 9;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		// Add the Table
		Database::exec("
		CREATE TABLE IF NOT EXISTS `api_tracker`
		(
			`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
			`cycle`					mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`api_name`				varchar(22)					NOT NULL	DEFAULT '',
			`times_accessed`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`site_handle`, `cycle`, `api_name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(site_handle) PARTITIONS 7;
		");
		
		return DatabaseAdmin::tableExists($this->tableKey);
	}
	
	
/****** Build the schema for the table ******/
	public function buildSchema (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schema->buildSchema();
	{
		Database::startTransaction();
		
		// Create Schmea
		$define = new SchemaDefine($this->tableKey, true);
		
		$define->set("site_handle")->title("Site Handle")->description("The site handle that accessed the API.")->isReadonly();
		$define->set("cycle")->title("Accessed In")->description("The month cycle that this was accessed during.")->pullType("method", "cycle");
		$define->set("api_name")->title("Name of API")->description("The API that the site connected to.")->fieldType("variable");
		$define->set("times_accessed")->description("The number of times this API was accessed by the site during the cycle.");
		
		return Database::endTransaction();
	}
	
	
/****** Set the rules for interacting with this table ******/
	public function __call
	(
		$name		// <str> The name of the method being called ("view", "search", "create", "delete")
	,	$args		// <mixed> The args sent with the function call (generaly the schema object)
	)				// RETURNS <mixed> The resulting schema object.
	
	// $schema->view($schema);		// Set the "view" options
	// $schema->search($schema);	// Set the "search" options
	{
		// Make sure that the appropriate schema object was sent
		if(!isset($args[0])) { return; }
		
		// Set the schema object
		$schema = $args[0];
		
		switch($name)
		{
			case "view":
				$schema->addFields("site_handle", "cycle", "api_name", "times_accessed");
				break;
				
			case "search":
				$schema->addFields("site_handle", "cycle", "api_name");
				break;
				
			case "create":
				$schema->addFields("site_handle", "cycle", "api_name", "times_accessed");
				break;
				
			case "edit":
				$schema->addFields("site_handle", "cycle", "api_name", "times_accessed");
				break;
		}
		
		return $schema;
	}
	
	
/****** The "FORM" pull method for the "cycle" field ******/
	public static function pullMethodForm_cycle
	(
		$postVal	// <int> The POST value that is currently assigned.
	)				// RETURNS <str>
	
	// $schema->pullMethod_cycle($type, $postVal);
	{
		$prepare = array();
		
		// Get values around the current date
		$year = date("Y") - 1;
		$month = date("m");
		
		for($a = 0;$a < 24;$a++)
		{
			$date = DateTime::createFromFormat("Ym", $year . $month);
			$prepare[$date->format('Ym')] = $date->format("F Y");
			$month++;
		}
		
		// Get values around the current setting
		$postVal = max((int) date("Ym"), $postVal);
		
		$year = substr($postVal, 0, 4);
		$month = substr($postVal, 4) - 5;
		
		for($a = 0;$a < 10;$a++)
		{
			$date = DateTime::createFromFormat("Ym", $year . $month);
			$prepare[$date->format('Ym')] = $date->format("F Y");
			$month++;
		}
		
		ksort($prepare);
		
		return $prepare;
	}
	
	
/****** The "VIEW" pull method for the "cycle" field ******/
	public static function pullMethodView_cycle
	(
		$postVal	// <int> The POST value that is currently assigned.
	)				// RETURNS <str>
	
	// $schema->pullMethod_cycle($type, $postVal);
	{
		$date = DateTime::createFromFormat("Ym", $postVal);
		
		return $date->format("F Y");
	}
	
}