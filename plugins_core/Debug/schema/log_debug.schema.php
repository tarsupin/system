<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class log_debug_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Debug Logs";		// <str> The title for this table.
	public $description = "Stores any debugging information tracked in the system.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "log_debug";		// <str> The name of the table.
	public $fieldIndex = array("uni_id", "date_logged");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = true;				// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 11;		// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Build the schema for the table ******/
	public function buildSchema (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schema->buildSchema();
	{
		Database::startTransaction();
		
		// Create Schmea
		$define = new SchemaDefine($this->tableKey, true);
		
		$define->set("uni_id")->title("UniFaction User ID")->description("The user's UniID, or UniFaction ID number.");
		$define->set("date_logged")->description("The timestamp of when this debug value was logged.")->fieldType("timestamp");
		$define->set("function_call")->description("The function that was called for this debug value.")->fieldType("variable");
		$define->set("file_path")->description("The file path where this debug value was caught.");
		$define->set("file_line")->description("The line number of the file where this debug value was caught.");
		$define->set("url_path")->description("The url that was accessed when this debug value was caught.");
		$define->set("content")->description("Additional information tracked with this debug value.");
		
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
				$schema->addFields("uni_id", "date_logged", "function_call", "file_path", "file_line", "url_path", "content");
				$schema->sort("date_logged", "desc");
				break;
				
			case "search":
				$schema->addFields("uni_id", "date_logged", "function_call", "file_path", "url_path", "content");
				break;
				
			case "create":
			case "edit":
				break;
		}
		
		return $schema;
	}
	
}