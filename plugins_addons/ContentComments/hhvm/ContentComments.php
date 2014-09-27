<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------------
------ About the ContentComments Plugin ------
----------------------------------------------


*/

abstract class ContentComments {
	
	
/****** Output the list of comments for this content entry ******/
	public static function draw
	(
		int $contentID			// <int> The ID of the content entry to output the comment section for.
	,	string $postURL			// <str> The URL to post to (the form action).
	,	int $commentLevel = 2	// <int> The comment level (e.g. Content::COMMENTS_STANDARD)
	): void						// RETURNS <void> outputs the appropriate data.
	
	// ContentComments::draw($contentID, $postURL, [$commentLevel]);
	{
		// Retrieve the necessary comments
		$comments = Database::selectMultiple("SELECT c.*, u.handle, u.display_name FROM content_comments_by_entry z INNER JOIN content_comments c ON z.comment_id=c.id INNER JOIN users u ON c.uni_id=u.uni_id WHERE z.content_id=? ORDER BY z.comment_id DESC LIMIT 0, 50", array($contentID));
		
		// Display Comment Section
		echo '
		<div class="comment-section" style="clear:both;">
			<h3>Comments</h3>';
		
		// Prepare Values
		$socialURL = URL::unifaction_social();
		$fastchatURL = URL::fastchat_social();
		
		// Display the Comments
		foreach($comments as $comment)
		{
			echo '
			<div class="comment">
				<div class="comment-left"><a href="' . $socialURL . '/' . $comment['handle'] . '"><img class="circimg" src="' . ProfilePic::image((int) $comment['uni_id'], "medium") . '" /></a></div>
				<div class="comment-right">
					<div class="comment-top">
						<div class="comment-data"><a href="' . $socialURL . '/' . $comment['handle'] . '"><span>' . $comment['display_name'] . '</span></a> <a class="handle" href="' . $fastchatURL . '/' . $comment['handle'] . '">@' . $comment['handle'] . '</a></div>
						<div class="comment-time-post">' . Time::fuzzy((int) $comment['date_posted']) . '</div>
					</div>
					<div class="comment-message">' . Comment::showSyntax($comment['comment']) . '</div>
				</div>
				<div class="comment-wrap"><div class="extralinks"><a href="/share?id=' . $comment['id'] . '"><span class="icon-comment"></span> Repost This</a> <a href="#"><span class="icon-coin"></span> Tip ' . $comment['display_name'] . '</a></div></div>
			</div>';
		}
		
		// Show the Comment Form (if applicable)
		if($commentLevel >= Content::COMMENTS_STANDARD or Me::$clearance >= 6)
		{
			echo '
			</div><div style="margin-top:22px;">
			<h4>Post a Comment</h4>';
			
			// Display the Comment Form
			self::drawForm($contentID, $postURL);
		}
		
		echo '</div>';
	}
	
	
/****** Draw the form for this comment system ******/
	public static function drawForm
	(
		int $contentID		// <int> The ID of the content entry to output the comment form for.
	,	string $postURL		// <str> The URL to post to (the form action).
	): void					// RETURNS <void> outputs the appropriate data.
	
	// ContentComments::drawForm($contentID, $postURL);
	{
		// If you're logged in, show the proper form
		if(Me::$loggedIn)
		{
			echo '
			<div>
				<form class="uniform" action="' . $postURL . '" method="post">' . Form::prepare(SITE_HANDLE . "-comment-" . $contentID) . '
					<p><textarea name="comment" placeholder="Write your comment here . . . " style="width:95%; height:120px;">' . (isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '') . '</textarea></p>
					<p><input type="submit" name="sub-comment" value="Post a Comment" /></p>
				</form>
			</div>';
		}
		
		// If you're not logged in, show a login option
		else
		{
			echo '
			<div><a class="button" href="/login">Login to Post a Comment</a></div>';
		}
	}
	
	
/****** Run the comment intepreter (such as if you post a comment) ******/
	public static function interpreter
	(
		int $contentID			// <int> The ID of the content entry that this interpreter is running for.
	,	string $sourceURL			// <str> The source of the URL, where links can return to.
	,	int $commentLevel = 2	// <int> The comment level (e.g. Content::COMMENTS_STANDARD)
	): void						// RETURNS <void>
	
	// ContentComments::interpreter($contentID, $sourceURL, [$commentLevel]);
	{
		// End the interpreter if no posting is allowed without clearance
		if($commentLevel < Content::COMMENTS_STANDARD and Me::$clearance < 6)
		{
			return;
		}
		
		// Run the form
		if(Form::submitted(SITE_HANDLE . "-comment-" . $contentID))
		{
			// Prepare Values
			$_POST['comment'] = (isset($_POST['comment']) ? $_POST['comment'] : '');
			
			if(!Me::$id)
			{
				Alert::error("Invalid User", "You must be logged in with a valid account.", 8);
			}
			
			// Validate the Form Values
			FormValidate::text("Comment", $_POST['comment'], 10, 500);
			
			// Post the Comment
			if(FormValidate::pass())
			{
				if(ContentComments::create($contentID, Me::$id, $_POST['comment'], $sourceURL))
				{
					Alert::success("Comment Posted", "Your comment was successfully posted to the system.");
					
					$_POST['comment'] = "";
				}
				else
				{
					Alert::error("Comment Failed", "An error was encountered while attempting to process your comment.");
				}
			}
		}
	}
	
	
/****** Create a Comment ******/
	public static function create
	(
		int $contentID		// <int> The ID of the content entry to post a comment to.
	,	int $uniID			// <int> The UniID that is posting the comment.
	,	string $comment		// <str> The comment to post.
	,	string $sourceURL		// <str> The source of the URL, where links can return to.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ContentComments::create($contentID, $uniID, $comment);
	{
		Database::startTransaction();
		
		// Insert the comment
		if($pass = Database::query("INSERT INTO content_comments (uni_id, comment, date_posted) VALUES (?, ?, ?)", array($uniID, $comment, time())))
		{
			if(Database::$lastID)
			{
				$pass = Database::query("INSERT INTO content_comments_by_entry (content_id, comment_id) VALUES (?, ?)", array($contentID, Database::$lastID));
			}
			else
			{
				$pass = false;
			}
		}
		
		if($success = Database::endTransaction($pass))
		{
			// Process the Comment through the UniFaction System
			Comment::process(Me::$id, $comment, $sourceURL, Me::$id, array());
		}
		
		return $success;
	}
	
}