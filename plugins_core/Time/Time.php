<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Time Plugin ------
-----------------------------------

This plugin provides you with tools to keep track of times for different purposes. It includes time that will be accurately synced between servers, times that are easy to read by humans (fuzzy time), conversions of time, etc.

This plugin is relied upon by other critical plugins, such as the API plugin.


------------------------------
------ Using Fuzzy Time ------
------------------------------

The Time::fuzzy() method is very useful for providing simple, human-readable strings from timestamp conversions. Most people won't know that the timestamp "1400376805" refers to early morning on May 18th in UTC, despite how useful the value is while programming. This is why converting timestamps to fuzzy time can be so helpful.

When you convert a timestamp to fuzzy time a string is returned that describes how soon (or how long ago) the timestamp was, such as "in two minutes" or "yesterday".

Using fuzzy time is very easy. You just run the timestamp through the Time::fuzzy($timestamp) method.

	// Returns "in one hour"
	echo Time::fuzzy(time() + 3600);
	
	// Returns "in ten minutes"
	echo Time::fuzzy(time() + 600);
	
	// Returns "five minutes ago"
	echo Time::fuzzy(time() - 300);


------------------------------
------ Time Conversions ------
------------------------------

Another useful feature of the Time plugin is the ability to convert words into time-based numerical values, similar to php's strtotime() function.

	// Returns numbers for each month
	echo Time::convertMonthToNumber("January");		// 1
	echo Time::convertMonthToNumber("September");	// 10
	
	// Returns numbers of week days
	echo Time::convertDayOfWeekToNumber("Monday");		// 1
	echo Time::convertDayOfWeekToNumber("Saturday");	// 6
	
	// Returns numbers of hours
	echo Time::convertHourToNumber("10 am");	// 10
	echo Time::convertHourToNumber("10 pm");	// 22
	echo Time::convertHourToNumber("2PM");		// 14
	echo Time::convertHourToNumber("1AM");		// 1


------------------------------
------ Methods Available ------
------------------------------

// Returns the month as a valid number
$month		= Time::convertMonthToNumber($month)

// Returns the day of week as a valid number
$dayOfWeek	= Time::convertDayOfWeekToNumber($day)

// Returns an hour as a valid number
$hour		= Time::convertHourToNumber($hour)

// Returns the current swatch time
$swatchTime	= Time::swatch()

// Returns unique timestamp for the current time
$uniqueTime	= Time::unique()

// TRUE if $match is within $range minutes of current unique()
$uniqueTime	= Time::unique($match, $range = 2)

// Returns a list of timezones
$timezones	= Time::timezones()

// Returns fuzzy time (e.g. "four hours ago")
$fuzzy		= Time::fuzzy($timestamp, [$relative])

*/

abstract class Time {
	
	
/****** Convert a month string to a valid number ******/
	public static function convertMonthToNumber
	(
		$month	// <mixed> The month to convert.
	)			// RETURNS <int> the numeric value of the month (e.g. 2 for "February").
	
	// $month = Time::convertMonthToNumber("March");	// Returns 3
	{
		// If the argument is a string (e.g. "January"), change it to a number.
		if(!is_numeric($month))
		{
			$translate = array("jan" => 1, "feb" => 2, "mar" => 3, "apr" => 4, "may" => 5, "jun" => 6, "jul" => 7, "aug" => 8, "sep" => 9, "oct" => 10, "nov" => 11, "dec" => 12);
			
			$month = substr(strtolower($month), 0, 3);
			
			// If the month couldn't be interpreted, end here
			if(!isset($translate[$month]))
			{
				return 0;
			}
			
			$month = $translate[$month];
		}
		
		// Restrict months from 1 to 12
		if($month < 1) { $month = 1; }
		else if($month > 12) { $month = 12; }
		
		return $month + 0;
	}
	
	
/****** Convert a day of the week string to a valid number ******/
	public static function convertDayOfWeekToNumber
	(
		$day	// <mixed> The day of the week to convert.
	)			// RETURNS <int> the numeric value of the day of the week (e.g. 3 for "Wednesday").
	
