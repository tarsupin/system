<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Flair {
	
	
/****** Grant flair to a user ******/
	public static function assign
	(
		int $uniID			// <int> The UniID to grant the auro to.
	,	string $siteHandle		// <str> The site handle that is granting the flair.
	,	string $title			// <str> The flair title being granted.
	,	int $timeToAdd = 0	// <int> The number of seconds to add this flair for (0 for infinite).
	): bool					// RETURNS <bool> TRUE if the user received the flair, FALSE on failure.
	
	// $success = Flair::assign($uniID, $siteHandle, $title, $addTime);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id"		=> (int) $uniID
		,	"site_handle"	=> Sanitize::variable($siteHandle)
		,	"title"			=> Sanitize::safeword($title)
		,	"add_time"		=> (int) $timeToAdd
		);
		
		return Connect::to("karma", "GrantFlairAPI", $packet);
	}
}