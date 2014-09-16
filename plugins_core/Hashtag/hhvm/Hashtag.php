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

Hashtag::submitContentEntry($uniID, $type, $title, $desc, $hashtags, $sourceURL, [$imageURL], [$mobileURL], [$videoURL], [$resub]);

Hashtag::submitImage($uniID, $imageURL, $message, $hashtags, $sourceURL, [$title], [$desc], [$mobileURL], [$type], [$resub]);

Hashtag::submitVideo($uniID, $videoURL, $message, $hashtags, $sourceURL, [$title], [$desc], [$type], [$resub]);

$tags = Hashtag::digHashtags($comment);	// Returns any tags in a comment (or text entry)
Hashtag::hasHashtags($content)			// Checks if the text has hashtags in it

*/

abstract class Hashtag {
	
	
/****** Submit a comment to the official hashtag site ******/
	public static function submitComment
	(
		int $uniID		// <int> The uniID that submitted the comment.
	,	string $message	// <str> The comment message.
	,	string $sourceURL	// <str> The url that points back to where the comment originated.
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitComment($uniID, $message, $sourceURL);
	{
		// Prepare Variables
		$message = Sanitize::text(strip_tags($message));
		
		if(strlen($message) > 1000) { $message = substr($message, 0, 1000); }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'message'		=> $message
		,	'source'		=> $sourceURL
		,	'hashtags'		=> self::digHashtags($message)
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
	,	string $desc			// <str> The message of the entry.
	,	array <int, str> $hashtags		// <int:str> The hashtags that the entry used.
	,	string $sourceURL		// <str> The url that points back to where the entry originated.
	,	string $imageURL = ""	// <str> The URL to the photo.
	,	string $mobileURL = ""	// <str> The URL to the mobile version of the image.
	,	string $videoURL = ""	// <str> The URL to the video.
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitContentEntry($uniID, $type, $title, $desc, $hashtags, $sourceURL, [$imageURL], [$mobileURL], [$videoURL], [$resub]);
	{
		// If the content entry is attaching an image
		if($imageURL or $mobileURL)
		{
			return self::submitImage($uniID, $imageURL, "", $hashtags, $sourceURL, $title, $desc, $mobileURL, $type, $resub);
		}
		
		// If the content entry is attaching a video
		if($videoURL)
		{
			return self::submitVideo($uniID, $videoURL, "", $hashtags, $sourceURL, $title, $desc, $type, $resub);
		}
		
		// Prepare Variables
		$title = Sanitize::safeword(strip_tags($title));
		$desc = Sanitize::text(strip_tags($desc));
		
		if(strlen($title) > 80) { $title = substr($title, 0, 80); }
		if(strlen($desc) > 255) { $desc = substr($desc, 0, 255); }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'attach_title'	=> $title
		,	'attach_desc'	=> $desc
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
	
	
/****** Submit a photo to the official hashtag site ******/
	public static function submitImage
	(
		int $uniID			// <int> The uniID that submitted the photo.
	,	string $imageURL		// <str> The URL to the photo.
	,	string $message		// <str> The message to include with the photo.
	,	array <int, str> $hashtags		// <int:str> An array of hashtags that were listed on the photo.
	,	string $sourceURL		// <str> The url to link to if the photo is clicked.
	,	string $title = ""		// <str> The title of the image, if applicable.
	,	string $desc = ""		// <str> The description of the image, if applicable.
	,	string $mobileURL = ""	// <str> The URL to the mobile version of the image.
	,	string $type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitImage($uniID, $imageURL, $message, $hashtags, $sourceURL, [$title], [$desc], [$mobileURL], [$type], [$resub]);
	{
		// Prepare Variables
		$message = Sanitize::text(strip_tags($message));
		$title = Sanitize::safeword(strip_tags($title));
		$desc = Sanitize::text(strip_tags($desc));
		
		if(strlen($message) > 1000) { $message = substr($message, 0, 1000); }
		if(strlen($title) > 80) { $title = substr($title, 0, 80); }
		if(strlen($desc) > 255) { $desc = substr($desc, 0, 255); }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'image_url'		=> $imageURL
		,	'mobile_url'	=> $mobileURL
		,	'attach_title'	=> $title
		,	'attach_desc'	=> $desc
		,	'message'		=> $message
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
	,	string $message		// <str> The message to include with the video.
	,	array <int, str> $hashtags		// <int:str> An array of hashtags that were listed on the video.
	,	string $sourceURL		// <str> The url to return to if the return link is checked.
	,	string $title = ""		// <str> The title of the image, if applicable.
	,	string $desc = ""		// <str> The description of the image, if applicable.
	,	string $type = ""		// <str> If set, this may alter the type of hashtag submission ('article', 'blog', etc)
	,	bool $resub = false	// <bool> TRUE if this is a resubmission of an earlier post.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Hashtag::submitVideo($uniID, $videoURL, $message, $hashtags, $sourceURL, [$title], [$desc], [$type], [$resub]);
	{
		// Prepare Variables
		$message = Sanitize::text(strip_tags($message));
		$title = Sanitize::safeword(strip_tags($title));
		$desc = Sanitize::text(strip_tags($desc));
		
		if(strlen($message) > 1000) { $message = substr($message, 0, 1000); }
		if(strlen($title) > 80) { $title = substr($title, 0, 80); }
		if(strlen($desc) > 255) { $desc = substr($desc, 0, 255); }
		
		// Prepare Packet
		$packet = array(
			'uni_id'		=> $uniID
		,	'video_url'		=> $videoURL
		,	'attach_title'	=> $title
		,	'attach_desc'	=> $desc
		,	'message'		=> $message
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
