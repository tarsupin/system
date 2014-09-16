<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class notifications_global_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Global Notifications";		// <str> The title for this table.
	public $description = "A list of user notifications.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "notifications_global";	// <str> The name of the table.
	public $fieldIndex = array("id");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 6;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 6;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 6;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		/*
			Global Notifictions aren't provided to the user until they log in (that way inactive users don't get them).
			Use the user's last login time to check what notifications to receive.
			
			Everyone's notifications are set to 2 when a global notification is posted (rather than 1)
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `notifications_global`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`message`				varchar(150)				NOT NULL	DEFAULT '',
			`url`					varchar(64)					NOT NULL	DEFAULT '',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`date_created`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
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
		
		$define->set("id")->title("ID")->description("The ID of the global notification.")->isUnique()->isReadonly()->fieldType("number");
		$define->set("message")->description("The message of the global notification.")->fieldType("text");
		$define->set("url")->description("The URL to visit if the notification is clicked.")->fieldType("url");
		$define->set("date_created")->description("The timestamp that the notification was created on.")->fieldType("timestamp");
		
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
				$schema->addFields("id", "message", "url", "date_created");
				$schema->sort("id", "desc");
				break;
				
			case "search":
				$schema->addFields("id", "message", "url", "date_created");
				break;
				
			case "create":
				$schema->addFields("message", "url", "date_created");
				break;
				
			case "edit":
				$schema->addFields("id", "message", "url", "date_created");
				break;
		}
		
		return $schema;
	}
	
}