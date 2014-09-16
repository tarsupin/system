<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the FormValidate Plugin ------
-------------------------------------------

This plugin provides common validations to a variety of form elements. These validations will automatically apply the appropriate error Alerts on any invalid results, which can be used to identify if a form was successful or not.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------
#!
// Make sure the form was submitted correctly before processing the input
if(Form::submitted())
{
	// Check if all of the input you sent is valid: 
	FormValidate::number("Year of Birth", $_POST['birthYear'], 1900, 2012);
	
	FormValidate::number_float("Percent of Interest", $_POST['interest'], 0.00, 100.00);
	
	FormValidate::variable("Username", $_POST['username'], 1, 22);		// Length between 1 and 22 (variable)
	FormValidate::text("Display Name", $_POST['displayName'], 1, 22);	// Length between 1 and 22 (text)
	FormValidate::text("My Biography", $_POST['biography']);			// No length requirements
	
	FormValidate::confirmation("Terms of Service", $_POST['tos']);
	
	// You may have custom checks that aren't handled by FormValidate()
	if($_POST['myAge'] < 13)
	{
		Alert::error("Age", "You must be at least 13 to use this site.");
	}
	
	// Now check if the form has passed
	if(FormValidate::pass())
	{
		echo "Everything checks out. Update the database and redirect to the success page!";
		
		header("Location: /login-success"); exit;
	}
	
	// If the form fails, output the alerts:
	else
	{
		echo Alert::display();
	}
}
##!


-------------------------------
------ Methods Available ------
-------------------------------

FormValidate::input($name, $value, $minLen, $maxLen = 0, $extra)	// Validates a simple text input field.
FormValidate::text($name, $value, $minLen, $maxLen = 0, $extra)		// Validates a text input (converts HTML entities)
FormValidate::variable($name, $value, $minLen, $maxLen, $extra)		// Validates a variable with $extra characters
FormValidate::url($name, $value, $minLen, $maxLen)					// Validates a url
FormValidate::number($name, $value, $minVal, $maxVal = 0)			// Validates a field that must be a number.
FormValidate::confirmation($name, $value)							// Validates a confirmation field.

FormValidate::pass()		// Returns true if there are no errors.

FormValidate::username($username);						// Validates a username.
FormValidate::email($email);							// Validates that your email is acceptable.
FormValidate::password($password, $confirm = false);	// Validates that your password is secure enough.

*/

abstract class FormValidate {
	
	
/****** Validate a Typical Input Field ******/
	public static function input
	(
		$name				// <str> The name of the input field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the field.
	,	$maxLength = 32		// <int> The maximum length of the field.
	,	$extraChars = ""	// <str> A string of extra characters to accept, if any.
	)						// RETURNS <void>
	