	// $day = Time::convertDayOfWeekToNumber("Friday");	// Returns 5
	{
		// If the argument is a string (e.g. "Monday"), change it to a number.
		if(!is_numeric($day))
		{
			$translate = array("mon" => 1, "tue" => 2, "wed" => 3, "thu" => 4, "fri" => 5, "sat" => 6, "sun" => 7);
			
			$day = substr(strtolower($day), 0, 3);
			
			if(!isset($translate[$day]))
			{
				return 0;
			}
			
			$day = $translate[$day];
		}
		
		// Prevent illegal days of the week
		if($day < 1) { $day = 1; }
		else if($day > 7) { $day = 7; }
		
		return $day + 0;
	}
	
	
/****** Convert an hour string to a valid number ******/
	public static function convertHourToNumber
	(
		$hour	// <mixed> The hour to convert.
	)			// RETURNS <int> the numeric value of the hour (e.g. 22 for "10 pm").
	
	// $hour = Time::convertHourToNumber("10 pm");		// Returns 22
	{
		// If the hour is a string (e.g. "3 pm", "1 am"), change it to a number.
		if(!is_numeric($hour))
		{
			$hour = strtolower($hour);
			
			if(strpos($hour, "pm") !== false)
			{
				$hour = (int) $hour + 12;
			}
		}
		
		return (int) $hour % 24;
	}
	
	
/****** Return the current cycle ******/
	public static function cycle
	(
		$type = "Ym"	// <str> The type of cycle (date format) such as: "Ym" for Year+Month
	,	$modifier = 0	// <int> The modifier, in seconds, to adjust the timestamp for.
	)					// RETURNS <int> The resulting cycle after modification
	
	// $cycle = Time::cycle([$type], [$modifier]);
	{
		return (int) date("Ym", time() + $modifier);
	}
	
	
/****** Return the current Swatch Time ******/
	public static function swatch (
	)			// RETURNS <int> the current swatch time.
	
	// $swatchTime = Time::swatch();	// Return the timestamp for two months ago
	{
		return (int) idate("B");
	}
	
	
/****** Return the current UniQUE Time ******/
	public static function unique
	(
		$match = -10	// <int> Set this to see if the unique time matches up.
	,	$range = 2		// <int> The range (in roughly minutes) to allow for a match to be considered valid.
	)					// RETURNS <mixed> the current swatch time.
	
	// $uniqueTime = Time::unique();			// Return a UniQUE timestamp
	// $uniqueTime = Time::unique($testTime);	// Check if $testTime is within range of the current UniQUE time
	{
		$stamp = (int) gmdate("zB");
		
		if($match != -10)
		{
			// Check if the timestamp is within a close range
			if(abs($match - $stamp) <= $range)
			{
				return true;
			}
			
			// On January 1st, check for the yearly switch values
			if($stamp <= $range)
			{
				if($match >= (364999 - $range))
				{
					return true;
				}
			}
			
			// None of the tests passed
			return false;
		}
		
		return $stamp;
	}
	
	
/****** Return a list of timezones ******/
	public static function timezones (
	)			// RETURNS <str:str> a list of timezones.
	
