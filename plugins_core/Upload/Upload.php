<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Upload Plugin ------
-------------------------------------

This is the parent upload class, which provides important methods for the other uploading classes:
	
	ImageUpload::		// this plugin is used to upload images
	
	FileUpload::		// this plugin is used to upload files
	
*/

class Upload {
	
	
/****** Plugin Variables ******/
	
	// Default Requirements
	public $maxFileSize = 2097152;			// <int> the maximum file size allowed (default: 2 megabytes)
	public $allowedMimes = array();			// <int:str> the allowed mime types
	public $allowedExtensions = array();	// <int:str> the allowed extensions
	public $maxFilenameLength = 22;			// <int> the maximum filename length to allow
	
	// Upload Data
	public $extension = "";			// <str> the file's extension
	public $toExtension = "";		// <str> the extension to save the file to
	public $mimeType = "";			// <str> the mime type of the file
	public $valid = true;			// <bool> TRUE if the file is valid and can be uploaded
	public $filesize = 0;			// <int> the file size of the image to be uploaded
	
	// Directory Data
	public $tempPath = "";			// <str> the temporary path where the image was uploaded to
	public $saveDirectory = "";		// <str> the path where the image will be saved to
	public $filename = "";			// <str> the name of the image
	
	// Upload Save Modes
	public $saveMode = 0;			// <int> the active saving mode (rules for overwrite, rename, etc)
	
	const MODE_STANDARD = 0;		// Saving fails if the filename is already taken.
	const MODE_OVERWRITE = 1;		// Overwrites existing images if the filename is taken.
	const MODE_RENAME = 2;			// Renames the file if the original filename is taken.
	const MODE_UNIQUE = 3;			// Provides a unique name if the original filename is taken.
	
	
/****** Make sure the file data is valid ******/
	public function validateFileData
	(
		$_filesData		// <str:mixed> Set to $_FILES[$theInputName]
	)					// RETURNS <bool> TRUE if successful, FALSE if there were errors.
	
