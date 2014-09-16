<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Button Plugin ------
-------------------------------------

This plugin generates important links and buttons that many UniFaction sites use.


* Button:: class.
	* "Friend Me" button and link.
	* "Follow Me" button and link.
	* "Share" button and link.
	* "Tip" buttons and links.
	* "Bookmark" button and link.

Buttons have: Icon or Title, URL, Return URL, Packet

-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Create a link to share an image
echo '<a href="' . Button::shareImage(SITE_URL . "/this-img.jpg", $mobileURL, $desc, $title, $sourceURL) . '">Share this Image</a>';

-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class Button {
	
	
/****** Plugin Variables ******/
	public static $returnURL = "";		// <str> The URL to return to after a button has been clicked.
	
	
/****** "Friend Me" Button ******/
	public static function addFriend
	(
		$friendID		// <int> The UniID of the person to add as a friend.
	)					// RETURNS <str> URL link for the add friend, or "" on failure.
	
	// $href = Button::addFriend($friendID);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Return the URL
		return '/action/Button/addFriend?param[0]=' . $friendID ;
	}
	
	
/****** "Friend Me" Action ******/
	public static function addFriend_TeslaAction
	(
		$friendID		// <int> The UniID of the friend.
	,	$clearance = 0	// <int> The clearance to run with (automatically passed).
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Button/addFriend?param[0]={$friendID}
	{
		// Prepare Values (force-sanitize to integer)
		$friendID = (int) $friendID;
		
		// If the UniID provided doesn't match your login
		if(!Me::$id)
		{
			return false;
		}
		
		// Loads an API to make the friend request (or confirms friend request)
		$packet = array(
			"uni_id" 		=> Me::$id		// The UniID responsible for the API call.
		,	"friend_id"		=> $friendID	// The UniID of the friend.
		);
		
		// Run the API
		$result = Connect::to("unifaction", "FriendAPI", $packet);
		
		switch($result)
		{
			case 0:
				Alert::saveError("Request Error", "There was an error requesting friends with that user.", 2);
				return false;
				
			case 1:
				Alert::saveSuccess("Request Successful", "You have successfully requested a friendship with that user.");
				return true;
				
			case 2:
				Alert::saveSuccess("Friend Success", "You are now friends with that user!");
				return true;
		}
		
		return false;
	}
	
	
/****** "Share Image on Social" Button ******/
	public static function shareImage
	(
		$imageURL		// <str> The URL of the image to share.
	,	$mobileURL = ""	// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""		// <str> The caption or message to associate with the image.
	,	$title = ""		// <str> The title to associate with the image.
	,	$sourceURL = ""	// <str> The URL to return to if this image is clicked.
	)					// RETURNS <str> the URL of the share link.
	
	// $href = Button::shareImage($imageURL, [$mobileURL], [$desc], [$title], [$sourceURL]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Share Image Array
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID of the page that you're posting to
		,	'poster_id'		=> Me::$id		// The person posting to the page (usually the same as UniID)
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("shareData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/share?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Share Video on Social" Button ******/
	public static function shareVideo
	(
		$videoURL		// <str> The URL of the video to share.
	,	$desc = ""		// <str> The description to associate with the image.
	,	$title = ""		// <str> The title to associate with the image.
	,	$sourceURL = ""	// <str> The URL to return to if this image is clicked.
	)					// RETURNS <str> the URL of the share link.
	
	// $href = Button::shareVideo($videoURL, [$desc], [$title], [$sourceURL]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Share Video Array
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID of the page that you're posting to
		,	'poster_id'		=> Me::$id		// The person posting to the page (usually the same as UniID)
		,	'video_url'		=> $videoURL	// The URL of the video to post
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("shareData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/share?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Share on Social" Action ******/
	public static function share_TeslaAction
	(
		$packet			// <str:mixed> The packet to send to UniFaction for sharing purposes.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Button/share?param[0]={$packet}
	{
		// Prepare Values
		$packet = Decrypt::run("shareData", $packet);
		$packet = Serialize::decode($packet);
		
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
	
	
/****** "Chat This" Button ******/
	public static function chatComment
	(
		$message			// <str> The message to chat.
	,	$sourceURL = ""		// <str> The URL to return to as a source.
	,	$origHandle = ""	// <str> The original handle that was responsible for the comment.
	)						// RETURNS <str> HTML output for the button.
	
	// Button::chatComment($message, [$sourceURL], [$origHandle]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Packet
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID posting the message
		,	'message'		=> $message		// Set this value to the message or caption to write
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originall posted the comment
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("chatData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/chat?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Chat This Image" Button ******/
	public static function chatImage
	(
		$imageURL			// <str> The URL of the image to share.
	,	$mobileURL = ""		// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""			// <str> The caption or message to associate with the image.
	,	$title = ""			// <str> The title to associate with the image.
	,	$sourceURL = ""		// <str> The URL to return to if this image is clicked.
	,	$origHandle = ""	// <str> The handle of the user that originally posted the image.
	)						// RETURNS <str> the URL of the share link.
	
	// $href = Button::chatImage($imageURL, [$mobileURL], [$desc], [$title], [$sourceURL], [$origHandle]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Chat Image Array
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID of the page that you're posting to
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originally posted the comment
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("chatData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/chat?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Chat This Video" Button ******/
	public static function chatVideo
	(
		$videoURL			// <str> The URL of the video to share.
	,	$desc = ""			// <str> The description to associate with the image.
	,	$title = ""			// <str> The title to associate with the image.
	,	$sourceURL = ""		// <str> The URL to return to if this image is clicked.
	,	$origHandle = ""	// <str> The handle of the user that originally posted the video.
	)						// RETURNS <str> the URL of the share link.
	
	// $href = Button::chatVideo($videoURL, [$desc], [$title], [$sourceURL], [$origHandle]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Share Image Array
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID of the page that you're posting to
		,	'video_url'		=> $videoURL	// The URL of the video to post
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		,	'orig_handle'	=> $origHandle	// The handle of the user that originally posted the comment
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("chatData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/chat?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
	
	
/****** "Chat This" Button ******/
	public static function chat_TeslaAction
	(
		$packet			// <str:mixed> The packet to send to UniFaction for sharing purposes.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// URL: /action/Button/chat?param[0]={$packet}
	{
		// Prepare Values
		$packet = Decrypt::run("chatData", $packet);
		$packet = Serialize::decode($packet);
		
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
	
	
/****** "Follow Me" Button ******/
	public static function addFollower
	(
		$uniID			// <int> The UniID of the person to add as a friend.
	)					// RETURNS <str> HTML output for the button.
	
	// $href = Button::button($tagID, $title, [$auto]);
	{
		// If this button is automatically submitting:
		if($auto)
		{
			// Redirect to plugin page that submits to API?
		}
	}
	
	
/****** "Chat This Article" Button ******/
/*
	public static function chatArticle
	(
		$imageURL			// <str> The URL of the image to share.
	,	$mobileURL = ""		// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""			// <str> The message to associate with the article, usually the first few sentences.
	,	$title = ""			// <str> The title to associate with the article.
	,	$sourceURL = ""		// <str> The URL of the article itself.
	,	$authorHandle = ""	// <str> The handle of the user that originally posted the article.
	)						// RETURNS <str> the URL of the share link.
	
	// $href = Button::chatArticle($imageURL, [$mobileURL], [$desc], [$title], [$sourceURL], [$authorHandle]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Chat Image Array
		$packet = array(
			'uni_id'		=> Me::$id			// The UniID of the page that you're posting to
		,	'type'			=> "article"		// The type of content being chatted
		,	'image_url'		=> $imageURL		// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL		// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title			// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc			// If set, this is the description of the attachment
		,	'source'		=> $sourceURL		// The URL of the sourced content
		,	'orig_handle'	=> $authorHandle	// The handle of the user that originally posted the content
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("chatData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/chat?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
//*/
	
/****** "Share Article on Social" Button ******/
/*
	public static function shareArticle
	(
		$imageURL		// <str> The URL of the image to share.
	,	$mobileURL = ""	// <str> The URL of the mobile version of image to share, if applicable.
	,	$desc = ""		// <str> The caption or message to associate with the image.
	,	$title = ""		// <str> The title to associate with the image.
	,	$sourceURL = ""	// <str> The URL to return to if this image is clicked.
	)					// RETURNS <str> the URL of the share link.
	
	// $href = Button::shareArticle($imageURL, [$mobileURL], [$desc], [$title], [$sourceURL]);
	{
		// Make sure the user is logged in and has a valid ID
		if(!Me::$id)
		{
			return "";
		}
		
		// Prepare the Share Article Array
		$packet = array(
			'uni_id'		=> Me::$id		// The UniID of the page that you're posting to
		,	'type'			=> "article"	// The type of content to share
		,	'poster_id'		=> Me::$id		// The person posting to the page (usually the same as UniID)
		,	'image_url'		=> $imageURL	// Set this value (absolute url) if you're posting an image
		,	'mobile_url'	=> $mobileURL	// Set this to the mobile verson of the image, if applicable
		,	'attach_title'	=> $title		// If set, this is the title of the attachment
		,	'attach_desc'	=> $desc		// If set, this is the description of the attachment
		,	'source'		=> $sourceURL	// The URL to link back to (if someone clicks on it)
		);
		
		// Prepare the Packet
		$packet = Serialize::encode($packet);
		$packet = urlencode(Encrypt::run("shareData", $packet, "fast"));
		
		// Return the URL
		return '/action/Button/share?param[0]=' . $packet . (self::$returnURL ? '&return=' . self::$returnURL : '');
	}
//*/

}

