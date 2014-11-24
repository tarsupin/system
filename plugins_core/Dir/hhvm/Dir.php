<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the Directory Class ------
---------------------------------------

This plugin is used to create, delete, move, read, or otherwise work with directories in the file system. In some cases, these methods are simple wrappers for directory functions that already exist in PHP.

Note: It is absolutely essential that you sanitize user input before running it through the "Dir" and "File" plugins. Do NOT assume that this plugin is sanitizing anything. You must be able to trust the data that you're passing to it. If you are using any user input for something directory-related, it MUST be passed through the Sanitize::filepath() method.

The methods in this plugin are fairly self-explanatory. You will need to make sure that PHP has the proper permissions to run some of these methods, however, such as to create a directory.

-------------------------------
------ Methods Available ------
-------------------------------

Dir::exists($directory)											// Checks if the directory exists.
Dir::create($directory, $perm = 0755, $recursive = true)		// Creates directory [Optional: Parent directories].
Dir::delete($directory, $recursive = true)						// Deletes directory [Optional: Contents too].
Dir::getFiles($directory, $recursive = false, $foldersToo)		// Return all files [Optional: Folders too].
Dir::getFolders($directory, $recursive = false)					// Return all folders in a directory.

Dir::setOwner($directory, $owner = "_default_", $rec = false)	// Sets the owner for the directory.
Dir::setGroup($directory, $owner = "_default_", $rec = false)	// Sets the group for the directory.
Dir::setPermissions($directory, $perm = 0755, $rec = false)		// Set directory permissions [Opt: Contents].

*/

abstract class Dir {
	
	
/****** Check if Directory Exists (Safely) ******/
	public static function exists
	(
		string $filepath		// <str> The full file path of the directory to check.
	): bool					// RETURNS <bool> TRUE if the directory safely exists, FALSE otherwise.
	
	// Dir::exists("/path/to/file");
	{
		// If the filepath is using illegal characters or entries, reject the function
		if(!isSanitized::filepath($filepath))
		{
			return false;
		}
		
		return is_dir($filepath);
	}
	
	
/****** Create a Directory ******/
# If the directory's parents do not exist, this function will create them unless the option is disabled.
	public static function create (
		string $directory				// <str> The directory that you want to create.
	,	$perm = 0755			// <octal> The mode / permissions (CHMOD) of the directory you're creating.
	,	bool $recursive = true		// <bool> If TRUE this method creates the parent directories. If FALSE, it won't.
	): bool							// RETURNS <bool> TRUE if directory exists at completion, FALSE if not.
	
	// Dir::create(APP_PATH . "/path/to/directory");
	// Dir::create(APP_PATH . "/path/to/directory", 0755, false);	// Won't create parent directories
	{
		// If the directory already exists (or if the directory is empty), our job is finished
		if(is_dir($directory) or $directory == "")
		{
			return true;
		}
		
		// Reject the directory if it has illegal characters
		if(!isSanitized::filepath($directory))
		{
			return false;
		}
		
		// Attempt to create the directory
		return mkdir($directory, $perm, $recursive);
	}
	
	
/****** Move a directory, including all files contained ******/
	public static function move (
		string $sourcePath		// <str> The directory that you want to move.
	,	string $destPath		// <str> The directory that you want to move the source to.
	): bool					// RETURNS <bool> TRUE if directory was moved, FALSE if not.
	
	// Dir::move($sourcePath, $destPath);
	{
		// Reject the directory if it has illegal characters
		if(!isSanitized::filepath($sourcePath) or !isSanitized::filepath($destPath))
		{
			return false;
		}
		
		// Create the base destination path
		self::create($destPath);
		
		// Get all files (including recursively) in the folder
		$files = self::getFiles($sourcePath, true, true);
		
		$success = true;
		
		// Copy all of the files
		foreach($files as $file)
		{
			if(is_dir($sourcePath . "/" . $file))
			{
				if(!self::create($destPath . '/' . $file))
				{
					$success = false;
				}
				
				continue;
			}
			
			if(!copy($sourcePath . '/' . $file, $destPath . '/' . $file))
			{
				$success = false;
			}
		}
		
		// Delete the source folder if the move was successful
		return ($success ? self::delete($sourcePath) : false);
	}
	
	
/****** Delete a Directory ******/
# By default, this effect will recursively delete any files or other folders in the directory. However, you can
# disable that functionality, which would prevent any deletion if it had child content.
	public static function delete (
		string $directory			// <str> The directory that you want to create.
	,	bool $recursive = true	// <bool> If TRUE this method will delete all directory contents. If FALSE, it wont.
	)						// <bool> Returns TRUE if the directory exists at completion, FALSE if not.
	
