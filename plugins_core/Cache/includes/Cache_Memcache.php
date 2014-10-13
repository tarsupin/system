<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }


/********************************
****** Cache with Memcache ******
********************************/

abstract class Cache {
	
	
/****** Plugin Variables ******/
	public static $memConn = null;
	
	const TYPE = "Memcache";
	
	
/****** Initialize the Class ******/
	public static function initialize (
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::initialize();
	{
		self::$memConn = new Memcached();
		self::$memConn->addServer('localhost', 11211);
		
		return true;
	}
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key				// <str> The variable name (key) that you want to add to the cache.
	,	$value				// <str> The value that you'd like to store in cache.
	,	$expire	= 60		// <int> The duration of the cache (in seconds).
	,	$expireFlux = 0		// <int> An optional expiration flux, to avoid cache dumping.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::set("usersOnline", "100", $expire, $expireFlux);
	{
		$expire = time() + $expire;
		
		if($expireFlux > 0)
		{
			$expire += mt_rand(0, $expireFlux);
		}
		
		return self::$memConn->set($key, $value, $expire);
	}
	
	
/****** Get Cached Variable ******/
	public static function get
	(
		$key		// <str> The variable that you want to retrieve from the cache.
	)				// RETURNS <mixed> the value of the variable, or FALSE on failure.
	
	// $value = Cache::get("usersOnline");
	{
		return self::$memConn->get($key);
	}
	
	
/****** Check if a Cached Variable exists ******/
	public static function exists
	(
		$key		// <str> The variable that you want to check if it exists.
	)				// RETURNS <bool> TRUE if exists, FALSE if not.
	
	// if(Cache::exists("usersOnline")) { echo "Value exists."; }
	{
		$val = self::$memConn->get($key);
		
		return ($val !== false ? true : false);
	}
	
	
/****** Delete a Cached Variable ******/
	public static function delete
	(
		$key		// <str> The variable that you want to delete from the cache.
	)				// RETURNS <bool> TRUE on success, FALSE on failure. 
	
	// Cache::delete("usersOnline");
	{
		return self::$memConn->delete($key);
	}
	
	
/****** Dummy Functions ******/
	public static function sql() { return true; }
	public static function showTables() { }
	public static function clearExpired() { return true; }
	public static function clear() { return true; }

}

// Initialize the class
Cache::initialize();
