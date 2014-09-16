<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class log_benchmark_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Benchmark Logs";		// <str> The title for this table.
	public $description = "Stores any benchmarks processed in the system.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "log_benchmark";		// <str> The name of the table.
	public $fieldIndex = array("benchmark_cycle", "file_call");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = true;				// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 5;			// <int> The clearance level required to view this table.
	public $permissionSearch = 5;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 11;		// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 7;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_benchmark`
		(
			`benchmark_cycle`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`key_group`				varchar(22)					NOT NULL	DEFAULT '',
			`key_subgroup`			varchar(22)					NOT NULL	DEFAULT '',
			`key_name`				varchar(22)					NOT NULL	DEFAULT '',
			
			`date_logged`			int(10)						NOT NULL	DEFAULT '0',
			
			`file_call`				varchar(32)					NOT NULL	DEFAULT '',
			`benchmark`				float(8,6)					NOT NULL	DEFAULT '0',
			
			UNIQUE (`benchmark_cycle`, `file_call`),
			INDEX (`key_group`, `key_subgroup`, `key_name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
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
		
		$define->set("benchmark_cycle")->description("The cycle number associated with the benchmark.");
		$define->set("key_group")->title("Group")->description("The benchmark group, which helps to categorize the logs.");
		$define->set("key_subgroup")->title("Subgroup")->description("The subgroup to filter categorization.");
		$define->set("key_name")->title("Name")->description("The name of the benchmark run.");
		$define->set("date_logged")->description("The timestamp that the benchmark was run.");
		$define->set("file_call")->description("The file that was called with this benchmark.");
		$define->set("benchmark")->description("The amount of time that passed since the last benchmark.");
		
		Database::endTransaction();
		
		return true;
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
				$schema->addFields("benchmark_cycle", "key_group", "key_subgroup", "key_name", "file_call", "benchmark", "date_logged");
				$schema->sort("key_group", "asc");
				$schema->sort("key_subgroup", "asc");
				$schema->sort("key_name", "asc");
				break;
				
			case "search":
				$schema->addFields("benchmark_cycle", "key_group", "key_subgroup", "key_name", "file_call", "benchmark");
				break;
				
			case "create":
			case "edit":
				break;
		}
		
		return $schema;
	}
	
}