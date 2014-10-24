<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the Security Plugin ------
---------------------------------------

This plugin provides methods for important security operations, such as hashing, padding, and more. This plugin is critical for certain functions in phpTesla.


-------------------------------
------ Methods Available ------
-------------------------------

$padValue	= Security::pad($stringToPad, $paddingstring);			// Pads the string
$strValue	= Security::unpad($stringToUnpad, $paddingString);		// Unpads the string

$passHash	= Security::setPassword("myPassword");					// Creates a password hash
$success	= Security::getPassword("myPassword", $passHash);		// Returns TRUE if the password validates

$hash		= Security::hash($value, $length = 64, $base = 64)		// Hashes a value (and returns $length characters)

$randInt	= Security::randInt($length = 32);						// Return random numbers as a string
$hash		= Security::randHash($length, $base = 64);				// Returns random hash of length $length

$convert	= Security::convertBaseArbitrary($value, $fromBase, $toBase);	// Converts from one base to another

$safeHTML	= Security::purify($unsafeHTML);						// Returns safe HTML (prevents XSS attacks)

Security::fingerprint()					// Run this to help resist fake sessions.

*/

abstract class Security {
	
	
/****** Class Variables ******/
	
	// Didn't add characters: 		// Originally didn't add "/", since it seemed to break; now use Text::safe()
	public static string $padChars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+=_;:!-,.@*~|string $%^&#()[]{}` ?'\"<>/\\"; // <str>
	
