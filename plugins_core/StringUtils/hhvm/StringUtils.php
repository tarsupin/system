<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the StringUtils Plugin ------
------------------------------------------

This plugin provides helper functions related to string manipulation and handling.


-------------------------------
------ Methods Available ------
-------------------------------

$argString = StringUtils::convertArrayToArgumentString($arguments, $maxLengthOfString);

$indexNum = StringUtils::strToIndex($string, [$strength], [$charList], [$lowercase]);

*/

abstract class StringUtils {
	
	
/****** Get a word list from designated text ******/
	public static function getWordList
	(
		string $text				// <str> The text to get the word list of.
	,	string $extraChars = ""	// <str> A string of characters to allow in words (other than letters and numbers).
	): void						// RETURNS <void>
	
	// $words = StringUtils::getWordList($text, [$extraChars]);
	{
		// Separate into words
		$wordList = array();
		$text = explode(" ", str_replace("	", " ", Sanitize::word($text, " 0123456789" . $extraChars)));
		
		foreach($text as $t)
		{
			if($t != "")
			{
				$wordList[] = $t;
			}
		}
		
		return $wordList;
	}
	
	
/****** Create Dropdown Options from Array ******/
	public static function createDropdownOptions
	(
		array <str, str> $options = array()	// <str:str> The list of options to add to the dropdown.
	,	string $selected = ""		// <str> The value to select in the dropdown.
	): string						// RETURNS <str> The HTML for the dropdown.
	
	// $dropdownOptions = StringUtils::createDropdownOptions($options, $selected);
	{
		$html = '';
		
		foreach($options as $key => $val)
		{
			$html .= '
			<option value="' . $key . '"' . ($selected == $key ? ' selected' : '') . '>' . $val . '</option>';
		}
		
		return $html;
	}
	
	
/****** Convert an array to an "argument" (parameter) string ******/
	public static function convertArrayToArgumentString
	(
		array <int, mixed> $args				// <int:mixed> The array to convert.
	,	mixed $maxLength = 57		// <mixed> The maximum length of characters before using "..."
	): string						// RETURNS <str> a string that looks like a standard parameter list.
	
	// $argString = StringUtils::convertArrayToArgumentString(array("hello", 100));
	{
		// Prepare values
		$argDisp = "";
		$comma = "";
		$totalLen = 0;
		
		foreach($args as $key => $arg)
		{
			switch(gettype($arg))
			{
				case "string":
					$argDisp .= $comma . '"' . $arg . '"';
					break;
				
				case "integer":
				case "double":
					$argDisp .= $comma . $arg;
					break;
				
				case "array":
					$argDisp .= $comma . 'array(' . self::convertArrayToArgumentString($arg, round($maxLength / 2)) . ')';
					break;
				
				default:
					$argDisp = '???';
					break;
			}
			
			$comma = ", ";
		}
		
		// Make sure the string is within the allowed character length
		if(strlen($argDisp) > $maxLength)
		{
			$argDisp = substr($argDisp, 0, $maxLength - 3) . "...";
		}
		
		return $argDisp;
	}
	
	
/****** Change a string to a sequential, numeric indexing ID ******
It is faster to sort by number than string, and can also be a much smaller requirement for storage. This function
allows you to change numbers into indexing values. Consider the examples below:

	[ String ]		[ Strength 3 ]			[ Strength 5 ]			[ Strength 7 ]
	"aaaaab"		52060					71270179				97568875051
	"aaaaac"		52060					71270180				97568876420
	"apple"			73161					100157594				137115746186
	"application"	73161					100157745				137115952962
	"zebra"			1323915					1812439672				2481229910968

*/
	public static function strToIndex
	(
		string $string				// <str> The string to return an integer indexer for
	,	int $strength = 5		// <int> How large the numerical index can get (reduces collision between similar words)
	,	string $charList = ""		// <str> The list of characters to use for this index
	,	bool $lowercase = true	// <bool> Set to TRUE if all strings get lowercased (allows sorting for dictionaries)
	): int						// RETURNS <int> the integer indexing equivalent of the string
	
	// $indexNum = StringUtils::strToIndex($string, [$strength], [$charList], [$lowercase]);
	{
		// Set characters to lowercase if applicable.
		if($lowercase)
		{
			$string = strtolower($string);
		}
		
		// Prepare the character list and other important variables
		$charList = $charList == "" ? "_abcdefghijklmnopqrstuvwxyz0123456789" : $charList;
		$cLen = strlen($charList);
		
		$total = 0;
		$doMany = min($strength + 1, strlen($string));
		
		// Cycle through the string to add up the values
		for($pos = 0;$pos < $doMany;$pos++)
		{
			if($next = strpos($charList, $string[$pos]))
			{
				$total += (pow($cLen, $strength) * $next);
			}
			
			$strength--;
		}
		
		return $total;
	}
}
