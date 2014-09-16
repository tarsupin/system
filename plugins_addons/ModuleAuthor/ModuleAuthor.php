<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------------
------ About the ModuleAuthor Plugin ------
---------------------------------------------


*/

abstract class ModuleAuthor {
	
	
/****** Plugin Variables ******/
	public static $type = "Author";			// <str>
	
	
/****** Get the data about the author ******/
	public static function get
	(
		$authorID		// <int> The UniID of the author.
	)					// RETURNS <str:str> the data array of each content entry for a related content slot.
	
	// $authorData = ModuleAuthor::get($authorID);
	{
		if(!$userData = Database::selectOne("SELECT a.blurb, u.handle FROM content_author a INNER JOIN users u ON a.uni_id=u.uni_id WHERE a.uni_id=? LIMIT 1", array($authorID)))
		{
			// Attempt to create an entry
			self::set($authorID, "");
			
			$userData = Database::selectOne("SELECT a.blurb, u.handle FROM content_author a INNER JOIN users u ON a.uni_id=u.uni_id WHERE a.uni_id=? LIMIT 1", array($authorID));
		}
		
		return $userData;
	}
	
	
/****** Draw the Form for the "About the Author" section ******/
	public static function drawForm
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleAuthor::drawForm($formClass);
	{
		// Retrieve the Author's Data
		$authorData = self::get($formClass->contentData['uni_id']);
		
		// Display the Form
		echo '
		<span style="font-weight:bold;">The Author, @' . $authorData['handle'] . ':</span><br />
		<img src="' . ProfilePic::image($formClass->contentData['uni_id'], "large") . '" style="margin-bottom:22px;" />
		
		<form class="uniform" action="' . $formClass->baseURL . '?action=meta&content=' . ($formClass->contentID + 0) . '&t=' . self::$type . '" method="post">' . Form::prepare(SITE_HANDLE . "-modAuthor") . '
			<p>
				<span style="font-weight:bold;">Details about the Author:</span><br />
				<textarea name="author_blurb" style="width:95%; height:100px;" placeholder="Details about the Author . . ." maxlength="200" tabindex="10">' . htmlspecialchars($authorData['blurb']) . '</textarea>
			</p>
			<p><input type="submit" name="submit" value="Update Author Data" tabindex="20" /></p>
		</form>';
	}
	
	
/****** Run the interpreter for this module ******/
	public static function interpret
	(
		$formClass			// <mixed> The class data.
	)						// RETURNS <void>
	
	// ModuleAuthor::interpret($formClass);
	{
		if(!Form::submitted(SITE_HANDLE . "-modAuthor")) { return; }
		
		if(isset($_POST['author_blurb']) and $_POST['author_blurb'])
		{
			if(self::set($formClass->contentData['uni_id'], $_POST['author_blurb']))
			{
				Alert::success("Author Updated", "The author has been updated.");
			}
		}
	}
	
	
/****** Set the data about an author ******/
	public static function set
	(
		$authorID		// <int> The UniID of the author.
	,	$blurb			// <str> The blurb (short message) about the author.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleAuthor::set($authorID, $blurb);
	{
		return Database::query("REPLACE INTO content_author (uni_id, blurb) VALUES (?, ?)", array($authorID, $blurb));
	}
	
	
/****** Show the widget for the author's details ******/
	public static function widget
	(
		$authorID		// <int> The UniID of the author.
	,	$sidePos = 30	// <int> The position in the sidebar to show this widget in.
	)					// RETURNS <void>
	
	// ModuleAuthor::widget($authorID, [$sidePos]);
	{
		// Retrieve the related articles
		if(!$authorData = self::get($authorID))
		{
			return;
		}
		
		if(!$authorData['blurb']) { return; }
		
		// Document List
		$html = '
		<div class="panel-box">
			<span class="panel-head">About the Author</span>
			<div style="padding:0px 16px 16px 16px; overflow:hidden;">
				<a href="' . URL::fastchat_social() . '/' . $authorData['handle'] . '" style="margin:0px; padding:0px;"><img src="' . ProfilePic::image($authorID, "medium") . '" style="float:left; margin-right: 8px; margin-bottom:2px;" /></a>
				<div style="text-align:justify;">' . $authorData['blurb'] . '</div>
			</div>
		</div>';
		
		WidgetLoader::add("SidePanel", ($sidePos + 0), $html);
	}
	
}
