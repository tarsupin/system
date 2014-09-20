<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ModuleText Plugin ------
-----------------------------------------

This plugin is the standard text module for the content system.


*/

abstract class ModuleText {
	
	
/****** Plugin Variables ******/
	public static string $type = "Text";					// <str>
	
	// Available Styles
	public static string $defaultClass = "content-txt";	// <str> The default style to apply for this block.
	
	public static array <str, str> $textStyles = array(		// <str:str> A list of styles associated with the text content blocks.
		"content-txt"			=> "Standard Text"
	,	"content-txt-header"	=> "Header Text"
	,	"content-txt-quote"		=> "Block Quote"
	);
	
	
/****** Run Behavior Tests for this module ******/
	public static function behavior
	(
		mixed $formClass		// <mixed> The form class.
	): void					// RETURNS <void>
	
	// ModuleText::behavior($formClass);
	{
		// Generate a new text block
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
	
	
/****** Retrieve the Text Block Contents ******/
	public static function get
	(
		int $blockID		// <int> The ID of the block to retrieve.
	,	bool $parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	): string					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleText::get($blockID, [$parse]);
	{
		if(!$result = Database::selectOne("SELECT class, title, body FROM content_block_text WHERE id=? LIMIT 1", array($blockID)))
		{
			return "";
		}
		
		// Display the Text Block
		return '
		<div class="' . ($result['class'] == "" ? "content-txt" : $result['class']) . '">
			' . ($result['title'] == "" ? "" : '<div class="block-title">' . $result['title'] . '</div>') . '
			<div class="block-body">' . ($parse ? nl2br(UniMarkup::parse($result['body'])) : nl2br($result['body'])) . '</div>
		</div>';
	}
	
	
/****** Draw the Form for the active Text Block ******/
	public static function drawForm
	(
		mixed $formClass		// <mixed> The form class.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// ModuleText::drawForm($formClass);
	{
		// Get the text block being edited
		if(!$result = Database::selectOne("SELECT class, title, body FROM content_block_text WHERE id=? LIMIT 1", array($formClass->blockID)))
		{
			return;
		}
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$textStyles, $result['class']);
		
		// Display the Form
		echo '
		<form class="uniform" action="' . $formClass->baseURL . '?content=' . ($formClass->contentID + 0) . '&t=' . $formClass->type . '&block=' . ($formClass->blockID + 0) . '" method="post">' . Form::prepare(SITE_HANDLE . "-modText") . '
			<p><select name="class">' . $dropdownOptions . '</select></p>
			<p><input type="text" name="title" value="' . htmlspecialchars($result['title']) . '" placeholder="Title . . ." size="64" maxlength="120" tabindex="10" autocomplete="off" autofocus /></p>
			<p>
				' . UniMarkup::buttonLine() . '
				<textarea id="core_text_box" name="body" placeholder="Body or paragraph . . ." tabindex="20" style="height:120px; width:95%;">' . $result['body'] . '</textarea>
			</p>
			<p><input type="submit" name="submit" value="Submit" tabindex="30" /></p>
		</form>';
	}
	
	
/****** Run the interpreter for Text Blocks ******/
	public static function interpret
	(
		mixed $formClass		// <mixed> The class data.
	): void					// RETURNS <void>
	
	// ModuleText::interpret($formClass);
	{
		if(!Form::submitted(SITE_HANDLE . "-modText")) { return; }
		
		// Prepare Values
		$_POST['class'] = (isset($_POST['class']) ? $_POST['class'] : '');
		$_POST['title'] = (isset($_POST['title']) ? $_POST['title'] : '');
		$_POST['body'] = (isset($_POST['body']) ? Text::convertWindowsText($_POST['body']) : '');
		
		// Validate the Form Values
		FormValidate::variable("Class", $_POST['class'], 0, 22, "-");
		FormValidate::safeword("Title", $_POST['title'], 0, 120, "'?\"");
		
		if(strlen($_POST['body']) < 10)
		{
			Alert::error("Body Length", "The length of your content is too short.");
		}
		else if(strlen($_POST['body']) > 4500)
		{
			Alert::error("Body Length", "The length of your content is too long.");
		}
		
		// Update the Text Block
		if(FormValidate::pass())
		{
			self::update($formClass->contentID, $formClass->blockID, $_POST['title'], $_POST['body'], $_POST['class']);
		}
	}
	
	
/****** Update a Text Block ******/
	public static function update
	(
		int $contentID		// <int> The ID of the content entry.
	,	int $blockID		// <int> The ID of the block.
	,	string $title			// <str> The title to set for the block.
	,	string $body			// <str> The body / message to set for the block.
	,	string $class			// <str> The class to assign to the block.
	): int					// RETURNS <int> the ID of the text block, or 0 on failure.
	
	// ModuleText::update($contentID, $blockID, $title, $body, $class);
	{
		// Prove that the active block is owned by the content
		if(!Database::selectOne("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, self::$type, $blockID)))
		{
			return 0;
		}
		
		// Update the Text Block
		Database::query("UPDATE content_block_text SET class=?, title=?, body=? WHERE id=? LIMIT 1", array($class, $title, $body, $blockID));
		
		return $blockID;
	}
	
	
/****** Create a Text Block ******/
	public static function create
	(
		mixed $formClass		// <mixed> The class data.
	): int					// RETURNS <int> the ID of the text block, or 0 on failure.
	
	// ModuleText::create($formClass);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_text (class, title, body) VALUES (?, ?, ?)", array(self::$defaultClass, "", "")))
		{
			return 0;
		}
		
		$lastID = Database::$lastID;
		
		// Assign it to a Content Segment
		return (ContentForm::createSegment($formClass->contentID, self::$type, $lastID) ? $lastID : 0);
	}
	
	
/****** Purge a segment block from a content entry ******/
	public static function purgeBlock
	(
		int $blockID		// <int> The ID of the content block to delete.
	,	int $contentID		// <int> The ID of the content entry.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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