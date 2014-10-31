<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Search Plugin ------
-------------------------------------

This plugin allows you to process fulltext search results to find meaningful connections between data that shares a lot of keywords or content. Though there is some overhead to the power of fulltext searching, it is a much simpler implementation than building a custom solution.

Here's roughly how the fulltext search works: let's say you have twenty articles about gaming. A few titles include "How to find the right materials in Minecraft", "Minecraft's greatest secrets", "Pong and it's brethren", and "Arcade games and being in the know."

A fulltext search might include a query like: "How do I find the bosses in Minecraft?"

The fulltext search would find keywords in your query (in this case, "bosses" and "Minecraft") and would compare them to the articles. In this case, it would return the articles about Minecraft.


-------------------------------------
------ Using the Search Plugin ------
-------------------------------------

For this plugin, we're ONLY concerned with two points of data:
	
	1. The "entry", which is the title of the entry as you will see it appear in a search box.
	
	2. The "extra keywords", which you don't see but help to filter the appropriate results.
	
So when we create a search entry, these are the only values that the fulltext search will look to. This is because we are only interested in providing curated search links with this plugin. If you want to do scanning through article content, you will need to build another plugin to handle additional columns.


-----------------------------------
------ Creating a Search Bar ------
-----------------------------------

To create a search bar, you can just use the static method available in this plugin:

	Search::searchBar($name, $siteURL, $scriptName, $placeholder);
	
This will load a search bar of the designated type.
	
	$name is the name you will use for the search bar (important for references).
	
	$siteURL is usually set to "" (for local URL), but can be set to other URLs.
	
	$scriptName is the script that gets loaded.
		* This will attempt to find a match in {APP_PATH}/controller/ajax/$scriptName.php
		* If not found, will try to find {SYS_PATH}/controller/ajax/$scriptName.php
		* You can see examples of these scripts in the {SYS_PATH}/controller/ajax directory.
	
	$placeholder is just the content that appears in the search bar when there is no text.
	
If you want to have a search bar for users, there is a pre-generated search bar for you:
	
	Search::searchBarUserHandle();


-------------------------------------
------ Creating Search Entries ------
-------------------------------------

If you want to add search entries, you can add them in the admin panel or automate several at once using the addSearchEntry() method.

This will essentially add a list of drop-down options to a search bar.


----------------------------------------------------
------ Simple working example of a search bar ------
----------------------------------------------------

	// Create some search entries
	$results = Search::addSearchEntry("update my password", "", URL::unifaction_com() . "/user-panel");
	$results = Search::addSearchEntry("password security tips", "passwords optimize secure improve strong", URL::unifaction_com() . "/user-panel");
	
	$test = new Search("How do I create a good password");
	var_dump($test);
	

---------------------------------------------
------ "Still Typing" vs. "Not Typing" ------
---------------------------------------------

By default, the Search plugin assumes that you are "still typing" when a query is added. This means that the last word in the list will be considered partial, and a wildcard token will be applied to it.

For example, if you enter the query:

	"How do I create a good pas"
	
It will assume that you are still typing the last word, and so it will still match any words of "pas*" such as "password", "pasta", "pastromi", etc.

If you set the mode to Search::NOT_TYPING, it will end this effect and take every word exactly as it is.

*/

class Search {
	
	
/****** Plugin Variables ******/
	public array <int, array<str, mixed>> $results = array();		// <int:[str:mixed]> The search results received from a query.
	
	const NOT_TYPING = 0;			// Set this mode if the query string is set - not being typed.
	const STILL_TYPING = 1;			// Set this mode if the query string is still being typed.
	const EXPANDED_SEARCH = 2;		// Set this mode if you're doing an expanded search.
	
	
/****** Run a standard search ******/
	public function __construct
	(
		string $query					// <str> The text that has been searched for.
	,	string $custom = ""			// <str> The custom search to run.
	,	int $mode = 1				// <int> Special modes to set (default = still typing).
	): void							// RETURNS <void>
	
