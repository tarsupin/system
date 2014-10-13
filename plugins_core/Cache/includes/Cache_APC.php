<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }


/***************************
****** Cache with APC ******
***************************/

abstract class Cache {
	
	
/****** Plugin Variables ******/
	const TYPE = "APC";
	
	
/****** Set Cached Variable ******/
	public static function set
	(
		$key				// <str> The variable name (key) that you want to add to the cache.
	,	$value				// <str> The value that you'd like to store in cache.
	,	$expire	= 60		// <int> The duration of the cache (in seconds).
	,	$expireFlux = 0		// <int> An optional expiration flux, to help alleviate potential cache dumping.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::set("usersOnline", "100", $expire, $expireFlux);
	{
		$expire = time() + $expire;
		
		if($expireFlux > 0)
		{
			$expire += mt_rand(0, $expireFlux);
		}
		if(Me::$id <= 10) { var_dump($expire); }
		
		return apc_store($key, $value, $expire);
	}
	
	
/****** Get Cached Variable ******/
	public static function get
	(
		$key		// <str> The variable that you want to retrieve from the cache.
	)				// RETURNS <mixed> the value of the variable, or FALSE if doesn't exist.
	
	// $value = Cache::get("usersOnline");
	{
		return apc_fetch($key);
	}
	
	
/****** Check if a Cached Variable exists ******/
	public static function exists
	(
		$key		// <str> The variable that you want to check if it exists.
	)				// RETURNS <bool> TRUE if exists, FALSE if not.
	
	// Cache::exists("usersOnline");
	{
		return apc_exists($key);
	}
	
	
/****** Delete a Cached Variable ******/
	public static function delete
	(
		$key		// <str> The variable that you want to delete from the cache.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::delete("usersOnline");
	{
		return apc_delete($key);
	}
	
	
/****** Clear the Cache ******/
	public static function clear (
	)		// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cache::clear();
	{
		return apc_clear_cache();
	}
	
	
/****** Dummy Functions ******/
	public static function sql() { return true; }
	public static function showTables() { }
	public static function clearExpired() { return true; }
}
