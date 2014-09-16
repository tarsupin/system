<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Migrate Plugin ------
--------------------------------------

This plugin allows you to migrate database changes between different versions.

	Relevant Files:
	/config/version.txt			// Contains the current version of the site, nothing else
	/config/migrate.php			// The file containing the database migrations.
	
	
------------------------------------------
------ Example of using this plugin ------
------------------------------------------

	// Add "more_data" column to the "users" table (version 1.01 update)
	if(Migrate::update(1.01, "Add more_data column to users table."))
	{
		Migrate::$pass = DatabaseAdmin::addColumn("users", "more_data", "varchar(32) not null", "");
	}
	
	// Run a script if lower than the existing version
	if(Migrate::update(1.02, "Add miscellaneous data"))
	{
		Migrate::$pass = Database::query("UPDATE users SET more_data=? WHERE uni_id < ? LIMIT 10", array("blah", 500));
	}
	
	
-------------------------------
------ Methods Available ------
-------------------------------

Migrate::getVersion()
Migrate::update($version, [$description])

Migrate::processFinal();

*/

abstract class Migrate {
	
	
/****** Class Variables ******/
	public static $pass = true;				// <bool> If set to FALSE, this abruptly ends Migrations
	public static $currentVersion = 0.00;	// <float> This gets set to the current migration version
	public static $lastVersion = 0.00;		// <float> Saves the last successful migration value (in case $pass fails)
	public static $newVersion = 0.00;		// <float> The newest migration value (may get listed as faulty)
	
	
/****** Add an entry to the migration list ******/
	public static function getVersion (
	)				// RETURNS <float> the current version of the site.
	
	// $version = Migrate::getVersion();
	{
		// Check if the current version has been set
		if(self::$currentVersion == 0.00)
		{
			// Make sure the version was set
			if(!File::exists(CONF_PATH . "/config/version.txt"))
			{
				File::create(CONF_PATH . "/config/version.txt", "0.01");
			}
			
			// Get the current version
			self::$currentVersion = floatval(File::read(CONF_PATH . "/config/version.txt"));
			
			// Make sure the final Migration process runs
			register_shutdown_function(array('Migrate', 'processFinal'));
		}
		
		return (float) self::$currentVersion;
	}
	
	
/****** Add an entry to the migration list ******/
	public static function update
	(
		$version	// <float> The version that you're migrating.
	,	$desc = ""	// <str> Description for this migration.
	)				// RETURNS <bool> TRUE if updating, FALSE if not.
	
	// Migrate::update($version, [$description]);
	{
		// If Migration broke at some point, end all following migrations
		if(!self::$pass) { return false; }
		
		// Run the update
		$curVersion = self::getVersion();
		
		if($pass = ($curVersion >= $version ? false : true))
		{
			// Announce the Migration
			echo '
			<div>Migrate (' . $version . ') : ' . $desc . '</div>';
			
			// Check if there was an already-valid migration that we can track
			if(self::$newVersion != 0.00)
			{
				self::$lastVersion = self::$newVersion;
			}
			
			// Set the new version
			self::$newVersion = $version;
		}
		
		return $pass;
	}
	
	
/****** Run the Final Migrate Process ******/
	public static function processFinal (
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Migrate::processFinal();
	{
		// If there was a failure during migration, revert to the last successful version
		if(!self::$pass)
		{
			echo '
			<div style="color:red;">Migrate (' . self::$newVersion . ') : ERROR! Was not able to process.</div>';
			
			if(self::$lastVersion != 0.00)
			{
				self::$newVersion = self::$lastVersion;
			}
			else
			{
				return false;
			}
		}
		
		// Set the new version
		if(self::$newVersion != 0.00)
		{
			File::write(CONF_PATH . "/config/version.txt", self::$newVersion);
		}
		
		// Announce Migration
		echo '
		<div>Migration process has finished.</div>';
		
		return true;
	}
	
}
