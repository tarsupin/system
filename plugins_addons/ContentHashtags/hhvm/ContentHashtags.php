<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------------
------ About the ContentHashtags Plugin ------
----------------------------------------------


*/

abstract class ContentHashtags {
	
	
/****** Plugin Variables ******/
	
	
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
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> The data associated with the content entry.
	
	// $entryIDs = ContentHashtags::getEntryIDs($hashtag, [$startPos], [$rows]);
	{
		return Database::selectMultiple("SELECT content_id FROM content_by_hashtag WHERE hashtag=? ORDER BY content_id DESC LIMIT " . ($startPos + 0) . ", " . ($rows + 0), array($hashtag));
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
	
	
/****** Return a Hashtag Dropdown (for selection purposes) ******/
	public static function hashtagDropdown 
	(
		string $selectedTag = ""	// <str> The hashtag to set as the default selection.
	): string						// RETURNS <str> An HTML select input filled by the site's hashtag options.
	
	// $dropdownHTML = ContentHashtags::hashtagDropdown($selectedTag);
	{
		// Prepare Values
		$html = "";
		$curGroup = "";
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