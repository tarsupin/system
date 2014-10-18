<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class GeoLocate {
	
	
/****** GeoLocating by IP ******/
	public static function getByIP
	(
		$ip			// <str> The IP to check the geo-location of.
	)				// RETURNS <mixed> The amount of currency the user has, FALSE on error.
	
	// list($country, $region, $city) = GeoLocate::getByIP($ip);
	{
		// Call ipinfo's API
		if($result = file_get_contents("http://ipinfo.io/" . $ip . "/geo"))
		{
			if($result = json_decode($result))
			{
				return array(
					$result['country']
				,	$result['region']
				,	$result['city']
				);
			}
		}
		
		return array("", "", "");
	}
	
}
