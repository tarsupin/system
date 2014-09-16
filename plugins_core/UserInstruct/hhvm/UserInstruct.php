<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the UserInstruct Plugin ------
-------------------------------------------

This plugin allows the system to set instructions for users. Instructions run on every page view for a user until the instruction has been dismissed.


------------------------------
------ Force Page Visit ------
------------------------------

The most common type of instruction to set for a user is to force visiting a particular URL. Though any type of instruction could be set, this one will likely account for the majority of instances.

Examples of forcing the user to visit a page may include confirming a value (email, TOS, registration, etc), or to perform an essential action on a site that they might otherwise miss.
	
	
-----------------------------------
------ Deleting Instructions ------
-----------------------------------

By default, instructions will only run once, and delete themselves immediately afterward. However, it is possible to prevent an instruction from deleting itself by setting UserInstruct::$delete to false within the behavior being called.


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Force the user to visit a particular URL
UserInstruct::create($uniID, $plugin, $behavior, [$params]);


-------------------------------
------ Methods Available ------
-------------------------------

// Get a list of the user's instructions
$instructions = UserInstruct::get($uniID);

// Create a new instruction
UserInstruct::create($uniID, $plugin, $behavior, [$params]);

// Run the user's instructions
UserInstruct::runInstructions($uniID);

// Delete an instruction with an ID
UserInstruct::delete($id, $uniID);

// Delete an instruction based on parameters provided
UserInstruct::deleteWhere($uniID, $plugin, $behavior, [$where]);

// Set whether or not a user has instructions
UserInstruct::setHasInstructions($uniID, $has);

*/

abstract class UserInstruct {
	
	
/****** Plugin Variables ******/
	public static int $lastInstructionID = 0;		// <int> Stores the last instruction ID activated.
	public static int $lastUniID = 0;				// <int> Stores the last UniID activated in an instruction.
	public static bool $delete = true;				// <bool> Stores whether or not to remove an instruction once run.
	public static bool $haltInstructions = false;	// <bool> TRUE if no further instructions should be activated.
	
	
/****** {Behavior} Force the user to visit a specific URL ******/
	public static function SendToURL_TeslaBehavior
	(
		string $toURL				// <str> The URL to send the user to.
	,	bool $runOnce = true		// <bool> TRUE to remove this instruction after first visit, FALSE for custom removal.
	): void						// RETURNS <void>
	
	// Plugin::runBehavior("UserInstruct", "SendToURL", $parameters);
	{
		// Since we're redirecting, make sure all instructions halt after this
		self::$haltInstructions = true;
		
		// Check if we're only running this URL once
		if($runOnce)
		{
			self::delete(self::$lastInstructionID, self::$lastUniID);
		}
		else
		{
			self::$delete = false;
		}
		
		global $url, $url_relative;
		
		// Prepare the URL and send the user to that page (unless already there)
		$sendURL = URL::parse($toURL);
		
		if($url_relative != $sendURL['path'])
		{
			header("Location: /" . $sendURL['path']); exit;
		}
	}
	
	
/****** Get the user's instructions ******/
	public static function get
	(
		int $uniID		// <int> The UniID to set an instruction for.
	): array <int, array<str, mixed>>				// RETURNS <int:[str:mixed]> the list of instructions for the user.
	
	// $instructions = UserInstruct::get($uniID);
	{
		return Database::selectMultiple("SELECT id, plugin, behavior, params FROM users_instructions WHERE uni_id=?", array($uniID));
	}
	
	
/****** Set a user's instruction ******/
	public static function create
	(
		int $uniID				// <int> The UniID to set an instruction for.
	,	string $plugin				// <str> The name of the plugin that will run the instruction.
	,	string $behavior			// <str> The plugin behavior to run.
	,	array <int, mixed> $params = array()	// <int:mixed> The parameters to include for the instruction call.
	): int						// RETURNS <int> the instruction ID, or 0 on failure.
	
