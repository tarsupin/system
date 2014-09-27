<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------------
------ About the ModuleRelated Plugin ------
---------------------------------------------

When you view an article or content entry, there are often many other sets of content or articles that can be associated with it. This section allows you to curate that list to maxmimize the relevance (rather than trying to rely on an algorithm).

*/

abstract class ModuleRelated {
	
	
/****** Plugin Variables ******/
	public static $type = "Related";			// <str>
	
	
/****** Get the list of related articles ******/
	public static function get
	(
		$contentID		// <int> The ID of the content entry.
	,	$limit = 5		// <int> The number of related content entries to show.
	)					// RETURNS <int:[str:mixed]> the data array of each content entry for a related content slot.
	
	// $relatedArticles = ModuleRelated::get($contentID, [$limit]);
	{
		return Database::selectMultiple("SELECT c.id, c.title, c.url_slug, c.thumbnail FROM content_related r INNER JOIN content_entries c ON r.related_id=c.id WHERE r.content_id=? AND c.thumbnail != ? LIMIT " . ($limit + 0), array($contentID, ""));
	}
	
	
/****** Draw the form for this module ******/
	public static function draw
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleRelated::draw($formClass);
	{
		// Delete Relative Content, if applicable
		if(isset($_GET['delRel']))
		{
			Database::query("DELETE FROM content_related WHERE content_id=? AND related_id=? LIMIT 1", array($formClass->contentID, $_GET['delRel']));
		}
		
		// Get the related content currently associated with this entry
		$relatedArticles = self::get($formClass->contentID, 10);
		
		// Display the Form
		echo '
		<div style="margin-top:22px;">
			<span style="font-weight:bold;">Related Posts:</span><br />';
		
		// Provide a list of existing content entries that are associated with this one
		if($relatedArticles)
		{
			echo '<div style="margin-bottom:8px;">';
			
			foreach($relatedArticles as $art)
			{
				echo '
				<div class="form-rel-art"><div class="form-rel-del"><a href="' . $formClass->baseURL . '?id=' . $formClass->contentID . '&delRel=' . $art['id'] . '">X</a></div><a href="' . $formClass->baseURL . '?id=' . ($art['id'] + 0) . '" target="_new"><img src="' . $art['thumbnail'] . '" /></a><br />' . $art['title'] . '</div>';
			}
			
			echo '</div>';
		}
		
		// Add a related content article by it's URL slug, relative to the base domain
		// You can insert a direct domain link here as well.
		echo '
			<p>
				<input type="text" name="rel_url" value="' . (isset($_POST['rel_url']) ? Sanitize::url($_POST['rel_url']) : '') . '" placeholder="URL of related post . . ." size="42" maxlength="100" autocomplete="off" tabindex="10" /> <input type="submit" name="add_rel_url" value="Add Related Post" />
			</p>
		</div>';
	}
	
	
/****** Run Behavior Checks ******/
	public static function behavior
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <void>
	
	// ModuleRelated::behavior($formClass);
	{
		// Delete a related entry, if applicable
		if(isset($_GET['delRel']))
		{
			self::delete($formClass->contentID, (int) $_GET['delRel']);
		}
	}
	
	
/****** Run the interpreter for this module ******/
	public static function interpret
	(
		$formClass			// <mixed> The class data.
	)						// RETURNS <void>
	
	// ModuleRelated::interpret($formClass);
	{
		// Add a related post
		if(isset($_POST['rel_url']) and $_POST['rel_url'])
		{
			if(self::createByURL($formClass->contentID, $_POST['rel_url']))
			{
				Alert::success("Content Associated", "The related content has been associated.");
			}
		}
	}
	
	
/****** Show the widget for the related content ******/
	public static function widget
	(
		$contentID		// <int> The ID of the content entry.
	,	$limit = 5		// <int> The number of related content entries to show.
	,	$sidePos = 20	// <int> The position in the sidebar to show this widget in.
	)					// RETURNS <void>
	
	// ModuleRelated::widget($contentID, [$limit], [$sidePos]);
	{
		// Retrieve the related articles
		if(!$relatedArticles = self::get($contentID, $limit))
		{
			return;
		}
		
		// Document List
		$html = '
		<div class="panel-box">
			<a href="#" class="panel-head">Related Content</a>
			<div style="padding:0px 16px 0px 16px;">';
		
		foreach($relatedArticles as $art)
		{
			$html .= '
			<div class="related-article"><a href="' . $art['url_slug'] . '"><img src="' . $art['thumbnail'] . '" /></a><div class="rel-title">' . $art['title'] . '</div></div>';
		}
		
		$html .= '
			</div>
		</div>';
		
		WidgetLoader::add("SidePanel", ($sidePos + 0), $html);
	}
	
	
/****** Associate a content entry with another ******/
	public static function create
	(
		$contentID		// <int> The ID of the content entry.
	,	$relatedID		// <int> The ID of the related content entry to associate.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleRelated::create($contentID, $relatedID);
	{
		return Database::query("REPLACE INTO content_related (content_id, related_id) VALUES (?, ?)", array($contentID, $relatedID));
	}
	
	
/****** Remove associations from a content entry ******/
	public static function delete
	(
		$contentID		// <int> The ID of the content entry.
	,	$relatedID = 0	// <int> The ID of the related content entry to disassociate.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleRelated::delete($contentID, [$relatedID]);
	{
		if($relatedID)
		{
			return Database::query("DELETE FROM content_related WHERE content_id=? AND related_id=? LIMIT 1", array($contentID, $relatedID));
		}
		
		return Database::query("DELETE FROM content_related WHERE content_id=?", array($contentID));
	}
	
	
/****** Associate a content entry with another by a URL string ******/
	public static function createByURL
	(
		$contentID		// <int> The ID of the content entry.
	,	$url			// <str> The URL to use for determining the content entry.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleRelated::createByURL($contentID, $url);
	{
		$path = "";
		
		// If we can't find any instances of domain values, assume the value is a direct slug
		if(strpos($url, ".") === false)
		{
			$path = $url;
		}
		
		// Parse the URL to get the path (slug)
		else
		{
			$parsedURL = URL::parse($url, true);
			
			$path = $parsedURL['path'];
		}
		
		// Attempt to find the ID of the related content based on the path located
		if($relatedID = (int) Database::selectValue("SELECT content_id FROM content_by_url WHERE url_slug=? LIMIT 1", array($path)))
		{
			return self::create($contentID, $relatedID);
		}
		
		return false;
	}
	
	
/****** Purge this module from a content entry ******/
	public static function purge
	(
		$contentID		// <int> The ID of the content entry to purge from.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleRelated::purge($contentID);
	{
		return Database::query("DELETE FROM content_related WHERE content_id=? LIMIT 1", array($contentID));
	}
	
}
