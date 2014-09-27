<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the ImageUpload Plugin ------
------------------------------------------

This plugin provides a method for uploading images. It will perform several validations on the image to make sure it meets the standards you are looking for during uploading.

Note: do not change the allowed mime types or extensions for the image uploads. These are processed through the Image plugin, which only accepts the mime types and extensions already provided by this plugin.


--------------------------------------------------
------ Simple example of uploading an image ------
--------------------------------------------------

// Check if the form is submitted
if(Form::submitted("upload-image"))
{
	// Initialize the plugin
	$imageUpload = new ImageUpload($_FILES['image']);
	
	// Set your image requirements
	$imageUpload->maxWidth = 1500;
	$imageUpload->maxFilesize = 1024 * 1000;	// 1 megabyte
		// ... and so forth
	
	// Save the image to a chosen path (in "overwrite" mode)
	if($imageUpload->save(APP_PATH . "/assets/images/" . $imageUpload->filename . "." . $imageUpload->extension, ImageUpload::MODE_OVERWRITE))
	{
		Alert::success("Image Uploaded!", "You have successfully uploaded the image!");
	}
}

// Create the image upload form
echo Alert::display() .'
<form action="/upload" method="post" enctype="multipart/form-data">' . Form::prepare("upload-image") . '
	Upload Image: <input type="file" name="image"> <input type="submit" value="Submit">
</form>';


------------------------------------------------
------ More advanced uploading techniques ------
------------------------------------------------

// This upload will take the original image and save it into multiple parts so that there is also a mobile version and
// a tablet version.

// Check if the form is submitted
if(Form::submitted('image-upload'))
{
	// Initialize the plugin
	$imageUpload = new ImageUpload($_FILES['image']);
	
	// Set your image requirements
	$imageUpload->maxWidth = 4200;
	$imageUpload->maxHeight = 3500;
	$imageUpload->minWidth = 50;
	$imageUpload->minHeight = 100;
	$imageUpload->maxFilesize = 1024 * 3000;	// 3 megabytes
	$imageUpload->saveMode = Upload::MODE_OVERWRITE;
		// ... and so forth
	
	// Set the image directory
	$srcData = Upload::fileBucketData(mt_rand(0, 100000));	// Change to a growing integer, like a primary key
	$bucketDir = '/assets/images/' . $srcData['main_directory'] . '/' . $srcData['second_directory'];
	$imageDir = APP_PATH . $bucketDir;
	
	// Save the image to a chosen path
	if($imageUpload->validate())
	{
		$image = new Image($imageUpload->tempPath, $imageUpload->width, $imageUpload->height, $imageUpload->extension);
		
		if(FormValidate::pass())
		{
			// Prepare the proper scaling for the image
			$origWidth = ($imageUpload->width < 900) ? $imageUpload->width : 900;
			
			// Prepare the filename for this image
			// $imageUpload->filename = $whateverIWant;
			
			// Save the original image
			$image->autoWidth($origWidth, $origWidth / $imageUpload->scale);
			$image->save($imageDir . "/" . $imageUpload->filename . ".jpg");
			
			$imageURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '.jpg';
			
			// Save the mobile version of the image
			if($origWidth > 400) { $origWidth = 360; }
			
			$image->autoWidth($origWidth, $origWidth / $imageUpload->scale);
			$image->save($imageDir . "/" . $imageUpload->filename . "-mobile.jpg");
			
			$mobileURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '-mobile.jpg';
			
			// Save the thumbnail version of the image
			if($origWidth > 180) { $origWidth = 180; }
			
			$image->autoWidth($origWidth, $origWidth / $imageUpload->scale);
			$image->save($imageDir . "/" . $imageUpload->filename . "-thumb.jpg");
			
			$thumbURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '-thumb.jpg';
		}
		
		// Now we can do whatever we want with the files:
		// Link to Original File: $imageDir . '/' . $imageUpload->filename . ".jpg"
	}
}

// Create the image upload form
echo Alert::display() .'
<form action="/upload" method="post" enctype="multipart/form-data">' . Form::prepare('image-upload') . '
	Upload Image: <input type="file" name="image"> <input type="submit" value="Submit">
</form>';


-------------------------------
------ Methods Available ------
-------------------------------

// Initialize the plugin
$imageUpload = new ImageUpload($_FILES['image']);

// Settings
$imageUpload->minWidth
$imageUpload->maxWidth
$imageUpload->minHeight
$imageUpload->maxHeight
$imageUpload->maxFileSize
$imageUpload->maxFilenameLength	
$imageUpload->quality				// Set between 0 and 100 for jpg images (100 is best)

// Save the image
$imageUpload->save($savePath);

// Validate the image
$success = $imageUpload->validate();

*/

class ImageUpload extends Upload {
	
	
/****** Plugin Variables ******/
	
	// Default Requirements
	public $allowedMimes = array("image/jpeg", "image/pjpeg", "image/png", "image/gif");	// <int:str> the allowed mime types for this upload
	public $allowedExtensions = array("jpg", "png", "gif");		// <int:str> the allowed extensions for this upload
	
