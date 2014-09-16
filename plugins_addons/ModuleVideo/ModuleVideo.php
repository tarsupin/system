<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ModuleVideo Plugin ------
-----------------------------------------

This plugin is the standard video module for the content system.


*/

abstract class ModuleVideo {
	
	
/****** Plugin Variables ******/
	public static $type = "Video";					// <str>
	
	// Available Styles
	public static $defaultClass = "content-vid-cent";	// <str> The default style to apply for this block.
	
	public static $videoStyles = array(	// <str:str> A list of styles associated with the video content blocks.
		"content-vid-cent"		=> "Centered Video with Caption"
	,	"content-vid-full"		=> "Full-Size Video with Caption"
	);
	
	
/****** Run Behavior Tests for this module ******/
	public static function behavior
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void>
	
	// ModuleVideo::behavior($formClass);
	{
		// Generate a new image block
		if($formClass->action == "segment" and !$formClass->blockID)
		{
			self::create($formClass);
		}
		
		// Delete a content block if that is being specified
		else if($formClass->action == "delete" and $formClass->contentID and $formClass->blockID)
		{
			self::purgeBlock($formClass->blockID, $formClass->contentID);
		}
		
		// Check for movement behaviors
		else if($formClass->action == "moveUp" and $formClass->contentID and $formClass->blockID)
		{
			ContentForm::moveUp($formClass->contentID, self::$type, $formClass->blockID);
		}
	}
	
	
/****** Retrieve the Video Block Contents ******/
	public static function get
	(
		$blockID		// <int> The ID of the block to retrieve.
	,	$parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	)					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleVideo::get($blockID, [$parse]);
	{
		if(!$result = Database::selectOne("SELECT class, video_url, caption FROM content_block_video WHERE id=? LIMIT 1", array($blockID)))
		{
			return;
		}
		
		// Get the Embed Value
		$embed = Attachment::getVideoEmbedFromURL($result['video_url']);
		
		// Display the Video Block
		return '
		<div class="' . ($result['class'] == "" ? "content-vid" : $result['class']) . '">
			<div class="video-body">' . $embed . '</div>
			' . ($result['caption'] == "" ? "" : '<div class="video-caption">' . nl2br(UniMarkup::parse($result['caption'])) . '</div>') . '
		</div>';
	}
	
	
/****** Draw the Form for the active Video Block ******/
	public static function drawForm
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleVideo::drawForm($formClass);
	{
		// Get the video block being edited
		if(!$result = Database::selectOne("SELECT class, video_url, caption FROM content_block_video WHERE id=? LIMIT 1", array($formClass->blockID)))
		{
			return;
		}
		
		// Get the embed value for this URL
		$embed = Attachment::getVideoEmbedFromURL($result['video_url']);
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$videoStyles, $result['class']);
		
		// Display the Form
		echo '
		<form class="uniform" action="' . $formClass->baseURL . '?content=' . ($formClass->contentID + 0) . '&t=' . $formClass->type . '&block=' . ($formClass->blockID + 0) . '" method="post">' . Form::prepare(SITE_HANDLE . "-modVideo") . '
			<p><select name="class">' . $dropdownOptions . '</select></p>
			<p>
				' . UniMarkup::buttonLine() . '
				<textarea id="core_text_box" name="caption" style="width:95%; height:100px;" placeholder="Caption or text for this video" tabindex="20">' . $result['caption'] . '</textarea>
			</p>
			<p>' . $embed . '</p>
			<p>Set Video URL: <input type="text" name="video_url" value="' . $result['video_url'] . '" tabindex="10" autocomplete="off" autofocus /></p>
			<p><input type="submit" name="submit" value="Submit" tabindex="30" /></p>
		</form>';
	}
	
	
/****** Run the interpreter for Video Blocks ******/
	public static function interpret
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <void>
	
	// ModuleVideo::interpret($formClass);
	{
		if(!Form::submitted(SITE_HANDLE . "-modVideo")) { return; }
		
		// Prepare Values
		$_POST['class'] = (isset($_POST['class']) ? $_POST['class'] : '');
		$_POST['caption'] = (isset($_POST['caption']) ? $_POST['caption'] : '');
		$_POST['video_url'] = (isset($_POST['video_url']) ? $_POST['video_url'] : '');
		
		// Validate the Form Values
		FormValidate::variable("Class", $_POST['class'], 0, 22, "-");
		FormValidate::text("Caption", $_POST['caption'], 0, 255);
		FormValidate::url("Video URL", $_POST['video_url'], 0, 72);
		
		// Update the Video Block
		if(FormValidate::pass())
		{
			self::update($formClass->contentID, $formClass->blockID, $_POST['video_url'], $_POST['caption'], $_POST['class']);
		}
	}
	
	
/****** Update a Video Block ******/
	public static function update
	(
		$contentID		// <int> The ID of the content entry to assign this video block to.
	,	$blockID = 0	// <int> The ID of the video block to edit (or 0 if creating a new one).
	,	$videoURL		// <str> The URL for the video.
	,	$caption		// <str> The caption for the video block.
	,	$class			// <str> The CSS class to assign to the text block.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleVideo::update($contentID, $blockID, $videoURL, $caption, $class);
	{
		// Prove that the active block is owned by the content
		if(!Database::selectOne("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, self::$type, $blockID)))
		{
			return 0;
		}
		
		// Update the Text Block
		Database::query("UPDATE content_block_video SET class=?, video_url=?, caption=? WHERE id=? LIMIT 1", array($class, $videoURL, $caption, $blockID));
		
		return $blockID;
	}
	
	
/****** Create a Video Block ******/
	public static function create
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleVideo::create($formClass);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_video (class, video_url, caption) VALUES (?, ?, ?)", array(self::$defaultClass, "", "")))
		{
			return false;
		}
		
		$lastID = Database::$lastID;
		
		// Assign it to a Content Segment
		return (ContentForm::createSegment($formClass->contentID, self::$type, $lastID) ? $lastID : 0);
	}
	
	
/****** Purge a segment block from a content entry ******/
	public static function purgeBlock
	(
		$blockID		// <int> The ID of the content block to delete.
	,	$contentID		// <int> The ID of the content entry.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleVideo::purgeBlock($blockID, $contentID);
	{
		Database::startTransaction();
		
		if($pass = Database::query("DELETE FROM content_block_video WHERE id=? LIMIT 1", array($blockID)))
		{
			$pass = ContentForm::deleteSegment($contentID, self::$type, $blockID);
		}
		
		return Database::endTransaction($pass);
	}
	
}
