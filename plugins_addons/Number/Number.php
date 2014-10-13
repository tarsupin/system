<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Number Class ------
------------------------------------

This plugin allows you to manipulate numbers in useful ways, such as to translate integers to human words.


------------------------------------
------ About the Number Class ------
------------------------------------



// Changes an integer to a human word.
$word = Number::toWord($number);

*/

abstract class Number {
	
	
/****** Save a set of boolean values into a single integer for storage ******/
	public static function boolIntSave
	(
		$booleanList	// <int:bool> A list of booleans to save into a single number
	)					// RETURNS <int> the integer value to save.
	
	// $int = Number::boolIntSave(array(true, false, true, ...));
	{
		// Prepare Values
		$booster = 1;
		$total = 0;
		
		// Cycle through the list of boolean values and update the total
		foreach($booleanList as $bool)
		{
			if($bool) { $total += $booster; }
			
			$booster *= 2;
		}
		
		// Return the integer value
		return $total;
	}
	
	
/****** Save a set of boolean values into a single integer for storage ******/
	public static function intBoolExtract
	(
		$integer		// <int> The integer value to extract boolean values from.
	,	$boolCount		// <int> The number of boolean values considered for the extraction.
	)					// RETURNS <int:bool> the list of boolean values.
	
	// $boolList = Number::intBoolExtract($integer, $boolCount);
	{
		// Prepare Values
		$booleanArray = array();
		$booster = 1;
		
		// Get the booster value
		for($a = 0;$a < $boolCount;$a++)
		{
			$booster *= 2;
		}
		
		// Cycle through each boolean entry and extract it's value
		while($boolCount > 0)
		{
			$booster = $booster / 2;
			
			if($integer >= $booster)
			{
				$booleanArray[] = true;
			
				$integer -= $booster;
			}
			else
			{
				$booleanArray[] = false;
			}
			
			$boolCount--;
		}
		
		// Return the integer value
		return array_reverse($booleanArray);
	}
	
	
/****** Change Number to Word ******/
# This code is a modified and optimized version of code originally provided by Coder4web and wolfe on Stack Overflow.
	public static function toWord	// <T>
	(
		$number		// <T> The number that you'd like to translate to a word.
	)				// RETURNS <str> Word (or phrase) of the number you translated.
	
	// echo Number::toWord(1022);	// Outputs "one thousand twenty two"
	{
		// Make sure the number is a positive value
		$number = (int) max(0, $number);
		
		// Strip any 0's from the front of the integer
		$strNum = ltrim((string) $number, '0');
		
		// Get the length of the number
		$numberLength = strlen($number);
		
		if($numberLength == 1)
		{
			return self::oneDigitToWord($number);
		}
		else if($numberLength == 2)
		{
			return self::twoDigitsToWord($number);
		}
		
		// Prepare Return String
		$word = "";
		
		switch($numberLength)
		{
			case 5:
			case 8:
			case 11:
				
				// If the number is higher than zero, we append words as appropriate
				if($strNum[0] > 0)
				{
					$word = self::twoDigitsToWord((int) ($strNum[0] . $strNum[1])) . " " . self::getUnitDigit($numberLength, $strNum[0]) . " ";
					
					return $word . " " . self::toWord((int) substr($strNum, 2));
				}
				
				// If the number is a zero, we skip any additions
				else
				{
					return $word . " " . self::toWord((int) substr($strNum, 1));
				}
		}
		
		if($strNum[0] > 0)
		{
			$word = self::oneDigitToWord((int) $strNum[0]) . " " . self::getUnitDigit($numberLength, (int) $strNum[0]) . " ";
		}
		
		return $word . " " . self::toWord((int) substr($strNum, 1));
	}
	
	
/****** Private Helper ******/
	private static function oneDigitToWord
	(
		$number		// <int> The single digit that you'd like to translate to a word.
	)				// RETURNS <str> the single number as a word.
	
	// self::oneDigitToWord($number)
	{
		switch($number)
		{
			case 0:	return "";
			case 1:	return "one";
			case 2:	return "two";
			case 3:	return "three";
			case 4:	return "four";
			case 5:	return "five";
			case 6:	return "six";
			case 7:	return "seven";
			case 8:	return "eight";
			case 9:	return "nine";
		}
		
		return "";
	}
	
	
/****** Private Helper ******/
	private static function twoDigitsToWord
	(
		$number		// <int> The two digits that you'd like to translate to a word.
	)				// RETURNS <str> the result as a word.
	
	// self::twoDigitsToWord($number)
	{
		$strNum = (string) $number;
		
		// If the two digit number starts with "1"
		if($strNum[0] == 1)
		{
			switch($strNum[1])
			{
				case "0":	return "ten";
				case "1":	return "eleven";
				case "2":	return "twelve";
				case "3":	return "thirteen";
				case "4":	return "fourteen";
				case "5":	return "fifteen";
				case "6":	return "sixteen";
				case "7":	return "seventeen";
				case "8":	return "eighteen";
				case "9":	return "nineteen";
			}
		}
		
		// Prepare Variables
		$extraSpace = ($strNum[1] == 0 ? "" : " ");
		$secondVal = (int) $strNum[1];
		
		switch($strNum[0])
		{
			case "2":	return "twenty" . $extraSpace . self::oneDigitToWord($secondVal);                
			case "3":	return "thirty" . $extraSpace . self::oneDigitToWord($secondVal);
			case "4":	return "forty" . $extraSpace . self::oneDigitToWord($secondVal);
			case "5":	return "fifty" . $extraSpace . self::oneDigitToWord($secondVal);
			case "6":	return "sixty" . $extraSpace . self::oneDigitToWord($secondVal);
			case "7":	return "seventy" . $extraSpace . self::oneDigitToWord($secondVal);
			case "8":	return "eighty" . $extraSpace . self::oneDigitToWord($secondVal);
			case "9":	return "ninety" . $extraSpace . self::oneDigitToWord($secondVal);
		}
		
		return "";
	}
	
	
/****** Private Helper ******/
	private static function getUnitDigit
	(
		$numberLength	// <int> The length of the number being converted.
	,	$number			// <int> The number you'd like to translate to a word.
	)					// RETURNS <str> the result as a word.
	
	// self::getUnitDigit($numberLength, $number)
	{
		switch($numberLength)
		{
			case 3:
			case 6:
			case 9:
			case 12:
				return "hundred";
			
			case 4:
			case 5:
				return "thousand";
			
			case 7:
			case 8:
				return "million";
			
			case 10:
			case 11:
				return "billion";
		}
		
		return "";
	}
}
