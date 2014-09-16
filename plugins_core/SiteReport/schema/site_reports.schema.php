<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class site_reports_schema {
	
	
/****** Plugin Variables ******/
	public $title = "Site Reports";		// <str> The title for this table.
	public $description = "User submitted reports.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "site_reports";		// <str> The name of the table.
	public $fieldIndex = array("id");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;				// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 5;			// <int> The clearance level required to view this table.
	public $permissionSearch = 5;		// <int> The clearance level required to search this table.
	public $permissionCreate = 6;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 6;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 6;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		/*
			submitter_id		// the user that submitted this report
			mod_id				// the ID of the mod handling this report
			uni_id				// the uniID being targeted (if relevant)
			
			importance_level	// the level of importance of this report (0 = still open)
			
			action				// the action of this report, e.g. "Stickied Thread #1029"
			url					// the url to link to
			details				// any details that need to be added to the report
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `site_reports`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`submitter_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`mod_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`importance_level`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`action`				varchar(32)					NOT NULL	DEFAULT '',
			`url`					varchar(128)				NOT NULL	DEFAULT '',
			`details`				text						NOT NULL	DEFAULT '',
			
			`timestamp`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
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
		
		$define->set("id")->title("Report ID")->description("The ID of the report.");
		$define->set("submitter_id")->title("Creator")->description("The user who submitted the report.")->pullType("database", array("table" => "users", "key" => "uni_id", "value" => "handle"));
		$define->set("mod_id")->title("Mod Handler")->description("The moderator that is handling this report.")->pullType("database", array("table" => "users", "key" => "uni_id", "value" => "handle"));
		$define->set("uni_id")->title("Related User")->description("The user that is relevant to this report, such as the user being flagged.")->pullType("database", array("table" => "users", "key" => "uni_id", "value" => "handle"));
		$define->set("importance_level")->description("The level of importance of this site report.")->pullType("select", "priority");
		$define->set("action")->description("The action associated with this report, or title of the report.");
		$define->set("url")->description("The URL associated with this report.")->fieldType("url");
		$define->set("details")->description("The details associated with this report.");
		
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
				$schema->addFields("id", "action", "mod_id", "uni_id", "importance_level", "url", "details", "submitter_id");
				$schema->sort("id", "desc");
				break;
				
			case "search":
				$schema->addFields("id", "action", "mod_id", "submitter_id", "uni_id", "importance_level", "url", "details");
				break;
				
			case "create":
				$schema->addFields("id", "action", "mod_id", "uni_id", "importance_level", "url", "details", "submitter_id");
				break;
				
			case "edit":
				$schema->addFields("id", "action", "mod_id", "uni_id", "importance_level", "url", "details", "submitter_id");
				break;
		}
		
		return $schema;
	}
	
}