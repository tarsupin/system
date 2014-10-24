<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Comment Plugin ------
--------------------------------------

This system works like a universal chat program of sorts - it is designed so that everyone can comment through a universal commenting system. If you process a comment through this class, it will automatically process itself through UniFaction with all of the features that UniFaction provides.

Some of the features of the comment system include:
	
	* Comments will automatically register with the Hashtag system.
	* Comments will automatically handle user handles and useer notifications.
	* Comments can be used to process tips.


--------------------------------------
------ How to process a Comment ------
--------------------------------------

The way your comments work on your own site may vary, so this plugin runs AFTER you have uploaded your comment to your site. Your comments may already be saved in your database before processing these comments with the UniFaction system. To process a commment, run the comment through the Comment::process() method:
	
	// Generic Call
	Comment::process($uniID, $comment);
	
	// Example of what it looks like
	Comment::process($user['uni_id'], "Hey @joeSmith, did you see #ThemApples");
	
There are optional parameters that you can set to make the comment more valuable, including setting a return URL that will allow others to find the specific page of where the comment originated. You can also set the UniID of the person the comment is replying to, which can activate reply functionality (such as notifications to the original poster, tips, etc.).
	
	// The UniID posting the comment
	$user['uni_id'] = 532483;
	
	// The comment to process through phpTesla / UniFaction
	$comment = "Hey @joeSmith, did you see #themApples?";
	
	// The URL that links back to this comment (optional)
	$sourceURL = "http://site.com/this-page#com-14";
	
	// The UniID that the comment is a reply to (if applicable)
	$reply['uni_id'] = 320123;
	
	// Process the comment
	Comment::process($user['uni_id'], $comment, $sourceURL, $reply['uni_id']);
	
	
-----------------------------------------
------ Show the syntax of comments ------
-----------------------------------------

There are two major syntax features for comments: linking to user profiles if their handle was shown, and linking to hashtags that were provided by the comment. For example, the comment below has two user handles and a hashtag:

	"@joeSmith, @friendOfMine, I just read the #HarryPotter series. It was pretty awesome."

If the comment syntax is shown, both of the user handles will link to those user's profiles. Clicking on the hashtag will direct the user to #HarryPotter hashtag.

To show comment syntax with your comments, you just need to run your comment through the ::showSyntax() method:

	$comment = Comment::showSyntax($comment);

It will be converted into HTML with the appropriate links set.


-------------------------------
------ Methods Available ------
-------------------------------

// Processes the comment through UniFaction
Comment::process($uniID, $comment, $link, $toUniID);

// Updates the comment syntax with links to users and hashtags
$comment = Comment::showSyntax($comment);

// Returns true if a comment has any @handles in it
Comment::hasUsers($comment);

// Returns a list of all @handles from a comment
$users = Comment::getUsers($comment);

*/

abstract class Comment {
	
	
/****** Process a Comment ******/
# This empowers a comment to use Hashtags and Notifications with other Users.
	public static function process
	(
		$uniID					// <int> The Uni-Account of the user that is commenting.
	,	$comment				// <str> The comment to post.
	,	$sourceURL = ""			// <str> The link to this particular comment.
	,	$responseToUniID = 0	// <int> The UniID of the target being commented to.
	,	$hashData = array()		// <str:mixed> The data necessary to process information for the hashtag.
	)							// RETURNS <bool> TRUE on success, FALSE if failed.
	
