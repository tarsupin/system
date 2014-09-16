<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class users_schema {
	
	
/****** Plugin Variables ******/
	public $title = "User List";		// <str> The title for this table.
	public $description = "Contains the list of UniFaction users that are registered on the site.";		// <str> The description of this table.
	
	// Table Settings
	public $tableKey = "users";			// <str> The name of the table.
	public $fieldIndex = array("uni_id");		// <int:str> The field(s) used for the index (for editing, deleting, row ID, etc).
	public $autoDelete = false;			// <bool> TRUE will delete rows instantly, FALSE will require confirmation.
	
	// Permissions
	// Note: Set a permission value to 11 or higher to disallow it completely.
	public $permissionView = 5;			// <int> The clearance level required to view this table.
	public $permissionSearch = 5;		// <int> The clearance level required to search this table.
	public $permissionCreate = 6;		// <int> The clearance level required to create an entry on this table.
	public $permissionEdit = 6;			// <int> The clearance level required to edit an entry on this table.
	public $permissionDelete = 8;		// <int> The clearance level required to delete an entry on this table.
	
	
/****** Install the table ******/
	public function install (
	)			// RETURNS <bool> TRUE if the installation was success, FALSE if not.
	
	// $schema->install();
	{
		// Add the Table
		Database::exec("
		CREATE TABLE IF NOT EXISTS `users`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`role`					varchar(12)					NOT NULL	DEFAULT '',
			`clearance`				tinyint(1)					NOT NULL	DEFAULT '0',
			
			`handle`				varchar(22)					NOT NULL	DEFAULT '',
			`display_name`			varchar(22)					NOT NULL	DEFAULT '',
			
			`timezone`				varchar(22)					NOT NULL	DEFAULT '',
			
			`date_joined`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_lastLogin`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`has_instructions`		tinyint(1)					NOT NULL	DEFAULT '0',
			`has_notifications`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`auth_token`			varchar(22)					NOT NULL	DEFAULT '',
			
			UNIQUE (`uni_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 7;
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
		
		$define->set("uni_id")->title("UniID")->description("UniFaction User ID")->fieldType("uni_id")->isUnique()->isReadonly();
		$define->set("role")->description("User's role on the site; e.g. user, mod, staff, admin, etc.")->pullType("select", "users-role");
		$define->set("clearance")->title("Clearance Level")->description("The level of clearance the user has.")->pullType("select", "users-clearance");
		$define->set("handle")->title("User Handle")->description("The reference name + identification that points to the user.")->isUnique()->isReadonly()->fieldType("variable");
		$define->set("display_name")->description("The name that the user displays on this site.");
		$define->set("timezone")->description("The timezone that the user is in.");
		$define->set("date_joined")->description("The date that the user joined")->fieldType("timestamp");
		$define->set("date_lastLogin")->title("Last Login")->description("The last time the user logged in.")->fieldType("timestamp");
		$define->set("has_instructions")->description("Indicates if the user has instructions to follow or not.")->pullType("select", "yes-no")->isBoolean();
		$define->set("has_notifications")->description("How many notifications the user has waiting.");
		$define->set("auth_token")->description("A token associated with the user for security purposes.");
		
		// Create Selection Options
		SchemaDefine::addSelectOption("users-clearance", 9, "9: Super Admin (webmaster)");
		SchemaDefine::addSelectOption("users-clearance", 8, "8: Admin");
		SchemaDefine::addSelectOption("users-clearance", 7, "7: Management");
		SchemaDefine::addSelectOption("users-clearance", 6, "6: Staff with Moderator Clearance");
		SchemaDefine::addSelectOption("users-clearance", 5, "5: Staff");
		SchemaDefine::addSelectOption("users-clearance", 4, "4: Intern, Assistant");
		SchemaDefine::addSelectOption("users-clearance", 3, "3: VIP / Trusted User");
		SchemaDefine::addSelectOption("users-clearance", 2, "2: User");
		SchemaDefine::addSelectOption("users-clearance", 1, "1: Limited User / Probationary");
		SchemaDefine::addSelectOption("users-clearance", 0, "0: Guest");
		SchemaDefine::addSelectOption("users-clearance", -1, "-1: Silenced User");
		SchemaDefine::addSelectOption("users-clearance", -2, "-2: Restricted User");
		SchemaDefine::addSelectOption("users-clearance", -3, "-3: Temporarily Banned User");
		SchemaDefine::addSelectOption("users-clearance", -9, "-9: Permanently Banned User");
		
		SchemaDefine::addSelectOption("users-role", "guest", "Guest");
		SchemaDefine::addSelectOption("users-role", "user", "User");
		SchemaDefine::addSelectOption("users-role", "supporter", "Supporter");
		SchemaDefine::addSelectOption("users-role", "vip", "VIP");
		SchemaDefine::addSelectOption("users-role", "staff", "Staff");
		SchemaDefine::addSelectOption("users-role", "mod", "Mod");
		SchemaDefine::addSelectOption("users-role", "admin", "Admin");
		SchemaDefine::addSelectOption("users-role", "superadmin", "Super Admin");
		
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
				$schema->addFields("uni_id", "handle", "display_name", "clearance", "date_joined");
				$schema->sort("handle");
				break;
				
			case "search":
				$schema->addFields("uni_id", "handle", "display_name", "clearance", "date_joined");
				break;
				
			case "create":
				$schema->addFields("uni_id", "handle", "display_name", "role", "clearance");
				break;
				
			case "edit":
				$schema->addFields("uni_id", "handle", "display_name", "role", "clearance");
				break;
		}
		
		return $schema;
	}
	
}