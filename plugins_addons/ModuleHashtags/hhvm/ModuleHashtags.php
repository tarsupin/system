<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------------
------ About the ModuleHashtags Plugin ------
---------------------------------------------

This plugin is the standard hashtag module for the content system.


*/

abstract class ModuleHashtags {
	
	
/****** Plugin Variables ******/
	public static string $type = "Hashtags";		// <str>
	
	
/****** Run behavior tests for this module ******/
	public static function behavior
	(
		mixed $formClass		// <mixed> The form class.
	): void					// RETURNS <void>
	
	// ModuleHashtags::behavior($formClass);
	{
		// Delete a hashtag from this content entry
		if($formClass->action == "meta" and isset($_GET['delete']))
		{
			self::delete($formClass->contentID, Sanitize::variable($_GET['delete']));
		}
	}
	
	
/****** Retrieve a list of hashtags assigned to a Content Entry ******/
	public static function get
	(
		int $contentID				// <int> The ID of the content entry to retrieve hashtags from.
	): array <int, str>							// RETURNS <int:str> The list of hashtags assigned to the content entry.
	
	// $hashtags = ModuleHashtags::get($contentID);
	{
		$hashtags = array();
		
		$hlist = Database::selectMultiple("SELECT hashtag FROM content_hashtags WHERE content_id=? AND submitted=?", array($contentID, 1));
		
		foreach($hlist as $h)
		{
			$hashtags[] = $h['hashtag'];
		}
		
		return $hashtags;
	}
	
	
/****** Retrieve a list of submitted hashtags vs. non-submited hashtags ******/
	public static function getBySub
	(
		int $contentID		// <int> The ID of the content entry to retrieve hashtags from.
	): array <int, array<int, str>>					// RETURNS <int:[int:str]> The list of hashtags assigned to the content entry.
	
