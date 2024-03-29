<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ModuleText Plugin ------
-----------------------------------------

This plugin is the standard text module for the content system.


*/

abstract class ModuleText {
	
	
/****** Plugin Variables ******/
	public static $type = "Text";					// <str>
	
	// Available Styles
	public static $defaultClass = "content-txt";	// <str> The default style to apply for this block.
	
	public static $textStyles = array(		// <str:str> A list of styles associated with the text content blocks.
		"content-txt"			=> "Standard Text"
	,	"content-txt-quote"		=> "Block Quote"
	);
	
	
/****** Retrieve the Text Block Contents ******/
	public static function get
	(
		$blockID		// <int> The ID of the block to retrieve.
	,	$parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	)					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleText::get($blockID, [$parse]);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_text WHERE id=?", array($blockID));
		
		// Display the Text Block
		return '
		<div class="' . ($result['class'] == "" ? "content-txt" : $result['class']) . '">
			<div class="block-body">' . ($parse ? nl2br(UniMarkup::parse($result['body'])) : nl2br($result['body'])) . '</div>
		</div>';
	}
	
	
/****** Draw the Form for the active Text Block ******/
	public static function draw
	(
		$blockID		// <int> The block ID.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleText::draw($blockID);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_text WHERE id=?", array($blockID));
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$textStyles, $result['class']);
		
		// Display the Form
		echo '
		<div>
			<select name="class[' . $blockID . ']">' . $dropdownOptions . '</select> &nbsp; &nbsp; ' . UniMarkup::buttonLine("module_text_" . $blockID) . '
		</div>
		<div style="margin-top:10px;">
			<div id="remaining-chars-' . $blockID . '"></div>
			<textarea id="module_text_' . $blockID . '" name="body[' . $blockID . ']" placeholder="Body or paragraph . . ." tabindex="20" style="height:120px; width:95%;" maxlength="45000" onkeyup="CheckCharsRemaining(\'module_text_' . $blockID . '\', \'remaining-chars-' . $blockID . '\', 45000)">' . $result['body'] . '</textarea>
		</div>';
	}
	
	
/****** Run the interpreter for Text Blocks ******/
	public static function interpret
	(
		$contentID		// <int> The ID of the content entry.
	,	$blockID		// <int> The ID of the block to interpret.
	)					// RETURNS <void>
	
	// ModuleText::interpret($contentID, $blockID);
	{
		// Sanitize Values
		$_POST['class'][$blockID] = Sanitize::variable($_POST['class'][$blockID], "-");
		$_POST['body'][$blockID] = (isset($_POST['body'][$blockID]) ? Text::convertWindowsText($_POST['body'][$blockID]) : '');
		
		if(strlen($_POST['body'][$blockID]) > 50000)
		{
			$_POST['body'][$blockID] = substr($_POST['body'][$blockID], 0, 50000);
			
			Alert::info("Body Length", "The length of one of your text boxes was cut short.");
		}
		
		// Update the Text Block
		self::update($contentID, $blockID, $_POST['body'][$blockID], $_POST['class'][$blockID]);
	}
	
	
/****** Update a Text Block ******/
	public static function update
	(
		$contentID		// <int> The ID of the content entry.
	,	$blockID		// <int> The ID of the block.
	,	$body			// <str> The body / message to set for the block.
	,	$class			// <str> The class to assign to the block.
	)					// RETURNS <int> the ID of the text block, or 0 on failure.
	
	// ModuleText::update($contentID, $blockID, $body, $class);
	{
		// Prove that the active block is owned by the content
		if(!Database::selectOne("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, self::$type, $blockID)))
		{
			return 0;
		}
		
		// Update the Text Block
		Database::query("UPDATE content_block_text SET class=?, body=? WHERE id=? LIMIT 1", array($class, $body, $blockID));
		
		return $blockID;
	}
	
	
/****** Create a Text Block ******/
	public static function create
	(
		$contentID		// <int> The content entry ID.
	)					// RETURNS <int> the ID of the text block, or 0 on failure.
	
	// ModuleText::create($contentID);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_text (class, body) VALUES (?, ?)", array(self::$defaultClass, "")))
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
		$blockID		// <int> The ID of the content block to delete.
	,	$contentID		// <int> The ID of the content entry.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleText::purgeBlock($blockID, $contentID);
	{
		Database::startTransaction();
		
		if($pass = Database::query("DELETE FROM content_block_text WHERE id=? LIMIT 1", array($blockID)))
		{
			$pass = ContentForm::deleteSegment($contentID, self::$type, $blockID);
		}
		
		return Database::endTransaction($pass);
	}
	
}
