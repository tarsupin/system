<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class navigation_loader_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Navigation Loader";		// <str> The title for this table.
	public $description = "Allows links to be loaded into navigation sections from the database.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "navigation_loader";			// <str> The name of the table.
	public $fieldIndex = array("group", "sort_order", "title");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 8;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 8;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `navigation_loader`
		(
			`nav_group`				varchar(22)					NOT NULL	DEFAULT '',
			
			`title`					varchar(22)					NOT NULL	DEFAULT '',
			`url`					varchar(22)					NOT NULL	DEFAULT '',
			`class`					varchar(22)					NOT NULL	DEFAULT '',
			
			`sort_order`			tinyint(2)		unsigned	NOT NULL	DEFAULT '99',
			
			INDEX (`nav_group`, `sort_order`)
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
		
		$define->set("nav_group")->title("Group")->description("The navigation group to categorize links into.");
		$define->set("title")->description("The title of the link.");
		$define->set("url")->title("URL")->description("The URL to visit when the link is clicked.");
		$define->set("class")->title("CSS Class")->description("The CSS Class to associate with the link.");
		$define->set("sort_order")->description("The order to sort this link in.");
		
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
				$schema->addFields("nav_group", "title", "url", "class", "sort_order");
				$schema->sort("nav_group");
				$schema->sort("sort_order");
				break;
				
			case "search":
				$schema->addFields("nav_group", "title", "url");
				break;
				
			case "create":
				$schema->addFields("nav_group", "title", "url", "class", "sort_order");
				break;
				
			case "edit":
				$schema->addFields("nav_group", "title", "url", "class", "sort_order");
				break;
		}
		
		return $schema;
	}
	
}