	// Dir::delete("/path/to/directory");			// Deletes the directory and all contents.
	// Dir::delete("/path/to/directory", false);	// Deletes the directory only if it's empty.
	{
		// End the function if the directory doesn't exist
		if(!is_dir($directory))
		{
			return false;
		}
		
		/****** Recursive Deletion ******/
		if($recursive == true)
		{
			$contents = self::getFiles($directory, false, true);
			
			foreach($contents as $content)
			{
				// Delete all files and folders properly
				if(is_dir($directory . '/' . $content))
				{
					self::delete($directory . '/' . $content);
				}
				else
				{
					unlink($directory . '/' . $content);
				}
			}
		}
		
		// Remove the directory
		return rmdir($directory);
	}
	
	
/****** Get Files in a Directory ******/
	public static function getFiles
	(
		string $directory			// <str> The directory path to scan.
	,	bool $recursive = false	// <bool> If TRUE, do a recursive search.
	,	mixed $foldersToo = false	// <mixed> If TRUE, return all folders as well.
	): array <int, str>						// RETURNS <int:str> list of files, or array() on failure.
	
	// $files = Dir::getFiles("/path/to/dir");				// Gets files within the directory
	// $files = Dir::getFiles("/path/to/dir", true);		// Scans all subfolders as well (a recursive search)
	// $files = Dir::getFiles("/path/to/dir", true, true);	// Also returns folders (in addition to files)
	{
		if(!self::exists($directory))
		{
			return array();
		}
		
		// Open the directory and review any contents inside
		if($handle = opendir($directory))
		{
			$fileList = array();
			$scanList = array();
			
			// Loop through all of the contents of the directory and add it to the list
			while(($file = readdir($handle)) !== false)
			{
				if($file != "." && $file != "..")
				{
					$fullPath = $directory . "/" . $file;
					
					// Add folders to the list if it was set that they should be included
					if(is_dir($fullPath))
					{
						$scanList[] = $file;
						
						if($foldersToo === true || $foldersToo === "only")
						{
							$fileList[] = $file;
						}
					}
					
					// Add the file to the list
					else if($foldersToo !== "only")
					{
						$fileList[] = $file;
					}
				}
			}
			
			closedir($handle);
			
			// Recursive Results
			if($recursive == true)
			{
				foreach($scanList as $recFile)
				{
					if($rFiles = self::getFiles($directory . '/' . $recFile, true, $foldersToo))
					{
						foreach($rFiles as $rFile)
						{
							$fileList[] = $recFile . '/' . $rFile;
						}
					}
				}
			}
			
			return $fileList;
		}
		
		return array();
	}
	
	
/****** Get Folders in a Directory ******/
	public static function getFolders (
		string $directory				// <str> The directory path to scan.
	,	bool $recursive = false		// <bool> If TRUE, do a recursive search (will scan all sub-folders as well).
	): array <int, str>							// RETURNS <int:str> a list of the folders in the directory.
	
	// Dir::getFolders("/path/to/dir");			// Returns subfolders of the parent folder
	// Dir::getFolders("/path/to/dir", true);	// Returns recursive list of all subfolders in the list
	{
		return self::getFiles($directory, $recursive, "only");
	}
	
	
/****** Scan for specific Files in a Directory ******/
# This function scans a directory for specific files by name.
	public static function searchForFiles
	(
		string $directory				// <str> The directory path to scan.
	,	string $filename				// <str> the file to search for.
	,	bool $recursive = true		// <bool> If TRUE, do a recursive search.
	,	bool $foldersToo = true		// <bool> If TRUE, return all folders with the same name as well.
	): array <int, str>							// RETURNS <int:str> list of files, or array() on failure.
	
