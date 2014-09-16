<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the CacheFile Plugin ------
----------------------------------------

This class is used to cache an entire chunk of text, such as a block of HTML. This can greatly reduce the load time of dynamic pages if they don't have to be updated constantly.

An example of why this function is useful is a "Today's Highlights" module on the site. The content needs to be updated every day, but the actual process of updating it might be somewhat intensive due to how many articles you need to parse through for the algorithm to complete.

Thus, rather than running the algorithm every single page view, we just run it once a day and cache the results in a text file that we can request.


----------------------------------------------------
------ How to save the text into a file cache ------
----------------------------------------------------

Saving the text is easy. Just decide on the name of the cache you'd like to save, and then add the data you want to cache. The code for this looks like this:

	// Get the text that you want to save
	$textData = someAlgorithm(time());
	
	// Add the text to a cached file
	// In this case, save it with the "highlights_today" key
	CacheFile::save("highlights_today", $textData);
	
	
-------------------------------------
------ Loading your file cache ------
-------------------------------------

The purpose of caching your data is so that you can refresh it every once in a while to prevent it from going stale. Therefore, you must set the "refresh" duration of the Cache. The "refresh" duration is how many seconds until the cache goes stale and will no longer serve content. When this happens, you need to run the algorithm again to save new content into the file.

Here's an example of how loading a file cache works in practice:
#!
// Load a cache page that regenerates after 15 seconds
if(!$cacheData = CacheFile::load("testCache", 15))
{
	CacheFile::save("testCache", "Save this value: " . md5(mt_rand()));
	
	$cachedData = CacheFile::load("testCache");
}
##!


-------------------------------
------ Methods Available ------
-------------------------------

// Saves the HTML chunk to a cached file
CacheFile::save($cacheName, $html)

// Loads cache if &lt; $refresh time passed
// A $refresh value of 0 means it will persist indefinitely
CacheFile::load($cacheName, $refresh = 0)


*/

abstract class CacheFile {
	
	
/****** Plugin Variables ******/
	public static $cacheDirectory = "/cache";
	
	
/****** Save a Cache File ******/
	public static function save
	(
		string $cacheName		// <str> The cache name that you'd like to save.
	,	string $text			// <str> The text that you'd like to save.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// CacheFile::save($cacheName, $text);
	{
		$cacheName = Sanitize::variable($cacheName, ".-");
		
		$filename = "/" . ltrim($cacheName, "/") . '.html';
		
		// Make sure the directory exists
		Dir::create(APP_PATH . self::$cacheDirectory . dirname($filename));
		
		// Write the new file
		return File::write(APP_PATH . self::$cacheDirectory . $filename, $text);
	}
	
	
/****** Return the page to load ******/
	public static function load
	(
		string $cacheName		// <str> The name of the cache to load.
	,	int $refresh = 0	// <int> The duration (in seconds) that should pass before you refresh the cache.
	,	bool $output = false	// <bool> TRUE will output the HTML when loading.
	): mixed					// RETURNS <mixed> the text of the cache, or FALSE if the cache is stale (or on failure).
	
	// CacheFile::load($cacheName, $refresh);
	{
		$cacheName = Sanitize::variable($cacheName, ".-");
		
		$filename = "/" . ltrim($cacheName, "/") . '.html';
		$path = APP_PATH . self::$cacheDirectory . $filename;
		
		if(file_exists($path))
		{
			// Check if the cache has gone stale
			if($refresh > 0)
			{
				$timePassed = time() - filemtime($path);
				
				if($timePassed > $refresh)
				{
					return false;
				}
			}
			
			// Load the cache and return successful
			if($output) { echo file_get_contents($path); return ""; }
			
			return file_get_contents($path);
		}
		
		return false;
	}
	
}
