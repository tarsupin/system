<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the ModuleSearch Plugin ------
-------------------------------------------

The ModuleSearch Plugin is designed to enable much more advanced searching and filtering options for content. By associating special filters with the content, you can create a database of information that you can call upon by multiple data points.

For example, let's say you wanted to build a site where you could track a database of historical people. The people could be filtered by occupation, major personality traits, gender, race, and so forth. This allows people to search the system much more effectively.


-------------------------------
------ Search Archetypes ------
-------------------------------

Some sites might want multiple types of filters to apply to different types of information. For example, a roleplaying site may create multiple types of content: NPCs, places, magical items, quests, etc.

Obviously, each of these content types will have different filters applied to them. For example, magical items might have value, rarity, magical sphere, setting, etc. However, the "quests" archetype would have very different filters such as difficulty, type of quest, etc.

Therefore, "archetypes" are used to indicate these different filters. All filters belong to a specific archetype. So the magical item filters would all fit into the "magical-items" archetype, for example.

Whenever using the ModuleSearch plugin, you MUST indicate what archetype is being used. Otherwise, it will not operate.


------------------------------------------
------ Setting Up Search Archetypes ------
------------------------------------------

To create the search archetypes and options, you can use the ::createFilterOption() method, which automatically generates the necessary archetypes (if they're not set) for each option you create.

The setup looks like this:
	
	// The General Setup
	ModuleSearch::createFilterOption($archetype, $filterName, $value, $humanTitle, $filterType);
	
	// Example of a "Magic Items: Power Level" filter
	ModuleSearch::createFilterOption("magic-items", "power", "powerful", "Powerful", ModuleSearch::FILTER_SINGLE);
	ModuleSearch::createFilterOption("magic-items", "power", "moderate", "Moderate", ModuleSearch::FILTER_SINGLE);
	ModuleSearch::createFilterOption("magic-items", "power", "weak", "Weak", ModuleSearch::FILTER_SINGLE);
	
	// This would create a search archetype called "Magic Items" that has a filter called "Power Level":
	<select name="power-level">
		<option value="">-- Any --</option>
		<option value="powerful">Powerful</option>
		<option value="moderate">Moderate</option>
		<option value="weak">Weak</option>
	</select>
	
	// Example of an "People: By Occupation" filter
	ModuleSearch::createFilterOption("people", "occupation", "actor", "Actor", ModuleSearch::FILTER_MULTI);
	ModuleSearch::createFilterOption("people", "occupation", "artist", "Artist", ModuleSearch::FILTER_MULTI);
	ModuleSearch::createFilterOption("people", "occupation", "composer", "Composer", ModuleSearch::FILTER_MULTI);
	ModuleSearch::createFilterOption("people", "occupation", "musician", "Musician", ModuleSearch::FILTER_MULTI);
	ModuleSearch::createFilterOption("people", "occupation", "singer", "Singer", ModuleSearch::FILTER_MULTI);
	
	
You will notice there are multiple types of search options that you can select from. These include:
	
	1. "Single-Option Filter Type", or "ModuleSearch::FILTER_SINGLE"
	
		This type means that you are only able to select ONE option (from a dropdown). Conversely, the entry can only fit into one option. An example of this would be birth month. Someone could not both be born in August AND September. They MUST be set to one.
	
	2. "Choice-Option Filter Type" or "ModuleSearch::FILTER_CHOICE"
	
		This type allows you to select any number of options within the same group to filter out options, but each item will only belong to ONE of them. They appear as checkbox options, that you can toggle on or off.
		
		An example of this type would be, "Which eras would you like to filter: Modern, Post-Apocalyptic, Futuristic, Ancient". Any eras that you select will be listed in the search filter.
		
		Note that the entries cannot be set into multiple eras (in this example). I must fit into ONE of the options.
	
	3. "Multi-Option Filter Type" or "ModuleSearch::FILTER_MULTI"
	
		This type is identical to FILTER_CHOICE, except that entries can belong to MULTIPLE versions of these rather than just one.
		
		For example, if you're looking for people, you might want to search for Musicians and Actors. However, some people might be BOTH Musicians and Actors, and therefore they may fit into both categories.
		
------------

// Display Search Widget
list($singleFilters, $choiceFilters, $multiFilters) = ModuleSearch::getFilterData($archetype);
$widgetHTML = ModuleSearch::widget($baseURL, $singleFilters, $choiceFilters, $multiFilters);
WidgetLoader::add("SidePanel", 12, $widgetHTML);

*/

abstract class ModuleSearch {
	
	
/****** Plugin Variables ******/
	public static string $type = "Search";			// <str>
	
	// Values handled internally
	public static array <int, int> $contentIDs = array();	// <int:int> A list of ContentIDs that were found by search.
	
	
/****** Plugin Variables ******/
	const FILTER_SINGLE = 1;		// Indicates the "single-option" filter type
	const FILTER_CHOICE = 2;		// Indicates the "choice option" filter type
	const FILTER_MULTI = 3;			// Indicates the "multi-option" filter type
	
	
/****** Get the data of the search filters for a particular archetype ******/
	public static function getFilterData
	(
		string $archetype		// <str> The type of archetype to extract filter data from.
	): array <int, array<str, array>>					// RETURNS <int:[str:array]> The filter data that was recovered.
	
	// list($singleFilters, $choiceFilters, $multiFilters) = ModuleSearch::getFilterData($archetype);
	{
		// Prepare Values
		$singleFilters = array();
		$choiceFilters = array();
		$multiFilters = array();
		
		// Gather the Filter Options
		if(!$results = Database::selectMultiple("SELECT f.filter_name, f.filter_type, o.hashtag, o.title FROM content_search_filters f INNER JOIN content_search_filter_opts o ON f.id=o.filter_id WHERE f.archetype=? ORDER BY f.filter_name", array($archetype)))
		{
			return array(array(), array(), array());
		}
		
		foreach($results as $res)
		{
			switch($res['filter_type'])
			{
				// If this is a "single-option-only" filter
				case self::FILTER_SINGLE:
					$singleFilters[$res['filter_name']][$res['hashtag']] = $res['title']; continue;
				
				// If this is a "choice option" filter
				case self::FILTER_CHOICE:
					$choiceFilters[$res['filter_name']][$res['hashtag']] = $res['title']; continue;
					
				// If this is a "multi-option" filter
				case self::FILTER_MULTI:
					$multiFilters[$res['filter_name']][$res['hashtag']] = $res['title']; continue;
			}
		}
		
		return array($singleFilters, $choiceFilters, $multiFilters);
	}
	
	
/****** Search the available content on the system ******/
	public static function search
	(
		array <str, array<str, str>> $singleFilters		// <str:[str:str]> The single-option filters involved in this search.
	,	array $choiceFilters		// <array> The choice-option filters involved in this search.
	,	array $multiFilters		// <array> The multi-option filters involved in this search.
	,	bool $boolMode = true	// <bool> TRUE if you are using boolean mode (forces each entry to be used)
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::search($singleFilters, $choiceFilters, $multiFilters, [$boolMode]);
	{
		// Make sure the appropriate information was sent
		if(!Form::submitted(SITE_HANDLE . "-ctb"))
		{
			return false;
		}
		
		// Return all entries in this archetype
		if(!$hashtags = ModuleSearch::getSearchedHashtags($singleFilters, $choiceFilters, $multiFilters))
		{
			return false;
		}
		
		// Prepare Values
		self::$contentIDs = array();
		$hashtagStr = "";
		$hashtagPrefix = ($boolMode ? " +" : " ");
		
		foreach($hashtags as $hashtag)
		{
			$hashtagStr .= $hashtagPrefix . $hashtag;
		}
		
		// Retrieve the list of entries that match the search
		$results = Database::selectMultiple("SELECT content_id FROM content_search WHERE MATCH(hashtags) AGAINST (?" . ($boolMode ? ' IN BOOLEAN MODE' : '') . ")", array($hashtagStr));
		
		foreach($results as $result)
		{
			self::$contentIDs[] = (int) $result['content_id'];
		}
		
		return true;
	}
	
	
/****** Display the Search Form ******/
	public static function widget
	(
		string $baseURL			// <str> The base URL to return to.
	,	array <str, array<str, str>> $singleFilters		// <str:[str:str]> The single-option filters involved in this search.
	,	array $choiceFilters		// <array> The choice-option filters involved in this search.
	,	array $multiFilters		// <array> The multi-option filters involved in this search.
	): string						// RETURNS <str> HTML
	
	// $widgetHTML = ModuleSearch::widget($baseURL, $singleFilters, $choiceFilters, $multiFilters);
	// WidgetLoader::add("SidePanel", 12, $widgetHTML);
	{
		$html = '
		<div class="panel-box">
			<form action="' . $baseURL . '" method="post">' . Form::prepare(SITE_HANDLE . "-ctb") . '
			<a href="#" class="panel-head">Search Filters<span class="icon-circle-right nav-arrow"></a>
			<div style="padding:0px 16px 8px 16px;">';
			
		// Show the Single Filters (ones that have only one option)
		foreach($singleFilters as $filter => $filterData)
		{
			$html .= '
			<div style="padding-bottom:8px;">
				<select name="' . $filter . '">
					<option value="">-- ' . ucwords(str_replace("-", " ", $filter)) . ' --</option>';
			
			foreach($filterData as $hashtag => $title)
			{
				$html .= '
					<option value="' . $hashtag . '"' . ((isset($_POST[$filter]) and $hashtag == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
			}
			
			$html .= '
				</select>
			</div>';
		}
		
		// Show the Choice Filters
		foreach($choiceFilters as $filter => $filterData)
		{
			$html .= '
			<div style="padding-bottom:8px;">
				<select name="' . $filter . '">
					<option value="">-- ' . ucwords(str_replace("-", " ", $filter)) . ' --</option>';
			
			foreach($filterData as $hashtag => $title)
			{
				$html .= '
					<option value="' . $hashtag . '"' . ((isset($_POST[$filter]) and $hashtag == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
			}
			
			$html .= '
				</select>
			</div>';
		}
		
		// Show the Multi-Select Filters
		foreach($multiFilters as $filter => $filterData)
		{
			$html .= '
			<div style="padding-bottom:8px;">
				<span style="font-weight:bold;">' . ucwords(str_replace("-", " ", $filter)) . ':</span><br />';
			
			foreach($filterData as $hashtag => $title)
			{
				$html .= '
				<div><input type="checkbox" name="' . $filter . '[' . $hashtag . ']"' . (isset($_POST[$filter][$hashtag]) ? ' checked' : '') . ' /> ' . $title . "</div>";
			}
			
			$html .= '
			</div>';
		}
		
		$html .= '
			<div><input type="submit" name="submit" value="Search" /></div>
			</div>
			</form>
		</div>';
		
		return $html;
	}
	
	
/****** Draw the form for this module ******/
	public static function draw
	(
		mixed $formClass		// <mixed> The form class.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// ModuleSearch::draw($formClass);
	{
		return;
	}
	
	
/****** Update a content entry's filters ******/
	public static function interpret
	(
		mixed $formClass		// <mixed> The class data.
	): void					// RETURNS <void>
	
	// ModuleSearch::interpret($formClass);
	{
		// If the content is official, we can add the search data
		if($formClass->contentData['status'] >= Content::STATUS_OFFICIAL)
		{
			// Get the array of hashtags associated with the content entry
			$hashtags = ModuleHashtags::get($formClass->contentID);
			
			$hashtagStr = implode(" ", $hashtags);
			
			// Update the search system with the hashtags for that entry
			ModuleSearch::updateEntryFilters($formClass->contentID, $hashtagStr);
			
			// Run the live update for this system
			ModuleSearch::liveSubmission($formClass->contentID);
		}
		else
		{
			ModuleSearch::guestSubmission($formClass->contentID);
		}
	}
	
	
/****** Handler for posting live submissions that involve search filters ******/
	public static function liveSubmission
	(
		int $contentID		// <int> The ID of the content entry to update.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::liveSubmission($contentID);
	{
		// Get the drafted hashtags for this submission
		if(!$hashtagStr = Database::selectValue("SELECT hashtags FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		return Database::query("REPLACE INTO content_search (content_id, hashtags) VALUES (?, ?)", array($contentID, $hashtagStr));
	}
	
	
/****** Handler for posting guest submissions that involve search filters ******/
	public static function guestSubmission
	(
		int $contentID		// <int> The ID of the content entry to update.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::guestSubmission($contentID);
	{
		return Database::query("DELETE FROM content_search WHERE content_id=? LIMIT 1", array($contentID));
	}
	
	
/****** Update a content entry's filters ******/
	public static function updateEntryFilters
	(
		int $contentID		// <int> The ID of the content entry to update.
	,	string $hashtagStr		// <str> The hashtag string.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::updateEntryFilters($contentID, $hashtags);
	{
		return Database::query("REPLACE INTO content_search_draft (content_id, hashtags) VALUES (?, ?)", array($contentID, $hashtagStr));
	}
	
	
/****** Get a content entry's filters ******/
	public static function getContentHashtags
	(
		int $contentID		// <int> The ID of the content entry to get hashtags from.
	): array <int, str>					// RETURNS <int:str> The hashtags for the content.
	
	// $hashtags = ModuleSearch::getContentHashtags($contentID);
	{
		// Get the search hashtags
		if(!$results = Database::selectOne("SELECT hashtags FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return array();
		}
		
		// Extract the hashtags
		$hashtags = explode(" ", $results['hashtags']);
		
		// Return the Values
		return $hashtags;
	}
	
	
/****** Obtain the hashtags posted from a search ******/
	public static function getSearchedHashtags
	(
		array $singleFilters		// <array> The single-option filters to check.
	,	array $choiceFilters		// <array> The choice-option filters to check.
	,	array $multiFilters		// <array> The multi-option filters to check.
	): array <int, str>						// RETURNS <int:str> The resulting hashtags that were searched.
	
	// $hashtags = ModuleSearch::getSearchedHashtags($singleFilters, $choiceFilters, $multiFilters);
	{
		// Retrieve the list of content entries
		$hashtags = array();
		
		foreach($singleFilters as $filter => $filterData)
		{
			if(isset($_POST[$filter]) and $_POST[$filter])
			{
				$hashtags[] = $_POST[$filter];
			}
		}
		
		foreach($choiceFilters as $filter => $filterData)
		{
			if(isset($_POST[$filter]) and $_POST[$filter])
			{
				$hashtags[] = $_POST[$filter];
			}
		}
		
		foreach($multiFilters as $filter => $filterData)
		{
			foreach($filterData as $hashtag => $val)
			{
				if(isset($_POST[$filter][$hashtag]))
				{
					$hashtags[] = $hashtag;
				}
			}
		}
		
		return $hashtags;
	}
	
	
/****** Get the ID of a specific filter ******/
	public static function getFilterID
	(
		string $archetype		// <str> The type of archetype on the filter to retrieve.
	,	string $filterName		// <str> The name of the filter to retrieve.
	): int					// RETURNS <int> The filter ID, or 0 on failure.
	
	// $filterID = ModuleSearch::getFilterID($archetype, $filterName);
	{
		return (int) Database::selectValue("SELECT id FROM content_search_filters WHERE archetype=? AND filter_name=? LIMIT 1", array($archetype, $filterName));
	}
	
	
/****** Create a Search Filter Option ******/
	public static function createFilterOption
	(
		string $archetype			// <str> The archetype to add this filter to.
	,	string $filterName			// <str> The name of the filter to create.
	,	string $hashtag			// <str> The hashtag to assign.
	,	string $title				// <str> The human-readable form of the hashtag.
	,	int $filterType = 1		// <int> The filter type to use (e.g. ModuleSearch::FILTER_SINGLE).
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::createFilterOption($archetype, $filterName, $hashtag, $title, [$filterType]);
	{
		// Check if the Filter already exists
		if(!$filter = Database::selectOne("SELECT id, filter_type FROM content_search_filters WHERE archetype=? AND filter_name=? LIMIT 1", array($archetype, $filterName)))
		{
			// Attempt to create the filter before proceeding with adding the option
			if(!Database::query("INSERT INTO content_search_filters (archetype, filter_name, filter_type) VALUES (?, ?, ?)", array($archetype, $filterName, $filterType)))
			{
				return false;
			}
			
			// Once the filter has been created - reattempt to retrieve it
			if(!$filter = Database::selectOne("SELECT id, filter_type FROM content_search_filters WHERE archetype=? AND filter_name=? LIMIT 1", array($archetype, $filterName)))
			{
				return false;
			}
		}
		
		return Database::query("REPLACE INTO content_search_filter_opts (filter_id, hashtag, title) VALUES (?, ?, ?)", array((int) $filter['id'], $hashtag, $title));
	}
	
	
/****** Delete a Search Filter (and all options associated with it) ******/
	public static function deleteFilter
	(
		string $archetype			// <str> The archetype to add this filter to.
	,	string $filterName			// <str> The name of the filter to create.
	,	string $hashtag			// <str> The hashtag to assign.
	): bool						// RETURNS <bool> TRUE if the value doesn't exist, FALSE on failure.
	
	// ModuleSearch::deleteFilter($archetype, $filterName, $hashtag);
	{
		// Get the Filter
		if($filterID = ModuleSearch::getFilterID($archetype, $filterName))
		{
			// Delete the filter options
			if(Database::query("DELETE FROM content_search_filter_opts WHERE filter_id=?", array($filterID)))
			{
				// Delete the filter
				return Database::query("DELETE FROM content_search_filters WHERE id=? LIMIT 1", array($filterID));
			}
		}
		
		return true;
	}
	
	
/****** Delete a Search Filter Option ******/
	public static function deleteFilterOption
	(
		string $archetype			// <str> The archetype to add this filter to.
	,	string $filterName			// <str> The name of the filter to create.
	,	string $hashtag			// <str> The hashtag to assign.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::deleteFilter($archetype, $filterName, $hashtag);
	{
		// Get the Filter
		if($filterID = ModuleSearch::getFilterID($archetype, $filterName))
		{
			return Database::query("DELETE FROM content_search_filter_opts WHERE filter_id=? AND hashtag=? LIMIT 1", array($filterID, $hashtag));
		}
	}
	
	
/****** Get a list of available search archetypes ******/
	public static function getArchetypes (
	): array <int, str>						// RETURNS <int:str> A list of available archetypes.
	
	// $archetypeList = ModuleSearch::getArchetypes();
	{
		$list = array();
		
		$results = Database::selectMultiple("SELECT DISTINCT archetype FROM content_search_filters", array());
		
		foreach($results as $res)
		{
			$list[] = $res['archetype'];
		}
		
		return $list;
	}
	
	
/****** Check if an archetype exists ******/
	public static function archetypeExists
	(
		string $archetype			// <str> The archetype to add this filter to.
	): bool						// RETURNS <bool> TRUE if the archetype exists, FALSE if not.
	
	// ModuleSearch::archetypeExists($archetype);
	{
		return (bool) Database::selectValue("SELECT archetype FROM content_search_filters WHERE archetype=? LIMIT 1", array($archetype));
	}
	
	
/****** Update an archetype setting on a content entry ******/
	public static function updateArchetype
	(
		int $contentID			// <int> The ID of the content entry to update the archetype of.
	,	string $archetype			// <str> The archetype to add this filter to.
	): bool						// RETURNS <bool> TRUE if the archetype exists, FALSE if not.
	
	// ModuleSearch::updateArchetype($contentID, $archetype);
	{
		$contentData = Content::get($contentID);
		
		if(isset($contentData['search_archetype']))
		{
			// If the content entry is already set to the desired archetype
			if($contentData['search_archetype'] == $archetype)
			{
				return true;
			}
			
			return Database::query("UPDATE content_entries SET search_archetype=? WHERE id=? LIMIT 1", array($archetype, $contentID));
		}
		
		return false;
	}
	
	
/****** Purge this module from a content entry ******/
	public static function purge
	(
		int $contentID		// <int> The ID of the content entry to purge from.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::purge($contentID);
	{
		if(!Database::query("DELETE FROM content_search WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		return Database::query("DELETE FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID));
	}
	
}