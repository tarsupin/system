<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------------
------ About the ThreatTracker Plugin ------
--------------------------------------------

This plugin allows you to alert the system of potential intrusions, hacks, unsanitized data, suspicious behavior, or illegal access. For example, the Sanitize class automatically looks for this plugin when illegal input is captured. If illegal input is captured, it tracks the unsanitized inputs that were used on your site.

To use this system, just use the ThreatTracker::log() method to capture any data you feel might be suspicious.

Note: Most of the data caught by this system will probably be the result of a well-meaning user trying to use some characters that weren't allowed for the textbox they were using. In other words, most of the information tracked will be false positives. To mitigate this issue, every threat can be tracked with a severity level from 0 to 10, which indicates how relevant of a risk you may be dealing with.

To help identify whether or not a value is risky, this plugin tries to provide you with "threat levels" to help you identify what may be a possible attack. 

This plugin does NOT provide protection against anything, nor is it designed to catch anything. It is just a simple aid for webmasters that want to run checks that can help to identify users or IPs that may be potentially hostile against the site.


-----------------------------------------
------ Example of using this class ------
-----------------------------------------
	
	// An example of spotting a null byte attack
	if(strpos($_GET['file_to_load'], "\0") !== false)
	{
		ThreatTracker::log(
			$_GET['file_to_load']
		,	ThreatTracker::THREAT_LETHAL
		,	"NULL BYTE injection was attempted by the user."
		);
	}


-------------------------------
------ Methods Available ------
-------------------------------

ThreatTracker::log($threatType, $severity, $threatText, $threatData, $function, $params, $file, $line);

ThreatTracker::pruneThreats($severity, $pruneDuration);

*/

abstract class ThreatTracker {
	
	
/****** Plugin Variables ******/
	public static $trackInput = false;		// <bool> Whether or not to track unsanitized input
	public static $trackActivity = false;	// <bool> Whether or not to track suspicious activity
	public static $minSeverity = 1;			// <int> The minimum severity level to track
	
	// Mode values
	const MODE_OFF = 0;					// Logs nothing [default mode]
	const MODE_LOG_ALL = 1;				// Logs everything
	const MODE_LOG_UNSANITIZED = 2;		// Logs unsanitized inputs
	const MODE_LOG_ACTIVITY = 3;		// Logs suspicious activity
	
	// Levels of severity
	const THREAT_UNLIKELY = 0;
	const THREAT_LOW = 1;
	const THREAT_MODERATE = 3;
	const THREAT_HIGH = 5;
	const THREAT_DANGEROUS = 7;
	const THREAT_LETHAL = 9;
	
	
/****** Set the ThreatTracker Mode ******/
	public static function setMode
	(
		$mode = 0		// <int> The mode to set.
	,	$severity = 1	// <int> The severity level to track.
	)					// RETURNS <void>
	
	// ThreatTracker::setMode(ThreatTracker::MODE_LOG_ALL, ThreatTracker::THREAT_HIGH);
	// ThreatTracker::setMode(ThreatTracker::MODE_LOG_ACTIVITY);
	{
		switch($mode)
		{
			case self::MODE_OFF:
				self::$trackInput = false;
				self::$trackActivity = false;
				break;
			
			case self::MODE_LOG_ALL:
				self::$trackInput = true;
				self::$trackActivity = true;
				break;
			
			case self::MODE_LOG_UNSANITIZED:
				self::$trackInput = true;
				break;
			
			case self::MODE_LOG_ACTIVITY:
				self::$trackActivity = true;
				break;
			
		}
		
		self::$minSeverity = $severity;
	}
	
	
/****** Log data into ThreatTracker ******/
	public static function log
	(
		$threatType				// <str> The type of threat being tracked (e.g. "input", "activity", etc.)
	,	$severity				// <int> The level of danger that is assumed by phpTesla about the data
	,	$threatText				// <str> The most relevant message associated with the potential threat
	,	$threatData = array()	// <int:str> The data that you want to track as a potential threat
	,	$function = ""			// <str> The class::function() being called
	,	$params = ""			// <str> The arguments passed to the function call
	,	$file = ""				// <str> The file that the effect was called in
	,	$line = 0				// <int> The file line that the effect was called at
	)							// RETURNS <void>
	
	// ThreatTracker::log($threatType, $severity, $threatText, $threatData, $function, $params, $file, $line);
	{
		// Serialize the tracked information
		$threatData = Serialize::encode($threatData);
		
		// Prepare Values
		$file = str_replace(dirname(SYS_PATH), "", $file);
		
		// Record the Threat
		Database::query("INSERT INTO `log_threat_tracker` (`severity`, `date_logged`, `threat_type`, `threat_text`, `uni_id`, `ip`, `function_call`, `file_path`, `file_line`, `url_path`, `data_captured`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($severity, time(), $threatType, $threatText, Me::$id, $_SERVER['REMOTE_ADDR'], $function . "(" . $params . ")", $file, $line, $_SERVER['REQUEST_URI'], $threatData));
		
		// 1 in 1000 chance to prune old threats
		if(mt_rand(1, 1000) == 500)
		{
			$day = 86400;
			
			// Prune low threats over 3 days old
			self::pruneThreats(self::THREAT_LOW, ($day * 3));
			
			// Prune moderate threats over 7 days old
			self::pruneThreats(self::THREAT_MODERATE, ($day * 7));
			
			// Prune high threats over 30 days old
			self::pruneThreats(self::THREAT_HIGH, ($day * 30));
			
			// Prune lethal threats over 60 days old
			self::pruneThreats(self::THREAT_LETHAL, ($day * 60));
		}
	}
	
	
/****** Prune Threats ******/
	public static function pruneThreats
	(
		$severity			// <int> The threat level of the data that was identified
	,	$pruneDuration		// <int> The timestamp up to which point to prune threats
	)						// RETURNS <void>
	
	// ThreatTracker::pruneThreats($severity, $pruneDuration);
	{
		Database::query("DELETE FROM `log_threat_tracker` WHERE severity IN (?, ?) AND date_logged <= ?", array($severity, $severity + 1, time() - $pruneDuration));
	}
	
}