	// $timezones = Time::timezones();
	{
		return array(
			'Pacific/Midway'       => "(GMT-11:00) Midway Island",
			'US/Samoa'             => "(GMT-11:00) Samoa",
			'US/Hawaii'            => "(GMT-10:00) Hawaii",
			'US/Alaska'            => "(GMT-09:00) Alaska",
			'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
			'America/Tijuana'      => "(GMT-08:00) Tijuana",
			'US/Arizona'           => "(GMT-07:00) Arizona",
			'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
			'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
			'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
			'America/Mexico_City'  => "(GMT-06:00) Mexico City",
			'America/Monterrey'    => "(GMT-06:00) Monterrey",
			'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
			'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
			'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
			'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
			'America/Bogota'       => "(GMT-05:00) Bogota",
			'America/Lima'         => "(GMT-05:00) Lima",
			'America/Caracas'      => "(GMT-04:30) Caracas",
			'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
			'America/La_Paz'       => "(GMT-04:00) La Paz",
			'America/Santiago'     => "(GMT-04:00) Santiago",
			'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
			'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
			'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
			'Atlantic/Azores'      => "(GMT-01:00) Azores",
			'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
			'Africa/Casablanca'    => "(GMT) Casablanca",
			'Europe/Dublin'        => "(GMT) Dublin",
			'Europe/Lisbon'        => "(GMT) Lisbon",
			'Europe/London'        => "(GMT) London",
			'Africa/Monrovia'      => "(GMT) Monrovia",
			'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
			'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
			'Europe/Berlin'        => "(GMT+01:00) Berlin",
			'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
			'Europe/Brussels'      => "(GMT+01:00) Brussels",
			'Europe/Budapest'      => "(GMT+01:00) Budapest",
			'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
			'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
			'Europe/Madrid'        => "(GMT+01:00) Madrid",
			'Europe/Paris'         => "(GMT+01:00) Paris",
			'Europe/Prague'        => "(GMT+01:00) Prague",
			'Europe/Rome'          => "(GMT+01:00) Rome",
			'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
			'Europe/Skopje'        => "(GMT+01:00) Skopje",
			'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
			'Europe/Vienna'        => "(GMT+01:00) Vienna",
			'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
			'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
			'Europe/Athens'        => "(GMT+02:00) Athens",
			'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
			'Africa/Cairo'         => "(GMT+02:00) Cairo",
			'Africa/Harare'        => "(GMT+02:00) Harare",
			'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
			'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
			'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
			'Europe/Kiev'          => "(GMT+02:00) Kyiv",
			'Europe/Minsk'         => "(GMT+02:00) Minsk",
			'Europe/Riga'          => "(GMT+02:00) Riga",
			'Europe/Sofia'         => "(GMT+02:00) Sofia",
			'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
			'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
			'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
			'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
			'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
			'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
			'Asia/Tehran'          => "(GMT+03:30) Tehran",
			'Europe/Moscow'        => "(GMT+04:00) Moscow",
			'Asia/Baku'            => "(GMT+04:00) Baku",
			'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
			'Asia/Muscat'          => "(GMT+04:00) Muscat",
			'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
			'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
			'Asia/Kabul'           => "(GMT+04:30) Kabul",
			'Asia/Karachi'         => "(GMT+05:00) Karachi",
			'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
			'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
			'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
			'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
			'Asia/Almaty'          => "(GMT+06:00) Almaty",
			'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
			'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
			'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
			'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
			'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
			'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
			'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
			'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
			'Australia/Perth'      => "(GMT+08:00) Perth",
			'Asia/Singapore'       => "(GMT+08:00) Singapore",
			'Asia/Taipei'          => "(GMT+08:00) Taipei",
			'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
			'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
			'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
			'Asia/Seoul'           => "(GMT+09:00) Seoul",
			'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
			'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
			'Australia/Darwin'     => "(GMT+09:30) Darwin",
			'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
			'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
			'Australia/Canberra'   => "(GMT+10:00) Canberra",
			'Pacific/Guam'         => "(GMT+10:00) Guam",
			'Australia/Hobart'     => "(GMT+10:00) Hobart",
			'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
			'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
			'Australia/Sydney'     => "(GMT+10:00) Sydney",
			'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
			'Asia/Magadan'         => "(GMT+12:00) Magadan",
			'Pacific/Auckland'     => "(GMT+12:00) Auckland",
			'Pacific/Fiji'         => "(GMT+12:00) Fiji",
		);
	}
	
	
/****** Fuzzy Time - Change a Timestamp to Simple Time Reference (i.e. "last week") ******/
	public static function fuzzy
	(
		$timestamp			// <int> The timestamp of the date to transfer to human-readable time.
	)						// RETURNS <str> a simple, fuzzy time reference.
	
	// echo Time::fuzzy(time() - 3600, [$relative]);	 // Outputs "one hour ago"
	{
		// Determine Fuzzy Time
		$timeDiff = $timestamp - time();
		
		// If the time difference is in the past, run the pastFuzzy() function
		if($timeDiff <= 0)
		{
			return self::pastFuzzy(abs($timeDiff), $timestamp);
		}
		
		return self::futureFuzzy($timeDiff, $timestamp);
	}
	
	
/****** Fuzzy Time (Future) - Private Helper ******/
	private static function futureFuzzy
	(
		$secondsUntil		// <int> The duration (in seconds) until the due date.
	,	$timestamp			// <int> The time that the event is occuring.
	)						// RETURNS <str> the fuzzy time (or a standard, formatted time).
	
