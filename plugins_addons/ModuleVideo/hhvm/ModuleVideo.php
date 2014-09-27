<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ModuleVideo Plugin ------
-----------------------------------------

This plugin is the standard video module for the content system.


*/

abstract class ModuleVideo {
	
	
/****** Plugin Variables ******/
	public static string $type = "Video";					// <str>
	
	// Available Styles
	public static string $defaultClass = "content-vid-cent";	// <str> The default style to apply for this block.
	
	public static array <str, str> $videoStyles = array(	// <str:str> A list of styles associated with the video content blocks.
		"content-vid-cent"		=> "Centered Video with Caption"
	,	"content-vid-full"		=> "Full-Size Video with Caption"
	);
	
	
/****** Retrieve the Video Block Contents ******/
	public static function get
	(
		int $blockID		// <int> The ID of the block to retrieve.
	,	bool $parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	): string					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleVideo::get($blockID, [$parse]);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_video WHERE id=?", array($blockID));
		
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
	public static function draw
	(
		int $blockID		// <int> The ID of the block.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// ModuleVideo::draw($blockID);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_video WHERE id=?", array($blockID));
		
		// Get the embed value for this URL
		$embed = Attachment::getVideoEmbedFromURL($result['video_url'][$blockID]);
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$videoStyles, $result['class']);
		
		// Display the Form
		echo '
		<p><select name="class[' . $blockID . ']">' . $dropdownOptions . '</select></p>
		<p>
			' . UniMarkup::buttonLine() . '
			<textarea id="video_caption_' . $blockID . '" name="caption[' . $blockID . ']" style="width:95%; height:100px;" placeholder="Caption or text for this video" tabindex="20" maxlength="255">' . $result['caption'] . '</textarea>
		</p>
		<p>' . $embed . '</p>
		<p>Set Video URL: <input type="text" name="video_url[' . $blockID . ']" value="' . $result['video_url'] . '" tabindex="10" autocomplete="off" autofocus maxlength="72" /></p>';
	}
	
	
/****** Run the interpreter for Video Blocks ******/
	public static function interpret
	(
		int $contentID		// <int> The ID of the content entry.
	,	int $blockID		// <int> The ID of the block to interpret.
	): void					// RETURNS <void>
	
	// ModuleVideo::interpret($contentID, $blockID);
	{
		// Sanitize Values
		$_POST['class'][$blockID] = Sanitize::variable($_POST['class'][$blockID], "-");
		$_POST['caption'][$blockID] = Sanitize::safeword($_POST['title'][$blockID], "'?\"");
		$_POST['video_url'][$blockID] = Sanitize::url($_POST['title'][$blockID]);
		
		// Update the Video Block
		self::update($contentID, $blockID, $_POST['video_url'][$blockID], $_POST['caption'][$blockID], $_POST['class'][$blockID]);
	}
	
	
/****** Update a Video Block ******/
	public static function update
	(
		int $contentID		// <int> The ID of the content entry to assign this video block to.
	,	int $blockID = 0	// <int> The ID of the video block to edit (or 0 if creating a new one).
	,	string $videoURL		// <str> The URL for the video.
	,	string $caption		// <str> The caption for the video block.
	,	string $class			// <str> The CSS class to assign to the text block.
	): int					// RETURNS <int> the ID of the image block, or 0 on failure.
	
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
		int $contentID		// <int> The content entry ID.
	): int					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleVideo::create($contentID);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_video (class, video_url, caption) VALUES (?, ?, ?)", array(self::$defaultClass, "", "")))
		{
			return 0;
		}
		
		$lastID = Database::$lastID;
		
		// Assign it to a Content Segment
		return (ContentForm::createSegment($contentID, self::$type, $lastID) ? $lastID : 0);
	}
	
	
/****** Purge a segment block from a content entry ******/
	public static function purgeBlock
	(
		int $blockID		// <int> The ID of the content block to delete.
	,	int $contentID		// <int> The ID of the content entry.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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