	// UserInstruct::create($uniID, $plugin, $behavior, [$params]);
	{
		// Prepare Values
		$plugin = Sanitize::variable($plugin);
		$behavior = Sanitize::variable($behavior);
		$params = Serialize::encode($params);
		
		// Insert the instruction
		if(Database::query("INSERT INTO users_instructions (uni_id, plugin, behavior, params) VALUES (?, ?, ?, ?)", array($uniID, $plugin, $behavior, $params)))
		{
			self::setHasInstructions($uniID, true);
			
			return Database::$lastID;
		}
		
		return 0;
	}
	
	
/****** Run all of a user's instructions ******/
	public static function runInstructions
	(
		int $uniID		// <int> The UniID to run instructions for.
	): bool				// RETURNS <bool> TRUE after success, or FALSE on failure.
	
	// UserInstruct::runInstructions($uniID);
	{
		if($instructions = self::get($uniID))
		{
			foreach($instructions as $ins)
			{
				// Check if the instruction processor should be halted
				if(self::$haltInstructions)
				{
					break;
				}
				
				// Set Values
				self::$lastInstructionID = $ins['id'];
				self::$lastUniID = $uniID;
				self::$delete = true;
				
				$params = Serialize::decode($ins['params']);
				
				// Run the instruction
				Plugin::runBehavior($ins['plugin'], $ins['behavior'], $params);
				
				// If the instruction didn't restrict a deletion, delete the instruction
				if(self::$delete)
				{
					self::delete($ins['id'], $uniID);
				}
			}
		}
		
		// Reset the user's instruction count
		if(count($instructions) == 0)
		{
			self::setHasInstructions($uniID, false);
		}
		
		return true;
	}
	
	
/****** Delete a user's instruction ******/
	public static function delete
	(
		int $id			// <int> The ID of the instruction to delete.
	,	int $uniID		// <int> The UniID that the instruction was assigned to.
	): bool				// RETURNS <bool> TRUE if the entry was deleted, FALSE if not.
	
	// UserInstruct::delete($id, $uniID);
	{
		if(Database::query("DELETE IGNORE FROM users_instructions WHERE id=? LIMIT 1", array($id)))
		{
			if(!Database::selectValue("SELECT COUNT(*) as totalNum FROM users_instructions WHERE uni_id=? LIMIT 1", array($uniID)))
			{
				self::setHasInstructions($uniID, false);
			}
			
			return true;
		}
		
		return false;
	}
	
	
/****** Delete a user's instruction based on specific details ******/
	public static function deleteWhere
	(
		int $uniID		// <int> The UniID that the instruction was assigned to.
	,	string $plugin		// <str> The name of the plugin the instruction used.
	,	string $behavior	// <str> The name of the behavior the instruction used.
	,	string $where = ""	// <str> The wildcard entry that the instruction will contain.
	): bool				// RETURNS <bool> TRUE if the entry was deleted, FALSE if not.
	
	// UserInstruct::deleteWhere($uniID, $plugin, $behavior, [$where]);
	{
		if(Database::query("DELETE IGNORE FROM users_instructions WHERE uni_id=? AND plugin=? AND behavior=? AND params LIKE ? LIMIT 1", array($uniID, $plugin, $behavior, "%" . $where . "%")))
		{
			if(!Database::selectValue("SELECT COUNT(*) as totalNum FROM users_instructions WHERE uni_id=? LIMIT 1", array($uniID)))
			{
				self::setHasInstructions($uniID, false);
			}
			
			return true;
		}
		
		return false;
	}
	
	
/****** Set whether or not the user has instructions ******/
	public static function setHasInstructions
	(
		int $uniID		// <int> The UniID to reset the instruction counter for.
	,	bool $has		// <bool> TRUE if the user has instructions, FALSE if not.
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// UserInstruct::setHasInstructions($uniID, $has);
	{
		return Database::query("UPDATE IGNORE users SET has_instructions=? WHERE uni_id=? LIMIT 1", array(($has ? 1 : 0), $uniID));
	}
	
}