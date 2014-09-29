<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------------
------ About the ContentHashtags Plugin ------
----------------------------------------------


*/

abstract class ContentHashtags {
	
	
/****** Plugin Variables ******/
	
	
/****** Create a hashtag that this site will use ******/
	public static function create
	(
		string $hashtag	// <str> The official hashtag to assign to this site.
	,	string $title		// <str> The title of the hashtag.
	): bool				// RETURNS <bool> TRUE if the hashtag was assigned to the site successfully, FALSE on failure.
	
	// ContentHashtags::create($hashtag, $title);
	{
		return Database::query("REPLACE INTO content_site_hashtags (hashtag, title) VALUES (?, ?)", array($hashtag, $title));
	}
	
	
/****** Get Content Entries based on a Hashtag ******/
	public static function getEntries
	(
		string $hashtag		// <str> The hashtag to retrieve entries from.
	,	int $startPos = 0	// <int> The starting position to retrieve rows from.
	,	int $rows = 20		// <int> The number of rows to retrieve.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> The data associated with the content entry.
	
	// $entries = ContentHashtags::getEntries($hashtag, [$startPos], [$rows]);
	{
		return Database::selectMultiple("SELECT c.* FROM content_by_hashtag h INNER JOIN content_entries c ON h.content_id=c.id WHERE h.hashtag=? ORDER BY h.content_id DESC LIMIT " . ($startPos + 0) . ", " . ($rows + 0), array($hashtag));
	}
	
	
/****** Get Content ID's based on a Hashtag ******/
	public static function getEntryIDs
	(
		string $hashtag		// <str> The hashtag to retrieve entry ID's from.
	,	int $startPos = 0	// <int> The starting position to retrieve rows from.
	,	int $rows = 20		// <int> The number of rows to retrieve.
	): array <int, int>					// RETURNS <int:int> The data associated with the content entry.
	
	// $contentIDs = ContentHashtags::getEntryIDs($hashtag, [$startPos], [$rows]);
	{
		$contentIDs = array();
		
		$results = Database::selectMultiple("SELECT content_id FROM content_by_hashtag WHERE hashtag=? ORDER BY content_id DESC LIMIT " . ($startPos + 0) . ", " . ($rows + 0), array($hashtag));
		
		foreach($results as $result)
		{
			$contentIDs[] = (int) $result['content_id'];
		}
		
		return $contentIDs;
	}
	
	
/****** Tag an entry by a particular hashtag ******/
	public static function tagEntry
	(
		int $contentID		// <int> The content entry ID to tag.
	,	array <int, str> $hashtagList	// <int:str> The hashtags to set.
	): bool					// RETURNS <bool> TRUE if the entry was tagged, FALSE if not.
	
	// ContentHashtags::tagEntry($contentID, $hashtagList);
	{
		$success = true;
		
		foreach($hashtagList as $htag)
		{
			if(!Database::query("REPLACE INTO content_by_hashtag (hashtag, content_id) VALUES (?, ?)", array($htag, $contentID)))
			{
				$success = false;
			}
		}
		
		return $success;
	}
	
	
/****** Return a list of hashtags for this site ******/
	public static function hashtagList (
	): array <int, str>					// RETURNS <int:str> A list of available hashtags.
	
	// $hashtagList = ContentHashtags::hashtagList();
	{
		$hashList = array();
		
		$results = Database::selectMultiple("SELECT hashtag FROM content_site_hashtags ORDER BY hashtag", array());
		
		foreach($results as $res)
		{
			$hashList[] = $res['hashtag'];
		}
		
		return $hashList;
	}
	
	
/****** Return a Content Form Hashtag Dropdown (for selection purposes) ******/
	public static function hashtagFormDropdown
	(
		int $contentID			// <int> The ID of the content.
	,	string $selectedTag = ""	// <str> The hashtag to set as the default selection.
	): string						// RETURNS <str> An HTML select input filled by the site's hashtag options.
	
	// $dropdownHTML = ContentHashtags::hashtagFormDropdown($contentID, $selectedTag);
	{
		// Prepare Values
		$html = "";
		$selectedTag = strtolower($selectedTag);
		
		// Retrieve the list of categories
		$results = Database::selectMultiple("SELECT hashtag FROM content_hashtags WHERE content_id=? ORDER BY hashtag", array($contentID));
		
		foreach($results as $res)
		{
			$html .= '
			<option value="' . $res['hashtag'] . '"' . ($selectedTag == strtolower($res['hashtag']) ? ' selected' : '') . '>#' . $res['hashtag'] . '</option>';
		}
		
		return $html;
	}
	
	
/****** Return a Full-Site Hashtag Dropdown (for selection purposes) ******/
	public static function hashtagSiteDropdown
	(
		string $selectedTag = ""	// <str> The hashtag to set as the default selection.
	): string						// RETURNS <str> An HTML select input filled by the site's hashtag options.
	
	// $dropdownHTML = ContentHashtags::hashtagSiteDropdown($selectedTag);
	{
		// Prepare Values
		$html = "";
		$selectedTag = strtolower($selectedTag);
		
		// Retrieve the list of categories
		$results = Database::selectMultiple("SELECT hashtag, title FROM content_site_hashtags ORDER BY hashtag", array());
		
		foreach($results as $res)
		{
			$html .= '
			<option value="' . $res['hashtag'] . '"' . ($selectedTag == strtolower($res['hashtag']) ? ' selected' : '') . '>' . $res['title'] . ' (#' . $res['hashtag'] . ')</option>';
		}
		
		return $html;
	}
	
}