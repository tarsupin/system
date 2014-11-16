<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Bookmarks {
	
	
/****** Assign a bookmark to a user ******/
	public static function add_TeslaAction (
	): bool					// RETURNS <bool> TRUE if the user added the bookmark successfully, FALSE on failure.
	
	// $success = Bookmarks::add_TeslaAction();
	{
		// Make sure the user is logged in
		if(!Me::$loggedIn)
		{
			return false;
		}
		
		global $config;
		
		// Prepare the Packet
		$packet = array(
			"uni_id"		=> Me::$id
		,	"title"			=> $config['site-name']
		,	"url"			=> SITE_URL
		);
		
		// Connect to this API from UniFaction
		if($success = Connect::to("karma", "AddBookmarkAPI", $packet))
		{
			Alert::saveSuccess("Bookmark Added", "You have added this site as a bookmark!");
		}
		
		return $success;
	}
}