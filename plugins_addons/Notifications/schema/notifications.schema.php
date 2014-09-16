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
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		/*
			`sender_id`			the uni_id that was responsible for sending the notification (usually 0, for server)
			`category`			the category that a notification fits into
			`url`				the url to go to if the notification is clicked
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `notifications`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`sender_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`category`				varchar(22)					NOT NULL	DEFAULT '',
			
			`message`				varchar(150)				NOT NULL	DEFAULT '',
			`url`					varchar(64)					NOT NULL	DEFAULT '',
			
			`sync_unifaction`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`, `category`, `date_created`),
			INDEX (`date_created`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 13;
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
		
		$define->set("uni_id")->title("Uni ID")->description("The UniFaction User ID.")->isReadonly()->fieldType("number");
		$define->set("sender_id")->description("The user responsible for sending the notification (0 is the server).")->isReadonly()->fieldType("number");
		$define->set("category")->description("The notification category.")->fieldType("input");
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
				$schema->addFields("uni_id", "category", "message", "url", "sender_id", "date_created", "sync_unifaction");
				$schema->sort("uni_id");
				$schema->sort("category");
				$schema->sort("date_created");
				break;
				
			case "search":
				$schema->addFields("uni_id", "sender_id", "category", "message", "url", "date_created", "sync_unifaction");
				break;
				
			case "create":
				$schema->addFields("uni_id", "sender_id", "category", "message", "url", "date_created", "sync_unifaction");
				break;
				
			case "edit":
				$schema->addFields("uni_id", "sender_id", "category", "message", "url", "date_created");
				break;
		}
		
		return $schema;
	}
	
}