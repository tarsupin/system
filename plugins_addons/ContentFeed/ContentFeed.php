<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the ContentFeed Plugin ------
------------------------------------------

The ContentFeed plugin provides methods to display content feeds and otherwise interact with them.

*/

abstract class ContentFeed {
	
	
/****** Plugin Variables ******/
	public static $activeHashtag = "";		// <str> The hashtag for this page.
	public static $backTagTitle = "";		// <str> The title of the back tag.
	public static $backTagURL = "/";		// <str> The URL to go to when the bag tag is clicked.
	
	
/****** Prepare a page for handling a content feed ******/
	public static function prepare (
	)							// RETURNS <void> runs the appropriate preparation methods.
	
	// ContentFeed::prepare([$searchArchetype]);
	{
		// Prepare Header Handling
		Photo::prepareResponsivePage();
		
		Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" /><script src="' . CDN . '/scripts/content-system.js"></script>');
	}
	
	
/****** Get a list of entry IDs based on a list of hashtags ******/
	public static function getEntryIDsByHashtags
	(
		$hashtagList		// <int:str> An array of hashtags that we're going to pull entry IDs from.
	,	$startPage = 1		// <int> The starting page.
	,	$rowsPerPage = 15	// <int> The number of entries to return per page.
	)						// RETURNS <int:int> a list of the content IDs based on recent posts.
	
	// $contentIDs = ContentFeed::getEntryIDsByHashtags($hashtagList, [$startPage], [$rowsPerPage]);
	{
		$contentIDs = array();
		
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("hashtag" => $hashtagList));
		
		$getList = Database::selectMultiple("SELECT DISTINCT content_id FROM content_by_hashtag WHERE " . $sqlWhere . " ORDER BY content_id DESC LIMIT " . (($startPage - 1) * $rowsPerPage) . ", " . ($rowsPerPage + 0), $sqlArray);
		
		foreach($getList as $getID)
		{
			$contentIDs[] = (int) $getID['content_id'];
		}
		
		return $contentIDs;
	}
	
	
/****** Get a list of the recent entry IDs (such as for the home page) ******/
	public static function getRecentEntryIDs
	(
		$status = 0		// <int> The minimum status allowed for content entries.
	)					// RETURNS <int:int> a list of the content IDs based on recent posts.
	
	// $contentIDs = ContentFeed::getRecentEntryIDs([$status]);
	{
		// Prepare Values
		$status = $status == 0 ? Content::STATUS_OFFICIAL : $status;
		$contentIDs = array();
		
		// Retrieve the list of content entries
		$getList = Database::selectMultiple("SELECT id FROM content_entries WHERE status >= ? ORDER BY date_posted DESC LIMIT 0, 20", array($status));
		
		foreach($getList as $getID)
		{
			$contentIDs[] = (int) $getID['id'];
		}
		
		return $contentIDs;
	}
	
	
/****** Get a list of the the user's entry IDs ******/
	public static function getUserEntryIDs
	(
		$uniID				// <int> The UniID to get the list of posts from.
	,	$startPage = 1		// <int> The starting page.
	,	$rowsPerPage = 15	// <int> The number of entries to return per page.
	)						// RETURNS <int:int> a list of the content IDs based on the user's posts.
	
	// $contentIDs = ContentFeed::getUserEntryIDs($uniID, [$startPage], [$rowsPerPage]);
	{
		// Prepare Values
		$contentIDs = array();
		
		if($uniID == Me::$id or Me::$clearance >= 6)
		{
			$getList = Database::selectMultiple("SELECT content_id FROM content_by_user WHERE uni_id=? ORDER BY content_id DESC LIMIT " . (($startPage - 1) * $rowsPerPage) . ", " . ($rowsPerPage + 0), array($uniID));
		}
		else
		{
			$getList = Database::selectMultiple("SELECT u.content_id FROM content_by_user u INNER JOIN content_entries c ON u.content_id=c.id WHERE u.uni_id=? AND c.status >= ? ORDER BY u.content_id DESC LIMIT " . (($startPage - 1) * $rowsPerPage) . ", " . ($rowsPerPage + 0), array($uniID, Content::STATUS_OFFICIAL));
		}
		
		foreach($getList as $getID)
		{
			$contentIDs[] = (int) $getID['content_id'];
		}
		
		return $contentIDs;
	}
	
	
