<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class widget_loader_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Widget Loader";		// <str> The title for this table.
	public $description = "Allows widget loading from the database and control panel.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "widget_loader";			// <str> The name of the table.
	public $fieldIndex = array("container", "widget");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 9;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		/*
			This table does not get edited primarily by this loader, but rather by the other widgets that make use of
			it. Widgets will provide instructions (and editing) with their own configurations.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `widget_loader`
		(
			`container`				varchar(22)					NOT NULL	DEFAULT '',
			`widget`				varchar(22)					NOT NULL	DEFAULT '',
			`sort_order`			tinyint(2)		unsigned	NOT NULL	DEFAULT '99',
			
			`instructions`			text						NOT NULL	DEFAULT '',
			
			UNIQUE (`container`, `widget`)
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
		
		$define->set("container")->description("The container that the widget is stored in");
		$define->set("widget")->description("The widget object to load.");
		$define->set("sort_order")->description("The order to sort this widget in.");
		$define->set("instructions")->title("Constructor Parameters")->description("The parameters that will be used to build the widget on initialization.");
		
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
				$schema->addFields("container", "widget", "sort_order", "instructions");
				$schema->sort("container");
				$schema->sort("widget");
				break;
				
			case "search":
				$schema->addFields("container", "widget", "sort_order", "instructions");
				break;
				
			case "create":
				break;
				
			case "edit":
				$schema->addFields("container", "widget", "sort_order", "instructions");
				break;
		}
		
		return $schema;
	}
	
}