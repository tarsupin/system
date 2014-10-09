<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Content Plugin ------
--------------------------------------


-- About Content Blocks --

Content Blocks are segments of an article (or "content entry", since it can also be a blog or other form of information). A "segment" can be a block of text, an image, a video, etc. These segments are added one at a time in a particular order, keeping them structured properly within the article.

For example, the first Content Block is usually an image that goes with the article. An article titled "Ten Things You Didn't Know About Football" might be an image block that also contains the introduction text.

Content Blocks can be edited individually. Unique blocks may appear from time to time, such as to create lists or tables. The most common forms are what you see in all typical articles, though: text, images, and videos.

This plugin provides the necessary methods to output the content blocks.

-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Retrieve important content data
$contentData = Content::get($contentID);

// Retrieve core data about this article (main title, body, image, etc)
$coreData = Content::scanForCoreData($contentID);

Content::validateClearance($coreData['status'], $coreData['uni_id']);

ModuleRelated::widget($contentID);
ModuleAuthor::widget(Me::$id);

// Prepare Values
$config['pageTitle'] = $coreData['title'];

// Run Comment Form, if applicable
if($contentData['comments'])
{
	ContentComments::interpreter($contentID, $url_relative, $contentData['comments']);
}

// Include Responsive Script
Photo::prepareResponsivePage();
Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" />');

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Display Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content">' . Alert::display();

// Display the Page
echo '
<h1>' . $coreData['title'] . '</h1>
<p style="margin-bottom:0px;">Published ' . date("F jS, Y", $coreData['date_posted']) . ' by <a href="' . URL::unifaction_social() . '/' . $coreData['handle'] . '">' . $coreData['display_name'] . '</a> (<a href="' . URL::fastchat_social() . '/' . $coreData['handle'] . '">@' . $coreData['handle'] . '</a>)</p>
<p><a href="#">Tip the Author</a> | <a href="' . Content::shareContent($contentID, "article") . '">Share this Article</a> | <a href="' . Content::chatContent($contentID, "article") . '">Chat this Article</a> | <a href="' . Content::setVote($contentID) . '">Like</a> | <a href="' . Content::flag($contentID) . '">Flag</a>';

// Display the hashtag list
if($hashtags)
{
	$hashtagURL = URL::hashtag_unifaction_com();
	
	echo '<br />';
	
	foreach($hashtags as $htag)
	{
		echo '<a class="c-hashtag" href="' . $hashtagURL . '/' . $htag . '">#' . $htag . '</a> ';
	}
}

echo '<p>';

Content::output($contentID);

