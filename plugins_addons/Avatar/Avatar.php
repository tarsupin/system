<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Avatar Plugin ------
-------------------------------------

This class allows you to show a user's "avatar" if they have one, which is used for participating in online games, forums, or sites that want to use graphical avatars that you can customize with clothing and equipment of your choice.

To edit your own avatar, go to UniFaction's avatar site and activate an avatar for your profile. You'll be able to equip your avatar with items that are available on the site. Other sites in UniFaction will use this avatar, and some may even require it.

To show a user's avatar on your site:

	$aviImage = Avatar::image($uniID);

For example, it could be used in an HTML image tag like this:

	echo '<img src="' . Avatar::image($uniID) . "' />';
	
	
-------------------------------
------ Methods Available ------
-------------------------------

Avatar::hasAvatar()				// Checks if the active user has an avatar or not

Avatar::image($uniID)			// Returns an avatar
Avatar::imageData($uniID)		// Returns the avatar image structure

*/

abstract class Avatar {
	
	
/****** Confirm that the user has an avatar ******/
	public static function hasAvatar (
	)					// RETURNS <bool> TRUE if user has an avatar, FALSE otherwise.
	
	// Avatar::hasAvatar();
	{
		if(!Me::$loggedIn) { return false; }
		
		return ((isset(Me::$vals['avatar_opt']) and Me::$vals['avatar_opt']) ? true : false);
	}
	
	
/****** Show Avatar Image ******/
	public static function image
	(
		$uniID			// <int> The UniID whose avatar you'd like to show.
	,	$aviID = 1		// <int> The ID of the specific avatar. 1 is the default one.			
	)					// RETURNS <str> URL of the avatar to show off.
	
	// $aviImage = Avatar:image($uniID, [$aviID]);
	{
		$aviData = self::imageData($uniID, $aviID);
		
		return $aviData['site'] . $aviData['image_directory'] . $aviData['main_directory'] . $aviData['second_directory'] . '/' . $aviData['filename'];
	}
	
	
/****** Get Avatar Data ******/
	public static function imageData
	(
		$uniID			// <int> The UniID whose avatar you'd like to get data of.
	,	$aviID = 1		// <int> The ID of the specific avatar. 1 is the default one.			
	)					// RETURNS <str:str> data pertaining to the avatar's image.
	
	// $aviData = Avatar:imageData($uniID, [$aviID]);
	{
		$fileAdd = substr(str_replace(array('+', '=', '/'), array('', '', ''), base64_encode(md5("avatar:" . $uniID . ($aviID != 1 ? "_" . $aviID : "")))), 0, 10);
		
		$avatar = array(
			"site"				=> URL::avatar_unifaction_com()
		,	"image_directory"	=> '/avatars'
		,	"main_directory"	=> '/' . ceil($uniID / 25000)
		,	"second_directory"	=> '/' . ceil(($uniID % 25000) / 100)
		,	"filename"			=> $fileAdd . '.png'
		);
		
		$avatar['path'] = $avatar['image_directory'] . $avatar['main_directory'] . $avatar['second_directory'] . "/" . $avatar['filename'];
		
		return $avatar;
	}
}
