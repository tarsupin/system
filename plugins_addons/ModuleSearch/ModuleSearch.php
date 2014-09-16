<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

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
		
*/

abstract class ModuleSearch {
	
	
/****** Plugin Variables ******/
	public static $type = "Search";			// <str>
	
	
/****** Plugin Variables ******/
	const FILTER_SINGLE = 1;		// Indicates the "single-option" filter type
	const FILTER_CHOICE = 2;		// Indicates the "choice option" filter type
	const FILTER_MULTI = 3;			// Indicates the "multi-option" filter type
	
	
/****** Update a content entry's filters ******/
	public static function interpret
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <void>
	
	// ModuleSearch::interpret($formClass);
	{
		// Update the Search Archetype
		if(Form::submitted(SITE_HANDLE . "-modSearch-arch"))
		{
			$_POST['search_archetype'] = (isset($_POST['search_archetype']) ? Sanitize::variable($_POST['search_archetype'], "-") : '');
			
			if(Database::query("UPDATE content_entries SET search_archetype=? WHERE id=? LIMIT 1", array($_POST['search_archetype'], $formClass->contentID)))
			{
				// If the content is official, we can add the search data
				if($formClass->contentData['status'] < Content::STATUS_OFFICIAL)
				{
					Database::query("DELETE IGNORE FROM content_search WHERE content_id=? LIMIT 1", array($formClass->contentID));
				}
				
				// Display the success and load the appropriate page
				Alert::saveSuccess("Search Updated", "The Search Archetype has been set.");
				
				header("Location: " . $formClass->baseURL . "?action=meta&content=" . $formClass->contentID . "&t=" . self::$type); exit;
			}
		}
		
		// Update the Filters
		if(Form::submitted(SITE_HANDLE . "-modSearch-filt"))
		{
			if(!isset($formClass->contentData['search_archetype']))
			{
				return;
			}
			
			// Prepare Values
			$keywords = "";
			
			// Get the list of filters for this archetype
			list($singleFilters, $choiceFilters, $multiFilters) = self::getFilterData($formClass->contentData['search_archetype']);
			
			// Cycle through the list of single-option filters
			foreach($singleFilters as $filter => $value)
			{
				// Check if the post data included this value
				if(isset($_POST[$filter]))
				{
					$keywords .= " " . $_POST[$filter];
				}
			}
			
			// Cycle through the list of choice-option filters
			foreach($choiceFilters as $filter => $value)
			{
				// Check if the post data included this value
				if(isset($_POST[$filter]))
				{
					foreach($_POST[$filter] as $keyword => $active)
					{
						$keywords .= " " . $keyword;
					}
				}
			}
			
			// Cycle through the list of multi-option filters
			foreach($multiFilters as $filter => $value)
			{
				// Check if the post data included this value
				if(isset($_POST[$filter]))
				{
					foreach($_POST[$filter] as $keyword => $active)
					{
						$keywords .= " " . $keyword;
					}
				}
			}
			
			// Sanitize the keyword list
			$keywords = Sanitize::variable($keywords, " ");
			
			Database::startTransaction();
			
			// Update the keywords into your draft
			if($pass = Database::query("REPLACE INTO content_search_draft (content_id, keywords) VALUES (?, ?)", array($formClass->contentID, trim($keywords))))
			{
				if($formClass->contentData['status'] >= Content::STATUS_OFFICIAL)
				{
					$pass = Database::query("REPLACE INTO content_search (content_id, keywords) VALUES (?, ?)", array($formClass->contentID, $formClass->contentData['search_archetype'] . $keywords));
				}
				else
				{
					$pass = Database::query("DELETE IGNORE FROM content_search WHERE content_id=? LIMIT 1", array($formClass->contentID));
				}
			}
			if(Database::endTransaction($pass))
			{
				Alert::success("Filters Updated", "The Search Filters have been updated.");
			}
		}
	}
	
	
/****** Draw the form for this module ******/
	public static function drawForm
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleSearch::drawForm($formClass);
	{
		// Get Search Archetypes
		if(!$archetypeList = ModuleSearch::getArchetypes())
		{
			echo "There are no search archetypes available on this system."; return;
		}
		
		// Assign a Search Archetype
		echo '
		<div style="margin-top:22px;">
		<form class="uniform" action="' . $formClass->baseURL . '?action=meta&content=' . ($formClass->contentID + 0) . '&t=' . self::$type . '" method="post">' . Form::prepare(SITE_HANDLE . "-modSearch-arch") . '
			<h3>Search Type</h3>
			<p>
				<select name="search_archetype">
					<option value="">-- None Selected --</option>';
			
			foreach($archetypeList as $arch)
			{
				echo '
				<option value="' . $arch . '"' . ($formClass->contentData['search_archetype'] == $arch ? ' selected' : '') . '>' . ucwords(str_replace("-", " ", $arch)) . '</option>';
			}
			
			echo '</select>
			<input type="submit" name="submit" value="Set" /></p>
		</form>
		</div>';
		
		// If you don't have a search archetype selected, end here
		if(!$formClass->contentData['search_archetype'])
		{
			return;
		}
		
		// Get the content entry's keywords
		$keys = self::getContentKeys($formClass->contentID);
		
		// Get the list of filters
		list($singleFilters, $choiceFilters, $multiFilters) = self::getFilterData($formClass->contentData['search_archetype']);
		
		// Display the search filter form
		echo '
		<div style="margin-top:22px;">
			<h3>Search Filters</h3>
			<form class="uniform" action="' . $formClass->baseURL . '?action=meta&content=' . ($formClass->contentID + 0) . '&t=' . self::$type . '" method="post">' . Form::prepare(SITE_HANDLE . "-modSearch-filt");
		
		// Show the Single Filters (ones that have only one option)
		foreach($singleFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . str_replace("-", " ", ucwords($filter)) . ':</span>
				<select name="' . $filter . '">
					<option value="">-- Select an Option --</option>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
					<option value="' . $keyword . '"' . (in_array($keyword, $keys) ? ' selected' : '') . '>' . $title . '</option>';
			}
			
			echo '
				</select>
			</p>';
		}
		
