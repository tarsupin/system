<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the File Plugin ------
-----------------------------------

This plugin provides methods for reading, writing, deleting, and otherwise working with files in the file system. In some cases, these methods are simple wrappers for directory functions that already exist in PHP.

Note: It is absolutely essential that you sanitize user input before running it through the "File" plugins. Do NOT assume that this plugin is sanitizing anything. You must be able to trust the data that you're passing to it. If you are using any user input for something file-related, it MUST be passed through the Sanitize::filepath() method.

The methods in this plugin are fairly self-explanatory. You will need to make sure that PHP has the proper permissions to run some of these methods, however, such as to create a file.


-------------------------------
------ Methods Available ------
-------------------------------

File::exists($filePath)									// Tests if the file exists (safe test / insertion).
File::read($filePath)									// Returns the contents of the file.
File::getLines($filePath)								// Returns the contents of the file.

File::create($filePath, $text)							// Creates a new file with provided text. Cannot overwrite.
File::write($filePath, $text)							// Writes a file with the text provided. Will overwrite.
File::edit($filePath, $replace, $needle, $needle..)		// Replaces a substring between the last two needles.
File::inject($filePath, $inject, $needle, $needle..)	// Injects a substring after the last needle.

File::delete($filePath)								// Deletes a file.
File::copy($fromPath, $toPath)						// Copies a file from one directory to another.
File::move($fromPath, $toPath)						// Moves a file from one directory to another.

File::setPermissions($filePath, $permMode = 0755)	// Sets the permission mode of a file.

*/

abstract class File {
	
	
/****** Check if File Path Exists (Safely) ******/
	public static function exists
	(
		string $filepath		// <str> The full file path of the file to check.
	): bool					// RETURNS <bool> TRUE if the file safely exists, FALSE otherwise.
	
	// File::exists("/path/to/file/myfile.php");
	{
		// If the filepath is using illegal characters or entries, reject the function
		if(!isSanitized::filepath($filepath))
		{
			return false;
		}
		
		return is_file($filepath);
	}
	
	
/****** Read File Contents ******/
	public static function read
	(
		string $filepath		// <str> The full file path of the file to read.
	): string					// RETURNS <str> Contents of the file (empty string if it doesn't exist).
	
	// $fileContents = File::read("/path/to/file/myfile.txt");
	{
		if(self::exists($filepath))
		{
			return file_get_contents($filepath);
		}
		
		return '';
	}
	
	
/****** Get Each Line in a File ******/
	public static function getLines
	(
		string $filepath		// <str> The full file path of the file to retrieve.
	): array <int, str>					// RETURNS <int:str> An array of the file's lines (in order).
	
	// File::getLines("/path/to/file/myfile.txt");
	{
		if(self::exists($filepath))
		{
			return file($filepath, FILE_IGNORE_NEW_LINES);
		}
		
		return array();
	}
	
	
/****** Create a File ******/
# This method creates a file with the desired content in it. If the file already exists, it will return false.
# Note: this file will attempt to create the directories leading up to the file if they do not currently exist.
	public static function create
	(
		string $filepath		// <str> The full file path of the file to create.
	,	string $text			// <str> The text to include in the file.
	): bool					// RETURNS <bool> TRUE if updated properly, FALSE if something went wrong.
	