	// $search = new Search($query, [$custom], [$mode]);
	{
		// Check if we are searching for user handles
		if($custom == "users")
		{
			$this->runUsers($query); return;
		}
		
		// If we're loading the standard search options
		else if($custom == "")
		{
			// Check standard search functions
			$len = strlen($query);
			
			if($len == 0)
			{
				// Find users
				$this->results[] = array(
					"entry"		=> "Type @ to find users."
				,	"url_path"	=> ""
				,	"score"		=> 9
				);
				
				return;
			}
			
			else if($len == 1)
			{
				switch($query)
				{
					case "@":
						// Find users
						$this->results[] = array(
							"entry"		=> "Type @ to find users."
						,	"url_path"	=> ""
						,	"score"		=> 9
						);
				}
				
				return;
			}
			
			// Special Character Functionality
			// If you start with an '@' sign, do a unique search with the users table
			if($query[0] == "@")
			{
				$query = substr($query, 1);
				$this->runUsers($query);
				
				return;
			}
		}
		
		// Get Values
		$table = "search_entries" . ($custom == "" ? "" : "_" . Sanitize::variable($custom));
		$words = StringUtils::getWordList($query, "'");
		$matchQuery = Sanitize::variable($query, " '");
		$fullTextMode = "";
		
		// If the user is typing a query:
		if($mode == 1)
		{
			$lastWord = $words[count($words) - 1];
			
			if(strlen($lastWord) > 2)
			{
				$words[count($words) - 1] = $lastWord . '*';
				
				// This allows us to do wildcards on the last keyword
				$fullTextMode = " IN BOOLEAN MODE";
				
				// Prepare the match query
				$matchQuery = implode(" ", $words);
			}
		}
		
		// If we're running expanded mode (query expansion)
		else if($mode == 2)
		{
			// Set Search Type: Natural Language vs. Query Expansion
			$fullTextMode = strlen($query) < 22 ? " WITH QUERY EXPANSION" : "";
		}
		
		// Retrieve the most relevant search results
		$this->results = Database::selectMultiple("SELECT entry_id, entry, extra_keywords, url_path, MATCH(entry, extra_keywords) AGAINST (?" . $fullTextMode . ") as score FROM " . $table . " WHERE MATCH(entry, extra_keywords) AGAINST (?" . $fullTextMode . ") ORDER BY score DESC LIMIT 5", array($matchQuery, $matchQuery));
	}
	
	
/****** Run a search through the users table ******/
	public function runUsers
	(
		string $query			// <str> The query string being searched.
	): void					// RETURNS <void>
	