	// FormValidate::input("Favorite Movie", $_POST['movie']);
	{
		// Prepare Values
		$maxLength = (int) $maxLength + 0;
		$minLength = (int) $minLength + 0;
		
		// Check String Length
		if($maxLength > 0 && strlen($value) > $maxLength)
		{
			Alert::error($name, $name . " cannot exceed " . ($maxLength + 0) . " characters in length.", 3);
		}
		else if($minLength > 0)
		{
			if(strlen($value) <= 0)
			{
				Alert::error($name, "You must provide a value for " . strtolower($name) . ".");
			}
			else if(strlen($value) < $minLength)
			{
				Alert::error($name, $name . " must be at least " . ($minLength + 0) . " characters in length.");
			}
		}
		
		// Check Valid Characters
		$value = Sanitize::safeword($value, "!@#$%^*" . $extraChars, false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error($name, $name . " does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** ALIAS of ::input() ******/
	public static function safeword
	(
		$name				// <str> The name of the input field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the field.
	,	$maxLength = 32		// <int> The maximum length of the field.
	,	$extraChars = ""	// <str> A string of extra characters to accept, if any.
	)						// RETURNS <void>
	
	// FormValidate::safeword("Favorite Movie", $_POST['movie']);
	{
		self::input($name, $value, $minLength, $maxLength, $extraChars);
	}
	
	
/****** Validate a Text Field ******/
	public static function text
	(
		$name				// <str> The name of the text field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the text field.
	,	$maxLength = 0		// <int> The maximum length of the text field.
	,	$extraChars = ""	// <str> A string of extra characters to accept, if any.
	)						// RETURNS <void>
	
	// FormValidate::text("Personal Quote", $_POST['quote'], 5, 120);
	{
		// Prepare Values
		$originalLength = strlen($value);
		$value = Text::safe($value);
		
		// Check String Length
		if($maxLength > 0 && strlen($value) > $maxLength)
		{
			if(strlen($value) > $originalLength && $originalLength <= $maxLength)
			{
				Alert::error($name, $name . " must be shortened due to special characters (" . (strlen($value) - $maxLength) . " extra)", 1);
			}
			else
			{
				Alert::error($name, $name . " cannot exceed " . ($maxLength + 0) . " characters (currently: " . strlen($value) . ")");
			}
		}
		else if($minLength > 0)
		{
			if(strlen($value) <= 0)
			{
				Alert::error($name, "You must provide a " . $name . ".");
			}
			else if(strlen($value) < $minLength)
			{
				Alert::error($name, "The " . $name . " must be at least " . ($minLength + 0) . " characters in length.");
			}
		}
		
		// Check Valid Characters
		$value = Sanitize::safeword($value, "'/\"!?@#$%^&*()[]+={}
" . $extraChars, false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error($name, "The " . $name . " does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** Validate a Variable Field ******/
	public static function variable
	(
		$name				// <str> The name of the text field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the text field.
	,	$maxLength = 0		// <int> The maximum length of the text field. (default 0 for none)
	,	$extraChars = ""	// <str> A string of extra characters to accept, if any.
	)						// RETURNS <void>
	
	// FormValidate::variable("Variable", $_POST['myVar'], 1, 20, "!.");
	{
		// Prepare Values
		$originalLength = strlen($value);
		
		// Check String Length
		if($maxLength > 0 && strlen($value) > $maxLength)
		{
			if(strlen($value) > $originalLength && $originalLength <= $maxLength)
			{
				Alert::error($name, $name . " must be shortened due to special characters (" . (strlen($value) - $maxLength) . " extra)", 1);
			}
			else
			{
				Alert::error($name, $name . " cannot exceed " . ($maxLength + 0) . " characters (currently: " . strlen($value) . ")");
			}
		}
		else if($minLength > 0)
		{
			if(strlen($value) <= 0)
			{
				Alert::error($name, "You must provide a " . $name . ".");
			}
			else if(strlen($value) < $minLength)
			{
				Alert::error($name, $name . " must be at least " . ($minLength + 0) . " characters in length.");
			}
		}
		
		// Check Valid Characters
		$value = Sanitize::variable($value, $extraChars, false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error($name, $name . " does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** Validate a URL ******/
	public static function url
	(
		$name				// <str> The name of the text field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the text field.
	,	$maxLength = 0		// <int> The maximum length of the text field. (default 0 for none)
	)						// RETURNS <void>
	
	// FormValidate::url($name, $value, $minLength, $maxLength);
	{
		// Prepare Values
		$originalLength = strlen($value);
		
		// Check String Length
		if($maxLength > 0 && strlen($value) > $maxLength)
		{
			Alert::error($name, $name . " cannot exceed " . ($maxLength + 0) . " characters (currently: " . strlen($value) . ")", 3);
		}
		else if($minLength > 0)
		{
			if(strlen($value) <= 0)
			{
				Alert::error($name, "You must provide a " . $name . ".");
			}
			else if(strlen($value) < $minLength)
			{
				Alert::error($name, $name . " must be at least " . ($minLength + 0) . " characters in length.");
			}
		}
		
		// Check Valid Characters
		$value = Sanitize::url($value, "", false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error($name, $name . " does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** Validate a File Path ******/
	public static function filepath
	(
		$name				// <str> The name of the text field.
	,	&$value				// <str> The value to validate.
	,	$minLength = 0		// <int> The minimum length of the text field.
	,	$maxLength = 0		// <int> The maximum length of the text field. (default 0 for none)
	,	$extraChars = ""	// <str> A string of extra characters to accept, if any.
	)						// RETURNS <void>
	
	// FormValidate::filepath($name, $value, $minLength, $maxLength, $extraChars);
	{
		// Prepare Values
		$originalLength = strlen($value);
		
		// Check String Length
		if($maxLength > 0 && strlen($value) > $maxLength)
		{
			Alert::error($name, $name . " cannot exceed " . ($maxLength + 0) . " characters (currently: " . strlen($value) . ")", 3);
		}
		else if($minLength > 0)
		{
			if(strlen($value) <= 0)
			{
				Alert::error($name, "You must provide a " . $name . ".");
			}
			else if(strlen($value) < $minLength)
			{
				Alert::error($name, $name . " must be at least " . ($minLength + 0) . " characters in length.");
			}
		}
		
		// Check Valid Characters
		$value = Sanitize::filepath($value, $extraChars, false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error($name, $name . " does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** Validate a Number Field (float) ******/
	public static function number_float
	(
		$name				// <str> The name of the input field.
	,	&$value				// <mixed> The value to validate.
	,	$minValue = 0.00	// <float> The minimum value of the field.
	,	$maxValue = 0.00	// <float> The maximum value of the field.
	)						// RETURNS <void>
	
	// FormValidate::number_float("UniJoule", $_POST['unijoule'], 0.01, 5000.00);
	{
		// Prepare Values
		if(is_string($value) and !is_numeric($value))
		{
			Alert::error($name, $name . " must be a number.", 4);
			
			return;
		}
		
		$value = (float) $value;
		
		// Check the Range
		if($value < $minValue)
		{
			$value = ($minValue + 0);
			
			Alert::error($name, $name . " must be no lower than " . ($minValue + 0) . ".");
		}
		
		else if($maxValue > 0 && $value > $maxValue)
		{
			$value = ($maxValue + 0);
			
			Alert::error($name, $name . " must be no higher than " . ($maxValue + 0) . ".");
		}
	}
	
	
/****** Validate a Number Field ******/
	public static function number
	(
		$name			// <str> The name of the input field.
	,	&$value			// <str> The value to validate.
	,	$minValue = 0	// <int> The minimum value of the field.
	,	$maxValue = 0	// <int> The maximum value of the field.
	)					// RETURNS <void>
	
	// FormValidate::number("Year of Birth", $_POST['birthYear'], 1900, 2012);
	{
		// Prepare Values
		$maxValue = (int) $maxValue + 0;
		$minValue = (int) $minValue + 0;
		
		if(!is_numeric($value))
		{
			Alert::error($name, $name . " must be a number.", 4);
		}
		
		$value = ($value + 0);
		
		// Check the Range
		if($value < $minValue)
		{
			$value = ($minValue + 0);
			
			Alert::error($name, $name . " must be no lower than " . ($minValue + 0) . ".");
		}
		
		else if($maxValue > 0 && $value > $maxValue)
		{
			$value = ($maxValue + 0);
			
			Alert::error($name, $name . " must be no higher than " . ($maxValue + 0) . ".");
		}
	}
	
	
/****** Validate a Confirmation Field (such as checkbox) ******/
	public static function confirmation
	(
		$name			// <str> The name of the checkbox.
	,	&$value			// <str> The value to validate (should be set to true or false).
	)					// RETURNS <void>
	
	// FormValidate::confirmation("Terms of Service", $_POST['tos']);
	{
		if(!$value)
		{
			Alert::error($name, "You must confirm " . $name);
		}
	}
	
	
/****** Validate a Username ******/
	public static function username
	(
		&$username		// <str> The username to validate.
	,	$minLength = 4	// <int> The minimum allowed length of the username.
	)					// RETURNS <void>
	
	// FormValidate::username($_POST['username']);
	{
		// Check String Length
		if(strlen($username) > 22)
		{
			Alert::error("Username", "The username cannot exceed 22 characters in length.", 1);
		}
		
		else if(strlen($username) < $minLength)
		{
			Alert::error("Username", "The username must be at least " . ($minLength + 0) . " characters in length.");
		}
		
		// Not Numeric
		else if(is_numeric($username))
		{
			Alert::error("Username", "The username cannot be numeric.", 4);
		}
		
		// Check Valid Characters
		$username = Sanitize::variable($username, "", false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error("Username", "The username does not allow: " . self::announceIllegalChars(Sanitize::$illegalChars), 3);
		}
	}
	
	
/****** Validates an Email ******/
	public static function email
	(
		$email			// <str> The email you're validating.
	,	$maxLen = 72	// <int> The maximum length of your email.
	)					// RETURNS <void>
	
	// FormValidate::email($_POST['email']);
	{
		// Check if Email Length too long
		$maxLen = max(48, $maxLen);
		
		if(strlen($email) > $maxLen)
		{
			Alert::error("Email", "The email must not be greater than " . ($maxLen + 0) . " characters.", 2);
		}
		
		// Make sure the email is properly formatted
		Email::valid($email);
	}
	
	
/****** Validate a Secure-Enough Password ******/
	public static function password
	(
		&$password			// <str> The password to validate.
	,	&$confirm = ""		// <str> The password confirmation to validate.
	,	$minLength = 8		// <int> The minimum length allowed for the password.
	)						// RETURNS <void>
	
	// FormValidate::password($_POST['password'], $_POST['confirmPassword']);
	{
		// Prepare Values
		$minLength = (int) max(6, $minLength + 0);
		
		// Check Password Length
		if(strlen($password) < $minLength)
		{
			Alert::error("Password", "The password must be " . ($minLength + 0) . " characters or more.");
		}
		
		// Check Confirmation
		if($confirm != "" and $password !== $confirm)
		{
			Alert::error("Password", "Your password and confirmation don't match.");
		}
	}
	
	
/****** Check if Form Validation passed ******/
	public static function pass (
	)					// RETURNS <bool> TRUE if validation passed, FALSE if not.
	
	// FormValidate::pass();
	{
		return (Alert::hasErrors() ? false : true);
	}
	
	
/****** Announce Illegal Characters ******/
	public static function announceIllegalChars
	(
		$illegalChars		// <str:int> The illegal characters that were identified.
	)						// RETURNS <str> the illegal characters identified
	
	// self::announceIllegalChars($illegalChars);
	{
		$announce = "";
		$maxShow = 6;
		
		// Quick replacements
		if(isset($illegalChars[' ']))
		{
			$illegalChars['(space)'] = $illegalChars[' '];
			unset($illegalChars[' ']);
		}
		
		foreach($illegalChars as $key => $count)
		{
			$announce .= ($announce === "" ? "" : ", ") . $key;
			
			if($maxShow == 0) { $announce .= ", and others."; break; }
			$maxShow--;
		}
		
		return htmlspecialchars($announce);
	}
	
	
}
