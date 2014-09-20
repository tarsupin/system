<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Text Plugin ------
-----------------------------------

This allows you to manipulate text in useful ways, such as to secure dangerous characters.


-------------------------------
------ Methods Available ------
-------------------------------

Text::safe($text)				// Changes illegal characters to safe HTML equivalents
Text::overprotective($text);	// Changes basically everything to safe HTML equivalents

*/

abstract class Text {
	
	
/****** Safe Conversion of Text into HTML ******/
# Special thanks to anonymous "info" poster on PHP.net
	public static function safe
	(
		$text		// <str> The text that you want to convert to safe HTML entities.
	)				// RETURNS <str> The safe HTML-ready text.
	
	// $safeText = Text::safe("<אבגדהוז¼½> and other such characters.");
	{
		$newText = "";
		$textLen = strlen($text);
		
		// Allows: !#$()*+,:;=?@[]^`{|}
		$allowed = array(33, 35, 36, 39, 40, 41, 42, 43, 44, 47, 58, 59, 61, 63, 64, 91, 93, 94, 96, 123, 124, 125);
		
		for($i = 0;$i < $textLen;$i++)
		{
			$chrNum = hexdec(rawurlencode(substr($text, $i, 1)));
			
			if($chrNum < 33 || $chrNum > 1114111)
			{
				$newText .= substr($text, $i, 1);
			}
			else
			{
				if(in_array($chrNum, $allowed))
				{
					$newText .= substr($text, $i, 1);
				}
				else
				{
					$newText .= "&#" . $chrNum . ";";
				}
			}
		}
		
		return $newText;
	}
	
	
/****** Completely Safe Conversion of Text into HTML ******/
# Special thanks to anonymous "info" poster on PHP.net.
# This system prevents basically every type of character, including things like ! and #
	public static function overProtective
	(
		$text		// <str> The text that you want to convert to safe HTML entities.
	)				// RETURNS <str> The safe HTML-ready text.
	
	// List of Example Characters Protected: ~-_. !\"#$%&'()*+,/:;<=>?@[\]^`{|}
	// $safeText = Text::overProtective("<אבגדהוז¼½> and other such characters.");
	{
		$newText = "";
		$textLen = strlen($text);
		
		for($i = 0;$i < $textLen;$i++)
		{
			$chrNum = hexdec(rawurlencode(substr($text, $i, 1)));
			
			if($chrNum < 33 || $chrNum > 1114111)
			{
				$newText .= substr($text, $i, 1);
			}
			else
			{
				$newText .= "&#" . $chrNum . ";";
			}
		}
		
		return $newText;
	}
	
	
/****** Convert Windows Text ******/
	public static function convertWindowsText
	(
		$text		// <str> The text that you want to convert windows text from.
	)				// RETURNS <str> The converted text.
	
	// $fixText = Text::convertWindowsText($text);
	{
		$text = htmlentities($text);
		
		$map = array(
			"&lsquo;" => "'"
		,	"&rsquo;" => "'"
		,	"&sbquo;" => ","
		,	"&ldquo;" => '"'
		,	"&rdquo;" => '"'
		,	"&ndash;" => '-'
		);
		
		return strtr($text, $map);
	}
	
}
