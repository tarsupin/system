<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }


/********************************
****** Cache with Database ******
********************************/

abstract class Cache {
	
	
/****** Plugin Variables ******/
	const TYPE = "Database";
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key				// <str> The variable name (key) that you want to add to the cache.
	,	$value				// <str> The value that you'd like to store in cache.
	,	$expire	= 600		// <int> The duration of the cache in seconds (default is 10 minutes).
	,	$expireFlux = 0		// <int> An optional expiration flux, to avoid cache dumping.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::set("usersOnline", "100", $expire, $expireFlux)
	{
		$expire += time() + $expire;
		
		if($expireFlux > 0)
		{
			$expire += mt_rand(0, $expireFlux);
		}
		
		// Check Database for existing value
		$keyData = Database::selectOne("SELECT expire FROM cache WHERE `key`=? LIMIT 1", array($key));
		
		if(isset($keyData['expire']))
		{
			return Database::query("UPDATE cache SET value=?, expire=? WHERE `key`=? LIMIT 1", array($value, $expire, $key));
		}
		
		return Database::query("INSERT INTO cache (`key`, value, expire) VALUES (?, ?, ?)", array($key, $value, $expire));
	}
	
	
/****** Get Cached Variable ******/
	public static function get
	(
		$key		// <str> The variable that you want to retrieve from the cache.
	)				// RETURNS <mixed> the value of the variable, or FALSE if doesn't exist.
	
	// $value = Cache::get("usersOnline");
	{
		// Garbage collection
		if(mt_rand(0, 2000) == 1022)
		{
			Cache::clearExpired();
		}
		
		// Get the Cached Value
		if(!$keyData = Database::selectOne("SELECT value, expire FROM cache WHERE `key`=? LIMIT 1", array($key)))
		{
			return false;
		}
		
		// If the Cached Value is expired, delete it and return false
		if((int) $keyData['expire'] <= time())
		{
			self::delete($key);
			return false;
		}
		
		return (isset($keyData['value']) ? $keyData['value'] : false);
	}
	
	
/****** Check if a Cached Variable exists ******/
	public static function exists
	(
		$key		// <str> The variable that you want to check if it exists.
	)				// RETURNS <bool> TRUE if exists, FALSE if not.
	
	// if(Cache::exists("usersOnline")) { echo "Value exists."; }
	{
		return Database::selectValue("SELECT `key` FROM cache WHERE `key`=? LIMIT 1", array($key));
	}
	
	
/****** Delete a Cached Variable ******/
	public static function delete
	(
		$key		// <str> The variable that you want to delete from the cache.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::delete("usersOnline");
	{
		return Database::query("DELETE FROM cache WHERE `key`=? LIMIT 1", array($key));
	}
	
	
/****** Clear any Cache Keys that have expired ******/
	public static function clearExpired (
	)		// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::clearExpired();
	{
		return Database::query("DELETE FROM cache WHERE expire < ?", array(time() - 3600));
	}
	
	
/****** Clear the Cache ******/
	public static function clear (
	)		// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::clear();
	{
		return Database::exec("TRUNCATE cache");
	}
	
	
/****** Generate `Cache` SQL ******/
	public static function sql()
	{ 
		Database::exec("
		CREATE TABLE IF NOT EXISTS `cache`
		(
			`key`			varchar(28)					NOT NULL	DEFAULT '',
			`value`			text						NOT NULL	DEFAULT '',
			`expire`		int(10)						NOT NULL	DEFAULT '0',
			
			UNIQUE (`key`),
			INDEX (`expire`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		return DatabaseAdmin::columnsExist("cache", array("key", "value", "expire"));
	}
	
	public static function showTables()
	{
		DatabaseAdmin::showTable("cache");
	}
}
