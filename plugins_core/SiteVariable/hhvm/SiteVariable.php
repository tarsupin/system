<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the SiteVariable Class ------
------------------------------------------

The SiteVariable Class can save and load key-value pairs in the database. These are most often configuration values that
don't change frequently, but are important to keep track of through the database. Since these values are stored in the
database, it's easy to modify them from the web application itself through the admin control panel.

For example, your site might want to use a condition that temporarily grants or denies access to the "live chat" page
based on whether or not it's under construction, staff are available on that day, etc. Your staff need to be able to
turn this value on or off in the web application. This could be done by setting:

	SiteVariable::save("site-configs", "allow-live-chat", 1);		// Indicates live chat should be ON
	SiteVariable::save("site-configs", "allow-live-chat", 0);		// Indicates live chat should be OFF
	
Then you could call that value later on your live chat page:

	$liveChat = SiteVariable::load("site-configs", "allow-live-chat");
	
	if($liveChat != 1) { die("The live chat page is not active right now. Please try again later."); }


------------------------------------------
------ Examples of using this class ------
------------------------------------------

	// Save some site-wide configurations
	SiteVariable::save("site-configs", "show-users-online", 1);
	SiteVariable::save("site-configs", "show-last-posts-module", 0);
	
	// Save the version data of your recent site updates
	SiteVariable::save("version-data", "last-updated-by", "Joe Smith");
	SiteVariable::save("version-data", "last-major-update", "October 22nd");
	SiteVariable::save("version-data", "current-version", 3.05);
	
	// Load all of your version data
	$versionData = SiteVariable::load("version-data");		// Returns an array of the three "version-data" results
	
	// Load only the current version
	$currentSiteVersion = SiteVariable::load("version-data", "current-version");		// Returns "3.05"

------------------------------------------
------ Performance and Optimization ------
------------------------------------------

Keep in mind that every call to the SiteVariable::load() method will make a query to the database. Loading the data with
child keys will reduce the number of results returned, which is not always the right decision. Consider these
examples:

	// Option #1 for getting your current version
	$versionData = SiteVariable::load("version-data");
	echo $versionData['current-version'];
	
	
	// Option #2 for getting your current version
	$currentVersion = SiteVariable::load("version-data", "current-version");
	echo $currentVersion;

Both of these examples are identical in what they return. However, even though it is technically faster to load option
#2, most pages will benefit more by using option #1. This is because most pages will need more than just one value in
your grouped data. Here's another example where both examples are producing the same results:

	// Option #1 for getting all of your version data
	$versionData = SiteVariable::load("version-data");
	
	echo $versionData['last-updated-by'] . " updated the site version to " . $versionData['current-version'] . \
		" on " . $versionData['last-major-update'];
	
	
	// Option #2 for getting all of your version data
	$lastUpdatedBy = SiteVariable::load("version-data", "last-updated-by");
	$currentVersion = SiteVariable::load("version-data", "current-version");
	$lastMajorUpdate = SiteVariable::load("version-data", "last-major-update");
	
	echo $lastUpdatedBy . " updated the site version to " . $currentVersion . " on " . $lastMajorUpdate;
	

In this case, Option #1 is definitively faster since it only makes one database call. Option #2 has to make three
separate calls to the database. If you group your data properly, its retrieval can be considerably optimized.

Note this table is intended to be lean and fast since it might be called frequently. It is not intended to grow in size
with user activity, such as being used to give every user a "last login time" field. If you want to make changes like
that, it is advised that you create a custom table or modify an existing one. This class should be reserved for
important site configurations.

If you want to use the functionality that SiteVariable offers on larger tables, you can create a database table with the same structure as the SiteVariable table. You can then interact with that table with SiteVariables by setting the $table parameter to "NAME_OF_TABLE" on the SiteVariable methods. Normally the $table parameter is ignored and defaults to the `site_variables` table.


-------------------------------
------ Methods Available ------
-------------------------------

SiteVariable::save($keyGroup, $keyName, $value)		// Permanently saves the value of a configuration

SiteVariable::load($keyGroup, [$keyName])			// Returns the value of a saved configuration

SiteVariable::delete($keyGroup, [$keyName])			// Deletes a saved configuration

*/

abstract class SiteVariable {
	
	
/****** Save a Site Value ******/
	public static function save
	(
		string $keyGroup		// <str> The key group to save the key in.
	,	string $keyName		// <str> The key to save.
	,	mixed $value			// <mixed> the value or integer to set the configuration to.
	,	string $table = ""		// <str> The database table to use (defaults to `site_variables`)
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SiteVariable::save($keyGroup, $keyName, $value);
	{
		$table = $table == "" ? "site_variables" : Sanitize::variable($table);
		
		return Database::query("REPLACE INTO " . $table . " (key_group, key_name, value) VALUES (?, ?, ?)", array($keyGroup, $keyName, json_encode($value)));
	}
	
	
/****** Retrieve a Site Value (or multiple values within a shared group) ******/
	public static function load
	(
		string $keyGroup		// <str> The key group to retrieve.
	,	string $keyName = ""	// <str> The key to retrieve (if empty, the function returns the entire key group).
	,	string $table = ""		// <str> The database table to use (defaults to `site_variables`)
	): mixed					// RETURNS <mixed> The value stored, or FALSE on failure.
	
	// SiteVariable::load($keyGroup, [$keyName]);
	{
		$table = $table == "" ? "site_variables" : Sanitize::variable($table);
		
		// Retrieve the whole key group
		if($keyName == "")
		{
			$keyList = array();
			
			$getValues = Database::selectMultiple("SELECT key_name, value FROM " . $table . " WHERE key_group=?", array($keyGroup));
			
			foreach($getValues as $value)
			{
				$keyList[$value['key_name']] = json_decode($value['value']);
			}
			
			return $keyList;
		}
		
		// Retrieve a specific key
		if($getValue = Database::selectValue("SELECT value FROM " . $table . " WHERE key_group=? AND key_name=? LIMIT 1", array($keyGroup, $keyName)))
		{
			return json_decode($getValue);
		}
		
		return false;
	}
	
	
/****** Delete a Site Value ******/
	public static function delete
	(
		string $keyGroup		// <str> The key group to delete keys from.
	,	string $keyName = ""	// <str> The key to delete (if empty, the function will delete the entire key group).
	,	string $table = ""		// <str> The database table to use (defaults to `site_variables`)
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SiteVariable::delete($keyGroup, [$keyName]);
	{
		$table = $table == "" ? "site_variables" : Sanitize::variable($table);
		
		if($keyName == "")
		{
			return Database::query("DELETE FROM " . $table . " WHERE key_group=?", array($keyGroup));
		}
		
		return Database::query("DELETE FROM " . $table . " WHERE key_group=? AND key_name=? LIMIT 1", array($keyGroup, $keyName));
	}
}
