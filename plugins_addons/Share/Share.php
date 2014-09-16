<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Share Plugin ------
-------------------------------------

This plugin allows you to share content on Social, FastChat, etc, with a simple wrapper.


-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class Share {
	
	
/****** "Share Image on Social" Share ******/
	public static function socialImage
	(
		$uniID			// <int> The UniID posting the message.
	,	$imageURL		// <str> The URL of the image to share.
	,	$mobileURL = ""	// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""		// <str> The caption or message to associate with the image.
	,	$title = ""		// <str> The title to associate with the image.
	,	$sourceURL = ""	// <str> The URL to return to if this image is clicked.
	)					// RETURNS <str> the URL of the share link.
	
	// $href = Share::shareImage($uniID, $imageURL, [$mobileURL], [$desc], [$title], [$sourceURL]);
	{
		// Prepare the Share Image Array
		$packet = array(
			'uni_id'		=> $uniID		// The UniID of the page that you're posting to
		,	'poster_id'		=> $uniID		// The person posting to the page (usually the same as UniID)
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Connect to Social's Publishing API
		$success = Connect::to("social", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Social Share", "You have successfully shared content to your Social Wall.");
		}
		else
		{
			Alert::saveError("Share Failed", "This content encountered an error while attempting to post on your Social Wall.");
		}
		
		return ($success === true);
	}
	
	
/****** "Share Video on Social" Action ******/
	public static function socialVideo
	(
		$uniID			// <int> The UniID posting the video.
	,	$videoURL		// <str> The URL of the video to share.
	,	$desc = ""		// <str> The description to associate with the image.
	,	$title = ""		// <str> The title to associate with the image.
	,	$sourceURL = ""	// <str> The URL to return to if this image is clicked.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Share::socialVideo($uniID, $videoURL, [$desc], [$title], [$sourceURL]);
	{
		// Prepare the Share Video Array
		$packet = array(
			'uni_id'		=> $uniID		// The UniID of the page that you're posting to
		,	'poster_id'		=> $uniID		// The person posting to the page (usually the same as UniID)
		,	'video_url'		=> $videoURL	// The URL of the video to post
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Connect to Social's Publishing API
		$success = Connect::to("social", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Social Share", "You have successfully shared the video to your Social Wall.");
		}
		else
		{
			Alert::saveError("Share Failed", "This content encountered an error while attempting to post on your Social Wall.");
		}
		
		return ($success === true);
	}
	
	
/****** "Share Article on Social" Action ******/
	public static function socialArticle
	(
		$uniID				// <int> The UniID posting the message.
	,	$title				// <str> The title of the article.
	,	$desc				// <str> The description / blurb for the article.
	,	$sourceURL = ""		// <str> The URL to return to (where the article is sourced).
	,	$imageURL = ""		// <str> The URL of the image to set.
	,	$mobileURL = ""		// <str> The URL of the mobile version of the image.
	,	$type = "article"	// <str> The type of content being shared; e.g. "blog", "article", etc.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Share::socialArticle($uniID, $title, $desc, $sourceURL, $imageURL, $mobileURL, $type);
	{
		// Prepare the Share Article Array
		$packet = array(
			'uni_id'		=> $uniID		// The UniID of the page that you're posting to
		,	'type'			=> $type		// The type of content to share
		,	'poster_id'		=> $uniID		// The person posting to the page (usually the same as UniID)
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Connect to Social's Publishing API
		$success = Connect::to("social", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Social Share", "You have successfully shared content to your Social Wall.");
		}
		else
		{
			Alert::saveError("Share Failed", "This content encountered an error while attempting to post on your Social Wall.");
		}
		
		return ($success === true);
	}
	
	
/****** "Chat a Comment" Action ******/
	public static function chatComment
	(
		$uniID				// <int> The UniID posting the message.
	,	$message			// <str> The message to chat.
	,	$sourceURL = ""		// <str> The URL to return to as a source.
	,	$origHandle = ""	// <str> The original handle that was responsible for the comment.
	)						// RETURNS <str> HTML output for the button.
	
	// Share::chatComment($uniID, $message, [$sourceURL], [$origHandle]);
	{
		// Prepare the Packet
		$packet = array(
			'uni_id'		=> $uniID		// The UniID posting the message
		,	'message'		=> $message		// Set this value to the message or caption to write
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originall posted the comment
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("fastchat", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Chat Share", "You have successfully shared content to your Chat Page.");
		}
		else
		{
			Alert::saveError("Chat Failed", "This content encountered an error while attempting to post on your Chat Page.");
		}
		
		return ($success === true);
	}
	
	
/****** "Chat an Image" Action ******/
	public static function chatImage
	(
		$uniID				// <int> The UniID posting the message.
	,	$imageURL			// <str> The URL of the image to share.
	,	$mobileURL = ""		// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""			// <str> The caption or message to associate with the image.
	,	$title = ""			// <str> The title to associate with the image.
	,	$sourceURL = ""		// <str> The URL to return to if this image is clicked.
	,	$origHandle = ""	// <str> The handle of the user that originally posted the image.
	)						// RETURNS <str> the URL of the share link.
	
	// Share::chatImage($uniID, $imageURL, [$mobileURL], [$desc], [$title], [$sourceURL], [$origHandle]);
	{
		// Prepare the Chat Image Array
		$packet = array(
			'uni_id'		=> $uniID		// The UniID of the page that you're posting to
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originally posted the comment
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("fastchat", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Chat Share", "You have successfully shared content to your Chat Page.");
		}
		else
		{
			Alert::saveError("Chat Failed", "This content encountered an error while attempting to post on your Chat Page.");
		}
		
		return ($success === true);
	}
	
	
/****** "Chat a Video" Action ******/
	public static function chatVideo
	(
		$uniID				// <int> The UniID posting the message.
	,	$videoURL			// <str> The URL of the video to share.
	,	$desc = ""			// <str> The description to associate with the image.
	,	$title = ""			// <str> The title to associate with the image.
	,	$sourceURL = ""		// <str> The URL to return to if this image is clicked.
	,	$origHandle = ""	// <str> The handle of the user that originally posted the video.
	)						// RETURNS <str> the URL of the share link.
	
	// Share::chatVideo($uniID, $videoURL, [$desc], [$title], [$sourceURL], [$origHandle]);
	{
		// Prepare the Chat Video Array
		$packet = array(
			'uni_id'		=> $uniID		// The UniID of the page that you're posting to
		,	'video_url'		=> $videoURL	// The URL of the video to post
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originally posted the comment
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("fastchat", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Chat Share", "You have successfully shared content to your Chat Page.");
		}
		else
		{
			Alert::saveError("Chat Failed", "This content encountered an error while attempting to post on your Chat Page.");
		}
		
		return ($success === true);
	}
	
	
/****** "Chat an Article" Action ******/
	public static function chatArticle
	(
		$uniID				// <int> The UniID posting the message.
	,	$imageURL			// <str> The URL of the image to share.
	,	$mobileURL = ""		// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""			// <str> The message to associate with the article, usually the first few sentences.
	,	$title = ""			// <str> The title to associate with the article.
	,	$sourceURL = ""		// <str> The URL of the article itself.
	,	$authorHandle = ""	// <str> The handle of the user that originally posted the article.
	,	$type = "article"	// <str> The type of article content, e.g. "article", "blog", etc.
	)						// RETURNS <str> the URL of the share link.
	
	// Share::chatArticle($uniID, $imageURL, [$mobileURL], [$desc], [$title], [$sourceURL], [$authorHandle], [$type]);
	{
		// Prepare the Chat Article Array
		$packet = array(
			'uni_id'		=> $uniID			// The UniID of the page that you're posting to
		,	'type'			=> $type			// The type of content being chatted
		,	'image_url'		=> $imageURL		// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL		// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title			// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc			// If set, this is the description of the attachment
		,	'source'		=> $sourceURL		// The URL of the sourced content
		,	'orig_handle'	=> $authorHandle	// The handle of the user that originally posted the content
		);
		
		// Connect to Chat's Publishing API
		$success = Connect::to("fastchat", "PublishAPI", $packet);
		
		if($success)
		{
			Alert::saveSuccess("Chat Share", "You have successfully shared content to your Chat Page.");
		}
		else
		{
			Alert::saveError("Chat Failed", "This content encountered an error while attempting to post on your Chat Page.");
		}
		
		return ($success === true);
	}

}
