<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ModuleImage Plugin ------
-----------------------------------------

This plugin is the standard image module for the content system.


*/

abstract class ModuleImage {
	
	
/****** Plugin Variables ******/
	public static $type = "Image";					// <str>
	
	// Available Styles
	public static $defaultClass = "content-img";	// <str> The default style to apply for this block.
	
	public static $imageStyles = array(	// <str:str> A list of styles associated with the image content blocks.
		"content-img"			=> "Image with Text"
	,	"content-img-left"		=> "Image on Left"
	,	"content-img-right"		=> "Image on Right"
	,	"content-img-cent"		=> "Centered Image, Small"
	,	"content-img-caption"	=> "Centered Image with Caption"
	);
	
	
/****** Run Behavior Tests for this module ******/
	public static function behavior
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void>
	
	// ModuleImage::behavior($formClass);
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
	
	
/****** Retrieve the Image Block Contents ******/
	public static function get
	(
		$blockID		// <int> The ID of the block to retrieve.
	,	$parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	)					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleImage::get($blockID, [$parse]);
	{
		if(!$result = Database::selectOne("SELECT class, title, body, image_url, mobile_url FROM content_block_image WHERE id=? LIMIT 1", array($blockID)))
		{
			return "";
		}
		
		// Get the image's class
		$photoClass = ($result['mobile_url'] != "" ? "post-image" : "post-image-mini");
		
		// Display the Image Block
		return '
		<div class="' . ($result['class'] == "" ? "content-img" : $result['class']) . '">
			' . (($result['image_url'] or $result['mobile_url']) ? Photo::responsive($result['image_url'], $result['mobile_url'], 450, "", 450, $photoClass) : '') . '
			' . ($result['title'] == "" ? "" : '<div class="block-title">' . $result['title'] . '</div>') . '
			<div class="block-body">' . ($parse ? nl2br(UniMarkup::parse($result['body'])) : nl2br($result['body'])) . '</div>
		</div>';
	}
	
	
/****** Draw the Form for the active Image Block ******/
	public static function drawForm
	(
		$formClass		// <mixed> The form class.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleImage::drawForm($formClass);
	{
		// Get the image block being edited
		if(!$result = Database::selectOne("SELECT class, title, body, image_url, mobile_url FROM content_block_image WHERE id=? LIMIT 1", array($formClass->blockID)))
		{
			return;
		}
		
		// Get the image's class
		$photoClass = ($result['mobile_url'] != "" ? "post-image" : "post-image-mini");
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$imageStyles, $result['class']);
		
		// Display the Form
		echo '
		<form class="uniform" action="' . $formClass->baseURL . '?content=' . ($formClass->contentID + 0) . '&t=' . $formClass->type . '&block=' . ($formClass->blockID + 0) . '" enctype="multipart/form-data" method="post">' . Form::prepare(SITE_HANDLE . "-modImage") . '
			<p><select name="class">' . $dropdownOptions . '</select></p>
			<p>Upload Image: <input type="file" name="image" value="" tabindex="30" /></p>
			<p><input type="text" name="title" value="' . $result['title'] . '" placeholder="Caption or Title of Image" size="64" maxlength="120"  tabindex="10" autocomplete="off" autofocus /></p>
			<p>
				' . UniMarkup::buttonLine() . '
				<textarea id="core_text_box" name="body" style="width:95%; height:130px;" placeholder="Text or Paragraph for the Image" tabindex="20">' . $result['body'] . '</textarea>
			</p>';
		
		// Show the Current Image for this Image Block (if it's already been uploaded)
		if($result['image_url'])
		{
			echo '
			<p>' . Photo::responsive($result['image_url'], $result['mobile_url'], 450, "", 450, $photoClass) . '</p>';
		}
		
		echo '
			<p><input type="submit" name="submit" value="Submit" tabindex="40" /></p>
		</form>';
	}
	
	
/****** Run the interpreter for Image Blocks ******/
	public static function interpret
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <void>
	