	// $results = $search->runUsers($query);
	{
		$query = Sanitize::variable($query);
		
		$fetch = Database::selectMultiple("SELECT u.uni_id, u.display_name, u.handle FROM users_handles h INNER JOIN users u ON h.uni_id=u.uni_id WHERE h.handle LIKE ? LIMIT 5", array($query . "%"));
		
		foreach($fetch as $val)
		{
			$this->results[] = array(
				"entry"		=> $val['display_name'] . ' (<strong>@' . $val['handle'] . '</strong>)'
			,	"url_path"	=> "/" . $val['handle']
			,	"uni_id"	=> (int) $val['uni_id']
			,	"handle"	=> $val['handle']
			,	"score"		=> 5
			);
		}
	}
	
	
/****** Add a search entry ******/
	public static function addSearchEntry
	(
		string $entry			// <str> The entry (e.g. "How to change my password")
	,	string $extraKeywords	// <str> A list of additional relevant words and tags for the search entry.
	,	string $url			// <str> The URL to visit when the entry is clicked.
	,	string $custom = ""	// <str> The custom search table to use (default is standard).
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $results = Search::addSearchEntry($entry, $extraKeywords, $url, [$custom]);
	{
		// Prepare Values
		$table = "search_entries";
		
		// If we're building a custom search
		if($custom != "")
		{
			// Update Values
			$table .= "_" . Sanitize::variable($custom);
			
			// Make sure the custom table exists, if applicable
			Database::exec("
			CREATE TABLE IF NOT EXISTS `" . $table . "`
			(
				`entry_id`				int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
				
				`entry`					varchar(72)					NOT NULL	DEFAULT '',
				`extra_keywords`		varchar(150)				NOT NULL	DEFAULT '',
				
				`url_path`				varchar(72)					NOT NULL	DEFAULT '',
				
				PRIMARY KEY (`entry_id`),
				FULLTEXT (`entry`, `extra_keywords`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8
			");
		}
		
		// Make sure the entry doesn't exist
		if($entryID = (int) Database::selectValue("SELECT entry_id FROM " . $table . " WHERE entry=? LIMIT 1", array($entry)))
		{
			return Database::query("UPDATE " . $table . " SET entry=?, extra_keywords=?, url_path=? WHERE entry_id=? LIMIT 1", array($entry, $extraKeywords, $url, $entryID));
		}
		
		return Database::query("INSERT IGNORE INTO " . $table . " (entry, extra_keywords, url_path) VALUES (?, ?, ?)", array($entry, $extraKeywords, $url));
	}
	
	
/****** Add a custom search bar ******/
	public static function searchBar
	(
		string $name = "searchCustom"					// <str> The name of this custom search.
	,	string $siteURL = ""							// <str> The URL that the site will load to (default is local).
	,	string $scriptName = "search-bar"				// <str> The /ajax/{scriptName} to load.
	,	string $placeholder = "search anything . . ."	// <str> The placeholder for the search bar.
	,	string $funcOnKeyUp = ""						// <str> An extra JS function to run on key-up.
	,	string $defaultValue = ""						// <str> The default value for the search bar.
	,	string $classPrefix = "search"					// <str> The class to style the box with.
	): string											// RETURNS <str> HTML to insert a search bar.
	
	// Search::searchBar($name, $siteURL, $scriptName, $placeholder, [$funcOnKeyUp], [$defaultValue], [$classPrefix]);
	{
		// Prepare Custom Functions
		$funcOnKeyUp = $funcOnKeyUp != "" ? " " . Sanitize::variable($funcOnKeyUp) . '(event)' : '';
		
		return '
		<div id="search-' . $name . '" class="' . $classPrefix . 'Wrap">
			<div class="' . $classPrefix . 'WrapInput">
				<input id="' . $name . 'InputID" class="' . $classPrefix . 'Input"
					type="text" name="' . $name . 'Input" placeholder="' . $placeholder . '" autocomplete="off"
					onkeyup=\'searchAjax("' . $siteURL . '", "' . $scriptName . '", "' . $name . 'HoverID", "' . $name . 'InputID"); showSelectedSearch(event, "' . $name . 'HoverID");' . $funcOnKeyUp  . '\'
					onfocus=\'focusSearch(event, "' . $name . 'HoverID");\'
					onblur=\'blurSearch(event, "' . $name . 'HoverID");\'
					value="' . htmlspecialchars($defaultValue) . '"
					/>
			</div>
			<div id="' . $name . 'HoverID" class="' . $classPrefix . 'Hover"></div>
		</div>';
	}
	
	
/****** Add the official search engine bar ******/
	public static function searchEngineBar (
	): string			// RETURNS <str> HTML to insert a search bar.
	
	// Search::searchEngineBar();
	{
		// return self::searchBar($name, URL::search_unifaction_com(), $scriptName, $placeholder, $funcOnKeyUp, $defaultValue, $classPrefix);
		
		// Prepare Custom Functions
		$funcOnKeyUP = "";
		//$funcOnKeyUp = $funcOnKeyUp != "" ? " " . Sanitize::variable($funcOnKeyUp) . '(event)' : '';
		
		return '
		<div id="search-search" class="searchWrap">
			<div class="searchWrapInput">
				<input id="searchInputID" class="searchInput"
					type="text" name="searchInput" placeholder="search anything . . ." autocomplete="off"
					onkeyup=\'searchEngineAjax(); showSelectedSearch(event, "searchHoverID");' . $funcOnKeyUP  . '\'
					onfocus=\'focusSearch(event, "searchHoverID");\'
					onblur=\'blurSearch(event, "searchHoverID");\'
					/>
			</div>
			<div id="searchHoverID" class="searchHover"></div>
		</div>';
	}
	
	
/****** Add a search bar for user handles ******/
	public static function searchBarUserHandle
	(
		string $name = "userHandle"		// <str> The name of the search bar.
	,	string $siteURL = ""				// <str> The URL of the site to connect to.
	,	string $funcOnKeyUp = ""			// <str> An extra JS function to run on key-up.
	,	string $defaultValue = ""			// <str> The default value for the search bar.
	,	string $classPrefix = "search"		// <str> The class to style the box with.
	,	string $placeholder = "User . . ."	// <str> The placeholder for the search bar.
	): string								// RETURNS <str> HTML to insert a search bar (for looking up users).
	
	// Search::searchBarUserHandle([$name], [$siteURL], [$funcOnKeyUp], [$defaultValue], [$classPrefix], [$placeholder]);
	{
		if($siteURL == "")
		{
			$siteURL = URL::unifaction_com();
		}
		
		return self::searchBar($name, $siteURL, "search-user-handle", $placeholder, $funcOnKeyUp, $defaultValue, $classPrefix);
	}
	
	
}