<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the SchemaDefine Plugin ------
-------------------------------------------

Schemas are a critical part of the system. They describe how the database tables for a plugin are meant to be interpreted and used. This allows administrative functions such as search and editing to interact with the database table much more effectively.

Schemas perform the following essential tasks:

	1. Schemas allow administrators to view database tables with pre-generated data that is relevant to them.
	
	2. Schemas allow filtering and sorting of information in the database.
	
	3. Schemas allow admins to search for important data for the plugin and it's database tables.
	
	4. Schemas allow admins to safely edit and delete data that it has described as being allowed.
	
	5. Schemas set the level of clearances required to make the appropriate changes to the table.
	
	
Basically, Schemas are designed to allow the admins of the site to interact with plugin data in an intelligent way (view, search, edit, etc) and only with the data that they need to interact with.

For example, the "users" table in the database has a column called "clearance", which is a numerical value from -9 to 9. However, those numbers don't mean anything to a typical administrator. Therefore, the schema defines "clearance" by using a dropdown menu that describes what each of the value means.

For example:
	
	"-3" translates to "Temporary Ban", which means the user would be unable to access the site right now.
	"0" translates to "Guest", which has the privileges of a guest.
	"3" translates to "Trusted User", which has the privileges of a trusted user.
	"5" translates to "Staff", which has staff privileges.
	
You can also use schemas to define fields as being read-only, which prevents them from being edited. You can set them as unique to indicate that the field cannot be recreated a second time. You can filter and sort certain tables by default with the viewing mechanism, or allow certain search functionality to exist.
	
Another important purpose of the schemas is to allow / disallow functionality in each table.

For example, it might be important for an admin to edit certain fields on a user table, such as their display name (such to change it from an inappropriate or offensive title to an appropriate one). However, deleting an entire user row could leave lots of missing data in other areas of the database. Since that would be a problem, the schema can define a table as not being possible to delete rows in.


-------------------------
------ Field Types ------
-------------------------

Field types and pull types define the style of field that you interface with in forms and tables. For example, a dropdown of clearance levels would be a "number" field type and an array("select" => "clearanceLevel") pull type. By defining these values, you will be able to interact with your schema in a practical way.

Field Types include:
	"number"			// A numerical value.
	"input"				// A string - a set of characters, generally for titles or short messages.
	"text"				// A long block of text, such as to store chunks of HTML.

------------------------
------ Pull Types ------
------------------------

Pull types are more complicated and also include a "Pull From" field. These values work together to determine how a field is interpreted.

A selection dropdown is the most common form of pull type, with the "pull from" field pointing to which dropdown type it should retrieve it's information from. The selection pull type means that you have a few options that are allowed for this field. For example, the "role" of a user may be limited to "guest", "user", "mod", "staff", and "admin". These five values can be saved under the "role" selection. When called, they will be used as the options for the selection.

To use this, you'll need to set:

	// "Pull Type" is set to "select"
	// "Pull From" is set to "role"
	$this->pullType("select", "role");
	
Then you'll need to create the "role" selection options, like this:

	SchemaDefine::addSelectOption("role", "guest", "Guest Account");
	SchemaDefine::addSelectOption("role", "user", "Registered User");
	SchemaDefine::addSelectOption("role", "mod", "Moderator");
	SchemaDefine::addSelectOption("role", "staff", "Staff Member");
	SchemaDefine::addSelectOption("role", "admin", "Administrator");
	
Now, when the field is viewed, it will provide the administrator with the options available in the list, and insert them into the database properly.

If the "role" selection type is empty, it will be an empty dropdown that appears when trying to edit that field. Therefore, you have to populate the entry using the method described above.


-----------------------------------------
------ Example of defining a table ------
-----------------------------------------



-------------------------------
------ Methods Available ------
-------------------------------

SchemaDefine::flush($table);									// Flush a schema table
SchemaDefine::addSelectOption($selectName, $key, $title);		// Add a selection option


*/

class SchemaDefine {
	
	
/****** Plugin Variables ******/
	public string $table = "";			// <str> The table that we're accessing for this schema call.
	public string $field = "";			// <str> The field that we're accessing for this schema call.
	
	// Numerical Maxes
	const MAX_TINYINT = 127;
	const MAX_SMALLINT = 32767;
	const MAX_MEDIUMINT = 8388607;
	const MAX_INT = 2147483647;
	const MAX_BIGINT = 9223372036854775807;
	
	
/****** Construct the schema define class ******/
	public function __construct
	(
		string $table			// <str> The table whose schema you are defining.
	,	bool $reset = false	// <bool> Flushes the entire schema of the table you're defining.
	): void					// RETURNS <void>
	