	{
		// If the timestamp is within the next hour
		if($secondsUntil < 3600)
		{
			// If the timestamp was within the last half hour
			if($secondsUntil < 1200)
			{
				if($secondsUntil < 45)
				{
					if($secondsUntil < 10)
					{
						return "in a few seconds";
					}
					
					return "in less than a minute";
				}
				
				return "in " . Number::toWord(ceil($secondsUntil / 60)) . " minutes";
			}
			
			return "in " . Number::toWord(round($secondsUntil / 60, -1)) . " minutes";
		}
		
		// If the timestamp is within the next month
		else if($secondsUntil < 86400 * 30)
		{
			// If the timestamp is within the day
			if($secondsUntil < 72000)
			{
				$hoursUntil = round($secondsUntil / 3600);
				
				if($hoursUntil == 1)
				{
					return "in an hour";
				}
				else if($hoursUntil > 12 && date('d', time()) != date('d', $timestamp))
				{
					return "tomorrow";
				}
				
				return "in " . Number::toWord($hoursUntil) . " hours";
			}
			
			// If the timestamp is within a week
			else if($secondsUntil < 86400 * 7)
			{
				$daysUntil = round($secondsUntil / 86400);
				
				if($daysUntil == 1)
				{
					return "in a day";
				}
				
				return "in " . Number::toWord($daysUntil) . " days";
			}
			
			// If the time is listed sometime next week
			if(date('W', $timestamp) - date('W', time()) == 1)
			{
				return "next week";
			}
			
			$weeksUntil = round($secondsUntil / (86400 * 7));
			
			if($weeksUntil == 1)
			{
				return "in a week";
			}
			
			return "in " . Number::toWord($weeksUntil) . " weeks";
		}
		
		// If the timestamp was listed in the next year
		else if($secondsUntil < 86400 * 365)
		{
			$monthsUntil = round($secondsUntil / (86400 * 30));
			
			if($monthsUntil == 1)
			{
				if(date('m', $timestamp) - date('m', time()) == 1)
				{
					return "next month";
				}
				
				return "in a month";
			}
			
			return "in " . Number::toWord($monthsUntil) . " months";
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
	
	
/****** Fuzzy Time (Past) - Private Helper ******/
	private static function pastFuzzy
	(
		$secondsAgo			// <int> The duration (in seconds) after the due date.
	,	$timestamp			// <int> The time that the event occurred.
	)						// RETURNS <str> the fuzzy time (or a standard, formatted time).
	
	{
		// If the timestamp was within the last hour
		if($secondsAgo < 3600)
		{
			// If the timestamp was within a minute or so
			if($secondsAgo <= 90)
			{
				if($secondsAgo < 50)
				{
					if($secondsAgo < 15)
					{
						return "just now";
					}
					
					return "seconds ago";
				}
				
				return "a minute ago";
			}
			else if($secondsAgo < 1200)
			{
				return Number::toWord(round($secondsAgo / 60)) . " minutes ago";
			}
			
			return floor($secondsAgo / 60) . " minutes ago";
		}
		
		// If the timestamp was within the last week
		elseif($secondsAgo < 86400 * 7)
		{
			// Several Hours Ago
			if($secondsAgo < 36000)
			{
				if($secondsAgo < 7200)
				{
					return "an hour ago";
				}
				
				return Number::toWord(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Yesterday (or, several hours ago as a fallback)
			else if($secondsAgo < 72000)
			{
				if(date('d', time()) != date('d', $timestamp))
				{
					return "yesterday";
				}
				
				return Number::toWord(floor($secondsAgo / 3600)) . " hours ago";
			}
			
			// Days Ago
			if($secondsAgo < 86400 * 1.5)
			{
				return "a day ago";
			}
			
			return Number::toWord(round($secondsAgo / 86400)) . " days ago";
		}
		
		// If the timestamp was within the last month
		else if($secondsAgo < 86400 * 30)
		{
			// If the time was listed sometime last week
			if(date('W', time()) - date('W', $timestamp) == 1)
			{
				return "last week";
			}
			
			$weeksAgo = $secondsAgo / (86400 * 7);
			
			if($weeksAgo == 1)
			{
				echo "a week ago";
			}
			
			return Number::toWord(round($weeksAgo)) . " weeks ago";
		}
		
		// Any other timestamp time
		else
		{
			// If it's the same year:
			if(date('y', time()) === date('y', $timestamp))
			{
				$monthsAgo = date('m', time()) - date('m', time() - $secondsAgo);
				
				if($monthsAgo == 0)
				{
					return "early this month";
				}
				else if($monthsAgo == 1)
				{
					return "last month";
				}
				else if($monthsAgo <= 3)
				{
					return Number::toWord($monthsAgo) . " months ago";
				}
				
				return "this " . date('F', $timestamp);
			}
			
			// If it wasn't the same year
			$yearsAgo = date('Y', time()) - date('Y', $timestamp);
			
			if($yearsAgo == 1)
			{
				return "last " . date('F', $timestamp);
			}
		}
		
		// Return the timestamp as a "Month Year" style
		return date("F Y", $timestamp);
	}
}