	public static array <str, int> $padArray = array("0" => 0, "1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5, "6" => 6, "7" => 7, "8" => 8, "9" => 9, "a" => 10, "b" => 11, "c" => 12, "d" => 13, "e" => 14, "f" => 15, "g" => 16, "h" => 17, "i" => 18, "j" => 19, "k" => 20, "l" => 21, "m" => 22, "n" => 23, "o" => 24, "p" => 25, "q" => 26, "r" => 27, "s" => 28, "t" => 29, "u" => 30, "v" => 31, "w" => 32, "x" => 33, "y" => 34, "z" => 35, "A" => 36, "B" => 37, "C" => 38, "D" => 39, "E" => 40, "F" => 41, "G" => 42, "H" => 43, "I" => 44, "J" => 45, "K" => 46, "L" => 47, "M" => 48, "N" => 49, "O" => 50, "P" => 51, "Q" => 52, "R" => 53, "S" => 54, "T" => 55, "U" => 56, "V" => 57, "W" => 58, "X" => 59, "Y" => 60, "Z" => 61, "+" => 62, "=" => 63, "_" => 64, ";" => 65, ":" => 66, "!" => 67, "-" => 68, "," => 69, "." => 70, "@" => 71, "*" => 72, "~" => 73, "|" => 74, "array <str, int> $" => 75, "%" => 76, "^" => 77, "&" => 78, "#" => 79, "(" => 80, ")" => 81, "[" => 82, "]" => 83, "{" => 84, "}" => 85, "`" => 86, " " => 87, "?" => 88, "'" => 89, "\"" => 90, "<" => 91, ">" => 92, "/" => 93, "\\" => 94);	// <str:int>
	
	
/****** Pad a String ******/
	public static function pad
	(
		string $valueToPad		// <str> The string to pad.
	,	string $paddingStr		// <str> The string to pad with.
	,	int $base = 64		// <int> The base value to pad with.
	): string					// RETURNS <str> The resulting pad.
	
	// $padValue = Security::pad("hello mister", "aaaaa cccccc"); // Returns "hello okuvgt"
	{
		// Prepare Values
		$chars_allowed = self::$padChars;
		$length = strlen($valueToPad);
		$newString = "";
		
		if($base > 0)
		{
			$chars_allowed = substr($chars_allowed, 0, $base);
		}
		
		$allowLen = strlen($chars_allowed);
		
		// Increase Length of Padding String if Too Short
		$count = 1;
		$orig = $paddingStr;
		
		while(strlen($paddingStr) < $length)
		{
			$paddingStr .= self::hash($orig . $count++);
		}
		
		// Cycle through each character in the string to pad, and then pad it
		for($i = 0;$i < $length;$i++)
		{
			// Pad the next character
			$padValue = self::$padArray[$valueToPad[$i]] + self::$padArray[$paddingStr[$i]];
			
			// Add the padded character to the new string
			$newString .= $chars_allowed[$padValue % $allowLen];
		}
		
		return $newString;
	}
	
	
/****** Unpad a String ******/
	public static function unpad
	(
		string $valueToPad 	// <str> The string to unpad.
	,	string $paddingStr		// <str> The string to unpad width.
	,	int $base = 64		// <int> The base value to pad with.
	): string					// RETURNS <str> The resulting pad.
	
	// $encData = Security::unpad("hello okuvgt", "aaaaa cccccc"); // Returns "hello mister"
	{
		// Get Defaults
		$chars_allowed = self::$padChars;
		$length = strlen($valueToPad);
		$newString = "";
		
		if($base > 0)
		{
			$chars_allowed = substr($chars_allowed, 0, $base);
		}
		
		$allowLen = strlen($chars_allowed);
		
		// Increase Length of Padding String if Too Short
		$count = 1;
		$orig = $paddingStr;
		
		while(strlen($paddingStr) < $length)
		{
			$paddingStr .= self::hash($orig . $count++);
		}
		
		// Cycle through each character in the string to pad, and then pad it
		for($i = 0;$i < $length;$i++)
		{
			// Get Padding Value
			$padValue = self::$padArray[$valueToPad[$i]] - self::$padArray[$paddingStr[$i]];
			
			// Pad Character
			while($padValue < 0) { $padValue += $allowLen; }
			
			// Add the padded character to the new string
			$newString .= $chars_allowed[$padValue];
		}
		
		return $newString;
	}
	
	
/****** Set a Password ******/
	public static function setPassword
	(
		string $password 				// <str> The plaintext password that you want to encrypt
	,	int $complexity = 5			// <int> The complexity of the password. Higher is more complex. [Exponential]
	,	string $hashType = "default"	// <str> The type of hash you're using, specific to this framework.
	): string							// RETURNS <str> The resulting hash.
	
	// $passHash = Security::setPassword("myPassword");
	{
		// Prepare Salt
		$salt = SITE_SALT;
		
		// Prepare the Hash Algorithm to use
		$hashAlgo = "_setHash_" . $hashType;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Security", $hashAlgo))
		{
			return self::$hashAlgo($password, $salt, $complexity);
		}
		
		return self::_setHash_default($password, $salt, $complexity);
	}
	
	
/****** Set `default` Hash Algorithm ******/
	private static function _setHash_default
	(
		string $password 		// <str> The plaintext password that you want to encrypt.
	,	string $salt			// <str> The salt to use for the hash algorithm.
	,	int $complexity		// <int> The amount of complexity used on the algorithm. [Exponential]
	): string					// RETURNS <str> The resulting hash. 128 characters in length.
	
	// self::_setHash_default("myPassword", "--otherSalts--");
	{
		// Append a Hash-Specific Salt
		$append = str_replace("$", "", self::randHash(27));
		$complex = mt_rand(1, ($complexity * $complexity));
		
		// Create a randomized hash salt that will be saved with the final hash
		$prep1 = substr(hash('sha512', $password . $salt . $append . $complex), 0, mt_rand(66, 86));
		
		// Return the hash (Note: We're using base64_encode for optimization purposes)
		return "default$" . $complexity . "$" . $append . "$" . base64_encode(hash('sha512', $prep1 . $salt . $append . $complex, true));
	}
	
	
/****** Get a Password ******/
	public static function getPassword
	(
		string $password 		// <str> The plaintext password that you're testing.
	,	string $passHash		// <str> The hashed value that you're trying to match.
	): bool					// RETURNS <bool> TRUE if the algorithm is valid based on the password provided.
	
	// $success = Security::getPassword("myPassword", $passHash);
	{
		// Gather Important Values
		$exp = explode("$", $passHash, 2);
		$hashAlgo = $exp[0];
		
		// Prepare Salt
		$salt = SITE_SALT;
		
		// Prepare the Hash Algorithm to use
		$hashAlgo = "_getHash_" . Sanitize::word($exp[0]);
		
		// Check if hash algorithm selected is valid
		if(method_exists("Security", $hashAlgo))
		{
			return self::$hashAlgo($password, $passHash, $salt);
		}
		
		return self::_getHash_default($password, $passHash, $salt);
	}
	
	
/****** Get `default` Hash Algorithm ******/
	public static function _getHash_default
	(
		string $password 		// <str> The plaintext password that you want to encrypt
	,	string $passHash		// <str> The hashed value that you're trying to match.
	,	string $salt			// <str> The salt that was provided to the algorithm
	): bool					// RETURNS <bool> TRUE if the algorithm is valid based on the password provided.
	
	// $passHash = self::_getHash_default("myPassword", $hashedValue, $salt);
	{
		// Gather Important Values
		$exp = explode("$", $passHash, 4);
		
		if(count($exp) < 4) { return false; }
		
		$complexity = $exp[1] * $exp[1];
		$soloKey = $exp[2];
		$passHash = $exp[3];
		
		$salt .= $soloKey;
		
		// Recreate the hash algorithm with all random length modifiers
		$matchPass = false;
		
		for($c = 1;$c <= $complexity;$c++)
		{
			$prep1 = hash('sha512', $password . $salt . $c);
			
			for($i = 66;$i <= 86;$i++)
			{
				if($passHash == base64_encode(hash('sha512', substr($prep1, 0, $i) . $salt . $c, true)))
				{
					$matchPass = true; // Sets to true, but keep running the algorithm to avoid time identification
				}
			}
		}
		
		return $matchPass;
	}
	
	
/****** Hash a Value ******/
	public static function hash
	(
		string $value			// <str> The value to be hashed
	,	int $length = 64	// <int> The length of the hash to return.
	,	int $base = 64		// <int> The base to use.
	): string					// RETURNS <str> The hashed value.
	
	// if(Security::hash("test", 15) == "XdNve4kc4fJsc73") { echo "Test Passed!"; }
	{
		if($base == 64)
		{
			$hash = hash('sha512', $value, true);
			return substr(base64_encode($hash), 0, $length);
		}
		else if($base == 62)
		{
			$hash = hash('sha512', $value, true);
			return substr(str_replace(array("+", "/", "="), array("", "", ""), base64_encode($hash)), 0, $length);
		}
		
		$hash = hash('sha512', $value);
		return substr(self::convertBaseArbitrary($hash, 16, $base), 0, $length);
	}
	
	
/****** Prepare a JSEncrypt Value ******/
	public static function jsEncrypt
	(
		string $key		// <str> The key for this encryption.
	,	string $salt = ""	// <str> The salt for this encryption.
	): string				// RETURNS <str> The resulting encrypted data.
	
	// $jsEncrypt = Encrypt::jsEncrypt($key, [$salt]);
	{
		return Security::hash(md5($key . $salt. date("yz")) . ":myJSEncryption:cross_site_functionality", 20, 62);
	}
	
	
/****** Secure Random Integer ******/
	public static function randInt
	(
		int $length	= 32		// <int> The length of the integer that you'd like to return.
	): int						// RETURNS <int> A random integer key of the desired length (string format).
	
	// $randInt = Security::randInt(25);	// Returns 25 random numbers as a string
	{
		$int = substr(microtime(), 2, 5);	// Creates extremely random piece with microseconds
		$int .= str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_LEFT);
		
		for($a = strlen($int);$a < $length;$a += 6)
		{
			$int .= str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_LEFT);
		}
		
