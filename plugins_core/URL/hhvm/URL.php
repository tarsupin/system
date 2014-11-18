<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------
------ About the URL Plugin ------
----------------------------------

This plugin provides simple functionality for URLs, which is also essential for loading the URL segments into $url during system configuration.


-----------------------------------------------
------ Calling Environment-Specific URLs ------
-----------------------------------------------

Developers may use multiple environments where URLs point differently. To solve this problem, you can use this plugin to call a single URL that will adapt itself to the specific environment that you are using.

For example, the following behavior may occur when loading URL::example_com()

	Local Environment:			"http://example.test"
	Staging Environment:		"http://example.mydevserver.com"
	Production Environment:		"http://example.com"

In other words, when you're on your local server, URL::example_com() will point to "http://example.test" for your own testing purposes, but it will point to "http://example.com" on the live server. This allows you to run multiple sites without having to change hard-coded URLs between different environments.

Note that any instances of ".unifaction.com" will also be translated into .test (or development environment equivalent).

Here's a list of examples how the URL plugin will interpret the dynamic values:
	
	// URL's that end in ".com"
	URL::sports_microfaction_com()
	
		Local:	sports.microfaction.test
		Dev:	sports.microfaction.phptesla.com
		Live:	sports.microfaction.com
	
	// URL's that end in "unifaction.com"
	URL::unifaction_com()
	
		Local:	auth.test
		Dev:	auth.phptesla.com
		Live:	auth.unifaction.com
	
	// URL's that end in something other than ".com"
	URL::unifaction_social()
		
		Local:	unifaction.social.test
		Dev:	unifaction.social.phptesla.com
		Live:	unifaction.social

-------------------------------
------ Methods Available ------
-------------------------------

$urlSegments = URL::getSegments([$url])		// Returns array of URL segments (e.g. domain.com/{segment1}/{segment2})

URL::toSlug($title);						// Change a value to a URL slug (e.g. "My Cool Post" to "my-cool-post")

$parsedURL = URL::parse($url)				// Parses a URL and returns an array of all its pieces

*/

abstract class URL {
	
	
/****** Returns an environment-specific domain ******/
	public static function __callStatic
	(
		string $name		// <str> The name of the function being called.
	,	array $args		// <array> Additional arguments being passed.
	): string				// RETURNS <str> The URL of the chosen site.
	
	// $url = URL::unifaction_com();
	{
		// Production URL
		if(ENVIRONMENT == "production")
		{
			return "http://" . str_replace("_", ".", $name);
		}
		
		if(strpos($name, "_unifaction_community") == false)
		{
			$name = str_replace("_unifaction_com", "", $name);
			$name = str_replace("_com", "", $name);
		}
		
		// Development URL
		if(ENVIRONMENT == "development")
		{
			if($name == "unifaction") { return "http://phptesla.com"; }
			
			return "http://" . str_replace("_", ".", $name) . '.phptesla.com';
		}
		
		// Localhost URL
		return "http://" . str_replace("_", ".", $name) . '.test';
	}
	
	
/****** Return the URL Segments for this Page Load ******/
	public static function getSegments
	(
		string $url = ""	// <str> The URL that you want to get the segments of; default is the current URL.
	): array <int, mixed>				// RETURNS <int:mixed> segments of the URL provided (e.g. "domain.com/{segment1}/{segment2}");
	
	// list($url, $url_relative) = URL::getSegments();
	{
		if($url == "")
		{
			if(!$url = $_SERVER['REQUEST_URI'])
			{
				return array();
			}
		}
		
		// Strip out any query string data (if used)
		$urlString = explode("?", rawurldecode($url));
		
		// Sanitize any unsafe characters from the URL
		$urlString = trim(Sanitize::variable($urlString[0], " -/.+"), "/");
		
		// Section the URL into multiple segments so that each can be added to the array individually
		$segments = explode("/", $urlString);
		
		// Some webmasters may use directories (e.g. "C:/www/mysite/") instead of a host (e.g. "mysite.test")
		// If we're on a localhost system, check for this behavior and remove if necessary
		if(ENVIRONMENT == "localhost")
		{
			$defSegments = explode("/", rtrim(SYS_PATH, "/"));
			$lastSegment = $defSegments[count($defSegments) - 1];
			
			if(in_array($lastSegment, $segments))
			{
				for($i = 0;$i < count($segments);$i++)
				{
					array_shift($segments);
					
					if(!isset($segments[$i]) || $segments[$i] != $lastSegment)
					{
						break;
					}
				}
			}
		}
		
		return array($segments, $urlString);
	}
	
	
/****** Change a value or title to a url slug ******/
	public static function toSlug
	(
		string $value		// <str> The value you'd like to change.
	): string				// RETURNS <str> a valid url slug.
	
