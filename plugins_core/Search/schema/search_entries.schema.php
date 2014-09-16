<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class search_entries_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Search Entries";		// <str> The title for this table.
	public $description = "Stores a list of search entries (auto-dropdown options) on your site.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "search_entries";		// <str> The name of the table.
	public $fieldIndex = array("entry_id");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 7;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 7;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 7;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `search_entries`
		(
			`entry_id`				int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`entry`					varchar(72)					NOT NULL	DEFAULT '',
			`extra_keywords`		varchar(150)				NOT NULL	DEFAULT '',
			
			`url_path`				varchar(72)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`entry_id`),
			FULLTEXT (`entry`, `extra_keywords`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8
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
		
		$define->set("entry_id")->title("Entry ID")->description("Entry ID")->isUnique()->isReadonly();
		$define->set("entry")->description("The search entry; the title of the dropdown.")->isUnique();
		$define->set("extra_keywords")->title("Additional Keywords")->description("Any additional keywords that apply to this entry.");
		$define->set("url_path")->title("URL")->description("The URL to visit when the search option is clicked.");
		
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
				$schema->addFields("entry_id", "entry", "extra_keywords", "url_path");
				$schema->sort("entry");
				break;
				
			case "search":
				$schema->addFields("entry_id", "entry", "extra_keywords", "url_path");
				break;
				
			case "create":
				$schema->addFields("entry", "extra_keywords", "url_path");
				break;
				
			case "edit":
				$schema->addFields("entry_id", "entry", "extra_keywords", "url_path");
				break;
		}
		
		return $schema;
	}
	
}