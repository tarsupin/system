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
		"content-img"				=> "Centered Image, 100% Width"
	,	"content-img-mid"			=> "Centered Image, 70% Width"
	,	"content-img-sm"			=> "Centered Image, 50% Width"
	,	"content-img-left"			=> "Image on Left, 40% Width"
	,	"content-img-left-sm"		=> "Image on Left, 25% Width"
	,	"content-img-right"			=> "Image on Right, 40% Width"
	,	"content-img-right-sm"		=> "Image on Right, 25% Width"
	);
	
	
/****** Retrieve the Image Block Contents ******/
	public static function get
	(
		$blockID		// <int> The ID of the block to retrieve.
	,	$parse = true	// <bool> TRUE to translate UniMarkup in this content block, FALSE if not.
	)					// RETURNS <str> the HTML block content.
	
	// $blockContent = ModuleImage::get($blockID, [$parse]);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_image WHERE id=?", array($blockID));
		
		// Get the image's class
		$photoClass = ($result['mobile_url'] != "" ? "post-image" : "post-image-mini");
		
		$result['img_class'] = $result['img_class'] == "" ? "content-img" : $result['img_class'];
		
		// Display the Image Block
		return '
		<div class="cmob-img ' . $result['img_class'] . '">
		<div class="' . $result['img_class'] . '-inner">
			' . ($result['credits'] == "" ? "" : '<div class="block-credits">' . $result['credits'] . '</div>') . '
			' . (($result['image_url'] or $result['mobile_url']) ? Photo::responsive($result['image_url'], $result['mobile_url'], 450, "", 450, $photoClass) : '') . '
		</div>
		' . ($result['caption'] == "" ? "" : '<div class="block-caption">' . $result['caption'] . '</div>') . '
		</div>';
	}
	
	
/****** Draw the Form for the active Image Block ******/
	public static function draw
	(
		$blockID		// <int> The ID of the block.
	)					// RETURNS <void> outputs the appropriate data.
	
	// ModuleImage::draw($blockID);
	{
		// Prepare Values
		$result = Database::selectOne("SELECT * FROM content_block_image WHERE id=?", array($blockID));
		
		// Get the image's class
		$photoClass = ($result['mobile_url'] != "" ? "post-image" : "post-image-mini");
		
		// Create the options for the class dropdown
		$dropdownOptions = StringUtils::createDropdownOptions(self::$imageStyles, $result['img_class']);
		
		// Display the Form
		echo '
		<div>';
		
		// Show the Current Image for this Image Block (if it's already been uploaded)
		if($result['image_url'])
		{
			echo '
			<div style="float:left; max-width:25%">' . Photo::responsive($result['image_url'], $result['mobile_url'], 450, "", 450, $photoClass) . '</div>
			<div style="margin-left:26%;">';
		}
		else
		{
			echo '<div>';
		}
		
		echo '
				<p><select name="img_class[' . $blockID . ']">' . $dropdownOptions . '</select></p>
				<p>Upload Image: <input type="file" name="image[' . $blockID . ']" value="" tabindex="30" /></p>
				<p><input type="text" name="caption[' . $blockID . ']" value="' . htmlspecialchars($result['caption']) . '" placeholder="Write caption here . . ." size="64" maxlength="180" tabindex="10" autocomplete="off" /></p>
				<p>Image credit: <input type="text" name="credits[' . $blockID . ']" value="' . $result['credits'] . '" placeholder="" size="64" maxlength="180" tabindex="10" autocomplete="off" /></p>
			</div>
		</div>';
	}
	
	
/****** Run the interpreter for Image Blocks ******/
	public static function interpret
	(
		$contentID		// <int> The ID of the content entry.
	,	$blockID		// <int> The ID of the block to interpret.
	)					// RETURNS <void>
	
	// ModuleImage::interpret($contentID, $blockID);
	{
		// Prepare Values
		$subImage = (isset($_FILES['image']) and $_FILES['image']['tmp_name'][$blockID] != "") ? true : false;
		
		$imageURL = "";
		$mobileURL = "";
		
		// Sanitize Values
		$_POST['img_class'][$blockID] = Sanitize::variable($_POST['img_class'][$blockID], '-');
		$_POST['credits'][$blockID] = Sanitize::safeword($_POST['credits'][$blockID], "'\"");
		$_POST['caption'][$blockID] = Sanitize::safeword($_POST['caption'][$blockID], "'?\"");
		
		// Load an image, if one was submitted
		if($subImage)
		{
			// Initialize the plugin
			$imageUpload = new ImageUpload($_FILES['image'], $blockID);
			
			// Set your image requirements
			$imageUpload->maxWidth = 4200;
			$imageUpload->maxHeight = 3500;
			$imageUpload->minWidth = 50;
			$imageUpload->minHeight = 50;
			$imageUpload->maxFilesize = 1024 * 3000;	// 3 megabytes
			$imageUpload->saveMode = Upload::MODE_OVERWRITE;
			
			// Set the image directory
			$srcData = Upload::fileBucketData($contentID, 10000);
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
					$imageUpload->filename = $contentID . "-" . $blockID;
					
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
		self::update($contentID, $blockID, $imageURL, $_POST['caption'][$blockID], $_POST['credits'][$blockID], $_POST['img_class'][$blockID], $mobileURL);
	}
	
	
/****** Update an Image Block ******/
	public static function update
	(
		$contentID		// <int> The ID of the content entry.
	,	$blockID		// <int> The ID of the block.
	,	$imageURL		// <str> The URL of the image.
	,	$caption		// <str> The caption to set for the block.
	,	$credits		// <str> The credits to set for the block.
	,	$class			// <str> The class to assign to the block.
	,	$mobileURL = ""	// <str> The URL of the mobile image, if applicable.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleImage::update($contentID, $blockID, $imageURL, $caption, $credits, $class, $mobileURL);
	{
		// Prove that the active block is owned by the content
		if(!Database::selectOne("SELECT block_id FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, self::$type, $blockID)))
		{
			return 0;
		}
		
		// If no image was provided, don't update the image values
		if(!$imageURL)
		{
			Database::query("UPDATE content_block_image SET img_class=?, caption=?, credits=? WHERE id=? LIMIT 1", array($class, $caption, $credits, $blockID));
		}
		
		// Update the Image Block
		else
		{
			Database::query("UPDATE content_block_image SET img_class=?, caption=?, credits=?, image_url=?, mobile_url=? WHERE id=? LIMIT 1", array($class, $caption, $credits, $imageURL, $mobileURL, $blockID));
		}
		
		return $blockID;
	}
	
	
/****** Create an Image Block ******/
	public static function create
	(
		$contentID		// <int> The content entry ID.
	)					// RETURNS <int> the ID of the image block, or 0 on failure.
	
	// ModuleImage::create($contentID);
	{
		// Create the Content Block
		if(!Database::query("INSERT INTO content_block_image (img_class, caption, credits, image_url, mobile_url) VALUES (?, ?, ?, ?, ?)", array(self::$defaultClass, "", "", "", "")))
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