	// echo URL::toSlug("The Best Blog Post");		// returns "the-best-blog-post"
	{
		// Transform the text into a friendly url
		$value = str_replace(" & ", " and ", trim($value));
		$value = str_replace(" ", "-", strtolower(Sanitize::variable($value, "- ")));
		
		// Cut the size of the url
		if(strlen($value) > 72)
		{
			while(strrpos($value, '-') > 62)
			{
				$value = substr($value, 0, strrpos($value, "-"));
			}
			
			// Final cut, in case the above didn't work
			if(strlen($value) > 72)
			{
				$value = substr($value, 0, 72);
			}
		}
		
		return $value;
	}
	
	
/****** Parse a URL ******/
	public static function parse
	(
		string $url				// <str> A URL to parse.
	,	bool $simple = false		// <bool> TRUE if you only want to return simple URL data.
	,	bool $sanitize = false	// <bool> TRUE if you want to sanitize the paths values.
	): array <str, str>						// RETURNS <str:str> the data for the parsed URL, array() if misformed.
	
	// $parsedURL = URL::parse($url, [$simple]);
	{
		$parseURL = parse_url($url);
		
		// Fix malformed hosts
		if(!isset($parseURL['host']) and isset($parseURL['path']))
		{
			$val = explode("/", $parseURL['path']);
			
			if($val[0] != "")
			{
				$parseURL['host'] = $val[0];
			}
			else
			{
				$parseURL['host'] = $_SERVER['SERVER_NAME'];
			}
			
			array_shift($val);
			
			// Sanitize the path values, if requested
			if($sanitize)
			{
				foreach($val as $key => $v)
				{
					$val[$key] = Sanitize::variable($v, " ./-+");
				}
			}
			
			// Prepare the Values
			$parseURL['urlSegments'] = $val;
			$parseURL['path'] = trim(implode("/", $val), "/");
		}
		else if(isset($parseURL['host']) and isset($parseURL['path']))
		{
			$val = explode("/", $parseURL['path']);
			
			array_shift($val);
			
			// Sanitize the path values, if requested
			if($sanitize)
			{
				foreach($val as $key => $v)
				{
					$val[$key] = Sanitize::variable($v, " ./-+");
				}
			}
			
			// Prepare the Values
			$parseURL['urlSegments'] = $val;
			$parseURL['path'] = trim(implode("/", $val), "/");
		}
		else if(isset($parseURL['host']))
		{
			// Do nothing
		}
		else
		{
			return array();
		}
		
		// Fix empty paths
		if(isset($parseURL['path']))
		{
			if($parseURL['path'] == "/" or $parseURL['path'] == "")
			{
				unset($parseURL['path']);
			}
		}
		
		// We can end here if we only need simple data
		if($simple) { return $parseURL; }
		
		// Get the Query Values
		if(isset($parseURL['query']))
		{
			$parseURL['queryValues'] = array();
			$qVals = explode("&", $parseURL['query']);
			
			foreach($qVals as $getVal)
			{
				$qNext = explode("=", $getVal, 2);
				$parseURL['queryValues'][$qNext[0]] = (isset($qNext[1]) ? $qNext[1] : "");
			}
		}
		
		// Get the Base Domain
		$hostSegments = explode(".", $parseURL['host']);
		$len = count($hostSegments);
		
		$parseURL["baseDomain"] = ($len > 2 ? $hostSegments[$len - 2] . '.' . $hostSegments[$len - 1] : $parseURL['host']);
		
		// Prepare Scheme
		$parseURL['scheme'] = isset($parseURL['scheme']) ? $parseURL['scheme'] : "http";
		
		// Prepare Full URL
		$parseURL['full'] = $parseURL['scheme'] . "://" . $parseURL['host'] . (isset($parseURL['path']) ? "/" . $parseURL['path'] : "") . (isset($parseURL['query']) ? "?" . $parseURL['query'] : "") . (isset($parseURL['fragment']) ? "#" . $parseURL['fragment'] : "");
		
		return $parseURL;
	}
}