	// list($submittedHashtags, $unsubmittedHashtags) = ModuleHashtags::getBySub($contentID);
	{
		if(!$results = Database::selectMultiple("SELECT hashtag, submitted FROM content_hashtags WHERE content_id=?", array($contentID)))
		{
			return array(array(), array());
		}
		
		$sub = array();
		$unsub = array();
		
		foreach($results as $res)
		{
			if($res['submitted'])
			{
				$sub[] = $res['hashtag'];
			}
			else
			{
				$unsub[] = $res['hashtag'];
			}
		}
		
		return array($sub, $unsub);
	}
	
	
/****** Draw the form for the Hashtags Module ******/
	public static function drawForm
	(
		mixed $formClass		// <mixed> The form class.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// ModuleHashtags::drawForm($formClass);
	{
		// Retrieve the list of hashtags from this content entry
		list($submittedHashtags, $unsubmittedHashtags) = self::getBySub($formClass->contentID);
		
		// Prepare Values
		$hashtagURL = URL::hashtag_unifaction_com();
		
		echo '
		<style>
			.hashtag { display:inline-block; margin-bottom:8px; background-color:#dddddd; padding:3px 6px 3px 6px; border-radius:6px; }
		</style>
		<div style="margin-top:22px;">';
		
		// List submitted hashtags, if available
		if($submittedHashtags)
		{
			echo '<div style="margin-bottom:22px;">
					<span style="font-weight:bold;">Submitted Hashtags:</span><br />';
			
			foreach($submittedHashtags as $hashtag)
			{
				echo '
				<div class="hashtag"><a href="' . $hashtagURL . '/' . $hashtag . '">#' . $hashtag . '</a></div>';
			}
			
			echo '</div>';
		}
		
		// List unsubmitted hashtags, if available
		if($unsubmittedHashtags)
		{
			echo '<div style="margin-bottom:22px;">
					<span style="font-weight:bold;">Queued Hashtags:</span><br />';
					
			if($formClass->contentData['status'] >= Content::STATUS_OFFICIAL or Content::$openPost == true)
			{
				echo '
					<span style="font-size:0.85em; color:red;">Note: The content has not been submitted to these hashtags yet. To submit them, go to "Settings" and click "Update".</span><br /><br />';
			}
			else
			{
				echo '
					<span style="font-size:0.85em; color:red;">Note: This content will be published to these hashtags once it is accepted as official.</span><br /><br />';
			}
			
			foreach($unsubmittedHashtags as $hashtag)
			{
				echo '
				<div class="hashtag"><a href="' . $hashtagURL . '/' . $hashtag . '">#' . $hashtag . '</a> <a href="' . $formClass->baseURL . '?action=meta&content=' . ($formClass->contentID + 0) . '&t=' . self::$type . '&delete=' . $hashtag . '&' . Link::prepare($formClass->contentData['uni_id'] . ":" . $formClass->contentID) . '">[X]</a></div>';
			}
			
			echo '</div>';
		}
		
		// If there are no hashtags available
		else if(!$submittedHashtags)
		{
			echo '<div style="margin-bottom:22px;">
					<span style="font-weight:bold;">Hashtags:</span><br />There are currently no hashtags assigned to this entry.</div>';
		}
		
		// Create the hashtag form
		echo '
			<form class="uniform" action="' . $formClass->baseURL . '?action=meta&content=' . ($formClass->contentID + 0) . '&t=' . self::$type . '" method="post">' . Form::prepare(SITE_HANDLE . "-modHashtags") . '
				<p>
					<input type="text" name="hashtag" value="' . (isset($_POST['hashtag']) ? Sanitize::variable($_POST['hashtag']) : '') . '" placeholder="Assign Hashtag . . ." size="22" maxlength="22" autocomplete="off" tabindex="10" autofocus />
					<input type="submit" name="add_hashtag" value="Add" />
				</p>
			</form>
		</div>';
	}
	
	
/****** Run the interpreter for the Hashtag Module ******/
	public static function interpret
	(
		mixed $formClass		// <mixed> The class data.
	): void					// RETURNS <void>
	
	// ModuleHashtags::interpret($formClass);
	{
		if(!Form::submitted(SITE_HANDLE . "-modHashtags")) { return; }
		
		// Prepare Values
		$_POST['hashtag'] = (isset($_POST['hashtag']) ? $_POST['hashtag'] : '');
		
		// Validate the Form Values
		FormValidate::variable("Hashtag", $_POST['hashtag'], 3, 22);
		
		// Update the Hashtag Data
		if(FormValidate::pass())
		{
			if(self::setHashtag($formClass->contentID, $_POST['hashtag']))
			{
				$_POST['hashtag'] = '';
				
				Alert::success("Hashtags Added", "The hashtag has been prepared for official submission.");
			}
		}
	}
	
	
/****** Add or update a Hashtag ******/
	public static function setHashtag
	(
		int $contentID		// <int> The ID of the content entry to assign this video block to.
	,	string $hashtag		// <str> The URL for the video.
	,	int $maxHash = 12	// <int> The number of hashtags the user is allowed to have.
	): bool					// RETURNS <bool> TRUE if the hashtag was updated, FALSE if not.
	
	// ModuleHashtags::setHashtag($contentID, $hashtag, [$maxHash]);
	{
		// Check how many hashtags
		$hashtags = self::get($contentID);
		
		// Prevent the user from having more than the allotted amount of hashtags
		if(count($hashtags) > $maxHash)
		{
			return false;
		}
		
		// Add the Hashtag
		return Database::query("INSERT IGNORE INTO content_hashtags (content_id, hashtag) VALUES (?, ?)", array($contentID, $hashtag));
	}
	
	
/****** Delete a hashtag from a content entry ******/
	public static function delete
	(
		int $contentID		// <int> The ID of the content entry to delete a hashtag from.
	,	string $hashtag		// <str> The hashtag to delete.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleHashtags::delete($contentID, $hashtag);
	{
		// Check if the hashtag has already been posted with the entry
		$submitted = Database::selectValue("SELECT submitted FROM content_hashtags WHERE content_id=? AND hashtag=? LIMIT 1", array($contentID, $hashtag));
		
		if($submitted === false or (int) $submitted == 1)
		{
			return false;
		}
		
		// Delete the hashtag from the entry
		return Database::query("DELETE FROM content_hashtags WHERE content_id=? AND hashtag=? LIMIT 1", array($contentID, $hashtag));
	}
	
	
/****** Indicate a list of hashtags that were submitted ******/
	public static function setSubmitted
	(
		int $contentID		// <int> The ID of the content entry to submit hashtags to.
	,	array <int, str> $hashtags		// <int:str> The hashtags that were submitted.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleHashtags::setSubmitted($contentID, $hashtags);
	{
		// Set the SQL values
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("hashtag" => $hashtags));
		
		array_unshift($sqlArray, $contentID);
		array_unshift($sqlArray, 1);
		
		// Update the Hashtag
		return Database::query("UPDATE content_hashtags SET submitted=? WHERE content_id=? AND " . $sqlWhere, $sqlArray);
	}
	
	
/****** Purge this module from a content entry ******/
	public static function purge
	(
		int $contentID		// <int> The ID of the content entry to purge from.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleHashtags::purge($contentID);
	{
		// We need to get a list of all hashtag entries that were submitted for content
		$hashtags = self::get($contentID);
		
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("hashtag" => $hashtags, "content_id" => array($contentID)));
		
		if($pass = Database::query("DELETE FROM content_by_hashtag WHERE " . $sqlWhere, $sqlArray))
		{
			$pass = Database::query("DELETE FROM content_hashtags WHERE content_id=?", array($contentID));
		}
		
		return $pass;
	}
	
}