	public $minWidth = 50;			// <int> the minimum image width allowed on this upload
	public $maxWidth = 3000;		// <int> the maximum image width allowed on this upload
	public $minHeight = 50;			// <int> the minimum image height allowed on this upload
	public $maxHeight = 3000;		// <int> the maximum image height allowed on this upload
	
	// Image Data
	public $quality = 90;			// <int> The quality (0 to 100) of the image (for jpg files)
	public $width = 0;				// <int> the width of the image
	public $height = 0;				// <int> the height of the image
	public $scale = 1;				// <int> the scale of the resulting image (width / height).
	
	
/****** Construct the image uploader ******/
	public function __construct
	(
		$_filesData			// <str:mixed> Set to $_FILES[$theInputName]
	,	$arrayVal = false	// <mixed> FALSE for standard use, $value if setting one of the array values to update.
	)						// RETURNS <void>
	
	// $imageUpload = new ImageUpload($_FILES['image'], [$arrayVal]);
	{
		// If we're selecting a single image uploaded from an array that was uploaded:
		if($arrayVal !== false)
		{
			if(!isset($_filesData['tmp_name'][$arrayVal]) or !isset($_filesData['type'][$arrayVal]) or !isset($_filesData['name'][$arrayVal]) or !isset($_filesData['size'][$arrayVal]))
			{
				return;
			}
			
			$_filesData['tmp_name'] = $_filesData['tmp_name'][$arrayVal];
			$_filesData['type'] = $_filesData['type'][$arrayVal];
			$_filesData['name'] = $_filesData['name'][$arrayVal];
			$_filesData['error'] = $_filesData['error'][$arrayVal];
			$_filesData['size'] = $_filesData['size'][$arrayVal];
		}
		
		// Validate the file data
		if(!$this->validateFileData($_filesData)) { return; }
		
		// Make sure the image is valid
		if(!exif_imagetype($this->tempPath)) { return; }
		
		// Prepare Values
		if(!$imageInfo = getimagesize($this->tempPath)) { return; }
		
		switch($imageInfo['mime'])
		{
			case "image/png":			$this->extension = "png";		break;
			case "image/gif":			$this->extension = "gif";		break;
			case "image/jpeg":			$this->extension = "jpg";		break;
		}
		
		// Set Image Details
		$this->filesize = $_filesData['size'];
		$this->width = $imageInfo[0];
		$this->height = $imageInfo[1];
		$this->scale = $this->width / $this->height;
		$this->mimeType = $imageInfo['mime'];
		
		// Set Upload Data
		$this->tempPath = $_filesData['tmp_name'];
		$this->filename = Sanitize::variable(substr($_filesData['name'], 0, strrpos($_filesData['name'], '.')), "- ");
		
		$this->valid = true;
	}
	
	
/****** Save the image ******/
	public function save
	(
		$savePath = ""	// <str> The path to save the image to.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $imageUpload->save($savePath);
	{
		// Attempt to validate the image
		if(!$this->validate()) { return false; }
		
		// Make sure the directory is valid
		if(!$this->validatePath($savePath)) { return false; }
		
		// Make sure the filename is valid
		if(!$this->handleFilename()) { return false; }
		
		// Save the image using the Image plugin
		$image = new Image($this->tempPath, $this->width, $this->height, $this->extension);
		$success = $image->save($this->saveDirectory . '/' . $this->filename . '.' . $this->toExtension, $this->quality);
		
		if(!$success)
		{
			Alert::error("Image Error", "There was an error uploading this image. Please try again.", 4);
			return false;
		}
		
		return true;
	}
	
	
/****** Validate the image to see if it should be allowed to be uploaded ******/
	public function validate (
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $imageUpload->validate();
	{
		// Check if the uploaded file is actually an image
		if(!in_array($this->mimeType, $this->allowedMimes))
		{
			Alert::error("Image Type", "You may not upload that type of image.", 8);
		}
		
		// Check the file size of the image
		if($this->filesize <= 0 or $this->filesize > $this->maxFileSize)
		{
			Alert::error("Image File Size", "The file size must be smaller than " . $this->maxFileSize . " bytes.", 3);
		}
		
		// Check the minimum and maximum width of the image
		if($this->minWidth == $this->maxWidth and $this->width != $this->minWidth)
		{
			Alert::error("Image Width", "The image must be " . $this->minWidth . " pixels in width.");
		}
		else if($this->width < $this->minWidth)
		{
			Alert::error("Image Width", "The image must be " . $this->minWidth . " pixels or greater in width.");
		}
		else if($this->width > $this->maxWidth)
		{
			Alert::error("Image Width", "The image must be " . $this->maxWidth . " pixels or less in width.");
		}
		
		// Check the minimum and maximum height of the image
		if($this->minHeight == $this->maxHeight and $this->height != $this->minHeight)
		{
			Alert::error("Image Height", "The image must be " . $this->minHeight . " pixels in height.");
		}
		else if($this->height < $this->minHeight)
		{
			Alert::error("Image Height", "The image must be " . $this->minHeight . " pixels or greater in height.");
		}
		else if($this->height > $this->maxHeight)
		{
			Alert::error("Image Height", "The image must be " . $this->maxHeight . " pixels or less in height.");
		}
		
		// Set invalid if there are any errors
		if(Alert::hasErrors())
		{
			$this->valid = false;
			return false;
		}
		
		return true;
	}
		
}
