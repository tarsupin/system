<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class log_email_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Email Records";		// <str> The title for this table.
	public $description = "Keeps track of emails that were processed.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "log_email";		// <str> The name of the table.
	public $fieldIndex = array("id");	// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 6;			// <int> The clearance level required to view this table.
	public $permissionSearch = 6;		// <int> The clearance level required to search this table.
	public $permissionCreate = 11;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 11;		// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_email`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`recipient`				varchar(72)					NOT NULL	DEFAULT '',
			`subject`				varchar(42)					NOT NULL	DEFAULT '',
			`message`				text						NOT NULL	DEFAULT '',
			
			`details`				text						NOT NULL	DEFAULT '',
			
			`date_sent`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`id`) PARTITIONS 5;
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
		
		$define->set("id")->title("Email ID")->description("The ID of the email.");
		$define->set("recipient")->description("The receipient of the email.")->fieldType("variable")->extraChars("@.+-");
		$define->set("subject")->description("The subject of the email.");
		$define->set("message")->description("The email message.");
		$define->set("details")->description("Extra details, such as if there are multiple recipients.");
		$define->set("date_sent")->description("The timestamp that the email was sent.");
		
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
				$schema->addFields("id", "recipient", "subject", "message", "details", "date_sent");
				$schema->sort("id", "desc");
				break;
				
			case "search":
				$schema->addFields("id", "recipient", "subject", "message", "date_sent");
				break;
				
			case "create":
			case "edit":
				break;
		}
		
		return $schema;
	}
	
}