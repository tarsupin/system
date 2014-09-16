<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the FileUpload Plugin ------
-----------------------------------------

This plugin handles the uploading of files, including performing validations.


------------------------------------------
------ Examples of uploading a file ------
------------------------------------------

// Check if the form is submitted
if(Form::submitted("upload-file"))
{
	// Initialize the plugin
	$fileUpload = new FileUpload($_FILES['myFile']);
	
	// Set file requirements (if the defaults don't suit your needs)
	$fileUpload->maxFilesize = 1024 * 1000;				// 1 megabyte
	
	// Set which file extensions are allowed (automatically adds most mime types)
	$fileUpload->allow("txt", "pdf", "psd", "gz", "zip", "rar");
	
	// Save the file to a chosen path (in "overwrite" mode)
	if($fileUpload->save(APP_PATH . "/assets/files/" . $fileUpload->filename . "." . $fileUpload->extension, Upload::MODE_OVERWRITE))
	{
		Alert::success("File Uploaded", "You have uploaded " . $fileUpload->filename . "." . $fileUpload->extension . " successfully!");
	}
}

// Create the file upload form
echo Alert::display() .'
<form action="/upload" method="post" enctype="multipart/form-data">' . Form::prepare("upload-file") . '
	Upload File: <input type="file" name="myFile"> <input type="submit" value="Submit">
</form>';


-------------------------------
------ Methods Available ------
-------------------------------

// Initialize the plugin
$fileUpload = new FileUpload($_FILES['myFile']);

// Settings
$fileUpload->maxFileSize
$fileUpload->maxFilenameLength

// Choose what file types to allow
$fileUpload->allow("psd", "txt", "pdf");

// Choose what additional mime types to add
// Note: you should avoid this method unless the "allow" did not locate the mime type
$fileUpload->allowMimeTypes("application/photoshop");

// Save the image
$fileUpload->save($savePath);

// Validate the image
$success = $fileUpload->validate();

*/

class FileUpload extends Upload {
	
	
/****** Plugin Variables ******/
	
	
/****** Construct the file uploader ******/
	public function __construct
	(
		array <str, mixed> $_filesData		// <str:mixed> Set to $_FILES[$theInputName]
	): void					// RETURNS <void>
	
	// $fileUpload = new FileUpload($_FILES['myFile']);
	{
		// Validate the file data
		if(!$this->validateFileData($_filesData))
		{
			return;
		}
		
		// Get the mime type of the file
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$this->mimeType = finfo_file($finfo, $this->tempPath);
		finfo_close($finfo);
	}
	
	
/****** Save the file ******/
	public function save
	(
		string $savePath = ""	// <str> The path to save the file to.
	,	int $mode = 0		// <int> The save mode to use.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $fileUpload->save($savePath);
	{
		// Set the save mode
		$this->saveMode = $mode;
		
		// Attempt to validate the image
		if(!$this->validateMime()) { return false; }
		
		// Make sure the path is valid
		if(!$this->validatePath($savePath)) { return false; }
		
		// Make sure the filename is valid
		if(!$this->handleFilename()) { return false; }
		
		// Save the file to the designated location
		if(!move_uploaded_file($this->tempPath, $this->saveDirectory . '/' . $this->filename . '.' . $this->extension))
		{
			Alert::error("Upload Error", "There was an error uploading this file. Please try again.", 4);
			return false;
		}
		
		return true;
	}
	
	
/****** Allow extensions (which autoloads their corresponding mime types) ******/
	public function allow
	(
		// args		// <str> The extension to allow.
	): void				// RETURNS <void>
	
	// $fileUpload->allow([.. $extension], [.. $extension]);
	{
		$args = func_get_args();
		
		for($a = 0, $len = count($args);$a < $len;$a++)
		{
			$extension = Sanitize::variable($args[$a]);
			
			if(!in_array($extension, $this->allowedExtensions))
			{
				$this->allowedExtensions[] = $extension;
				
				$mimeTypes = MimeType::get($extension);
				
				foreach($mimeTypes as $value)
				{
					$value = Sanitize::variable($value, "./-");
					
					if(!in_array($value, $this->allowedMimes))
					{
						$this->allowedMimes[] = $value;
					}
				}
			}
		}
	}
	
	
/****** Allow Mime Types ******/
	public function allowMimeTypes
	(
		// args		// <str> The mime type to allow.
	): void					// RETURNS <void>
	
	// $fileUpload->allowMimeTypes([.. $mimeType], [.. $mimeType]);
	{
		$args = func_get_args();
		
		for($a = 0, $len = count($args);$a < $len;$a++)
		{
			$mimeType = Sanitize::variable($args[$a], "./-");
			
			if(!in_array($mimeType, $this->allowedMimes))
			{
				$this->allowedMimes[] = $mimeType;
			}
		}
	}
	
}