		return substr($int, 0, $length);
	}
	
	
/****** Random Hash ******/
	public static function randHash
	(
		int $length	= 64		// <int> The length of the hash that you'd like to return.
	,	int $base = 64			// <int> The base to use for hash conversion.
	): string						// RETURNS <str> A rand hash key of the desired length.
	
	// $hash = Security::randHash(25)		// Returns 25 random characters
	// $hash = Security::randHash(25, 62);	// Returns 25 random characters (of base 62 characters)
	{
		$result = "";
		
		for($a = 0;$a < $length;$a++)
		{
			$result .= self::$padChars[mt_rand(0, $base - 1)];
		}
		
		return $result;
	}
	
	
/****** Get the hash of a designated file ******/
	public static function filehash
	(
		string $filepath		// <str> The path to the file to get the hash of.
	): string					// RETURNS <str> The hash of the file.
	
	// $filehash = Security::filehash($filepath);
	{
		return str_replace(array("+", "/", "="), array("", "", ""), base64_encode(sha1_file($filepath, true)));
	}
	
	
/****** Arbitrary Conversions Between Bases (thanks to stack overflow) ******/
	public static function convertBaseArbitrary
	(
		string $value			// <str> The value to convert to another base.
	,	int $fromBase		// <int> The base that you're converting from.
	,	int $toBase 		// <int> The base that you're converting to.
	): string					// RETURNS <str> The resulting base change.
	
	// $newValue = Security::convertBaseArbitrary("my awesome hash", 64, 10);
	{
		// Prepare other values
		$length = strlen($value);
		$nibbles = array();
		$result = '';
		
		for($i = 0;$i < $length;++$i)
		{
			$nibbles[] = self::$padArray[$value[$i]];
		}
		
		do
		{
			$value = 0;
			$newlen = 0;
			
			for($i = 0;$i < $length;++$i)
			{
				$value = $value * $fromBase + $nibbles[$i];
				
				if ($value >= $toBase)
				{
					$nibbles[$newlen++] = (int)($value / $toBase);
					$value %= $toBase;
				}
				else if ($newlen > 0)
				{
					$nibbles[$newlen++] = 0;
				}
			}
			
			$length = $newlen;
			$result = self::$padChars[$value] . $result;
		}
		while ($newlen !== 0);
		
		return $result;
	}
	
	
