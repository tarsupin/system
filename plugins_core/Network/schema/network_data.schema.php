<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class network_data_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Network Connections";		// <str> The title for this table.
	public $description = "Identifies the connections between this site and others.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "network_data";		// <str> The name of the table.
	public $fieldIndex = array("site_handle");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;				// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 7;			// <int> The clearance level required to view this table.
	public $permissionSearch = 7;		// <int> The clearance level required to search this table.
	public $permissionCreate = 9;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 9;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 9;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `network_data`
		(
			`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
			`site_name`				varchar(48)					NOT NULL	DEFAULT '',
			`site_clearance`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`site_url`				varchar(48)					NOT NULL	DEFAULT '',
			`site_key`				varchar(100)				NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`site_handle`)
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
		
		$define->set("site_handle")->description("The site handle of the site connecting to this one.")->fieldType("variable");
		$define->set("site_name")->description("The name of the site.");
		$define->set("site_clearance")->title("Clearance")->description("The level of clearance the site is provided.")->pullType("select", "site-clearance");
		$define->set("site_url")->title("URL")->description("The URL to the site.")->fieldType("url");
		$define->set("site_key")->title("API Key")->description("The shared API key between this site and ours.");
		
		// Create Selection Options
		SchemaDefine::addSelectOption("site-clearance", 9, "9: UniFaction Auth Site Only");
		SchemaDefine::addSelectOption("site-clearance", 8, "8: UniFaction Core Sites");
		SchemaDefine::addSelectOption("site-clearance", 7, "7: Official Site");
		SchemaDefine::addSelectOption("site-clearance", 6, "6: Trusted Site");
		SchemaDefine::addSelectOption("site-clearance", 0, "0: Standard Permissions");
		
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
				
				$schema->addFields("site_handle", "site_name", "site_clearance", "site_url");
				
				if(Me::$clearance > $this->permissionEdit)
				{
					$schema->addFields("site_key");
				}
				
				$schema->sort("site_handle");
				
				break;
				
			case "search":
				$schema->addFields("site_handle", "site_name", "site_clearance", "site_url");
				break;
				
			case "create":
				$schema->addFields("site_handle", "site_name", "site_clearance", "site_url", "site_key");
				break;
				
			case "edit":
				$schema->addFields("site_handle", "site_name", "site_clearance", "site_url", "site_key");
				break;
		}
		
		return $schema;
	}
	
}