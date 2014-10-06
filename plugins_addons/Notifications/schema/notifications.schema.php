<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class notifications_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Notifications";		// <str> The title for this table.
	public $description = "A list of user notifications.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "notifications";	// <str> The name of the table.
	public $fieldIndex = array("uni_id", "category", "date_created");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = true;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 6;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 6;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 6;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Build the schema for the table ******/
	public function buildSchema (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schema->buildSchema();
	{
		Database::startTransaction();
		
		// Create Schmea
		$define = new SchemaDefine($this->tableKey, true);
		
		$define->set("uni_id")->title("Uni ID")->description("The UniFaction User ID.")->isReadonly()->fieldType("number");
		$define->set("sender_id")->description("The user responsible for sending the notification (0 is the server).")->isReadonly()->fieldType("number");
		$define->set("note_type")->description("The notification type.")->fieldType("input");
		$define->set("message")->description("The message of the notification.")->fieldType("text");
		$define->set("url")->description("The URL to visit if the notification is clicked.")->fieldType("url");
		$define->set("sync_unifaction")->title("Sync to UniFaction")->description("Whether or not this notification is to be synced with UniFaction.")->fieldType("boolean")->pullType("select", "boolean");
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
				$schema->addFields("uni_id", "note_type", "message", "url", "sender_id", "date_created", "sync_unifaction");
				$schema->sort("uni_id");
				$schema->sort("note_type");
				$schema->sort("date_created");
				break;
				
			case "search":
				$schema->addFields("uni_id", "sender_id", "note_type", "message", "url", "date_created", "sync_unifaction");
				break;
				
			case "create":
				$schema->addFields("uni_id", "sender_id", "note_type", "message", "url", "date_created", "sync_unifaction");
				break;
				
			case "edit":
				$schema->addFields("uni_id", "sender_id", "note_type", "message", "url", "date_created");
				break;
		}
		
		return $schema;
	}
	
}