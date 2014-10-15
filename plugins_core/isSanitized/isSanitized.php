<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the isSanitized Class ------
-----------------------------------------

This plugin provides all the functionality of the Sanitize class without returning a sanitized value. Instead, it returns TRUE or FALSE as to whether or not the value was actually sanitized.

For example, isSanitized::word($input) will return TRUE if the $input is only using letters. If the method has anything non-letter characters, it would return FALSE. Using the same logic, Sanitize::word($input) will strip all non-letter characters from the input and then return the sanitized data.


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// To return a Sanitized Value
$value = Sanitize::word("Hello$!?");		// Returns "Hello"

// To return whether or not the value was sanitized
$value = isSanitized::word("Hello$!?");		// Returns FALSE
$value = isSanitized::word("Hello");		// Returns TRUE


-------------------------------
------ Methods Available ------
-------------------------------

This class uses an identical list to the Sanitize methods available.

*/

abstract class isSanitized {
	
	
/****** Call Static ******/
	public static function __callStatic
	(
		$name			// <str> The method that will be called.
	,	$arguments		// <int:mixed> The arguments being passed.
	)					// RETURNS <bool> TRUE if there were nothing unsanitized, FALSE if there was.
	{
		$val = call_user_func_array(array("Sanitize", $name), $arguments);
		
		return ($arguments[0] === $val);
	}
	
}