	// $files = Dir::getFiles("/path/to/dir", "filename.ext");
	// $files = Dir::getFiles("/path/to/dir", "filename.ext", true, [$recursive]);
	// $files = Dir::getFiles("/path/to/dir", "filename.ext", true, [$recursive]);
	{
		if(!self::exists($directory))
		{
			return array();
		}
		
		// Open the directory and review any contents inside
		if($handle = opendir($directory))
		{
			$fileList = array();
			
			// Loop through all of the contents of the directory and add it to the list
			while(($file = readdir($handle)) !== false)
			{
				if($file != "." && $file != "..")
				{
					$fullPath = $directory . "/" . $file;
					
					// Scan file for usefulness
					if(strpos($file, $filename) !== false)
					{
						array_push($fileList, $file);
					}
					
					// Recursive Results
					if($recursive == true)
					{
						if($rFiles = self::searchForFiles($directory . '/' . $file, $filename, $foldersToo, true))
						{
							foreach($rFiles as $rFile)
							{
								if(strpos($rFile, $filename) !== false)
								{
									array_push($fileList, $file . '/' . $rFile);
								}
							}
						}
					}
				}
			}
			
			closedir($handle);
			
			return $fileList;
		}
		
		return array();
	}
	
	
/****** Set Owner (chown) of a Directory ******/
	public static function setOwner(
		string $directory				// <str> The directory to set permissions on.
	,	string $owner = "_default_"	// <str> The owner name (user name) to give permissions on the directory / files.
	,	bool $recursive = false		// <bool> If TRUE, sets all recursive content to the same permissions.
	)							// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// Dir::setOwner("/path/to/directory", "www-data");			// Sets directory owner to "www-data"
	// Dir::setOwner("/path/to/directory", "www-data", true);	// Sets "www-data" permissions on all children content
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setOwner($directory . '/' . $content, $owner, true);
			}
		}
		
		// If the owner is set to "_default_", then set it to the name of the current user
		if($owner == "_default_")
		{
			// Make sure it's possible to identify the current user - if not, end the function
			if(!function_exists("exec"))
			{
				return false;
			}
			
			$owner = exec("whoami");
		}
		
		return chown($directory, $owner);
	}
	
	
/****** Set File Group (chgrp) of a Directory ******/
	public static function setGroup(
		string $directory				// <str> The directory to set permissions on.
	,	string $group = "_default_"	// <str> The name of the group to set.
	,	bool $recursive = false		// <bool> If TRUE, sets all recursive data to the same permissions.
	)							// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// Dir::setGroup("/path/to/directory", "www-data");			// Sets directory group to "www-data"
	// Dir::setGroup("/path/to/directory/, "www-data", true);	// Recursive directory, sets contents to "www-data"
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setGroup($directory . '/' . $content, $group, true);
			}
		}
		
		// If the group is set to "_default_", then set it to the name of the current user
		if($group == "_default_")
		{
			// Make sure it's possible to identify the current user - if not, end the function
			if(!function_exists("exec"))
			{
				return false;
			}
			
			$group = exec("whoami");
		}
		
		return chgrp($directory, $group);
	}
	
	
/****** Set Permissions (chmod) of a Directory ******/
	public static function setPermissions(
		string $directory					// <str> The directory to set permissions on.
	,	int $permissionMode = 755		// <int> The number used to set the permission mode (e.g. 755, etc)
	,	bool $recursive = false			// <bool> If TRUE, sets all contents inside to the same permissions.
	)								// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// Dir::setPermissions("/path/to/directory", 744);			// Sets directory to mode 744
	// Dir::setPermissions("/path/to/directory", 755, true);	// Sets directory and all contents to mode 755
	{
		/****** Recursive Permissions ******/
		if($recursive == true && is_dir($directory))
		{
			$contents = self::getFiles($directory, true);
			
			foreach($contents as $content)
			{
				self::setPermissions($directory . '/' . $content, $permissionMode, true);
			}
		}
		
		// Append a "0" to the integer to make it valid
		if(is_numeric($permissionMode) && strlen($permissionMode) == 3)
		{
			$permissionMode = "0" . $permissionMode;
		}
		
		return chmod($directory, $permissionMode);
	}
}