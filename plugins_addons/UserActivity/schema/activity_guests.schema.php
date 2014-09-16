<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class activity_guests_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Guest Activity";		// <str> The title for this table.
	public $description = "Tracks the guest activity on the site.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "activity_guests";			// <str> The name of the table.
	public $fieldIndex = array("guest_ip");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = true;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 8;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `activity_guests` (
			
			`guest_ip`				varchar(45)					NOT NULL	DEFAULT '',
			`date_lastVisit`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`guest_ip`),
			INDEX (`date_lastVisit`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
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
		
		$define->set("guest_ip")->title("Guest IP")->description("The IP of the useer.")->isUnique()->isReadonly();
		$define->set("date_lastVisit")->title("Last Visit Time")->description("The timestamp of the last visit from this guest.")->fieldType("timestamp");
		
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
				$schema->addFields("guest_ip", "date_lastVisit");
				$schema->sort("guest_ip");
				break;
				
			case "search":
				$schema->addFields("guest_ip", "date_lastVisit");
				break;
				
			case "create":
				break;
				
			case "edit":
				$schema->addFields("guest_ip", "date_lastVisit");
				break;
		}
		
		return $schema;
	}
	
}