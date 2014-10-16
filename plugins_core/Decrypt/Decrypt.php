<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Decrypt Plugin ------
--------------------------------------

This plugin provides allows you to decrypt data that originated from one of phpTesla's encryption algorithms. This is essential for private APIs and the like.

The only method you'll need to run with the Decrypt plugin is Decrypt::run(). The plugin will automatically use the data provided to determine how to read the encryption. This is automated because the Encrypt plugin will automatically include a value that indicates what type of encryption it's using.

For example, the encrypted data might look like this:
	
	"default|2t2LnNbJ4XvtFcM9EhQD9hJl=hO|c4S/fyDztw|F=OW290gIgnK6Ad+Clxsr=Yb3VWEsfiOli2otC"
	
The first segment says "default", which indicates that the Decrypt plugin should use the "default" style of decryption on the string.


-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Prepare an encryption key
$encryptionKey = "secret key";

// Encrypt some data
$encryptedData = Encrypt::run($encryptionKey, "Some data to encrypt");
var_dump($encryptedData);

// Decrypt the data
$decryptedData = Decrypt::run($encryptionKey, $encryptedData);
var_dump($decryptedData);


---------------------------------
------ Methods Available ------
---------------------------------

Decrypt::run($key, $encryptedData);

*/

abstract class Decrypt {
	
	
/****** Decrypt Data ******/
	public static function run
	(
		$key 			// <str> The original key that was used for the encryption.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> the decrypted string (or garbage, if decrypted improperly).
	
	// $decryptedData = Decrypt::run("myPassword", $encryptedData);
	{
		// Gather Important Values
		$exp = explode("|", $encryptedData, 2);
		$algo = $exp[0];
		
		// Check the default algorithm first
		if($algo == "")
		{
			return self::_getDec_default($key, $exp[1]);
		}
		
		$algo = "_getDec_" . $algo;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Decrypt", $algo))
		{
			return self::$algo($key, $exp[1]);
		}
		
		return self::_getDec_default($key, $exp[1]);
	}
	
	
/****** `default` Decryption Algorithm ******/
	public static function _getDec_default
	(
		$key 			// <str> The original key that you used to encrypt the data.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> The decrypted string (or garbage, if decrypted improperly).
	
	// $data = self::_getDec_default($key, $encryptedData);
	{
		// Gather Important Values
		$exp = explode("|", $encryptedData, 3);
		
		if(count($exp) < 3) { return ""; }
		
		$append = $exp[0];
		$checker = $exp[1];
		$encryptedData = $exp[2];
		
		// Generate the prepared hash that was used to mask the password
		$prep1 = Security::hash($key . $append);
		
		// Create a padding string
		$padString = "";
		$encLength = strlen($encryptedData);
		$count = 1;
		
		while(strlen($padString) < $encLength)
		{
			$padString .= Security::hash($prep1 . $append . $count);
			
			$count++;
		}
		
		// Get the unpadded string (first step)
		$finalStep = Security::unpad($encryptedData, $padString);
		
		// Now reverse the final pad that destroys patterns
		$finalPad = substr($finalStep, 0, 10);
		$encryptedData = substr($finalStep, 10);
		
		// Get the unecrypted value
		$decryptedData = Security::unpad($encryptedData, $finalPad);
		
		// Confirm whether or not the confirmation checker is valid
		$mustValidateAs = str_replace("|", "", Security::hash($key . $append . $decryptedData, 10));
		
		if($checker !== $mustValidateAs)
		{
			// If the confirmation checker is invalid, reject the decryption (might be tampering attempt)
			
			// Optional: we can track any attempts to modify data if we care to. If the code gets here,
			// it means the user was trying to tamper with the encryption.
			
			// Return a random hash of equal length to the original string (because why not)
			return Security::randHash(strlen($encryptedData));
		}
		
		// We revert / and =s now that we're done
		return base64_decode(str_replace("=s", "/", $decryptedData));
	}
	
	
/****** `fast` Decryption Algorithm ******/
	public static function _getDec_fast
	(
		$key 			// <str> The original key that you used to encrypt the data.
	,	$encryptedData	// <str> The data to decrypt.
	)					// RETURNS <str> The decrypted string (or garbage, if decrypted improperly).
	
	// $data = self::_getDec_fast($key, $encryptedData);
	{
		// Make sure this decryption will work on this system
		if(!function_exists("mcrypt_decrypt"))
		{
			return "";
		}
		
		// Only the first 32 characters of the key were sent, and done so with the Security::hash method
		$key = Security::hash($key, 32, 64);
		
		// Begin decryption
		$encryptedData = base64_decode($encryptedData);
		$vectorSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
		
		$vector = substr($encryptedData, 0, $vectorSize);
		$encryptedData = substr($encryptedData, $vectorSize);
		$decryptedData = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $encryptedData, MCRYPT_MODE_CBC, $vector);
		
		// mcrypt pads the return string with nulls, so we need to trim the end
		return rtrim($decryptedData, "\0");
	}
	
	
/****** `open` Encryption Algorithm ******/
	private static function _getDec_open
	(
		$key 		// <str> The key that you want to use for your encryption.
	,	$encData	// <str> The data that you want to encrypt.
	)				// RETURNS <str> The resulting encryption.
	
	// self::_setEnc_open("myKey", "Data to encrypt");
	{
		return base64_decode($encData);
	}
	
}
