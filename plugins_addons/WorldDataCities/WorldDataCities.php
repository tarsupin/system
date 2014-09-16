<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------------
------ About the WorldDataCities Plugin ------
----------------------------------------------

This plugin allows the creation of and interaction with a large city database of the world's cities.


// Excellent resource for country codes, region codes, etc.
http://en.wikipedia.org/wiki/ISO_3166-1

// The region codes that MaxMind world uses is here:
http://en.wikipedia.org/wiki/List_of_FIPS_region_codes_%28M-O%29#NH:_Vanuatu

*/

abstract class WorldDataCities {
	
	
/****** Insert city data from MaxMind World Cities ******/
# https://www.maxmind.com/en/worldcities
# Note: License requires: "This product includes data created by MaxMind, available from http://www.maxmind.com"
	public static function insertMaxMindCSV
	(
		$pathToFile			// <str> The path to the CSV file of world city data.
	,	$startPos = 0		// <int> The position to start at.
	,	$limitNum = 50000	// <int> The number of 
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// WorldDataCities::insertMaxMindCSV($pathToFile, $startPos, $limitNum);
	{
		/*
		$_GET['nextStart'] = isset($_GET['nextStart']) ? $_GET['nextStart'] + 0 : 0;
		$position = $_GET['nextStart'];
		$num = 300000;
		
		$nextPos = WorldDataCities::insertMaxMindCSV(SYS_PATH . "/assets/files/worldcities.txt", $position, $num);
		
		header("Location: /test?nextStart=" . $nextPos); exit;
		*/
		
		$file = file_get_contents($pathToFile, false, null, $startPos, $limitNum);
		
		/*
		echo '
		<pre>' . $file . '</pre>';
		*/
		
		$fileLines = explode("\n", $file);
		
		$strlen = strlen($fileLines[count($fileLines) - 1]);
		$nextPos = $startPos + $limitNum - $strlen;
		
		var_dump($fileLines[0]);
		
		Database::startTransaction();
		
		foreach($fileLines as $line)
		{
			$lineData = explode(",", $line);
			
			if(!isset($lineData[6])) { continue; }
			
			// country code = $lineData[0]
			// city = $lineData[1]
			// city with accents = $lineData[2]
			// region number = $lineData[3]
			// population = $lineData[4]
			// latitude = $lineData[5]
			// longitude = $lineData[6]
			
			$latRound = floor($lineData[5]);
			$longRound = floor($lineData[6]);
			
			// Enter the city into the database
			Database::query("INSERT INTO world_data_cities (country_code, city, city_accents, region_code, population) VALUES (?, ?, ?, ?, ?)", array($lineData[0], $lineData[1], Text::safe($lineData[2]), $lineData[3], $lineData[4]));
			
			if($cityID = Database::$lastID)
			{
				Database::query("INSERT INTO world_data_geolocation (city_id, latitude_round, latitude, longitude_round, longitude) VALUES (?, ?, ?, ?, ?)", array($cityID, $latRound, number_format((float) $lineData[5], 3), $longRound, number_format((float) $lineData[6], 3)));
			}
		}
		
		Database::endTransaction();
		
		return $nextPos ? true : false;
	}
	
	
/****** Get city data by city name ******/
	public static function getByCityName
	(
		$queryString		// <str> The query string to use to search for the appropriate city.
	)						// RETURNS <int:mixed> the full list of city data between these gps boundaries.
	
	// $cities = WorldDataCities::getByCityName($queryString);
	{
		
	}
	
	
/****** Get city data within a particular GPS area ******/
	public static function getGPSBox
	(
		$latitudeMin		// <int> The minimum latitude.
	,	$latitudeMax		// <int> The maximum latitude.
	,	$longitudeMin		// <int> The minimum longitude.
	,	$longitudeMax		// <int> The maximum longitude.
	)						// RETURNS <int:mixed> the full list of city data between these gps boundaries.
	
	// $cities = WorldDataCities::getGPSBox($latitudeMin, $latitudeMax, $longitudeMin, $longitudeMax);
	{
		
	}
	
}