	// $field = new SchemaDefine($table);
	{
		// Sanitize Values
		$this->table = Sanitize::variable($table);
		
		// We need the admin privileges to this database to function properly
		if(!Database::$rootActive)
		{
			Database::initRoot();
			
			if(!Database::$rootActive) { return false; }
		}
		
		// Flush the schema for this table, if instructed to
		if($reset)
		{
			self::flush($table);
		}
	}
	
	
/****** Construct the schema define class ******/
	public function set
	(
		string $field		// <str> The field to define.
	)				// RETURNS <this>
	
	// $field = new SchemaDefine($table, $field);
	{
		// Sanitize Values
		$this->field = Sanitize::variable($field);
		
		// Grab the column data and ensure it exists, otherwise no need to create it
		if(!$columnData = DatabaseAdmin::getColumnData($this->table, $this->field))
		{
			return $this;
		}
		
		// If the field doesn't exist in the schema, use the defaults from that table to guesstimate it
		if(!Database::selectValue("SELECT field_key FROM schema_fields WHERE table_key=? AND field_key=? LIMIT 1", array($this->table, $this->field)))
		{
			// Prepare Values
			$setValues = array(
				"field_type"	=> ""
			,	"min_value"		=> 0
			,	"max_value"		=> 0
			,	"decimals"		=> 0
			,	"default_value"	=> ""
			);
			
			// Data Type
			switch($columnData['DATA_TYPE'])
			{
				case "tinytext":
				case "varchar":
					$setValues['field_type'] = "input";
					break;
				
				case "tinyint":
				case "smallint":
				case "mediumint":
				case "int":
				case "bigint":
				case "double":
					$setValues['field_type'] = "number";
					break;
					
				case "float":
					$setValues['field_type'] = "number";
					$setValues['decimals'] = $columnData['NUMERIC_SCALE'];
					break;
					
				case "text":
				case "mediumtext":
				case "longtext":
					$setValues['field_type'] = "text";
					break;
			}
			
			// If the field type is a number
			if($setValues['field_type'] == "number")
			{
				$minMult = 1;
				$maxMult = 1;
				
				if(strpos($columnData['COLUMN_TYPE'], "unsigned"))
				{
					$minMult = 0;
					$maxMult = 2;
				}
				
				// Prepare the minimum and maximum values based on numeric precision
				$numAvailable = (int) $columnData['NUMERIC_PRECISION'] - (int) $columnData['NUMERIC_SCALE'];
				$setValues['max_value'] = (int) str_pad("", $numAvailable, "9");
				$setValues['min_value'] = (0 - $setValues['max_value']) * $minMult;
				
				// Adjust minimum and maximum by intentional imitation on column type
				$colCheck = str_replace(array($columnData['DATA_TYPE'], "(", ")"), array("", "", ""), $columnData['COLUMN_TYPE']);
				
				if(is_numeric($colCheck))
				{
					$colCheck = (int) $colCheck;
					
					$setValues['max_value'] = min($setValues['max_value'], (int) str_pad("", $colCheck, "9"));
					$setValues['min_value'] = max($setValues['min_value'], (0 - $setValues['max_value']) * $minMult);
				}
				
				// Prepare minimum and maximum values based on numeric limits and sign type
				switch($columnData['DATA_TYPE'])
				{
					case "tinyint":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_TINYINT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_TINYINT * $maxMult);
						break;
						
					case "smallint":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_SMALLINT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_SMALLINT * $maxMult);
						break;
						
					case "mediumint":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_MEDIUMINT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_MEDIUMINT * $maxMult);
						break;
					
					case "int":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_INT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_INT * $maxMult);
						break;
					
					case "bigint":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_BIGINT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_BIGINT * $maxMult);
						break;
					
					case "double":
						$setValues['min_value'] = max($setValues['min_value'], (0 - self::MAX_BIGINT) * $minMult);
						$setValues['max_value'] = min($setValues['max_value'], self::MAX_BIGINT * $maxMult);
						break;
				}
			}
			else
			{
				$setValues['max_value'] = $columnData['CHARACTER_MAXIMUM_LENGTH'];
			}
			
			// Other Default Settings
			$setValues['title'] = ucwords(str_replace(array("_", "-"), array(" ", " "), $this->field));
			$setValues['default_value'] = $columnData['COLUMN_DEFAULT'];
			
			Database::query("INSERT IGNORE INTO schema_fields (table_key, field_key, title, field_type, min_value, max_value, decimals, default_value) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($this->table, $this->field, $setValues['title'], $setValues['field_type'], $setValues['min_value'], $setValues['max_value'], $setValues['decimals'], $setValues['default_value']));
		}
		
		return $this;
	}
	
	