	// $upload->validateFileData($_FILES['myFile']);
	{
		// Make sure something was submitted
		if(!$_filesData) { return false; }
		
		// Determine if there are errors with the file
		if(isset($_filesData['error']) and $_filesData['error'] > 0)
		{
			switch($_filesData['error'])
			{
				case 1:
				case 2:
					Alert::error("File Upload", "File Error #" . $_filesData['error'] . " - The file size exceeds the allowance.", 1);
					break;
				
				case 3:
				case 7:
				case 8:
					Alert::error("File Upload", "File Error #" . $_filesData['error'] . " - Unable to finish uploading the file.");
					break;
				
				case 4:
					Alert::error("File Upload", "File Error #" . $_filesData['error'] . " - No file was uploaded.");
					break;
				
				case 6:
					Alert::error("File Upload", "File Error #" . $_filesData['error'] . " - A temporary directory does not exist.", 4);
					break;
				
				default:
					Alert::error("File Upload", "Unknown File Error - That image cannot be uploaded.", 4);
					break;
			}
			
			$this->valid = false;
			return false;
		}
		
		// Prepare Values
		$this->tempPath = $_filesData['tmp_name'];
		$this->filesize = $_filesData['size'];
		
		// Prepare additiona values
		$exp = explode(".", $_filesData['name']);
		
		$this->filename = Sanitize::variable($exp[0], "- ");
		$this->extension = Sanitize::variable($exp[count($exp) - 1]);
		
		$this->valid = true;
		return true;
	}
	
	
/****** Validate the file to see if it should be allowed to be uploaded ******/
	public function validateMime (
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $upload->validateMime();
	{
		// Check if the uploaded file is actually an image
		if(!in_array($this->mimeType, $this->allowedMimes))
		{
			Alert::error("Mime Type", "You may not upload that type of file.", 8);
			
			$this->valid = false;
			return false;
		}
		
		return true;
	}
	
	
/****** Validate the path to upload to ******/
	public function validatePath
	(
		$savePath = ""	// <str> The path to save the file to.
	)					// RETURNS <bool> TRUE if the validation is successful, FALSE if not.
	
	// $upload->validatePath($savePath);
	{
		
		if($this->valid == false) { return false; }
		
		// If the save path is valid
		$saveInfo = pathinfo($savePath);
		
		if(!isset($saveInfo['basename']) or !isset($saveInfo['dirname']) or !isset($saveInfo['extension']))
		{
			$this->valid = false;
			return false;
		}
		
		// Set values
		$this->saveDirectory = $saveInfo['dirname'];
		$this->filename = $saveInfo['filename'];
		$this->toExtension = $saveInfo['extension'];
		
		// Make sure the characters are valid
		$this->saveDirectory = rtrim(str_replace("\\", "/", $this->saveDirectory), "/");
		
		if(!isSanitized::filepath($this->saveDirectory . '/' . $this->filename . '.' . $this->extension))
		{
			Alert::error("Upload Filename", "The save destination is invalid - illegal extension or characters.", 9);
			
			$this->valid = false;
			return false;
		}
		
		// Confirm that the directory exists (otherwise create it)
		if(!Dir::create($this->saveDirectory))
		{
			Alert::error("Upload Directory", "The upload directory cannot be created. Please check permissions.", 4);
			
			$this->valid = false;
			return false;
		}
		
		return true;
	}
	
	
/****** Validate a file location (the path to upload to) ******/
	public function handleFilename (
	)					// RETURNS <bool> TRUE if the validation is successful, FALSE if not.
	
	// $upload->handleFilename();
	{
		// Check if the extension used is allowed
		if(!in_array($this->toExtension, $this->allowedExtensions))
		{
			Alert::error("Illegal Extension", "That file extension is not allowed.", 8);
			
			$this->valid = false;
			return false;
		}
		
		// If the image is provided a unique name (disregards original name)
		if($this->saveMode == self::MODE_UNIQUE)
		{
			$saltLen = 4;
			
			while($saltLen++ < 11 && $saltLen <= $this->maxFilenameLength)
			{
				$miscSalt = Security::randHash($saltLen, 62);
				
				if(!File::exists($this->saveDirectory . '/' . $miscSalt . '.' . $this->toExtension))
				{
					$this->filename = $miscSalt;
					
					return true;
				}
			}
			
			Alert::error("File Name", "Ending due to naming availability being overly exhausted.");
			
			$this->valid = false;
			return false;
		}
		
		// Check if a file of the same name has been uploaded
		if(File::exists($this->saveDirectory . '/' . $this->filename . '.' . $this->toExtension))
		{
			// Switch activity based on the image's save mode
			switch($this->saveMode)
			{
				// If the image is to be overwritten
				case self::MODE_OVERWRITE:
					if(strlen($this->filename) > $this->maxFilenameLength)
					{
						Alert::error("File Name Length", "The length of the image's filename has exceeded allowance.", 1);
						
						$this->valid = false;
						return false;
					}
					return true;
				
				// If the image will be renamed if a naming conflict is caught
				case self::MODE_RENAME:
					$saltLen = 3;
					
					while(true)
					{
						$miscSalt = Security::randHash($saltLen, 62);
						
						if(!File::exists($this->saveDirectory . '/' . substr($this->filename, $this->maxFilenameLength - $saltLen - 1) . '-' . $miscSalt . '.' . $this->toExtension))
						{
							$this->filename .= substr($this->filename, $this->maxFilenameLength - $saltLen - 1) . '-' . $miscSalt;
							return true;
						}
						
						if($saltLen++ > 7)
						{
							Alert::error("File Name", "Ending due to file's naming convention being too highly consumed.");
							
							$this->valid = false;
							return false;
						}
					}
					
					return true;
				
				// If the image is to be named AS-IS, no changes allowed
				case self::MODE_STANDARD:
				default:
					Alert::error("File Name", "A file already exists with that name.");
					
					$this->valid = false;
					return false;
			}
		}
		
		// Check if the filename is too long
		if(strlen($this->filename) > $this->maxFilenameLength)
		{
			Alert::error("File Name Length", "The length of the filename has exceeded allowance.", 1);
			
			$this->valid = false;
			return false;
		}
		
		return true;
	}
	
	
/****** Get the file location to retrieve something at ******/
	public static function fileBucket
	(
		$numericalValue		// <int> The numerical value of the integer to separate the files.
	,	$filename			// <str> The filename of the image.
	,	$ext				// <str> The extension of the image.
	,	$size = ""			// <str> The size that you'd like to return the image in.
	,	$divide = 2500		// <int> The amount to divide by for the bucket's purposes.
	)						// RETURNS <str> the source file of the image.
	
	// echo '<img src="' . Upload::fileBucket($numericalValue, $filename, $ext, "thumbnail", [$divide]) . '" />';
	{
		return "/" . ceil($numericalValue / $divide) . "/" . ($numericalValue % $divide) . "/" . $filename . ($size == "" ? "" : "-" . $size) . "." . $ext;
	}
	
	
/****** Get the data for the file location that you'll use ******/
	public static function fileBucketData
	(
		$numericalValue		// <int> The numerical value of the integer to separate the files.
	,	$divide = 2500		// <int> The amount to divide by for the bucket's purposes.
	)						// RETURNS <str:mixed> the data for the source directories of the image.
	
	// $srcData = Upload::fileBucketData($numericalValue, [$divide]);
	{
		return array(
			'main_directory'		=> ceil($numericalValue / $divide)
		,	'second_directory'		=> ($numericalValue % $divide)
		);
	}
		
}
