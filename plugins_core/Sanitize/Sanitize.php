<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Sanitize Class ------
--------------------------------------

User input can never be trusted. The best way to ensure that user input is safe is to use a whitelisting technique so that all user input can only have specific characters that you've deemed acceptable. Though this doesn't protect against every type of vulnerability, it will help ward off many security vulnerabilities.

If any of these Sanitize methods catch a value that doesn't belong, it will attempt to alert the "ThreatTracker" plugin of the unsanitized data being used. It will provide a suspicion / threat level of the input, allowing you to filter out anything you deem low priority. In addition to the attempt, it will inform you of the user that was making the attempt, the IP, and other details.

----------------------------------
------ How the plugin works ------
----------------------------------

The Sanitize methods each take a single variable that you provide it (always a string, except for the ::number() method) and cleanse it of any characters that were not explicitly allowed to be used.

For example, you can set your variables to:

	$value = Sanitize::word($value);			// only use letters
	$value = Sanitize::variable($value)			// only use letters, numbers, and underscores
	$value = Sanitize::punctuation($value);		// only use letters, numbers, and certain punctuation


These methods also include a parameter called $extraChars. The $extraChars parameter allows you to add additional characters to pre-defined whitelists. For example:

	$value = Sanitize::word($value, "123");			// only use letters and the numbers 1, 2, and 3
	$value = Sanitize::variable($value, "!#"		// only use letters, numbers, underscores, !, and #
	$value = Sanitize::punctuation($value, "{}");	// use punctuation and these brackets: {  }


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// User goes to the URL: page.php?myInput=YES;*!_123.01

echo $_GET['myInput'];									// Returns "YES;*!_123.01"

echo Sanitize::word($_GET['myInput']);					// Returns "YES"
echo Sanitize::word($_GET['myInput'], "123");			// Returns "YES12301"
echo Sanitize::whitelist($_GET['myInput'], "_.123");	// Returns "_123.1"
echo Sanitize::whitelist($_GET['myInput'], "ABCDE");	// Returns "E"

-------------------------------
------ Methods Available ------
-------------------------------

Sanitize::whitelist($input, $charsAllowed);				// Whitelists the exact characters you provide.

Sanitize::whileValid($input, $useMethod, $extraChars = "");		// Returns substring up until something doesn't match.

Sanitize::word($input, $extraChars = "");				// Allows letters
Sanitize::variable($input, $extraChars = "");			// Allows letters, numbers, underscore
Sanitize::safeword($input, $extraChars = "");			// Allows letters, numbers, space, and _-.,:;|
Sanitize::punctuation($input, $extraChars = "");		// Allows safeword options, plus punctuation and some symbols
Sanitize::text($input, $extraChars = "");				// Allows punctuation options, plus brackets and extra symbols
Sanitize::encryption($input, $extraChars = "");			// Allows characters used in phpTesla encryption
Sanitize::url($input, $extraChars = "");				// Sanitizes an allowable URL

Sanitize::number($input, [$min], [$max], [$isFloat]);	// Use this format for min and max range

Sanitize::filepath($input);								// Sanitizes an allowable file path (including slashes)

*/

abstract class Sanitize {
	
	
/****** Plugin Variables ******/
	
	// This value records all illegal characters from the last sanitization
	public static $illegalChars = array();		// <str:int>
	
	
/****** Sanitize: Whitelist ******/
#Sanitizes user input so that only the characters that you want to be present are allowed. It will not care about
# what position any of the characters are in - you can use regular expressions whenever that is required.

#If there are characters sanitized (i.e. characters that didn't belong were stripped from the input), this method
# will attempt to create a warning for the admins of a potential hack attempt.
	public static function whitelist
	(
		$valueToSanitize	// <str> The value you're going to sanitize.
	,	$charsAllowed		// <str> A list of specific characters to add to the whitelist.
	,	$strict = false		// <bool> TRUE to end sanitization once a non-matching character is found.
	)						// RETURNS <str> the sanitized value (as an acceptably formatted word).
	
	// $string = Sanitize::whitelist($string, "abcd");	// The string will only allow the characters: a, b, c, and d
	{
		/****** Prepare Variables *****/
		self::$illegalChars = array();		// Resets the last illegal characters to nothing
		
		$originalString = $valueToSanitize;
		$illegalCount = 0;
		
		// Cycle through each letter in the word to sanitize and check if there is a character that shouldn't be there.
		for($i = 0, $len = strlen($valueToSanitize);$i < $len;$i++)
		{
			// If something shouldn't be there, strip it out.
			if(strpos($charsAllowed, $valueToSanitize[$i - $illegalCount]) === false)
			{
				// If this is running in strict mode and there is an illegal character, end abruptly
				if($strict === true)
				{
					return substr($valueToSanitize, 0, $i);
				}
				
				// Prepare values
				$getChar = $valueToSanitize[$i - $illegalCount];
				
				// Track illegal characters
				if(!isset(self::$illegalChars[$getChar]))
				{
					self::$illegalChars[$getChar] = 1;
				}
				else
				{
					self::$illegalChars[$getChar] += 1;
				}
				
				$valueToSanitize = substr_replace($valueToSanitize, "", $i - $illegalCount, 1);
				$illegalCount++;
			}
		}
		
		// Send a warning if the user input had to be sanitized
		if($originalString != $valueToSanitize)
		{
			// Increase the warning level if there is an abundance of illegal characters
			$severity = ($illegalCount >= 3 ? 1 : 0);
			$severity += ($illegalCount >= 8 ? 1 : 0);
			
			// Prepare variables for testing the offending characters
			$offensiveCount = 0;
			$lethalCount = 0;
			
			$offensiveChars = "\\/%()?&'\";<>" . chr(0);
			$lethalChars = "\\<>" . chr(0);
			
			// Every time a potentially dangerous character is identified, increase the chance of warning
			for($i = 0;$i < strlen($originalString);$i++)
			{
				// If something shouldn't be there, strip it out.
				if(strpos($offensiveChars, $originalString[$i]) !== false && strpos($charsAllowed, $originalString[$i]) === false)
				{
					$offensiveCount++;
					
					if(strpos($lethalChars, $originalString[$i]) !== false)
					{
						$lethalCount++;
					}
				}
			}
			
			// Increase the warning level if multiple dangerous characters are found
			$severity += ($offensiveCount >= 1 ? 2 : 0);
			$severity += ($offensiveCount >= 3 ? 2 : 0);
			$severity += min(3, $lethalCount);
			
			// Prepare a warning of potential abuse
			self::warnOfPotentialAttack($originalString, "Unsanitized Input", $severity, 1);
		}
		
		return $valueToSanitize;
	}
	
	
/****** Sanitize While Valid ******/
# Sanitizes input so that it returns everything up until the first non-matching character is found.
	public static function whileValid
	(
		$valueToSanitize		// <str> The value you're going to sanitize.
	,	$useMethod				// <str> The method that you're going to use.
	,	$extraChars = ""		// <str> A list of specific characters to add to the whitelist.
	)							// RETURNS <str> input until the first non-matching character.
	
	// $word = Sanitize::whileValid("hello123and45", "word", "15");		// Returns "hello1"
	// $word = Sanitize::whileValid("hello123and45", "word", "123");	// Returns "hello123and"
	{
		$allowMethods = array("whitelist", "word", "variable", "safeword", "punctuation", "text");
		
		if(in_array($useMethod, $allowMethods))
		{
			return self::$useMethod($valueToSanitize, $extraChars, true);
		}
		
		return self::whitelist($valueToSanitize, $extraChars, true);
	}
	
	
/****** Sanitize Methods ******/
	public static function __callStatic
	(
		$name		// <str> The name of the function being called.
	,	$args		// <int:mixed> Additional arguments being passed.
	)				// RETURNS <str> input until the first non-matching character.
	
	// $word = Sanitize::word($someValue);
	{
		if(!isset($args[1])) { $args[1] = ""; }
		if(!isset($args[2])) { $args[2] = false; }
		
		switch($name)
		{
			case "word":
				return self::whitelist($args[0], "eariotnslcudpmhgbfywkvEARIOTNSLCUDPMHGBFYWKV" . $args[1] . "xzjqXZJQ", $args[2]);
			
			case "variable":
				return self::whitelist($args[0], "eariotnslcudpmhgbfywkv0123456789_EARIOTNSLCUDPMHGBFYWKV" . $args[1] . "xzjqXZJQ", $args[2]);
			
			case "safeword":
				return self::whitelist($args[0], "eariotnslcudpmhgbfywkv0123456789_- .,:;|EARIOTNSLCUDPMHGBFYWKV" . $args[1] . "xzjqXZJQ", $args[2]);
			
			case "punctuation":
				return self::whitelist($args[0], "eariotnslcudpmhgbfywkv0123456789_- .,:;|EARIOTNSLCUDPMHGBFYWKVxzjqXZJQ'\"!?@#$%^&*+=" . chr(9) . chr(10) . $args[1], $args[2]);
			
			case "text":
				return self::whitelist($args[0], "eariotnslcudpmhgbfywkv0123456789_- .,:;|EARIOTNSLCUDPMHGBFYWKVxzjqXZJQ'\"!?@#$%^&*()[]+={}~`" . chr(9) . chr(10) . $args[1], $args[2]);
			
			case "encryption":
				return self::whitelist($args[0], Security::$padChars . $args[1], $args[2]);
			
			case "url":
				return self::variable($args[0], "eariotnslcudpmhgbfywkv0123456789_EARIOTNSLCUDPMHGBFYWKVxzjqXZJQ:/-+?=%#&." . $args[1], $args[2]);
		}
		
		return "";
	}
	
	
/****** Sanitize Number ******/
	public static function number	// <T>
	(
		$numberToSanitize		// <mixed> The number you're going to sanitize.
	,	$minRange = 0			// <T> The minimum range allowed.
	,	$maxRange = 9999999999	// <T> The maximum range allowed.
	,	$isFloat = false		// <bool> TRUE if the value is a float rather than an integer.
	)							// RETURNS <mixed> the sanitized value that results after sanitizing.
	
	// Sanitize::number(1000, 0, 2000);				// Allows a range of 0 to 2000.
	// Sanitize::number(1000, 3.82, 12.73, true);	// Allows a range of 3.82 to 12.73.
	{
		// Set to float or int
		if($isFloat == true)
		{
			$number = (float) $numberToSanitize;
		}
		else
		{
			$number = (int) $numberToSanitize;
		}
		
		// Apply the appropriate range
		if($number < $minRange) { $number = $minRange; }
		else if($number > $maxRange) { $number = $maxRange; }
		
		return $number;
	}
	
	
/****** Sanitize a File Path ******/
# Sanitizes user input for an allowable file path. Only letters, numbers, and underscores are allowed, as well as
# the forward slashes necessary to identify the path. Parent paths are rejected (forcing an absolute path from
# the directory you're in), and the extension for any filename can (and should) be enforced.

# *NOTE* This is the not the safest way to protect a file path when user input is involved. If you need to
# allow a user to have control over folders, you should use a sanitization method on the folder name itself
# rather than allow directory slashes to be used. This method should only be used for administrative users that
# have a reason to access multiple custom directories.

# This function DOES NOT CARE if the file exists or not - it is simply trying to validate a proper directory and
# file path. If you want to test if the file exists, you'll want to use the File class.

# If there are characters present that don't belong, it will alert the Security class to warn of potential hacks.
# Severe warnings may occur if the user attempts to enter parent paths ("../") or uses null bytes.
	public static function filepath
	(
		$valueToSanitize	// <str> The input to sanitize
	,	$extAllowed = ""	// <mixed> The extensions that are allowed. Default allows any extensions.
	)						// RETURNS <str> sanitized file path, or "" on failure.
	
	// $filepath = "./data/" . Sanitize::filepath($myFilepath);
	// $filepath = "./documents/" . Sanitize::filepath($myTextFile, "txt");
	// $filepath = "./images/" . Sanitize::filepath($myImage, array("png", "jpg", "gif"));
	{
		// If a null byte is present, we assume it is an obvious hack attempt
		if(strpos($valueToSanitize, "\0") !== false)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Null Byte Attack", 10);
			return "";
		}
		
		// Sanitize any improper characters out of the string
		$valueToSanitize = trim($valueToSanitize);
		$valueToSanitize = self::variable($valueToSanitize, " :/.-\\");
		
		/****** Check For Severe Warnings ******/
		
		// If there is a parent path entry, this is definitely too suspicious to ignore
		if(strpos($valueToSanitize, "../") !== false)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Parent Path Injection", 9);
			return "";
		}
		
		// If there is a parent path entry without the slash, this is both broken and suspicious
		elseif(strpos($valueToSanitize, "..") !== false)
		{
			self::warnOfPotentialAttack($valueToSanitize, "Invalid File Path", 8);
			return "";
		}
		
		/****** Verify the File Extension ******/
		if($extAllowed !== "")
		{
			// Retrieve the last "." present and use that to identify the file extension
			$dotPos = strrpos($valueToSanitize, ".");
			
			$getExtension = substr($valueToSanitize, $dotPos);
			
			// If there are multiple file extensions allowed
			if(is_array($extAllowed) === true)
			{
				// If the file extension isn't one of the allowed types, report a warning and end
				// A second check is made in case the programmer added a "." to the extensions allowed list
				if(!in_array($getExtension, $extAllowed) && !in_array(str_replace(".", "", $getExtension), $extAllowed))
				{
					self::warnOfPotentialAttack($valueToSanitize, "Illegal File Extension", 6);
					return "";
				}
			}
			
			// If there is only one file extension allowed
			else
			{
				// If the file extension didn't match what was allowed
				if($getExtension !== $extAllowed)
				{
					self::warnOfPotentialAttack($valueToSanitize, "Illegal File Extension", 6);
					return "";
				}
			}
		}
		
		return $valueToSanitize;
	}
	
	
