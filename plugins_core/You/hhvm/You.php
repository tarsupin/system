<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------------------------------------
------ About the "You" Plugin (the user you are actively viewing) ------
------------------------------------------------------------------------

This plugin sets up and handles the active *viewing* user that you're interacting with. For example, if Joe owns the "Super Cool Blog" and that's the page you're viewing, the "You" plugin would point to Joe.


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

Most sites that use the "You" plugin will load the plugin's variables in a custom way, such as:
	
	if($viewedUser = User::getDataByHandle($url[0], "uni_id, display_name, handle"))
	{
		You::$id = $viewedUser['uni_id'];
		You::$name = $viewedUser['display_name'];
		You::$handle = $viewedUser['handle'];
	}


-------------------------------
------ Methods Available ------
-------------------------------

You::load($uniID, [$columns]);		// Loads the user's values.

*/

abstract class You {
	
	
/****** Prepare Variables ******/
	public static int $id = 0;			// <int> The UniID of the "You" user
	public static string $name = "";		// <str> The display name of the "You" user
	public static string $handle = "";		// <str> The user handle of the "You" user
	
	
/****** Load "You" (the user being viewed or interacted with) ******/
	public static function load
	(
		int $uniID			// <int> The ID of the Uni-Account to load.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// You::load();
	{
		if(!$userData = User::get($uniID, "uni_id, handle, display_name"))
		{
			return false;
		}
		
		// Set the appropriate "You" values
		You::$id = (int) $userData['uni_id'];
		You::$handle = $userData['handle'];
		You::$name = $userData['display_name'];
		
		return true;
	}
}