		// Show the Choice-Select Filters
		foreach($choiceFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . str_replace("-", " ", ucwords($filter)) . ':</span>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
				<input type="checkbox" name="' . $filter . '[' . $keyword . ']"' . (in_array($keyword, $keys) ? ' checked' : '') . ' /> ' . $title;
			}
			
			echo '
			</p>';
		}
		
		// Show the Multi-Select Filters
		foreach($multiFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . str_replace("-", " ", ucwords($filter)) . ':</span>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
				<input type="checkbox" name="' . $filter . '[' . $keyword . ']"' . (in_array($keyword, $keys) ? ' checked' : '') . ' /> ' . $title;
			}
			
			echo '
			</p>';
		}
		
		echo '
			<p><input type="submit" name="submit" value="Update Filters" /></p>
			</form>
		</div>';
	}
	
	
/****** Handler for posting live submissions that involve search filters ******/
	public static function liveSubmission
	(
		$contentID		// <int> The ID of the content entry to update.
	,	$archetype		// <str> The search archetype that the content uses.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::liveSubmission($contentID, $archetype);
	{
		// Get the drafted keywords for this submission
		if(!$keywords = Database::selectValue("SELECT keywords FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		return Database::query("REPLACE INTO content_search (content_id, keywords) VALUES (?, ?)", array($contentID, $archetype . " " . $keywords));
	}
	
	
/****** Handler for posting guest submissions that involve search filters ******/
	public static function guestSubmission
	(
		$contentID		// <int> The ID of the content entry to update.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::guestSubmission($contentID);
	{
		return Database::query("DELETE FROM content_search WHERE content_id=? LIMIT 1", array($contentID));
	}
	
	
/****** Update a content entry's filters ******/
	public static function updateEntryFilters
	(
		$contentID		// <int> The ID of the content entry to update.
	,	$archetype		// <str> The archetype of this content.
	,	$keywords		// <str> The keywords.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::updateEntryFilters($contentID, $archetype, $keywords);
	{
		return Database::query("UPDATE content_search_draft SET keywords=? WHERE content_id=? LIMIT 1", array($archetype . " " . $keywords, $contentID));
	}
	
	
/****** Get a content entry's filters ******/
	public static function getContentKeys
	(
		$contentID		// <int> The ID of the content entry to get keywords from.
	)					// RETURNS <int:str> The keywords for the content.
	
	// $keys = ModuleSearch::getContentKeys($contentID);
	{
		// Get the search keys
		if(!$results = Database::selectOne("SELECT keywords FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return array();
		}
		
		// Extract the keys
		$keys = explode(" ", $results['keywords']);
		
		// Return the Values
		return $keys;
	}
	
	
/****** Obtain the keys posted from a search ******/
	public static function getSearchResultKeys
	(
		$singleFilters		// <array> The single-option filters to check.
	,	$choiceFilters		// <array> The choice-option filters to check.
	,	$multiFilters		// <array> The multi-option filters to check.
	)						// RETURNS <int:str> The resulting keys that were searched.
	
	// $keysSearched = ModuleSearch::getSearchResultKeys($singleFilters, $choiceFilters, $multiFilters);
	{
		// Retrieve the list of content entries
		$keys = array();
		
		foreach($singleFilters as $filter => $filterData)
		{
			if(isset($_POST[$filter]) and $_POST[$filter])
			{
				$keys[] = $_POST[$filter];
			}
		}
		
		foreach($choiceFilters as $filter => $filterData)
		{
			if(isset($_POST[$filter]) and $_POST[$filter])
			{
				$keys[] = $_POST[$filter];
			}
		}
		
		foreach($multiFilters as $filter => $filterData)
		{
			foreach($filterData as $key => $val)
			{
				if(isset($_POST[$filter][$key]))
				{
					$keys[] = $key;
				}
			}
		}
		
		return $keys;
	}
	
	
/****** Get the ID of a specific filter ******/
	public static function getFilterID
	(
		$archetype		// <str> The type of archetype on the filter to retrieve.
	,	$filterName		// <str> The name of the filter to retrieve.
	)					// RETURNS <int> The filter ID, or 0 on failure.
	
	// $filterID = ModuleSearch::getFilterID($archetype, $filterName);
	{
		return (int) Database::selectValue("SELECT id FROM content_search_filters WHERE archetype=? AND filter_name=? LIMIT 1", array($archetype, $filterName));
	}
	
	
/****** Get the data of the search filters for a particular archetype ******/
	public static function getFilterData
	(
		$archetype		// <str> The type of archetype to extract filter data from.
	)					// RETURNS <int:[str:array]> The filter data that was recovered.
	
	// list($singleFilters, $choiceFilters, $multiFilters) = ModuleSearch::getFilterData($archetype);
	{
		// Prepare Values
		$singleFilters = array();
		$choiceFilters = array();
		$multiFilters = array();
		
		// Gather the Filter Options
		if(!$results = Database::selectMultiple("SELECT f.filter_name, f.filter_type, o.keyword, o.title FROM content_search_filters f INNER JOIN content_search_filter_opts o ON f.id=o.filter_id WHERE f.archetype=? ORDER BY f.filter_name", array($archetype)))
		{
			return array(array(), array(), array());
		}
		
		foreach($results as $res)
		{
			switch($res['filter_type'])
			{
				// If this is a "single-option-only" filter
				case self::FILTER_SINGLE:
					$singleFilters[$res['filter_name']][$res['keyword']] = $res['title']; continue;
				
				// If this is a "choice option" filter
				case self::FILTER_CHOICE:
					$choiceFilters[$res['filter_name']][$res['keyword']] = $res['title']; continue;
					
				// If this is a "multi-option" filter
				case self::FILTER_MULTI:
					$multiFilters[$res['filter_name']][$res['keyword']] = $res['title']; continue;
			}
		}
		
		return array($singleFilters, $choiceFilters, $multiFilters);
	}
	
	
/****** Create a Search Filter Option ******/
	public static function createFilterOption
	(
		$archetype			// <str> The archetype to add this filter to.
	,	$filterName			// <str> The name of the filter to create.
	,	$keyword			// <str> The keyword to assign.
	,	$title				// <str> The human-readable form of the keyword.
	,	$filterType = 1		// <int> The filter type to use (e.g. ModuleSearch::FILTER_SINGLE).
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::createFilterOption($archetype, $filterName, $keyword, $title, [$filterType]);
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
		
		// Prepare a prefix to avoid name collisions
		$keyPrefix = substr($archetype, 0, 2) . substr($filterName, 0, 2) . "_";
		
		return Database::query("REPLACE INTO content_search_filter_opts (filter_id, keyword, title) VALUES (?, ?, ?)", array((int) $filter['id'], $keyPrefix . $keyword, $title));
	}
	
	
/****** Delete a Search Filter (and all options associated with it) ******/
	public static function deleteFilter
	(
		$archetype			// <str> The archetype to add this filter to.
	,	$filterName			// <str> The name of the filter to create.
	,	$keyword			// <str> The keyword to assign.
	)						// RETURNS <bool> TRUE if the value doesn't exist, FALSE on failure.
	
	// ModuleSearch::deleteFilter($archetype, $filterName, $keyword);
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
		$archetype			// <str> The archetype to add this filter to.
	,	$filterName			// <str> The name of the filter to create.
	,	$keyword			// <str> The keyword to assign.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::deleteFilter($archetype, $filterName, $keyword);
	{
		// Get the Filter
		if($filterID = ModuleSearch::getFilterID($archetype, $filterName))
		{
			return Database::query("DELETE FROM content_search_filter_opts WHERE filter_id=? AND keyword=? LIMIT 1", array($filter_id, $keyword));
		}
	}
	
	
/****** Get a list of available search archetypes ******/
	public static function getArchetypes (
	)						// RETURNS <int:str> A list of available archetypes.
	
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
		$archetype			// <str> The archetype to add this filter to.
	)						// RETURNS <bool> TRUE if the archetype exists, FALSE if not.
	
	// ModuleSearch::archetypeExists($archetype);
	{
		return (bool) Database::selectValue("SELECT archetype FROM content_search_filters WHERE archetype=? LIMIT 1", array($archetype));
	}
	
	
/****** Update an archetype setting on a content entry ******/
	public static function updateArchetype
	(
		$contentID			// <int> The ID of the content entry to update the archetype of.
	,	$archetype			// <str> The archetype to add this filter to.
	)						// RETURNS <bool> TRUE if the archetype exists, FALSE if not.
	
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
	
	
/****** Search the available content on the system ******/
	public static function search
	(
		$archetype			// <str> The archetype of this content.
	,	$keys				// <int:str> The single-use keywords to match with this system.
	,	$boolMode = true	// <bool> TRUE if you are using boolean mode (forces each entry to be used)
	)						// RETURNS <int:int> TRUE on success, FALSE on failure.
	
	// $contentIDs = ModuleSearch::search($archetype, $keys, [$boolMode]);
	{
		// Prepare Values
		$contentIDs = array();
		
		// Return all entries in this archetype
		if(!$keys)
		{
			$results = Database::selectMultiple("SELECT content_id FROM content_search WHERE MATCH(keywords) AGAINST (?)", array($archetype));
		}
		
		// Run Standard Search
		else
		{
			$keywords = "";
			$keyPrefix = ($boolMode ? " +" : " ");
			
			foreach($keys as $key)
			{
				$keywords .= $keyPrefix . $key;
			}
			
			// Retrieve the list of entries that match the search
			$results = Database::selectMultiple("SELECT content_id FROM content_search WHERE MATCH(keywords) AGAINST (?" . ($boolMode ? ' IN BOOLEAN MODE' : '') . ")", array($archetype . " " . $keywords));
		}
		
		foreach($results as $result)
		{
			$contentIDs[] = (int) $result['content_id'];
		}
		
		return $contentIDs;
	}
	
	
/****** Display the Search Form ******/
	public static function displayForm
	(
		$baseURL			// <str> The base URL to return to.
	,	$singleFilters		// <array> The single-option filters involved in this search.
	,	$choiceFilters		// <array> The choice-option filters involved in this search.
	,	$multiFilters		// <array> The multi-option filters involved in this search.
	)						// RETURNS <int:int> TRUE on success, FALSE on failure.
	
	// ModuleSearch::displayForm($baseURL, $singleFilters, $choiceFilters, $multiFilters);
	{
		echo '
		<form class="uniform" action="' . $baseURL . '" method="post">' . Form::prepare(SITE_HANDLE . "-ctb");
		
		// Show the Single Filters (ones that have only one option)
		foreach($singleFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . ucwords(str_replace("-", " ", $filter)) . ':</span>
				<select name="' . $filter . '">
					<option value="">-- Any --</option>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
					<option value="' . $keyword . '"' . ((isset($_POST[$filter]) and $keyword == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
			}
			
			echo '
				</select>
			</p>';
		}
		
		// Show the Choice Filters (can only select one, but the options can fit into multiple)
		foreach($choiceFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . ucwords(str_replace("-", " ", $filter)) . ':</span>
				<select name="' . $filter . '">
					<option value="">-- Any --</option>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
					<option value="' . $keyword . '"' . ((isset($_POST[$filter]) and $keyword == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
			}
			
			echo '
				</select>
			</p>';
		}
		
		// Show the Multi-Select Filters (can select multiple, and the entry can also fit into multiple)
		foreach($multiFilters as $filter => $filterData)
		{
			echo '
			<p>
				<span style="font-weight:bold;">' . ucwords(str_replace("-", " ", $filter)) . ':</span>';
			
			foreach($filterData as $keyword => $title)
			{
				echo '
				<input type="checkbox" name="' . $filter . '[' . $keyword . ']"' . (isset($_POST[$filter][$keyword]) ? ' checked' : '') . ' /> ' . $title;
			}
			
			echo '
			</p>';
		}
		
		echo '
			<p><input type="submit" name="submit" value="Search Library" /></p>
		</form>';
	}
	
	
/****** Display the Search Form ******/
	public static function widget
	(
		$baseURL			// <str> The base URL to return to.
	,	$singleFilters		// <array> The single-option filters involved in this search.
	,	$choiceFilters		// <array> The choice-option filters involved in this search.
	,	$multiFilters		// <array> The multi-option filters involved in this search.
	)						// RETURNS <str> HTML
	
	// $widgetHTML = ModuleSearch::widget($baseURL, $singleFilters, $choiceFilters, $multiFilters);
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
					<option value="">-- Any ' . ucwords(str_replace("-", " ", $filter)) . ' --</option>';
			
			foreach($filterData as $keyword => $title)
			{
				$html .= '
					<option value="' . $keyword . '"' . ((isset($_POST[$filter]) and $keyword == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
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
					<option value="">-- Any ' . ucwords(str_replace("-", " ", $filter)) . ' --</option>';
			
			foreach($filterData as $keyword => $title)
			{
				$html .= '
					<option value="' . $keyword . '"' . ((isset($_POST[$filter]) and $keyword == $_POST[$filter]) ? ' selected' : '') . '>' . $title . '</option>';
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
			
			foreach($filterData as $keyword => $title)
			{
				$html .= '
				<div><input type="checkbox" name="' . $filter . '[' . $keyword . ']"' . (isset($_POST[$filter][$keyword]) ? ' checked' : '') . ' /> ' . $title . "</div>";
			}
			
			$html .= '
			</div>';
		}
		
		$html .= '
			<div><input type="submit" name="submit" value="Filter Search" /></div>
			</div>
			</form>
		</div>';
		
		return $html;
	}
	
	
/****** Purge this module from a content entry ******/
	public static function purge
	(
		$contentID		// <int> The ID of the content entry to purge from.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleSearch::purge($contentID);
	{
		if(!Database::query("DELETE FROM content_search WHERE content_id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		return Database::query("DELETE FROM content_search_draft WHERE content_id=? LIMIT 1", array($contentID));
	}
	
}
