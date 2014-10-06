<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

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
		int $uniID			// <int> The uniID that submitted the comment.
	,	string $description	// <str> The comment message.
	,	string $sourceURL		// <str> The url that points back to where the comment originated.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		int $uniID			// <int> The uniID that submitted the entry.
	,	string $type			// <str> The type of content entry ('article', 'blog', etc)
	,	string $title			// <str> The title of the entry.
	,	string $description	// <str> The description of the entry.
	,	array <int, str> $hashtags		// <int:str> The hashtags that the entry used.
	,	string $sourceURL		// <str> The url that points back to where the entry originated.
	,	string $thumbnail = ""	// <str> The URL to the photo.
	,	string $videoURL = ""	// <str> The URL to the video.
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		int $uniID			// <int> The uniID that submitted the photo.
	,	string $thumbnail		// <str> The URL to the thumbnail.
	,	array <int, str> $hashtags		// <int:str> An array of hashtags that were listed on the photo.
	,	string $sourceURL		// <str> The url to link to if the photo is clicked.
	,	string $title			// <str> The title of the image, if applicable.
	,	string $description	// <str> The description of the image, if applicable.
	,	string $type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		int $uniID			// <int> The uniID that submitted the video.
	,	string $videoURL		// <str> The URL to the video.
	,	array <int, str> $hashtags		// <int:str> An array of hashtags that were listed on the video.
	,	string $sourceURL		// <str> The url to return to if the return link is checked.
	,	string $title			// <str> The title of the image, if applicable.
	,	string $description	// <str> The description of the image, if applicable.
	,	string $type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		string $comment	// <str> The comment that you want to dig through for tags.
	): array <int, str>				// RETURNS <int:str> of hashtags that were marked in the comment, empty array if nothing.
	
	// $tags = Hashtag::digHashtags("This was the bomb. #epicsauce #nofilter");	// Returns array("epicauce", "nofilter")
	// $tags = Hashtag::digHashtags($comment);
	{
		// Prepare Values
		$hashtags = array();
		
		// Need to parse here
		$input = Parse::positionsOf($comment, "#");
		
		foreach($input as $pos)
		{
			// Make sure the hashtag isn't preceeded by an & sign
			if($pos > 0 && $comment[$pos - 1] == "&")
			{
				continue;
			}
			
			// Get the hashtag
			$getTags = Sanitize::whileValid(substr($comment, $pos + 1, 22), "variable", '-');
			
			if(strlen($getTags) > 0)
			{
				$hashtags[] = $getTags;
				
				// Don't allow more than seven hashtags to be sent at once
				if(count($hashtags) >= 7)
				{
					break;
				}
			}
		}
		
		return array_unique($hashtags);
	}
	
	
/****** Checks if the text contains hashtags ******/
	public static function hasHashtags
	(
		string $comment	// <str> The comment that you want to dig through for hashtags.
	): bool				// RETURNS <bool> true if there are any hashtags.
	
	// Hashtag::hasHashtags("This was the bomb. #epicsauce #nofilter");	// Returns true
	{
		// Need to parse here
		$input = Parse::positionsOf($comment, "#");
		
		foreach($input as $pos)
		{
			// Make sure the hashtag isn't preceeded by an & sign
			if($pos > 0 && $comment[$pos - 1] == "&")
			{
				continue;
			}
			
			// Get the hashtag
			$getTags = Sanitize::whileValid(substr($comment, $pos + 1, 22), "variable", '-');
			
			if(strlen($getTags) > 0)
			{
				return true;
			}
		}
		
		return false;
	}
}