	// Comment::process($uniID, $comment, $sourceURL, $responseToUniID, $hashData);
	{
		// Check for hashtags and upload if applicable
		if(Hashtag::hasHashtags($comment))
		{
			// Upload the Image URL
			if(isset($hashData['thumbnail']))
			{
				$hashtags = Hashtag::digHashtags($comment);
				
				$title = (isset($hashData['title']) ? $hashData['title'] : "");
				$desc = (isset($hashData['description']) ? $hashData['description'] : "");
				
				Hashtag::submitImage($uniID, $hashData['thumbnail'], $comment, $hashtags, $sourceURL, $title, $desc);
			}
			
			// Upload the Video URL
			else if(isset($hashData['video_url']))
			{
				$hashtags = Hashtag::digHashtags($comment);
				
				$title = (isset($hashData['title']) ? $hashData['title'] : "");
				$desc = (isset($hashData['description']) ? $hashData['description'] : "");
				
				Hashtag::submitVideo($uniID, $hashData['video_url'], $comment, $hashtags, $sourceURL, $title, $desc);
			}
			
			// Upload Comments
			else
			{
				Hashtag::submitComment($uniID, $comment, $sourceURL);
			}
			
			// Check for site-specific custom hashtags, and activate them if applicable
			/*
			if(class_exists("MyCustomTags"))
			{
				$tags = Hashtag::digHashtags($comment);
				
				foreach($tags as $tag)
				{
					$tag = strtolower($tag);
					
					if(method_exists("MyCustomTags", $tag))
					{
						call_user_func_array(array("MyCustomTags", $tag), array($objectID, $commentID, $uniID));
					}
				}
			}
			*/
		}
		
		// Check for @handles, and notify them if applicable
		if($userList = self::getUsers($comment))
		{
			foreach($userList as $recipient)
			{
				if(!$recipientID = User::getIDByHandle($recipient))
				{
					$userData = User::silentRegister($recipient);
					
					$recipientID = (int) $userData['uni_id'];
				}
				
				if($recipientID and $recipientID != $uniID)
				{
					Notifications::create($recipientID, $sourceURL, $comment);
				}
			}
		}
		
		return true;
	}
	
	
/****** Show comment syntax, which adds links to user handles and hashtags ******/
	public static function showSyntax
	(
		$message				// <str> The notification message.
	,	$baseHandleSite = ""	// <str> The base site (url) to link handles to.
	)							// RETURNS <str> The message with appropriate links.
	
	// $message = Comment::showSyntax($message);
	{
		// Prepare Values
		$pos = 0;
		$count = 0;
		
		if($baseHandleSite == "")
		{
			$baseHandleSite = URL::fastchat_social();
		}
		
		// Loop through the syntax on the comment
		while(true)
		{
			$pos = strpos($message, "@", $pos);
			
			if($pos === false)
			{
				break;
			}
			
			// Get the Hashtag
			$user = Sanitize::whileValid((string) substr($message, $pos + 1, 22), "variable", '-');
			
			if(strlen($user) > 0)
			{
				$change = '<a href="' . $baseHandleSite . '/' . $user . '">@' . $user . '</a>';
				
				$message = substr_replace($message, $change, $pos, strlen($user) + 1);
				$pos += strlen($change);
			}
			
			if(++$count >= 10) { break; }
		}
		
		// List all valid hashtags
		$pos = 0;
		$count = 0;
		
		$hashtagURL = URL::hashtag_unifaction_com();
		
		while(true)
		{
			$pos = strpos($message, "#", $pos);
			
			if($pos === false) { break; }
			if(substr($message, $pos - 1, 1) == "&") { $pos++; continue; }
			
			// Get the hashtag
			$tag = Sanitize::whileValid(substr($message, $pos + 1, 22), "variable", '-');
			
			if(strlen($tag) > 0)
			{
				$change = '<a href="' . $hashtagURL . '/' . $tag . '">#' . $tag . '</a>';
				
				$message = substr_replace($message, $change, $pos, strlen($tag) + 1);
				$pos += strlen($change);
			}
			else
			{
				$pos += 1;
			}
			
			if(++$count >= 12) { break; }
		}
		
		// Return the message with new links available
		return $message;
	}
	
	
/****** Checks if the comment contains users ******/
	public static function hasUsers
	(
		$comment	// <str> The comment that you want to dig through for users marked as alerted.
	)				// RETURNS <bool> TRUE if there are users marked.
	
	// Comment::hasUsers("@joe and @bob, you guys are awesome!");	// Returns true
	{
		// Need to parse here
		$input = Parse::positionsOf($comment, "@");
		
		foreach($input as $pos)
		{
			$getUser = Sanitize::whileValid(substr($comment, $pos + 1, 22), "variable", '-');
			
			if(strlen($getUser) > 0)
			{
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Return user handles listed in the message (e.g. @MyHandle, @JoeSmith) ******/
	public static function getUsers
	(
		$comment	// <str> The comment that you want to dig through for users marked as alerted.
	)				// RETURNS <int:str> of users that were marked for alert.
	
	// $users = Comment::getUsers("@joe and @bob, You were great!");	// Returns array("joe", "bob")
	{
		// Prepare Values
		$users = array();
		
		// Need to parse here
		$input = Parse::positionsOf($comment, "@");
		
		foreach($input as $pos)
		{
			$getUser = Sanitize::whileValid(substr($comment, $pos + 1, 22), "variable", '-');
			
			if(strlen($getUser) > 0)
			{
				$users[] = $getUser;
				
				// Don't allow more than five users to be referred at once
				if(count($users) >= 5)
				{
					break;
				}
			}
		}
		
		return $users;
	}
}