/****** Alert the application of a potential attack ******/
# This function is called when one of the other Sanitize methods catches illegal user input. It attempts to alert the
# application with the plugin:

#	ThreatTracker::illegalInput($warningSeverity, $warningType, $illegalInput, $illegalChars, $uniID, $remoteIP)

#	// Warning Severity Levels
#	0	= Unsanitized data, but not particularly suspicious
#	1-2	= Paranoia? Probably nothing to worry about
#	3-4	= Possible Attack - involves many suspicious characters unrelated to the value
#	5-6	= Suspicious - hard to accidentally get this level of suspicion, but it's possible
#	7-9	= Probable attack - not something that is likely to be an error
#	10	= Definitely an attack - only a trained penetration tester would do this
	private static function warnOfPotentialAttack
	(
		$unsafeContent		// <str> The illegal input that was captured
	,	$threatText = ""	// <str> The type of warning to announce
	,	$severity = 0		// <int> The severity level of this particular input
	,	$traceDepth = 0		// <int> Internal value to aid in identifying how many additional backtrace steps are needed
	)						// RETURNS <void>
	
	// self::warnOfPotentialAttack($unsafeContent, "Invalid File Path", 4);
	{
		// Record this if the system is tracking input of this severity level
		if(ThreatTracker::$trackInput == true and ThreatTracker::$minSeverity <= $severity)
		{
			// Prepare Values
			$threatData = array(
				"Input Caught"			=> $unsafeContent
			,	"Illegal Characters"	=> self::$illegalChars
			);
			
			$backtrace = debug_backtrace();
			$origin = $backtrace[2 + $traceDepth];
			
			$function = (isset($origin['class']) ? $origin['class'] . $origin['type'] : "") . (isset($origin['function']) ? $origin['function'] : "");
			$params = isset($origin['args']) ? StringUtils::convertArrayToArgumentString($origin['args']) : "";
			
			// Log the threat
			ThreatTracker::log("input", $severity, $threatText, $threatData, $function, $params, $origin['file'], $backtrace[1 + $traceDepth]['line']);
		}
	}
}