/****** Display the Feed Header ******/
	public static function displayHeader
	(
		$title			// <str> The header of this feed.
	,	$backTitle = ""	// <str> The title of the previous page (breadcrumb).
	,	$backURL = ""	// <str> The URL of the previous page (breadcrumb).
	)					// RETURNS <void> runs the appropriate preparation methods.
	
	// ContentFeed::displayHeader($title, $backTitle, $backURL);
	{
		echo '
		<div id="c-feed-head">';
		
		if($backURL)
		{
			echo '
			<div id="c-feed-head-tagcell"><div id="c-feed-navtag"><a href="' . $backURL . (strpos($backURL, "?") == false ? Me::$slg : "") . '">' . $backTitle . '</a></div></div>';
		}
		
		echo '
			<div id="c-feed-head-title"><h1>' . $title . ' </h1></div>';
		
		if(self::$activeHashtag)
		{
			echo '
			<div class="c-tag-wrap">
				<div class="c-tag-prime">
					<div class="c-tp-plus">
						<a class="c-tp-plink" href="' . Feed::follow(self::$activeHashtag) . '"><span class="icon-circle-plus"></span></a>
					</div>
					<a class="c-hlink" href="' . URL::hashtag_unifaction_com() . '/' . self::$activeHashtag . Me::$slg . '">#' . self::$activeHashtag . '</a>
				</div>
			</div>';
		}
		
		echo '
		</div>';
	}
	
	
/****** Scan the content to retrieve core feed data ******/
	public static function scanFeed
	(
		$contentIDs			// <int:int> The array of content IDs to retrieve feed data for.
	,	$doTracking = true	// <bool> TRUE if you're going to show tracking values.
	,	$uniID = 0			// <int> The UniID that is viewing the feed (used for votes, nooches, etc).
	)						// RETURNS <int:[str:mixed]> the core data for the article.
	
	// $feedData = ContentFeed::scanFeed($contentIDs, [$doTracking], [$uniID]);
	{
		// Prepare Values
		$feedData = array();
		
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("c.id" => $contentIDs));
		
		// Retrieve the Content Data
		if($doTracking)
		{
			if($uniID)
			{
				array_unshift($sqlArray, $uniID);
				
				$pullFeed = Database::selectMultiple("SELECT c.id, c.uni_id, c.url, c.url_slug, c.title, c.thumbnail, c.description, c.date_posted, c.status, u.handle, c.primary_hashtag, u.display_name, t.*, tu.shared as user_shared, tu.vote as user_vote, tu.nooch as user_nooch FROM content_entries c LEFT JOIN users u ON c.uni_id=u.uni_id LEFT JOIN content_tracking t ON c.id=t.content_id LEFT JOIN content_tracking_users tu ON tu.uni_id=? AND tu.content_id=c.id WHERE " . $sqlWhere, $sqlArray);
			}
			else
			{
				$pullFeed = Database::selectMultiple("SELECT c.id, c.uni_id, c.url, c.url_slug, c.title, c.thumbnail, c.description, c.date_posted, c.status, u.handle, c.primary_hashtag, u.display_name, t.* FROM content_entries c LEFT JOIN users u ON c.uni_id=u.uni_id LEFT JOIN content_tracking t ON c.id=t.content_id WHERE " . $sqlWhere, $sqlArray);
			}
		}
		else
		{
			$pullFeed = Database::selectMultiple("SELECT c.id, c.uni_id, c.url, c.url_slug, c.title, c.thumbnail, c.description, c.date_posted, c.status, u.handle, c.primary_hashtag, u.display_name FROM content_entries c LEFT JOIN users u ON c.uni_id=u.uni_id WHERE " . $sqlWhere, $sqlArray);
		}
		
		// Loop through each feed entry
		foreach($pullFeed as $scanData)
		{
			// Recognize Integers
			$scanData['id'] = (int) $scanData['id'];
			$scanData['uni_id'] = (int) $scanData['uni_id'];
			$scanData['date_posted'] = (int) $scanData['date_posted'];
			$scanData['status'] = (int) $scanData['status'];
			
			// Make sure a description exists
			if(!$scanData['description'])
			{
				// Pull the description by finding it in the blocks
				if($textBlock = Database::selectValue("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? ORDER BY sort_order ASC LIMIT 1", array($scanData['id'], "Text")))
				{
					if($txtBlock = Database::selectValue("SELECT body FROM content_block_text WHERE id=? LIMIT 1", array((int) $textBlock)))
					{
						// Prepare the description
						$scanData['description'] = ($scanData['description'] == "" ? UniMarkup::strip($txtBlock) : $scanData['description']);
						
						$scanData['description'] = html_entity_decode($scanData['description'], ENT_QUOTES);
						
						if(strlen($scanData['description']) > 250)
						{
							$scanData['description'] = substr($scanData['description'], 0, 247) . "...";
						}
						
						// Upload the description
						Database::query("UPDATE content_entries SET description=? WHERE id=? LIMIT 1", array($scanData['description'], $scanData['id']));
					}
				}
			}
			
			// Add the entry to the final feed data
			$feedData[$scanData['id']] = $scanData;
		}
		
		// Return Feed Data
		return $feedData;
	}
	
	