// Show Comments, if applicable
if($contentData['comments'])
{
	ContentComments::draw($contentID, $url_relative, $contentData['comments']);
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");

*/

abstract class Content {
	
	
/****** Plugin Variables ******/
	public static string $returnURL = "";		// <str> The URL to return to after a content button has been clicked.
	public static bool $openPost = false;	// <bool> Set to TRUE if this content allows open posting (like blogs).
	
	public static array <str, mixed> $contentData = array();	// <str:mixed> The content data for the system.
	
	const STATUS_DRAFT = 0;			// The post is in draft form - nobody can view it right now.
	const STATUS_PRIVATE = 2;		// Private post - available to friends or mods only.
	const STATUS_GUEST = 4;			// Public post - viewable to the public.
	const STATUS_OFFICIAL = 6;		// The post has been officially published.
	const STATUS_FEATURED = 8;		// The content has been featured.
	
	const COMMENTS_UNAVAILBLE = 0;	// Disables comments for the entire system.
	const COMMENTS_DISABLED = 1;	// Disables comments for a specific entry.
	const COMMENTS_MODERATE = 2;	// All comments require moderation approval.
	const COMMENTS_NO_POSTING = 3;	// Prevents any further posting to this entry (unless you're a mod / admin).
	const COMMENTS_STANDARD = 4;	// Provides standard comment type for this entry.
	
	const VOTING_UNAVAILBLE = 0;	// Disables voting for the entire system.
	const VOTING_DISABLED = 1;		// Disables voting for the specific entry.
	const VOTING_FREEZE = 3;		// Prevents any further voting (unless you're a mod), but shows the existing votes.
	const VOTING_STANDARD = 4;		// Provides standard voting type for this entry.
	
	
/****** Prepare a page for handling a content feed ******/
	public static function prepare
	(
		int $contentID		// <int> The ID of the content to prepare.
	): void					// RETURNS <void> runs the appropriate preparation methods.
	
	// ContentFeed::prepare($contentID);
	{
		// Retrieve important content data
		self::$contentData = self::load($contentID);
		
		// Recognize Integers
		self::$contentData['uni_id'] = (int) self::$contentData['uni_id'];
		self::$contentData['status'] = (int) self::$contentData['status'];
		
		// Make sure the user has appropriate clearance to view this page
		self::validateClearance(self::$contentData['status'], self::$contentData['uni_id']);
		
		// Prepare Modules for this Entry
		ModuleRelated::widget($contentID);
		ModuleAuthor::widget(self::$contentData['uni_id']);
		
		// Prepare Values
		self::$returnURL = "/" . self::$contentData['url_slug'];
		Feed::$returnURL = "/" . self::$contentData['url_slug'];
		
		// Run Comment Form, if applicable
		if(self::$contentData['comments'])
		{
			ContentComments::interpreter($contentID, self::$returnURL, self::$contentData['comments']);
		}
		
		// Run Tip Exchanges
		/*
		if($getData = Link::getData("send-tip-article") and is_array($getData) and isset($getData[0]))
		{
			// Get the user from the post
			Credits::tip(Me::$id, (int) $getData[0]);
		}
		*/
		
		// Prepare Metaheader Scripts
		Photo::prepareResponsivePage();
		
		Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" /><script src="' . CDN . '/scripts/content-system.js"></script>');
	}
	
	
/****** Get Content Entry ******/
	public static function get
	(
		int $contentID		// <int> The ID of the content entry to retrieve.
	): array <str, mixed>					// RETURNS <str:mixed> The data array for the content entry.
	
	// $contentData = Content::get($contentID);
	{
		return Database::selectOne("SELECT * FROM content_entries WHERE id=? LIMIT 1", array($contentID));
	}
	
	
/****** Load Data for a Full Retrieval on a Content Entry ******/
	public static function load
	(
		int $contentID		// <int> The ID of the content entry to retrieve.
	): array <str, mixed>					// RETURNS <str:mixed> The data array for the content entry.
	
	// $contentData = ContentLoad::load($contentID);
	{
		$contData = Database::selectOne("SELECT e.*, c.body, u.handle, u.display_name FROM content_entries e LEFT JOIN content_cache c ON e.id=c.content_id INNER JOIN users u ON e.uni_id=u.uni_id WHERE e.id=? LIMIT 1", array($contentID));
		
		// Recognize Integers
		$contData['uni_id'] = (int) $contData['uni_id'];
		$contData['status'] = (int) $contData['status'];
		$contData['voting'] = (int) $contData['voting'];
		$contData['comments'] = (int) $contData['comments'];
		
		return $contData;
	}
	
	
/****** Output a Content Entry ******/
	public static function output
	(
		int $contentID		// <int> The ID of the content entry.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// Content::output($contentID);
	{
		// Get the list of content segments
		$results = Database::selectMultiple("SELECT block_id, type FROM content_block_segment WHERE content_id=? ORDER BY sort_order ASC", array($contentID));
		
		// Display the appropriate block type
		foreach($results as $result)
		{
			if(method_exists("Module" . $result['type'], "get"))
			{
				echo call_user_func(array("Module" . $result['type'], "get"), (int) $result['block_id']);
			}
		}
	}
	
	
/****** Display Content ******/
	public static function display (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// Content::display();
	{
		// Prepare Values
		$contentID = (int) self::$contentData['id'];
		
		// Display the Header
		echo '
		<h1 style="line-height:120%; padding-bottom:4px;">' . self::$contentData['title'] . '</h1>
		<p style="margin-top:0px; margin-bottom:8px;">' . date("F jS, Y", self::$contentData['date_posted']) . ' by <a href="' . URL::unifaction_social() . '/' . self::$contentData['handle'] . '">' . self::$contentData['display_name'] . '</a> (<a href="' . URL::fastchat_social() . '/' . self::$contentData['handle'] . '">@' . self::$contentData['handle'] . '</a>)</p>
		<hr class="c-hr-dotted" />';
		
		// Pull the tracking data for this entry
		$trackingData = ContentTrack::getData($contentID, Me::$id);
		
		// Make sure the user tracking values are at least available
		if(!isset($trackingData['user_vote']))
		{
			$trackingData['user_vote'] = 0;
			$trackingData['user_nooch'] = 0;
			$trackingData['user_shared'] = 0;
		}
		
		// Display the Content Tracking Data
		if(isset($trackingData['views']) and $trackingData['views'])
		{
			$trackingData['tips'] = round($trackingData['tipped_amount'] * 10);
			$boostClicked = $trackingData['user_vote'] == 1 ? "-track" : "";
			$noochClicked = $trackingData['user_nooch'] >= 3 ? "-track" : "";
		}
		else
		{
			$trackingData['votes_up'] = 0;
			$trackingData['nooch'] = 0;
			$trackingData['tips'] = 0;
			$boostClicked = "";
			$noochClicked = "";
		}
		
		echo '
		<div class="c-options">
			<ul class="c-opt-list" style="text-align:left;">
				<li id="boost-count-' . $contentID . '" class="c-bubble bub-boost">' . $trackingData['votes_up'] . '</li>
				<li id="boost-track-' . $contentID . '" class="c-boost' . $boostClicked . '"><a href="javascript:track_boost(' . $contentID . ');"><span class="c-opt-icon icon-rocket"></span><span class="c-desktop"> &nbsp;Boost</span></a></li>
				
				<li id="nooch-count-' . $contentID . '" class="c-bubble bub-nooch">' . $trackingData['nooch'] . '</li>
				<li id="nooch-track-' . $contentID . '" class="c-nooch' . $noochClicked . '"><a href="javascript:track_nooch(' . $contentID . ');"><span class="c-opt-icon icon-nooch"></span><span class="c-desktop"> &nbsp;Nooch</span></a></li>
				
				<li id="tip-count-' . $contentID . '" class="c-bubble bub-tip">' . $trackingData['tips'] . '</li>
				<li id="tip-track-' . $contentID . '" class="c-tip"><a href="javascript:track_tip(' . $contentID . ');"><span class="c-opt-icon icon-coin"></span><span class="c-desktop"> &nbsp;Tip</span></a></li>
			</ul>
		</div>';
		
		// <a href="' . Content::$returnURL . "?" . Link::prepareData("send-tip-article", self::$contentData['uni_id']) . '">Tip the Author</a
		// <a href="' . Content::setVote($contentID) . '">Boost</a>
		
		// Extra Buttons
		echo '
		<style>
			.but-share { padding:8px; background-color:#4bc7c7; color:white !important; border-radius:6px; }
			.but-share:hover { background-color:#8fdad7; }
		</style>

		<p style="margin-top:16px;">
			<a class="but-share" href="' . Content::shareContent($contentID, "article") . '"><span class="icon-group"></span> Share</a>
			<a class="but-share" href="' . Content::chatContent($contentID, "article") . '"><span class="icon-comments"></span> Chat</a>';
		
		if(Me::$clearance >= 6 or self::$contentData['uni_id'] == Me::$id)
		{
			echo '
			<a class="but-share" href="/write?id=' . $contentID . '"><span class="icon-pencil"></span> Edit Post</a>';
		}
		
		echo '
			<a class="but-share" href="' . Content::flag($contentID) . '"><span class="icon-flag"></span> Flag</a>
		</p>';
		
		echo '
		<hr class="c-hr-dotted" />';
		
		// Hashtag List
		$hashtagURL = URL::hashtag_unifaction_com();
		
		if(self::$contentData['primary_hashtag'])
		{
			echo '
			<div style="margin-bottom:18px;">
				<div class="c-tag-wrap-full">
					<div class="c-tag-prime">
						<div class="c-tp-plus">
							<a class="c-tp-plink" href="' . Feed::follow(self::$contentData['primary_hashtag']) . '"><span class="icon-circle-plus"></span></a>
						</div>
						<a class="c-hlink" href="' . $hashtagURL . '/' . self::$contentData['primary_hashtag'] . '">#' . self::$contentData['primary_hashtag'] . '</a>
					</div>';
			
			// Retrieve a list of hashtags for this article
			if($hashtags = ModuleHashtags::get($contentID))
			{
				foreach($hashtags as $tag)
				{
					if($tag == self::$contentData['primary_hashtag']) { continue; }
					
					echo '
					<div class="c-htag-full"><a class="c-hlink" href="' . $hashtagURL . '/' . $tag . '">#' . $tag . '</a></div>';
				}
			}
			
			echo '
				</div>
			</div>';
		}
		
		// Display the Body Text
		if(self::$contentData['body'])
		{
			echo self::$contentData['body'];
		}
		else
		{
			Content::output($contentID);
		}
		
		// Show Comments, if applicable
		if(self::$contentData['comments'])
		{
			ContentComments::draw($contentID, self::$returnURL, self::$contentData['comments']);
		}
	}
	
	
/****** Get Content Entry ******/
	public static function validateClearance
	(
		int $status				// <int> The data for the content that you're validating clearance for.
	,	int $ownerID			// <int> The UniID of the person who created the content.
	): void						// RETURNS <void>
	
	// Content::validateClearance($status, $ownerID);
	{
		if($status < Content::STATUS_GUEST)
		{
			// If it's not your page, clearance is disallowed
			if($ownerID != Me::$id)
			{
				Alert::saveError("Invalid Clearance", "You do not have clearance to view that article.");
			
				header("Location: /"); exit;
			}
		}
		
		if($status < Content::STATUS_OFFICIAL and self::$openPost == false)
		{
			Alert::info("Guest Submission", "Note: This article is a guest submission.");
		}
	}
	
	
/****** Queue a content entry for approval ******/
	public static function queue
	(
		int $contentID		// <int> The ID of the content entry to queue for approval.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Content::queue($contentID);
	{
		return Database::query("REPLACE INTO content_queue (content_id, last_update) VALUES (?, ?)", array($contentID, time()));
	}
	
	
/****** Approve a content entry in the queue ******/
	public static function approveQueue
	(
		int $contentID		// <int> The ID of the queued content entry to approve.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Content::approveQueue($contentID);
	{
		// Check if the ID is valid and exists in the queue
		if(!$contentData = Database::selectOne("SELECT c.* FROM content_queue q INNER JOIN content_entries c ON q.content_id=c.id WHERE q.content_id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		// Remove the queued entry
		$success = Database::query("DELETE FROM content_queue WHERE content_id=? LIMIT 1", array($contentID));
		
		// Make sure the content entry is still set as a guest post, waiting for official posting
		if($contentData['status'] == Content::STATUS_GUEST)
		{
			// Update the content entry under the rules of the new status
			$success = Database::query("UPDATE content_entries SET status=? WHERE id=? LIMIT 1", array(Content::STATUS_OFFICIAL, $contentID));
		}
		
		return $success;
	}
	
	
/****** Deny a content entry in the queue ******/
	public static function denyQueue
	(
		int $contentID		// <int> The ID of the queued content entry to deny.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Content::denyQueue($contentID);
	{
		return Database::query("DELETE FROM content_queue WHERE content_id=? LIMIT 1", array($contentID));
	}
	
	
/****** Scan the article to retrieve core information ******/
# This method scans through the first few blocks of content to retrieve important data about the article, such as the
# main image used (or video) and the heading text.
	public static function scanForCoreData
	(
		int $contentID			// <int> The ID of the block to retrieve.
	,	int $blocksToScan = 4	// <int> The number of blocks to scan.
	): array <str, mixed>						// RETURNS <str:mixed> the core data for the article.
	
	// $coreData = Content::scanForCoreData($contentID, [$blocksToScan]);
	{
		// Get the Content Data
		if(!$scanData = Database::selectOne("SELECT c.uni_id, c.url_slug, c.title, c.thumbnail, c.date_posted, c.status, u.handle, c.primary_hashtag, u.display_name FROM content_entries c LEFT JOIN users u ON c.uni_id=u.uni_id WHERE c.id=? LIMIT 1", array($contentID)))
		{
			return array();
		}
		
		// Recognize Integers
		$scanData['uni_id'] = (int) $scanData['uni_id'];
		$scanData['date_posted'] = (int) $scanData['date_posted'];
		$scanData['status'] = (int) $scanData['status'];
		
		// Prepare Required Values
		$scanData['body'] = "";
		$scanData['image_url'] = "";
		$scanData['mobile_url'] = "";
		$scanData['video_url'] = "";
		$scanData['video_thumb'] = "";
		
		// Scan the blocks
		$results = Database::selectMultiple("SELECT type, block_id FROM content_block_segment WHERE content_id=? ORDER BY sort_order ASC LIMIT " . ($blocksToScan), array($contentID));
		
		foreach($results as $result)
		{
			$result['block_id'] = (int) $result['block_id'];
			
			// Retrieve a text block
			if($result['type'] == "Text")
			{
				if($txtBlock = Database::selectValue("SELECT body FROM content_block_text WHERE id=? LIMIT 1", array($result['block_id'])))
				{
					// Prepare Values
					$scanData['body'] = ($scanData['body'] == "" ? UniMarkup::strip($txtBlock) : $scanData['body']);
				}
			}
			
			// Retrieve an image block
			else if($result['type'] == "Image")
			{
				if($imgBlock = Database::selectOne("SELECT image_url, mobile_url FROM content_block_image WHERE id=? LIMIT 1", array($result['block_id'])))
				{
					// Retrieve the images, if applicable
					if(!$scanData['image_url'] and $imgBlock['image_url'])
					{
						$scanData['image_url'] = $imgBlock['image_url'];
						
						if($imgBlock['mobile_url'])
						{
							$scanData['mobile_url'] = $imgBlock['mobile_url'];
						}
						
						// Unset the video thumbnail (so that we're not confusing what will be active)
						if($scanData['video_thumb'])
						{
							$scanData['video_thumb'] = "";
						}
					}
				}
			}
			
			// Retrieve an video block
			else if($result['type'] == "Video")
			{
				if($videoURL = Database::selectValue("SELECT video_url FROM content_block_video WHERE id=? LIMIT 1", array($result['block_id'])))
				{
					if(!$scanData['video_url'] and $videoURL)
					{
						$scanData['video_url'] = $videoURL;
						
						// Get the video thumbnail, but only if the thumbnail hasn't been set yet
						if(!$scanData['thumbnail'])
						{
							$scanData['video_thumb'] = Attachment::getVideoImageFromURL($scanData['video_url']);
						}
					}
				}
			}
			
			// End the loop if we have all of the necessary information retrieved
			if($scanData['body'] and $scanData['image_url'])
			{
				break;
			}
		}
		
		// Apply a video thumbnail if there is not an adequate image thumbnail
		if($scanData['video_thumb'] and !$scanData['image_url'])
		{
			$scanData['image_url'] = $scanData['video_thumb'];
			$scanData['mobile_url'] = $scanData['video_thumb'];
		}
		
		// Prepare Values
		$scanData['body'] = html_entity_decode($scanData['body'], ENT_QUOTES);
		
		if(strlen($scanData['body']) > 250)
		{
			$scanData['body'] = substr($scanData['body'], 0, 247) . "...";
		}
		
		// Return Core Data
		return $scanData;
	}
	
	
/****** "Chat This Content" Button ******/
	public static function chatContent
	(
		int $contentID		// <int> The content ID of the content entry you're chatting.
	,	string $type = "blog"	// <str> The type of content being chatted.
	): string					// RETURNS <str> the URL of the share link.
	
	// $href = Content::chatContent($contentID, $type);
	{
		// Prepare the Chat Data
		$contentInfo = Serialize::encode(array($contentID, Me::$id, $type));
		$contentInfo = urlencode(Encrypt::run("cCData", $contentInfo, "fast"));
		
		return '/action/Content/chatContent?param[0]=' . $contentInfo . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Chat This Content" Action ******/
	public static function chatContent_TeslaAction
	(
		string $contentInfo	// <str> The data for the content that we're chatting.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Content/chatContent?param[0]={$contentInfo}
	{
		// Prepare Values
		$contentInfo = Decrypt::run("cCData", $contentInfo);
		
		list($contentID, $uniID, $type) = Serialize::decode($contentInfo);
		
		if(!$contentID or !$uniID)
		{
			return false;
		}
		
		// Recognize Integers
		$contentID = (int) $contentID;
		$uniID = (int) $uniID;
		
		// Get the Article Data
		$coreData = Content::scanForCoreData($contentID);
		
		// Prepare the Chat Image Array
		$packet = array(
			'uni_id'		=> $uniID					// The UniID of the page that you're posting to
		,	'type'			=> $type					// The type of content being chatted (blog, article, etc)
		,	'thumbnail'		=> $coreData['thumbnail']	// The thumbnail URL if you're posting an image
		,	'title'			=> $coreData['title']		// If set, this is the title of the attachment
		,	'description'	=> $coreData['body']		// If set, this is the description of the attachment
		,	'source'		=> SITE_URL . "/" . $coreData['url_slug']		// The URL of the sourced content
		,	'orig_handle'	=> $coreData['handle']		// The handle of the user that originally posted the content
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("fastchat", "PublishAPI", $packet);
		
		if($success)
		{
			// Run the Content Tracker, if applicable
			if(class_exists("ContentTrack"))
			{
				$contentTrack = new ContentTrack($contentID, $uniID);
				
				$contentTrack->share();
			}
			
			Alert::saveSuccess("Chat Share", "You have successfully shared content to your Chat Page.");
		}
		else
		{
			Alert::saveError("Chat Failed", "This content encountered an error while attempting to post on your Chat Page.");
		}
		
		return ($success === true);
	}
	
	
/****** "Share This Content" Button ******/
	public static function shareContent
	(
		int $contentID		// <int> The content ID of the content entry you're sharing.
	,	string $type = "blog"	// <str> The type of content being chatted.
	): string					// RETURNS <str> the URL of the share link.
	
	// $href = Content::shareContent($contentID, $type);
	{
		// Prepare the Chat Data
		$contentInfo = Serialize::encode(array($contentID, Me::$id, $type));
		$contentInfo = urlencode(Encrypt::run("shareCData", $contentInfo, "fast"));
		
		return '/action/Content/shareContent?param[0]=' . $contentInfo . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Share This Content" Action ******/
	public static function shareContent_TeslaAction
	(
		array <int, int> $contentInfo	// <int:int> The data for the content that we're sharing.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Content/shareContent?param[0]={$contentInfo}
	{
		// Prepare Values
		$contentInfo = Decrypt::run("shareCData", $contentInfo);
		
		list($contentID, $uniID, $type) = Serialize::decode($contentInfo);
		
		if(!$contentID or !$uniID)
		{
			return false;
		}
		
		// Get the Article Data
		$coreData = Content::scanForCoreData($contentID);
		
		// Prepare the Chat Image Array
		$packet = array(
			'uni_id'		=> $uniID					// The UniID of the page that you're posting to
		,	'type'			=> $type					// The type of content being chatted (blog, article, etc)
		,	'poster_id'		=> $uniID					// The person posting to the page (usually the same as UniID)
		,	'thumbnail'		=> $coreData['thumbnail']	// Set this value (absolute url) if you're posting an image
		,	'title'			=> $coreData['title']		// If set, this is the title of the attachment
		,	'description'	=> $coreData['body']		// If set, this is the description of the attachment
		,	'source'		=> SITE_URL . "/" . $coreData['url_slug']		// The URL of the sourced content
		,	'orig_handle'	=> $coreData['handle']		// The handle of the user that originally posted the content
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("social", "PublishAPI", $packet);
		
		if($success)
		{
			// Run the Content Tracker, if applicable
			if(class_exists("ContentTrack"))
			{
				$contentTrack = new ContentTrack($contentID, $uniID);
				
				$contentTrack->share();
			}
			
			Alert::saveSuccess("Social Share", "You have successfully shared content to your Social Wall.");
		}
		else
		{
			Alert::saveError("Share Failed", "This content encountered an error while attempting to post on your Social Wall.");
		}
		
		return ($success === true);
	}
	
	
/****** "Vote" Button ******/
	public static function setVote
	(
		int $contentID		// <int> The content ID of the content entry you're voting on.
	,	$voteUp = true	// <boo> TRUE to vote something up, FALSE to vote it down.
	): string					// RETURNS <str> the URL of the share link.
	
	// $href = Content::setVote($contentID, [$voteUp]);
	{
		// Prepare the Chat Data
		$voteData = Serialize::encode(array($contentID, Me::$id, $voteUp));
		$voteData = urlencode(Encrypt::run("doVoteDataChk", $voteData, "fast"));
		
		return '/action/Content/vote?param[0]=' . $voteData . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Vote" Action ******/
	public static function vote_TeslaAction
	(
		array <int, mixed> $voteData		// <int:mixed> The voting data that you passed.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Content/vote?param[0]={$voteData}
	{
		// Prepare Values
		$voteData = Decrypt::run("doVoteDataChk", $voteData);
		
		list($contentID, $uniID, $voteUp) = Serialize::decode($voteData);
		
		if(!$contentID or !$uniID)
		{
			return false;
		}
		
		// Run the Content Tracker, if applicable
		if(!class_exists("ContentTrack"))
		{
			return false;
		}
		
		// Run the Vote
		$contentTrack = new ContentTrack($contentID, $uniID);
		
		if($voteUp)
		{
			$contentTrack->voteUp();
		}
		else
		{	
			$contentTrack->voteDown();
		}
		
		return true;
	}
	
	
/****** "Flag Content" Button ******/
	public static function flag
	(
		int $contentID		// <int> The content ID of the content entry you're flagging.
	): string					// RETURNS <str> the URL of the share link.
	
	// $href = Content::flag($contentID);
	{
		// Prepare the Chat Data
		$voteData = Serialize::encode(array($contentID, Me::$id));
		$voteData = urlencode(Encrypt::run("flagContentbtn", $voteData, "fast"));
		
		return '/action/Content/flag?param[0]=' . $voteData . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Vote" Action ******/
	public static function flag_TeslaAction
	(
		array <int, mixed> $voteData		// <int:mixed> The flag data that you passed.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Content/flag?param[0]={$voteData}
	{
		// Prepare Values
		$voteData = Decrypt::run("flagContentbtn", $voteData);
		
		list($contentID, $uniID) = Serialize::decode($voteData);
		
		if(!$contentID or !$uniID)
		{
			return false;
		}
		
		// Run the Content Tracker, if applicable
		if(!class_exists("ContentTrack"))
		{
			return false;
		}
		
		// Run the Vote
		$contentTrack = new ContentTrack($contentID, $uniID);
		$contentTrack->flag();
		
		return true;
	}
	
}