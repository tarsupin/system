<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Hashtag Plugin ------
--------------------------------------

This plugin will handle hashtags, including updating them to the hashtag site.

This plugin also provides some custom hashtags that will allow you to tip the users responsible the comment is responding to.


-------------------------------
------ Methods Available ------
-------------------------------

Hashtag::submitComment($uniID, $message, $sourceURL);

Hashtag::submitContentEntry($uniID, $type, $title, $desc, $hashtags, $sourceURL, [$thumbnail], [$videoURL], [$resub]);

Hashtag::submitImage($uniID, $thumbnail, $message, $hashtags, $sourceURL, [$title], [$desc], [$type], [$resub]);

Hashtag::submitVideo($uniID, $videoURL, $message, $hashtags, $sourceURL, [$title], [$desc], [$type], [$resub]);

$tags = Hashtag::digHashtags($comment);	// Returns any tags in a comment (or text entry)
Hashtag::hasHashtags($content)			// Checks if the text has hashtags in it

*/

abstract class Hashtag {
	
	
/****** Submit a comment to the official hashtag site ******/
	public static function submitComment
	(
		$uniID			// <int> The uniID that submitted the comment.
	,	$description	// <str> The comment message.
	,	$sourceURL		// <str> The url that points back to where the comment originated.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitComment($uniID, $description, $sourceURL);
	{
		// Prepare Variables
		$description = Sanitize::text(strip_tags($description));
		
		if(strlen($description) > 255) { $description = substr($description, 0, 252) . "..."; }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'description'	=> $description
		,	'source'		=> $sourceURL
		,	'hashtags'		=> self::digHashtags($description)
		);
		
		// Run the API
		return (bool) Connect::to("hashtag", "PublishAPI", $packet);
	}
	
	
/****** Submit a content entry to the official hashtag site ******/
	public static function submitContentEntry
	(
		$uniID			// <int> The uniID that submitted the entry.
	,	$type			// <str> The type of content entry ('article', 'blog', etc)
	,	$title			// <str> The title of the entry.
	,	$description	// <str> The description of the entry.
	,	$hashtags		// <int:str> The hashtags that the entry used.
	,	$sourceURL		// <str> The url that points back to where the entry originated.
	,	$thumbnail = ""	// <str> The URL to the photo.
	,	$videoURL = ""	// <str> The URL to the video.
	,	$resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitContentEntry($uniID, $type, $title, $description, $hashtags, $sourceURL, [$thumbnail], [$videoURL], [$resub]);
	{
		// If the content entry is attaching an image
		if($thumbnail)
		{
			return self::submitImage($uniID, $thumbnail, $hashtags, $sourceURL, $title, $description, $type, $resub);
		}
		
		// If the content entry is attaching a video
		if($videoURL)
		{
			return self::submitVideo($uniID, $videoURL, $hashtags, $sourceURL, $title, $description, $type, $resub);
		}
		
		return false;
	}
	
	
/****** Submit a photo to the official hashtag site ******/
	public static function submitImage
	(
		$uniID			// <int> The uniID that submitted the photo.
	,	$thumbnail		// <str> The URL to the thumbnail.
	,	$hashtags		// <int:str> An array of hashtags that were listed on the photo.
	,	$sourceURL		// <str> The url to link to if the photo is clicked.
	,	$title			// <str> The title of the image, if applicable.
	,	$description	// <str> The description of the image, if applicable.
	,	$type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	$resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitImage($uniID, $thumbnail, $hashtags, $sourceURL, [$title], [$description], [$type], [$resub]);
	{
		// Prepare Variables
		$title = Sanitize::safeword(strip_tags($title));
		$description = Sanitize::text(strip_tags($description));
		
		if(strlen($title) > 80) { $title = substr($title, 0, 80); }
		if(strlen($description) > 253) { $description = substr($description, 0, 250) . "..."; }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'thumbnail'		=> $thumbnail
		,	'title'			=> $title
		,	'description'	=> $description
		,	'hashtags'		=> $hashtags
		,	'source'		=> $sourceURL
		);
		
		// Send a unique type with the hashtag, if applicable
		if($type)
		{
			$packet['type'] = $type;
		}
		
		// Indicate that this is a resubmission, if applicable
		if($resub)
		{
			$packet['resubmitted'] = true;
		}
		
		// Run the API
		return (bool) Connect::to("hashtag", "PublishAPI", $packet);
	}
	
	
/****** Submit a video to the official hashtag site ******/
	public static function submitVideo
	(
		$uniID			// <int> The uniID that submitted the video.
	,	$videoURL		// <str> The URL to the video.
	,	$hashtags		// <int:str> An array of hashtags that were listed on the video.
	,	$sourceURL		// <str> The url to return to if the return link is checked.
	,	$title			// <str> The title of the image, if applicable.
	,	$description	// <str> The description of the image, if applicable.
	,	$type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	$resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitVideo($uniID, $videoURL, $hashtags, $sourceURL, [$title], [$description], [$type], [$resub]);
	{
		// Prepare Variables
		$title = Sanitize::safeword(strip_tags($title));
		$description = Sanitize::text(strip_tags($description));
		
		if(strlen($title) > 80) { $title = substr($title, 0, 80); }
		if(strlen($description) > 253) { $description = substr($description, 0, 250) . "..."; }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'video_url'		=> $videoURL
		,	'title'			=> $title
		,	'description'	=> $description
		,	'hashtags'		=> $hashtags
		,	'source'		=> $sourceURL
		);
		
		// Send a unique type with the hashtag, if applicable
		if($type)
		{
			$packet['type'] = $type;
		}
		
		// Indicate that this is a resubmission, if applicable
		if($resub)
		{
			$packet['resubmitted'] = true;
		}
		
		// Run the API
		return (bool) Connect::to("hashtag", "PublishAPI", $packet);
	}
	
	
/****** Return a list of hashtags marked in a comment ******/
	public static function digHashtags
	(
		$comment	// <str> The comment that you want to dig through for tags.
	)				// RETURNS <int:str> of hashtags that were marked in the comment, empty array if nothing.
	
	// $tags = Hashtag::digHashtags("This was the bomb. #epicsauce #nofilter");	// Returns array("epicauce", "nofilter")
	// $tags = Hashtag::digHashtags($comment);
	{
		preg_match_all('#(?>^|\s)\#([\w-]+?)#iUs', $comment, $matches);
		$matches = $matches[1];
		
		$hashtags = array_unique($matches);
		
		while(count($hashtags) > 7)
		{
			array_pop($hashtags);
		}
		
		return $hashtags;
	}
	
	
/****** Checks if the text contains hashtags ******/
	public static function hasHashtags
	(
		$comment	// <str> The comment that you want to dig through for hashtags.
	)				// RETURNS <bool> true if there are any hashtags.
	
	// Hashtag::hasHashtags("This was the bomb. #epicsauce #nofilter");	// Returns true
	{
		return (bool) preg_match('#(?>^|\s)\#([\w-]+?)#iUs', $comment);
	}
}