/****** Purify a block of HTML, Prevent XSS ******/
	public static function purify
	(
		string $unsafeHTML		// <str> The HTML that you want to clean.
	): string					// RETURNS <str> The safe HTML.
	
	// $safeHTML = Security::purify($unsafeHTML);
	{
		// Get the HTMLPurifier Library
		if(!method_exists("HTMLPurifier_Config", "createDefault"))
		{
			require(SYS_PATH . '/libraries/htmlpurifier/HTMLPurifier.auto.php');
		}
		
		// If the purifier library was detected, utilize it
		if(method_exists("HTMLPurifier_Config", "createDefault"))
		{
			$config = HTMLPurifier_Config::createDefault();
			$purifier = new HTMLPurifier($config);
			$clean_html = $purifier->purify($unsafeHTML);
			
			return $clean_html;
		}
		
		return "{error: unsanitized html}";
	}
	
	
/****** Purify a URL, Prevent XSS ******/
	public static function purifyURL
	(
		string $unsafeURL		// <str> The URL that you want to clean.
	): string					// RETURNS <str> The safe URL.
	
	// $safeHTML = Security::purifyURL($unsafeURL);
	{
		// Get the HTMLPurifier Library
		if(!method_exists("HTMLPurifier_Config", "createDefault"))
		{
			require(SYS_PATH . '/libraries/htmlpurifier/HTMLPurifier.auto.php');
		}
		
		// If the purifier library was detected, utilize it
		if(method_exists("HTMLPurifier_Config", "createDefault"))
		{
			$config = HTMLPurifier_Config::createDefault();
			$purifier = new HTMLPurifier_AttrDef_URI();
			$context = new HTMLPurifier_Context();
			$cleanURL = $purifier->validate($unsafeURL, $config, $context);
			
			return $cleanURL;
		}
		
		return "{error: unsanitized url}";
	}
	
	
/****** Fingerprint User & Force Session Update if Necessary ******/
	public static function fingerprint(
	): bool					// RETURNS <bool> TRUE if successful, FALSE otherwise.
	
	// Security::fingerprint();		// Checks user's fingerprint. Forces new session if illegitimate.
	{
		// Check if the user agent matches up between page loads.
		// If it doesn't, that's suspicious - let's destroy the session to avoid potential hijacking.
		if(isset($_SESSION[SITE_HANDLE]['USER_AGENT']))
		{
			if($_SERVER['HTTP_USER_AGENT'] !== $_SESSION[SITE_HANDLE]['USER_AGENT'])
			{
				session_destroy();
			}
		}
		elseif(isset($_SERVER['HTTP_USER_AGENT']))
		{
			// Keep track of the current user agent
			$_SESSION[SITE_HANDLE]['USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
		}
		
		// Prepare a session-based CSRF token if not present
		// Note: if the user logs out (or times out), this will reset, causing existing pages to fail functionality.
		if(!isset($_SESSION[SITE_HANDLE]['csrfToken']))
		{
			$_SESSION[SITE_HANDLE]['csrfToken'] = self::randHash(64);
		}
		
		return true;
	}
	
}