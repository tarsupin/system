<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the UniqueID Plugin ------
---------------------------------------

This plugins creates unique IDs (integers in sequential order). This effect is similar to an auto_increment key an SQL table. It is used to map objects to uniquely identified numeric "address" to reference them later.

This functionality was built to allow certain database tables to increment values without needing to have an auto-incremented field. This allows certain tables to have good partitioning structures, while retaining certain advantages of having unique IDs.

UniqueID ID's created with this class are maintained as unique across the entire site, so they could be shared across multiple object types. Once an ID is assigned, the address counter will increment and the next ID will be 1 higher.

It is also possible to track any number of tables (or objects) with a shared UniqueID counter, and always be able to identify each object based on its unique ID.

Note: you can create multiple ID counters if you need different sets of unique IDs. For example, threads may need to have unique ID's from the UniqueID counter "thread", while posts could use the UniqueID counter "post".


-----------------------------------------
------ Example of using this class ------
-----------------------------------------
	
	//
	// firstpage.php
	//
	
	// Setup the UniqueID Table
	Database::initRoot();
	UniqueID::sql();
	
	// Run the first instances of UniqueID
	echo UniqueID::get();			// Returns 1
	echo UniqueID::get();			// Returns 2
	

	//
	// secondpage.php
	//
	
	// The second page load shows the next unique incremented values:
	echo UniqueID::get();				// Returns:	3
	echo UniqueID::get();				// Returns:	4
	echo UniqueID::get();				// Returns:	5
	
	
	//
	// thirdpage.php
	//
	
	UniqueID::newCounter("different");	// Creates a new UniqueID Tracker
	
	echo UniqueID::get("different");		// Returns "1";
	echo UniqueID::get();					// Returns "6";
	echo UniqueID::get("different");		// Returns "2";


-------------------------------
------ Methods Available ------
-------------------------------

$uniqueID = UniqueID::get([$name])		// Returns a unique ID from the desired UniqueID tracker

UniqueID::newCounter($name)

*/

abstract class UniqueID {
	
	
/****** Return a new UniqueID ID ******/
	public static function get
	(
		$name = "unique"		// <str> The name of the address tracker to use (e.g. "thread", "post", etc)
	)							// RETURNS <int> the numeric ID to provide. 0 means it failed.
	
	// $uniqueID = UniqueID::get([$name]);
	{
		// Get the current value set
		if(!$current = (int) Database::selectValue("SELECT value FROM site_variables WHERE key_group=? AND key_name=? LIMIT 1", array("uniqueIDs", $name)))
		{
			return 0;
		}
		
		// Update the counter for next time
		if(!Database::query("UPDATE site_variables SET value=? WHERE key_group=? AND key_name=? LIMIT 1", array(($current + 1), "uniqueIDs", $name)))
		{
			return 0;
		}
		
		return $current;
	}
	
	
/****** Adds a new unique address tracker ******/
	public static function newCounter
	(
		$name		// <str> The name of the address tracker to create (e.g. "thread", "post", etc)
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// UniqueID::newCounter($name);
	{
		return SiteVariable::save("uniqueIDs", $name, 1);
	}
	
}
