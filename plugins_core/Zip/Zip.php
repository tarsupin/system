<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------
------ About the Zip Plugin ------
----------------------------------

This plugin allows you to package and unpackage zip files.


----------------------------------------------------
------ Example of zipping and unzipping files ------
----------------------------------------------------

Standard zipping and unzipping of files is very easy:
	
	// Zip a directory
	Zip::package("/path/to/directory", "/path/to/save/zipfile.zip");
	
	// Zip a text file
	Zip::package("/path/to/file.txt", "/path/to/save/zipfile.zip");
	
	// Unzip a zipped package
	Zip::unpackage("/path/to/zipfile.zip", "/path/to/unpackage");


--------------------------------------
------ Parent Directory Setting ------
--------------------------------------

If you want to have the zip file also include it's parent name, set the third parameter $incParent to true.

	// Saves the parent folder in the zip
	Zip::package("/path/to/directory", "/path/to/save/zipfile.zip", true);
	
What this means is that the zip will contain something like this:
	
	"zipfile.zip" contains:
		/PARENT_DIRECTORY
			/file1.txt
			/file2.txt
		
Instead of this:

	"zipfile.zip" contains:
		/file1.txt
		/file2.txt
	
This syntax may occassionally be helpful for certain package handling functionality.

	
-------------------------------
------ Methods Available ------
-------------------------------

Zip::package($source, $targetFile)				// Takes the contents of a directory and zips them up.
Zip::unpackage($sourceFile, $targetDirectory)	// Unzips the contents of a directory into the target folder.

*/

abstract class Zip {
	
	
/****** Zip a Directory ******/
	public static function package
	(
		$source				// <str> The path to the file or directory to zip.
	,	$targetFile			// <str> The filepath where you would like to save the .zip file.
	,	$incParent = false	// <bool> If TRUE, include the base parent directory in the zip.
	)						// RETURNS <bool> TRUE on success, FALSE otherwise.
	
	// Zip::package('/path/to/compress/', './compressed.zip');
	{
		// Make sure we're able to use the zip library
		if(!extension_loaded('zip')) { return false; }
		
		// Prepare the path
		$source = str_replace('\\', '/', realpath($source));
		
		// Make sure the file exists and is safe
		if(!isSanitized::filepath($source)) { return false; }
		
		if(!file_exists($source)) { return false; }
		
		// Make sure the directory exists
		$targetDir = dirname($targetFile);
		
		if(!is_dir($targetDir))
		{
			Dir::create($targetDir);
		}
		
		// Prepare the Zip Functionality
		$zip = new ZipArchive();
		
		if(!$zip->open($targetFile, ZIPARCHIVE::CREATE))
		{
			return false;
		}
		
		// Run the Zip Processer
		if(is_dir($source) === true)
		{
			$baseDir = '';
			
			if($incParent)
			{
				$exp = explode("/", $source);
				$baseDir = $exp[count($exp) - 1] . '/';
				$zip->addEmptyDir($baseDir);
			}
			
			$files = Dir::getFiles($source, true, true);
			
			foreach($files as $file)
			{
				if(is_dir($source . '/' . $file) === true)
				{
					$zip->addEmptyDir($baseDir . $file);
				}
				else if(is_file($source . '/' . $file) === true)
				{
					$zip->addFile($source . '/' . $file, $baseDir . $file);
				}
			}
		}
		else if(is_file($source) === true)
		{
			$zip->addFile($file);
		}
		
		return $zip->close();
	}
	
	
/****** Unzip a Zipped File into a Directory ******/
	public static function unpackage
	(
		$sourceFile			// <str> The filepath of the file to unzip.
	,	$targetDirectory	// <str> The filepath where you would like to place the files that result.
	)						// RETURNS <bool> TRUE on success, FALSE otherwise.
	
	// Zip::unpackage('./compressed.zip', '/path/to/unzip/in/');
	{
		// Make sure the parent directory exists
		$parentDir = dirname($targetDirectory);
		
		if(!is_dir($parentDir))
		{
			Dir::create($parentDir);
		}
		
		// Unzip the package
		$zip = new ZipArchive;
		
		if($zip->open($sourceFile) === true)
		{
			$zip->extractTo($targetDirectory);
			$zip->close();
			
			return true;
		}
		
		return false;
	}
}
