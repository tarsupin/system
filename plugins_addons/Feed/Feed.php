<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Feed Plugin ------
-----------------------------------

This plugin enables the site to follow hashtags, as well as contribute content to the Feed site.

*/

abstract class Feed {
	
	
/****** Plugin Variables ******/
	public static $feedSalt = "ffData#jfV_8*";		// <str> The salt / key to use for encryption purposes.
	public static $returnURL = "";					// <str> The URL to return to after an action is made.
	public static $feedID = 0;						// <int> The Feed ID that was returned.
	
	
/****** Return a URL to "follow" something ******/
	public static function follow
	(
		$hashtag			// <str> The hashtag to follow; e.g. "GBPackers", "MadisonWIFood", etc.
	,	$returnURL = ""		// <str> The URL to return to if this is clicked.
	,	$isFollow = true	// <bool> TRUE if we're following, FALSE if not following
	)						// RETURNS <str> the URL of the follow link.
	
	// $href = Feed::follow($hashtag, [$returnURL], [$isFollow]);
	{
		// Set the base Return URL, if applicable
		if(!$returnURL and self::$returnURL)
		{
			$returnURL = self::$returnURL;
		}
		
		$hashtag = Sanitize::variable($hashtag);
		
		$followData = Serialize::encode(array(Me::$id, $hashtag, $isFollow));
		$followData = urlencode(Encrypt::run(self::$feedSalt, $followData, "fast"));
		
		return '/action/Feed/follow?param[0]=' . $followData . ($returnURL ? '&return=' . $returnURL : '');
	}
	
	
/****** Return a URL to "unfollow" something ******/
	public static function unfollow
	(
		$hashtag			// <str> The hashtag to unfollow; e.g. "GBPackers", "MadisonWIFood", etc.
	,	$returnURL = ""		// <str> The URL to return to if this is clicked.
	)						// RETURNS <str> the URL of the unfollow link.
	
	// $href = Feed::unfollow($hashtag, [$returnURL]);
	{
		return follow($hashtag, $returnURL, false);
	}
	
	
/****** Run the "Follow" Action ******/
	public static function follow_TeslaAction
	(
		$followData		// <str> The encrypted data string to be interpreted.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Feed/follow?param[0]={$followData}
	{
		// Prepare Values
		$followData = Decrypt::run(self::$feedSalt, $followData);
		list($uniID, $hashtag, $isFollow) = Serialize::decode($followData);
		
		if(!$hashtag) { return false; }
		
		// Prepare Values
		$uniID = (int) $uniID;
		$hashtag = Sanitize::variable($hashtag);
		$isFollow = (bool) $isFollow;
		
		if(!$uniID)
		{
			Alert::saveError("Follow Failure", "You must log in to follow #" . $hashtag);
			
			return false;
		}
		
		// Prepare the feed submission packet
		$packet = array(
			"uni_id"			=> $uniID			// The UniID of the person setting a feed tracker
		,	"hashtag"			=> $hashtag			// The name of the tag, such as the category of the site
		,	"follow"			=> $isFollow		// TRUE if you're following, FALSE if you're unfollowing
		);
		
		// Submit the packet
		$success = Connect::to("feed", "FollowHashtagAPI", $packet);
		
		// Add the item being followed to the user's tracker list
		if($success)
		{
			Alert::saveSuccess("Follow Success", "You are now following #" . $hashtag);
		}
		
		// Remove the item being unfollowed from the user's tracker list
		else
		{
			Alert::saveError("Follow Failed", "There was an error while attempting to follow #" . $hashtag);
		}
		
		return ($success === true);
	}
	
	
/****** Submit a Feed Entry to the Feed Site ******/
	public static function submit
	(
		$authorID			// <int> The UniID of the original author of the content.
	,	$url				// <str> The source URL for where this content exists; the URL to visit it.
	,	$title				// <str> The title of the content.
	,	$blurb				// <str> The blurb or short description of the content.
	,	$imageURL = ""		// <str> The URL of the image to use for this content, if applicable (generally mobile sized)
	,	$siteHandle = ""	// <str> The site handle that this entry belongs to.
	,	$primeHashtag = ""	// <str> The primary hashtag of the article.
	,	$hashtags = array()	// <int:str> The list of hashtags that are pointed to this feed entry.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Feed::submit($authorID, $url, $title, $blurb, $imageURL, $siteHandle, $primeHashtag, $hashtags);
	{
		// Prepare Values
		self::$feedID = 0;
		
		$url = Sanitize::url($url);
		$title = Sanitize::safeword($title, "?");
		$blurb = Sanitize::safeword($blurb, "?");
		$imageURL = Sanitize::url($imageURL);
		$siteHandle = ($siteHandle ? Sanitize::variable($siteHandle) : SITE_HANDLE);
		$primeHashtag = Sanitize::variable($primeHashtag);
		$hashtagList = array();
		
		foreach($hashtags as $hashtag)
		{
			$hashtagList[] = Sanitize::variable($hashtag);
		}
		
		// Prepare the feed submission packet
		$packet = array(
			"site_handle"		=> $siteHandle		// The site that is providing the feed
		,	"author_id"			=> $authorID		// The user's handle that posted the original content
		,	"url"				=> $url				// The URL source to access this content
		,	"title"				=> $title			// The title of the feed content
		,	"blurb"				=> $blurb			// The blurb associated with the feed to give a description about it
		,	"image_url"			=> $imageURL		// The small image (mobile sized) to accompany this content feed
		,	"primary_hashtag"	=> $primeHashtag	// The primary hashtag associated with this feed.
		,	"hashtags"			=> $hashtagList		// The list of hashtags being submitted to the feed system
		);
		
		// Submit the packet
		if($success = Connect::to("feed", "PushFeed", $packet))
		{
			if(isset(Connect::$meta['feed_id']))
			{
				self::$feedID = (int) Connect::$meta['feed_id'];
			}
		}
		
		return $success;
	}
	
	
/****** Update a feed entry with additional hashtags ******/
	public static function update
	(
		$feedID				// <int> The ID of the feed entry to point a hashtag to.
	,	$primeHashtag		// <str> The primary hashtag associated with this entry.
	,	$hashtags			// <str> The hashtags to point at the feed entry.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Feed::update($feedID, $primeHashtag, $hashtags);
	{
		// Prepare Values
		$primeHashtag = Sanitize::variable($primeHashtag);
		$hashtagList = array();
		
		foreach($hashtags as $hashtag)
		{
			$hashtagList[] = Sanitize::variable($hashtag);
		}
		
		// Prepare the feed submission packet
		$packet = array(
			"feed_id"			=> $feedID			// The feed ID to update.
		,	"primary_hashtag"	=> $primeHashtag	// The primary hashtag.
		,	"hashtags"			=> $hashtagList		// The list of hashtags to update to the feed.
		);
		
		// Submit the packet
		return Connect::to("feed", "PushFeedUpdate", $packet);
	}
	
}
