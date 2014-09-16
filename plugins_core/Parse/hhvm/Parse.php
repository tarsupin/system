<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Parse Plugin ------
------------------------------------

This plugin provides a series of methods that allow you to parse through text.


-------------------------------
------ Methods Available ------
-------------------------------

Parse::pos($haystack, $needle.., [$needle..])			// Returns the integer position of the last needle
Parse::positionsOf($haystack, $needle);					// Returns every position of $needle in the $haystack
Parse::posLastTwo($haystack, $needle.., [$needle..])	// Returns the last two needle positions

Parse::between($haystack, $needle.., [$needle..])		// Returns the content between the last two needles, non-inclusive
Parse::through($haystack, $needle.., [$needle..])		// Returns the content between the last two needles, inclusive
Parse::before($haystack, $needle.., [$needle..])		// Returns the content before the last needle, non-inclusive
Parse::after($haystack, $needle.., [$needle..])			// Returns the content after the last needle, inclusive

*/

abstract class Parse {
	
	
/****** Find the last position in text ******/
	public static function pos
	(
		string $text		// <str> The text to parse.
					// <args> <str> The position of the next needle to check for.
	): int				// RETURNS <int> position of the last needle provided.
	
	// $pos = Parse::pos("This is a Top hat.", "a", "T");		// Returns position of the "T" in "Top Hat"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return false; }
			
			$position = $nextPosition;
		}
		
		if(count($args) <= 1) { return false; }
		
		return $position;
	}
	
	
/****** Find all positions of $needle in the $haystack ******/
	public static function positionsOf
	(
		string $haystack	// <str> The text to parse.
	,	string $needle		// <str> The positions to check for with the needle.
	): array <int, int>				// RETURNS <int:int> the positions (int) where the needle was found.
	
	// $pos = Parse::positionsOf("@joe and @bob, how are you?", "@");	// Returns "0" and "9"
	{
		// Prepare Values
		$positions = array();
		$curPos = -1;
		
		while(true)
		{
			$nextPos = strpos($haystack, $needle, $curPos + 1);
			
			if($nextPos > $curPos)
			{
				$curPos = $nextPos;
				
				array_push($positions, $curPos);
				
				continue;
			}
			
			break;
		}
		
		return $positions;
	}
	
	
/****** Retrieve the positions of the last two needles ******/
	public static function posLastTwo
	(
		string $text		// <str> The text to parse.
	): array <int, int>				// RETURNS <int:int> the text between the last two needles.
	
	// list($pos1, $pos2) = Parse::posLastTwo("Such a hat!", "Such", "hat");	// Returns pos of "Such" and "hat"
	{
		// Prepare Values
		$args = func_get_args();
		$lastPosition = -1;
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return array(); }
			
			$lastPosition = $position;
			$position = $nextPosition;
		}
		
		if(count($args) <= 1) { return array(); }
		
		if($lastPosition == -1)
		{
			return array();
		}
		
		return array($lastPosition, $position);
	}
	
	
/****** Retrieve the string between the last two needles ******/
	public static function between
	(
		string $text		// <str> The text to parse.
	): string				// RETURNS <str> the text between the last two needles.
	
	// echo Parse::between("How are you doing today?", "are", "today");	// Returns " you doing "
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		$strlen = 0;
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $position + $strlen, $nextPosition - $position - $strlen);
			}
			
			$strlen = strlen($args[$i]);
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string through the last two needles (inclusive) ******/
	public static function through
	(
		string $text		// <str> The text to parse.
	): string				// RETURNS <str> the text through the last two needles.
	
	// echo Parse::through("How are you doing today?", "are", "today");	// Returns "are you doing today"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		$strlen = 0;
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			$strlen = strlen($args[$i]);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $position, $nextPosition - $position + $strlen);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string after the final needle ******/
	public static function after
	(
		string $text		// <str> The text to parse.
	): string				// RETURNS <str> the text after the last needle.
	
	// echo Parse::after("How are you doing today?", "are", "doing");	// Returns " today?"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, $nextPosition);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
	
/****** Retrieve the string after the final needle ******/
	public static function before
	(
		string $text		// <str> The text to parse.
	): string				// RETURNS <str> the text after the last needle.
	
	// echo Parse::after("How are you doing today?", "are", "doing");	// Returns " today?"
	{
		// Prepare Values
		$args = func_get_args();
		$position = -1;
		$argsLength = count($args);
		
		for($i = 1;$i < $argsLength;$i++)
		{
			$nextPosition = strpos($text, $args[$i], $position + 1);
			
			if($nextPosition === false) { return ""; }
			
			// If you're on the last cycle, return the string
			if($i == $argsLength - 1)
			{
				return substr($text, 0, $nextPosition);
			}
			
			$position = $nextPosition;
		}
		
		return "";
	}
	
}