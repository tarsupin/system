<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the ProfilePic Plugin ------
-----------------------------------------

This plugin allows you to display user's profile picture and site icons (the images that represent each site). The user's profile picture is the image that appears in the upper right corner when you log in. Profile pictures are used throughout the entire system. When you see someone's page, you'll often see their profile picture somewhere on it.

The profile pictures are saved at http://profilepic.unifaction.com - and they are saved in three separate sizes. The sizes are small, medium, and large.

To return a direct link to a user's profile pic, simply use the following code:

	$avatarImage = ProfilePic::image($uniID);

For example, it could be used in an HTML image tag like this:

	echo '<img src="' . ProfilePic::image($uniID) . "' />';

	

------------------------------------------------------------
------ Retrieving different sizes of Profile Pictures ------
------------------------------------------------------------

There are multiple picture sizes that you can retrieve. The default is the "small" pic. To change which size of picture you return, use the second parameter:

	ProfilePic::image($uniID, "small");		// 46 x 46 - the default size
	ProfilePic::image($uniID, "medium");	// 64 x 64
	ProfilePic::image($uniID, "large");		// 128 x 128
	
	ProfilePic::site($siteHandle, "small");		// 46 x 46 - the default size
	ProfilePic::site($siteHandle, "medium");	// 64 x 64
	ProfilePic::site($siteHandle, "large");		// 128 x 128

For example, you can return "large" pics and icons like this:

	// Draws a "large" profile pic (128x128)
	echo '<img src="' . ProfilePic::image($uniID, "large") . '" />';
	
	// Draws a "large" site icon (128x128)
	echo '<img src="' . ProfilePic::site($siteHandle, "large") . '" />';
	
	
--------------------------------------------
------ Default Sizes for Guest Images ------
--------------------------------------------

/pics/0/0/ZaV7xVMxN4.jpg		// Small
/pics/0/0/H1qwa2JbLC.jpg		// Medium
/pics/0/0/Psi9nAH46o.jpg		// Large


-------------------------------
------ Methods Available ------
-------------------------------

// Returns a profile pic of the designated size
ProfilePic::image($uniID, $size = "small")

// Returns the profile pic's IMG structure
ProfilePic::imageData($uniID, $size = "small")

// Returns a site icon of the designated size
ProfilePic::image($uniID, $size = "small")

// Returns the site icon's IMG structure
ProfilePic::imageData($uniID, $size = "small")


*/

abstract class ProfilePic {
	
	
/****** Return ProfilePic Image ******/
	public static function image
	(
		$uniID				// <int> The UniID that you'd like to show the profile pic of.
	,	$size = "small"		// <str> The size of the profile pic you'd like to return ("small", "medium", or "large").
	)						// RETURNS <str> URL of the profile pic to display.
	
	// echo '<img class="circimg-small" src="' . ProfilePic::image($uniID, "small") . '" />';		// 46x46 "small" image
	// echo '<img src="' . ProfilePic::image($uniID, "medium") . '" />';	// 64x64 "medium" image
	// echo '<img src="' . ProfilePic::image($uniID, "large") . '" />';		// 128x128 "large" image
	{
		$picData = self::imageData($uniID, $size);
		
		return $picData['site'] . $picData['image_directory'] . $picData['main_directory'] . $picData['second_directory'] . '/' . $picData['filename'] . '.' . $picData['ext'];
	}
	
	
/****** Get Profile Picture Data ******/
	public static function imageData
	(
		$uniID				// <int> The UniID that you'd like to get profile pic data for.
	,	$size = "small"		// <str> The size of the profile pic you'd like to return data for.
	)						// RETURNS <str:str> data pertaining to the profile pic's image.
	
	// var_dump(ProfilePic::imageData($uniID, "small"));
	{
		return array(
			"site"				=> URL::profilepic_unifaction_com()
		,	"image_directory"	=> '/pics'
		,	"main_directory"	=> '/' . ceil($uniID / 25000)
		,	"second_directory"	=> '/' . ceil(($uniID % 25000) / 100)
		,	"size"				=> $size
		,	"filename"			=> Security::hash($uniID . '.' . $size, 10, 62)
		,	"ext"				=> 'jpg'
		);
	}
	
	
/****** Return Site Icon Image ******/
	public static function site
	(
		$siteHandle			// <str> The site handle that you'd like to show the icon of.
	,	$size = "small"		// <str> The size of the site icon you'd like to return ("small", "medium", or "large").
	)						// RETURNS <str> URL of the site icon to display.
	
	// echo '<img src="' . ProfilePic::site($siteHandle, "small") . '" />';		// 46x46 "small" image
	// echo '<img src="' . ProfilePic::site($siteHandle, "medium") . '" />';	// 64x64 "medium" image
	// echo '<img src="' . ProfilePic::site($siteHandle, "large") . '" />';		// 128x128 "large" image
	{
		$siteData = self::siteData($siteHandle, $size);
		
		return $siteData['site'] . $siteData['image_directory'] . $siteData['main_directory'] . $siteData['second_directory'] . '/' . $siteData['filename'] . '.' . $siteData['ext'];
	}
	
	
/****** Get Site Icon Data ******/
	public static function siteData
	(
		$siteHandle			// <str> The site handle that you'd like to show the avatar of.
	,	$size = "small"		// <str> The size of the site icon you'd like to return data for.
	)						// RETURNS <str:str> data pertaining to the site's image.
	
	// var_dump(ProfilePic::siteData($siteHandle, "small"));
	{
		$hash = strtolower(Security::hash($siteHandle, 5, 62));
		
		return array(
			"site"				=> URL::profilepic_unifaction_com()
		,	"image_directory"	=> '/site-icons'
		,	"main_directory"	=> '/' . substr($hash, 0, 3)
		,	"second_directory"	=> '/' . substr($hash, 3, 2)
		,	"size"				=> $size
		,	"filename"			=> $siteHandle . '-' . $size
		,	"ext"				=> 'jpg'
		);
	}
	
}
