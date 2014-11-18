<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Flair {
	
	
/****** Grant flair to a user ******/
	public static function assign
	(
		$uniID			// <int> The UniID to grant the auro to.
	,	$siteHandle		// <str> The site handle that is granting the flair.
	,	$title			// <str> The flair title being granted.
	,	$timeToAdd = 0	// <int> The number of seconds to add this flair for (0 for infinite).
	)					// RETURNS <bool> TRUE if the user received the flair, FALSE on failure.
	
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