	// File::create("/path/to/file/myfile.txt", "Some content that I would like to write to this file.");
	{
		return self::write($filepath, $text, false) == 0 ? false : true;
	}
	
	
/****** Write to a File ******/
# This method overwrites a file's content with content of your own. If the file doesn't exist, it creates it. The
# permissions need to be valid in order to write to this file.
# Note: this file will attempt to create the directories leading up to the file if they do not currently exist.
	public static function write
	(
		string $filepath			// <str> The full file path of the file to write.
	,	string $text				// <str> The text to include in the file.
	,	bool $overwrite = true	// <bool> Sets whether or not this function should overwrite existing files.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::write("/path/to/file/myfile.txt", "Some content that I would like to write to this file.");
	{
		// Check if the file already exists, and react accordingly
		if(self::exists($filepath) && $overwrite == false)
		{
			return false;
		}
		
		// Make sure the directories leading to the file exist
		$pos = strrpos($filepath, "/");
		$fileDirectory = substr($filepath, 0, $pos);
		
		if(!is_dir($fileDirectory))
		{
			Dir::create($fileDirectory);
		}
		
		// Write the content to the file
		return (bool) file_put_contents($filepath, $text);
	}
	
	
/****** Edit a substring in a File ******/
# This method modifies a file's content with content of your own. The file must exist. Permissions must be valid.
	public static function edit
	(
		string $filepath		// <str> The full file path of the file to edit.
	,	string $replace		// <str> The text to include in the file.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::edit($filePath, $replace, $needle, $needle..)
	{
		// Get the file's contents
		if(!$text = self::read($filepath))
		{
			return false;
		}
		
		// Identify the needles
		$args = func_get_args();
		
		if(count($args) < 4) { return false; }
		
		$needles = array();
		
		for($i = 2;$i < count($args);$i++)
		{
			$needles[] = $args[$i];
		}
		
		// Parse the string and get the proper positions
		$sendArgs = $needles;
		array_unshift($sendArgs, $text);
		
		list($pos1, $pos2) = call_user_func_array(array("Parse", "posLastTwo"), $sendArgs);
		
		$pos1 += strlen($needles[count($needles) - 2]);
		
		// Edit the Content
		$text = substr_replace($text, $replace, $pos1, $pos2 - $pos1);
		
		// Write the content to the file
		return (bool) file_put_contents($filepath, $text);
	}
	
	
/****** Inject a substring into a File ******/
# This method injects a substring into a file. The file must exist. Permissions must be valid.
	public static function inject
	(
		string $filepath			// <str> The full file path of the file to edit.
	,	string $inject				// <str> The text to inject into the file.
	): bool						// RETURNS <bool> TRUE if updated properly, FALSE if something went wrong.
	
	// File::inject($filePath, $inject, $needle, $needle..)
	{
		// Get the file's contents
		if(!$text = self::read($filepath))
		{
			return false;
		}
		
		// Identify the needles
		$args = func_get_args();
		
		if(count($args) < 4) { return false; }
		
		$needles = array();
		
		for($i = 2;$i < count($args);$i++)
		{
			$needles[] = $args[$i];
		}
		
		// Parse the string and get the proper positions
		$sendArgs = $needles;
		array_unshift($sendArgs, $text);
		
		$pos = call_user_func_array(array("Parse", "pos"), $sendArgs);
		
		$pos += strlen($needles[count($needles) - 1]);
		
		// Edit the Content
		$text = substr_replace($text, $inject, $pos, 0);
		
		// Write the content to the file
		return file_put_contents($filepath, $text);
	}
	
	
/****** Delete a File ******/
	public static function delete
	(
		string $filepath		// <str> The full file path of the file to delete.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::delete("/path/to/file/myfile.txt");
	{
		return unlink($filepath);
	}
	
	
/****** Copy a File ******/
	public static function copy
	(
		string $fromPath		// <str> The full file path of the file to copy.
	,	string $toPath			// <str> The full file path of the new location to copy/clone the original file.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::copy("/path/to/file/myfile.txt", "/new/path/myfile.txt");
	{
		return copy($fromPath, $toPath);
	}
	
	
/****** Move a File ******/
	public static function move
	(
		string $fromPath		// <str> The full file path of the file to move.
	,	string $toPath			// <str> The full file path of the new location - i.e. where you're moving the original file to.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::move ("/path/to/file/myfile.txt", "/new/path/myfile.txt");
	{
		return rename($fromPath, $toPath);
	}
	
	
/****** Set Permissions of a File ******/
	public static function setPermissions
	(
		string $filePath				// <str> The full file path of the file to set permissions on.
	,	int $permissionMode = 755	// <int> The number used to set the permission mode. (i.e. 0755, 755, etc)
	): bool							// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// File::setPermissions("/path/to/file/myfile.txt", 0755);	// Sets permissions to 0755
	{
		// Make sure the file exists and isn't a directory
		if(!self::exists($filePath))
		{
			return false;
		}
		
		// Append a "0" to the integer to make it valid
		if(is_numeric($permissionMode) && strlen($permissionMode) == 3)
		{
			$permissionMode = (string) "0" . $permissionMode;
		}
		
		return chmod($filePath, $permissionMode);
	}
}
