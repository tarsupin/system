<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the Obfuscate Plugin ------
----------------------------------------

This plugin provides methods to obfuscate php code, making it much more time consuming to reverse engineer the system. It should also provide a negligible speed benefit.

Because this system relies heavily on certain plugins names to function properly (such as with APIs and actions), we do not rename plugin names or methods.

Variables are changed throughout the entire codebase. To ensure consistency across all pages, the variables are saved using a hash algorithm. The variables will become six-character long words to avoid collisions.

-------------------------------
------ Methods Available ------
-------------------------------


*/

// Set constants that will be used in this plugin
if(!defined('T_ML_COMMENT'))
{
	define('T_ML_COMMENT', T_COMMENT);
}
else
{
	define('T_DOC_COMMENT', T_ML_COMMENT);
}

abstract class Obfuscate {
	
	
/****** Plugin Variables ******/
	public static bool $removeWhitespace = true;			// <bool> TRUE will remove unnecessary whitespace.
	public static bool $removeComments = true;			// <bool> TRUE will remove comments.
	public static bool $obfuscateVariables = true;		// <bool> TRUE will obfuscate all variables.
	
	public static array <int, str> $reservedValue = array('array <int, str> $this', 'array <int, str> $_GET', 'array <int, str> $_POST', 'array <int, str> $_FILES', "true", "false", "self", "__call", "__callStatic", "__construct");		// <int:str> A list of reserved values to skip obfuscation on.
	
	
/****** Run the Obfuscatation Algorithm ******/
	public static function run
	(
		string $phpCode		// <str> The php code to obfuscate.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $obfuscatedCode = Obfuscate::file($phpCode);
	{
		// Get the tokenizer list for the php code
		$tokens = token_get_all($phpCode);
		
		// Prepare Values
		$output = "";
		
		// Cycle through each token
		foreach($tokens as $key => $token)
		{
			// If the token is just a single string character, add it to the output normally
			if(is_string($token))
			{
				$output .= $token;
			}
			
			// If the token is an array, we must carefully consider the effects
			else
			{
				list($tokenType, $value) = $token;
				
				switch($tokenType)
				{
					// Despite being named "string", this actually applies to a variety of tokens that would not
					// seem to be related, such as class names, methods, and some variables (but not all).
					case T_STRING:
						
						// If the value follows a $this-> or self:: method, obfuscate the value.
						if(self::$obfuscateVariables and isset($tokens[($key - 2)][1]))
						{
							if(in_array($tokens[($key - 2)][1], array('$this', 'self')))
							{
								$output .= runSpecial($value);
								//$output .= '<span style="color:red;font-weight:bold;">' . htmlspecialchars($value) . "</span>";
								break;
							}
						}
						
						/*
							// Note:
							// If we choose to add different styles of obfuscation later, these functions below may
							// play an important role. With the current system, they are unnecessary.
							
							// If the value is reserved, skip any obfuscation.
							if(in_array($value, $reservedValue))
							{
								$output .= $value; break;
							}
							
							// If the value is a pre-defined function of PHP, skip any obfuscation.
							if(function_exists($value))
							{
								$output .= $value; break;
							}
						*/
						
						// The default effect is to skip obfuscation
						$output .= $value;
						break;
					
					// If the token is a variable, run tests to see if it needs to be obfuscated
					case T_VARIABLE;
						
						// Skip obfuscation if it is a reserved value
						if(in_array($value, $reservedValue))
						{
							$output .= $value; break;
						}
						
						$output .= (self::$obfuscateVariables ? runSpecial($value) : $value);
						// $output .= '<span style="color:red;font-weight:bold;">' . htmlspecialchars($value) . "</span>";
						break;
					
					// If the token is a comment
					case T_COMMENT: 
					case T_ML_COMMENT:
					case T_DOC_COMMENT:
						if(!self::$removeComments) { $output .= $value; }
						break;
					
					// If the token is whitespace
					case T_WHITESPACE;
						$output .= (self::$removeWhitespace ? " " : $value); break;
					
					// For all other tokens, return the standard value
					default:
						$output .= $value; break;
				}
			}
		}
		
		// Return the obfuscated code
		return $output;
	}
	
	
/****** Obfuscate a File ******/
	public static function file
	(
		string $filepath		// <str> The filepath of the file to obfuscate.
	,	string $savepath		// <str> The filepath of where to save the new obfuscated file.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Obfuscate::file($filepath, $savepath);
	{
		// Retrieve the file that you intend to obfuscate
		$fileContents = file_get_contents($filepath);
		
		// Run the obfuscation
		$obfuscatedCode = self::run($fileContents);
		
		// Load the obfuscated code into the saving file
		// 
	}
	
	
/****** Return an obfuscated variable name ******/
	public static function setVariable
	(
		string $variable		// <str> The variable to obfuscate.
	): string					// RETURNS <str> The obfuscated form of the variable.
	
	// $variable = Obfuscate::setVariable($variable);
	{
		$hasSign = ($variable[0] == "$");
		
		$hash = base64_encode(hash('sha512', $variable, true));
		
		return ($hasSign ? "$" : "") . strtolower(substr(Sanitize::word($hash), 0, 6));
	}
	
}