/****** Set values on the field schema ******/
	public function __call
	(
		string $name		// <str> The name of the method being called.
	,	array <int, mixed> $args		// <int:mixed> The arguments being called.
	)				// RETURNS <this>
	
	// $schemaDefine->title("My Title");
	{
		$value = isset($args[0]) ? $args[0] : "";
		
		switch($name)
		{
			case "title":
			case "description":
			case "decimals":
				return $this->callUpdate($name, $value);
			
			case "minValue":
				return $this->callUpdate("min_value", $value);
			
			case "maxValue":
				return $this->callUpdate("max_value", $value);
				
			case "default":
				return $this->callUpdate("default_value", $value);
				
			case "extraChars":
				return $this->callUpdate("extra_chars", $value);
				
			case "fieldType":
			case "type":
				
				if($fieldType = $this->validateFieldType($value))
				{
					return $this->callUpdate("field_type", $fieldType);
				}
				
				return $this;
				
			case "unique":
			case "isUnique":
				return $this->callUpdate("is_unique", $value !== false ? true : false);
				
			case "readonly":
			case "isReadonly":
				return $this->callUpdate("is_readonly", $value !== false ? true : false);
				
			case "boolean":
			case "isBoolean":
				$this->callUpdate("min_value", 0);
				$this->callUpdate("max_value", 1);
				$this->callUpdate("pull_type", "select");
				break;
		}
		
		return $this;
	}
	
	
/****** A helper function for updating the field list ******/
	private function callUpdate
	(
		string $column			// <str> The name of the column to update.
	,	mixed $value			// <mixed> The value to set the column to.
	)					// RETURNS <this>
	
	// $this->callUpdate($column, $value);
	{
		Database::query("UPDATE schema_fields SET `" . Sanitize::variable($column) . "`=? WHERE table_key=? AND field_key=? LIMIT 1", array($value, $this->table, $this->field));
		
		return $this;
	}
	
	
/****** Set what selection to pull from ******/
	public function pullType
	(
		string $pullType		// <str> The pull type ("select", "method", "database", etc).
	,	mixed $pullFrom		// <mixed> The location to pull from, such as within the selection arguments.
	)					// RETURNS <this>
	
	// $this->pullType($pullType, $pullFrom);
	// $this->pullType("select", "boolean");
	// $this->pullType("method", "methodToCall");
	// $this->pullType("database", array("table" => "users", "key" => "uni_id", "value" => "handle"));
	{
		$special = "";
		
		// If pulling from a database, the join rules have to saved into specific instructions
		if($pullType == "database")
		{
			if(isset($pullFrom['table']) and isset($pullFrom['key']) and isset($pullFrom['value']))
			{
				$special = Database::selectValue("SELECT special_instructions FROM schema_fields WHERE table_key=? AND field_key=? LIMIT 1", array($this->table, $this->field));
				
				$special = Serialize::decode($special);
				
				$special["pull_table"] = Sanitize::variable($pullFrom['table']);
				$special["pull_key"] = Sanitize::variable($pullFrom['key']);
				$special["pull_value"] = Sanitize::variable($pullFrom['value']);
				
				$special = Serialize::encode($special);
				
				$pullFrom = "special";
				
				// Process a valid database pull type
				Database::query("UPDATE schema_fields SET pull_type=?, pull_from=?, special_instructions=? WHERE table_key=? AND field_key=? LIMIT 1", array(Sanitize::variable($pullType), Sanitize::variable($pullFrom, "-"), $special, $this->table, $this->field));
				
				return $this;
			}
		}
		
		// Process the pull type
		Database::query("UPDATE schema_fields SET pull_type=?, pull_from=? WHERE table_key=? AND field_key=? LIMIT 1", array(Sanitize::variable($pullType), Sanitize::variable($pullFrom, "-"), $this->table, $this->field));
		
		return $this;
	}
	
	
/****** Validate a field type ******/
	public function validateFieldType
	(
		string $fieldType		// <str> The field type that you're attempting to add.
	): string					// RETURNS <str> the valid field type.
	
	// $fieldType = $schemaDefine->validateFieldType($fieldType);
	{
		switch($fieldType)
		{
			case "variable":
				return "variable";
				
			case "uni_id":
				return "uni_id";
				
			case "url":
				return "url";
				
			case "file":
			case "filename":
			case "filepath":
				return "filepath";
				
			case "string":
			case "safeword":
			case "input":
				return "input";
			
			case "integer":
			case "float":
			case "number":
				return "number";
			
			case "text":
			case "textarea";
				return "text";
				
			case "timestamp":
				return "timestamp";
		}
		
		return "";
	}
	
	
/****** Flush an entire table schema ******/
	public static function flush
	(
		string $table			// <str> The name of the table to flush.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SchemaDefine::flush($table);
	{
		return Database::query("DELETE FROM schema_fields WHERE table_key=?", array($table));
	}
	
	
/****** Add a selection option ******/
	public static function addSelectOption
	(
		string $selectName		// <str> The name of the selection schema to add
	,	mixed $key			// <mixed> The value of the key for the selection.
	,	string $title			// <str> The value of the title for the selection's key.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SchemaDefine::addSelectOption($selectName, $key, $title);
	{
		return Database::query("INSERT IGNORE INTO schema_selections (`selection_name`, `arg_key`, `arg_value`) VALUES (?, ?, ?)", array($selectName, $key, $title));
	}
	
}