/****** Output a content feed ******/
	public static function displayFeed
	(
		$contentIDs			// <int:int> The array that contains the content entry IDs for the feed.
	,	$doTracking = true	// <bool> TRUE if we're going to show the tracking row.
	,	$uniID = 0			// <int> The UniID viewing the feed, if applicable.
	)						// RETURNS <void> outputs the appropriate line.
	
	// ContentFeed::displayFeed($contentIDs, [$doTracking], [$uniID]);
	{
		// Make sure Content IDs are available
		if(!$contentIDs)
		{
			echo "No articles available here at this time."; return;
		}
		
		// Prepare Values
		$socialURL = URL::unifaction_social();
		$hashtagURL = URL::hashtag_unifaction_com();
		
		// Pull the necessary feed data
		$feedData = self::scanFeed($contentIDs, $doTracking, $uniID);
		
		// Loop through the content entries in the feed
		// Looping with the $contentIDs variable allow us to maintain the proper ordering
		foreach($contentIDs as $contentID)
		{
			// Retrieve the feed data relevant to this particular entry (main title, description, image, etc)
			$coreData = $feedData[$contentID];
			
			// Prepare Values
			$aggregate = $coreData['url'] == "" ? false : true;
			
			// Determine the Article URL
			if($coreData['status'] == Content::STATUS_DRAFT)
			{
				$articleURL = "/draft?id=" . $contentID;
			}
			else
			{
				$articleURL = $aggregate ? $coreData['url'] . "/" . $coreData['url_slug'] : "/" . $coreData['url_slug'];
			}
			
			// Display the Content
			echo '
			<hr class="c-hr" />
			<div class="c-feed-wrap">
				<div class="c-feed-left">';
			
			// If we have a thumbnail version of the image, use that one
			if($coreData['thumbnail'])
			{
				echo '<a href="' . $articleURL . '">' . Photo::responsive($coreData['thumbnail'], "", 950, "", 950, "c-feed-img") . '</a>';
			}
			
			// If you have the clearance to edit this article
			if(Me::$clearance >= 6 or ($coreData['uni_id'] == Me::$id))
			{
				echo '<div class="c-feed-edit"><a href="/write?id=' . $contentID . '">Edit Post</a></div>';
			}
			
			echo '
				</div>
				<div class="c-feed-right">
					<div class="c-feed-date feed-desktop">' . date("m/j/y", $coreData['date_posted']) . '</div>
					<div class="c-feed-title"><a href="' . $articleURL . '">' . ($coreData['status'] == Content::STATUS_DRAFT ? "[Draft] " : "") . $coreData['title'] . '</a></div>';
			
			if($coreData['handle'])
			{
				echo '
					<div class="c-feed-author feed-desktop">Written by <a href="' . $socialURL . '/' . $coreData['handle'] . '">' . $coreData['display_name'] . '</a> (<a href="' . $socialURL . '/' . $coreData['handle'] . '">@' . $coreData['handle'] . '</a>)</div>';
			}
			
			echo '
					<div class="c-feed-body">' . $coreData['description'] . '</div>';
			
			// Hashtag List
			if($coreData['primary_hashtag'])
			{
				echo '
					<div class="c-tag-wrap">
						<div class="c-tag-prime">
							<div class="c-tp-plus">
								<a class="c-tp-plink" href="' . Feed::follow($coreData['primary_hashtag']) . '"><span class="icon-circle-plus"></span></a>
							</div>
							<a class="c-hlink" href="' . $hashtagURL . '/' . $coreData['primary_hashtag'] . '">#' . $coreData['primary_hashtag'] . '</a>
						</div>';
				
				// Retrieve a list of hashtags for this article
				if($hashtags = ModuleHashtags::get($contentID) and $hashtags != array($coreData['primary_hashtag']))
				{
					echo '
						<div class="c-elip"><a class="c-hlink" href="#">. . .</a></div>';
					
					foreach($hashtags as $tag)
					{
						if($tag == $coreData['primary_hashtag']) { continue; }
						
						echo '
						<div class="c-htag-vis"><a class="c-hlink" href="' . $hashtagURL . '/' . $tag . '">#' . $tag . '</a></div>';
					}
				}
				
				echo '
					</div>';
			}
			
			echo '
				</div>
			</div>';
			
			// If there is no content tracking being displayed, end this loop
			if($doTracking)
			{
				// Prepare Values
				$boostClicked = "";
				$noochClicked = "";
				$jsAgg = "";
				
				// Make sure the user tracking values are at least available
				if(!isset($coreData['user_vote']))
				{
					$coreData['user_vote'] = 0;
					$coreData['user_nooch'] = 0;
					$coreData['user_shared'] = 0;
				}
				
				// Display the Content Tracking Data
				if($aggregate)
				{
					$coreData['tips'] = "?";
					$coreData['votes_up'] = "?";
					$coreData['nooch'] = "?";
					$jsAgg = ', 1';
				}
				else if($coreData['views'])
				{
					$coreData['tips'] = round($coreData['tipped_amount'] * 10);
					$boostClicked = $coreData['user_vote'] == 1 ? "-track" : "";
					$noochClicked = $coreData['user_nooch'] >= 3 ? "-track" : "";
				}
				else
				{
					$coreData['votes_up'] = 0;
					$coreData['nooch'] = 0;
					$coreData['tips'] = 0;
				}
				
				echo '
				<hr class="c-hr-dotted" />
				<div class="c-options">
					<ul class="c-opt-list">
						<li id="boost-count-' . $contentID . '" class="c-bubble bub-boost">' . $coreData['votes_up'] . '</li>
						<li id="boost-track-' . $contentID . '" class="c-boost' . $boostClicked . '"><a href="javascript:track_boost(' . $contentID . $jsAgg . ');"><span class="c-opt-icon icon-rocket"></span><span class="c-desktop"> &nbsp;Boost</span></a></li>
						
						<li id="nooch-count-' . $contentID . '" class="c-bubble bub-nooch">' . $coreData['nooch'] . '</li>
						<li id="nooch-track-' . $contentID . '" class="c-nooch' . $noochClicked . '"><a href="javascript:track_nooch(' . $contentID . $jsAgg . ');"><span class="c-opt-icon icon-nooch"></span><span class="c-desktop"> &nbsp;Nooch</span></a></li>
						
						<li id="tip-count-' . $contentID . '" class="c-bubble bub-tip">' . $coreData['tips'] . '</li>
						<li id="tip-track-' . $contentID . '" class="c-tip"><a href="javascript:track_tip(' . $contentID . $jsAgg . ');"><span class="c-opt-icon icon-coin"></span><span class="c-desktop"> &nbsp;Tip</span></a></li>
					</ul>
				</div>';
			}
		}
	}
	
}