	// ModuleImage::interpret($formClass);
	{
		if(!Form::submitted(SITE_HANDLE . "-modImage")) { return; }
		
		// Prepare Values
		$subImage = (isset($_FILES['image']) and $_FILES['image']['tmp_name'] != "") ? true : false;
		
		$imageURL = "";
		$mobileURL = "";
		
		$_POST['class'] = (isset($_POST['class']) ? $_POST['class'] : '');
		$_POST['title'] = (isset($_POST['title']) ? $_POST['title'] : '');
		$_POST['body'] = (isset($_POST['body']) ? $_POST['body'] : '');
		
		// Validate the Form Values
		FormValidate::variable("Class", $_POST['class'], 0, 22, "-");
		FormValidate::safeword("Title", $_POST['title'], 0, 120, "'?");
		FormValidate::text("Body", $_POST['body'], 0, 4500);
		
		// Load an image, if one was submitted
		if($subImage)
		{
			// Initialize the plugin
			$imageUpload = new ImageUpload($_FILES['image']);
			
			// Set your image requirements
			$imageUpload->maxWidth = 4200;
			$imageUpload->maxHeight = 3500;
			$imageUpload->minWidth = 50;
			$imageUpload->minHeight = 50;
			$imageUpload->maxFilesize = 1024 * 3000;	// 3 megabytes
			$imageUpload->saveMode = Upload::MODE_OVERWRITE;
			
			// Set the image directory
			$srcData = Upload::fileBucketData($formClass->contentID, 10000);
			$bucketDir = '/assets/content/' . $srcData['main_directory'] . '/' . $srcData['second_directory'];
			$imageDir = CONF_PATH . $bucketDir;
			
			// Save the image to a chosen path
			if($imageUpload->validate())
			{
				$image = new Image($imageUpload->tempPath, $imageUpload->width, $imageUpload->height, $imageUpload->extension);
				
				if(FormValidate::pass())
				{
					// Prepare the proper scaling for the image
					$origWidth = ($imageUpload->width < 900) ? $imageUpload->width : 900;
					
					// Prepare the filename for this image
					$imageUpload->filename = $formClass->contentID . "-" . $formClass->blockID;
					
					// Save the original image
					$image->autoWidth($origWidth, (int) ($origWidth / $imageUpload->scale));
					$image->save($imageDir . "/" . $imageUpload->filename . ".jpg");
					
					$imageURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '.jpg';
					
					if($origWidth > 550)
					{
						$origWidth = 360;
						
						// Save the mobile version of the image
						$image->autoWidth($origWidth, (int) ($origWidth / $imageUpload->scale));
						$image->save($imageDir . "/" . $imageUpload->filename . "-mobile.jpg");
						
						$mobileURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '-mobile.jpg';
					}
				}
			}
		}
		
		// Update the Image Block
		if(FormValidate::pass())
		{
			self::update($formClass->contentID, $formClass->blockID, $imageURL, $_POST['title'], $_POST['body'], $_POST['class'], $mobileURL);
		}
	}
	
	
/****** Update an Image Block ******/
	public static function update
	(
		$contentID		// <int> The ID of the content entry.
	,	$blockID		// <int> The ID of the block.
	,	$imageURL		// <str> The URL of the image.
	,	$title			// <str> The title to set for the block.
	,	$body			// <str> The body / message to set for the block.
	,	$class			// <str> The class to assign to the block.
	,	$mobileURL = ""	// <str> The URL of the mobile image, if applicable.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleImage::update($contentID, $blockID, $imageURL, $title, $body, $class, $mobileURL);
	{
		// Prove that the active block is owned by the content
		if(!Database::selectOne("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, self::$type, $blockID)))
		{
			return 0;
		}
		
		// If no image was provided, don't update the image values
		if(!$imageURL)
		{
			Database::query("UPDATE content_block_image SET class=?, title=?, body=? WHERE id=? LIMIT 1", array($class, $title, $body, $blockID));
		}
		
		// Update the Image Block
		else
		{
			Database::query("UPDATE content_block_image SET class=?, title=?, body=?, image_url=?, mobile_url=? WHERE id=? LIMIT 1", array($class, $title, $body, $imageURL, $mobileURL, $blockID));
		}
		
		return $blockID;
	}
	
	
/****** Create an Image Block ******/
	public static function create
	(
		$formClass		// <mixed> The class data.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleImage::create($formClass);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_image (class, title, body, image_url, mobile_url) VALUES (?, ?, ?, ?, ?)", array(self::$defaultClass, "", "", "", "")))
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
		$blockID		// <int> The ID of the content block to delete.
	,	$contentID		// <int> The ID of the content entry.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleImage::purgeBlock($blockID, $contentID);
	{
		$pass = false;
		
		Database::startTransaction();
		
		if($imageURLs = Database::selectOne("SELECT image_url, mobile_url FROM content_block_image WHERE id=? LIMIT 1", array($blockID)))
		{
			if($pass = Database::query("DELETE FROM content_block_image WHERE id=? LIMIT 1", array($blockID)))
			{
				$pass = ContentForm::deleteSegment($contentID, self::$type, $blockID);
			}
		}
		
		if(Database::endTransaction($pass))
		{
			// Delete the images
			self::purgeImage($imageURLs['image_url']);
			self::purgeImage($imageURLs['mobile_url']);
		}
		
		return $pass;
	}
	
	
/****** Delete the image ******/
	public static function purgeImage
	(
		$imageURL		// <str> The URL of the image to delete.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ModuleImage::purge($imageURL);
	{
		$urlData = URL::parse($imageURL);
		
		if(isset($urlData['path']) and File::exists($urlData['path']))
		{
			return File::delete(CONF_PATH . '/' . trim($urlData['path'], "/"));
		}
		
		return false;
	}
	
}
