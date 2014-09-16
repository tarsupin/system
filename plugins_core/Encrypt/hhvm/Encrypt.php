<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Encrypt Plugin ------
--------------------------------------

This plugin allows you to encrypt data. phpTesla uses this plugin frequently, such as when when transferring secure or private data through its APIs.

To decrypt an encrypted value, you must use the Decrypt plugin.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------

// Prepare an encryption key
$encryptionKey = "secret key";

// Encrypt some data
$encryptedData = Encrypt::run($encryptionKey, "Some data to encrypt");
var_dump($encryptedData);

// Decrypt the data
$decryptedData = Decrypt::run($encryptionKey, $encryptedData);
var_dump($decryptedData);

-------------------------------
------ Methods Available ------
-------------------------------

Encrypt::run($key, $dataToEncrypt, [$encryptionType]);

*/

abstract class Encrypt {
	
	
/****** Encrypt Data ******/
	public static function run
	(
		string $key	 		// <str> The key that you want to use for your encryption (required to decrypt).
	,	string $encData		// <str> The data to encrypt.
	,	string $encType = ""	// <str> The type of encryption you're using, specific to this framework.
	): string					// RETURNS <str> The resulting encrypted data.
	
	// $encryptedData = Encrypt::run("myKey", "Some data to encrypt");
	{
		// Check the default algorithm first
		if($encType == "")
		{
			return self::_setEnc_default($key, $encData);
		}
		
		// Prepare the Encryption Algorithm to use
		$hashAlgo = "_setEnc_" . $encType;
		
		// Check if hash algorithm selected is valid
		if(method_exists("Encrypt", $hashAlgo))
		{
			return self::$hashAlgo($key, $encData);
		}
		
		return self::_setEnc_default($key, $encData);
	}
	
	
/****** `default` Encryption Algorithm ******/
	private static function _setEnc_default
	(
		string $key 		// <str> The key that you want to use for your encryption.
	,	string $encData	// <str> The data that you want to encrypt.
	): string				// RETURNS <str> The resulting encryption.
	
	// self::_setEnc_default("myKey", "Data to encrypt");
	{
		// Convert the encrypted data to base 64 so that padding will work appropriately
		// We have to change / to =s so that the Security::pad() method won't break
		$encData = str_replace("/", "=s", base64_encode($encData));
		
		// Append a Hash-Specific Salt
		$append = str_replace("|", "", Security::randHash(27, 64));
		
		// Create the confirmation checker (prevents tampering)
		// If someone tries to modify the sent data by reusing the same salt, this forces them to generate an entirely
		// new confirmation hash based on the new data (which means they still need to know the algorithm and key).
		$checker = str_replace("|", "", Security::hash($key . $append . $encData, 10));
		
		// Create a prepared hash to mask the key
		// This turns the key (such as "my password") into a long hash to mask the original
		$prep1 = Security::hash($key . $append);
		
		// Vary the encryption data for final pass pad
		// This prevents the final decryption string from possessing any patterns, such as if there were two identical
		// sets of data sent. Even if you knew the strings were identical, you can't match them because of this step.
		$finalPad = Security::randHash(10);
		$encData = $finalPad . Security::pad($encData, $finalPad);
		
		// Create a padding string
		$padString = "";
		$encLength = strlen($encData);
		$count = 1;
		
		while(strlen($padString) < $encLength)
		{
			$padString .= Security::hash($prep1 . $append . $count);
			
			$count++;
		}
		
		// Return the encryption string
		return "|" . $append . "|" . $checker . "|" . Security::pad($encData, $padString);
	}
	
	
/****** `fast` Encryption Algorithm ******/
# Note: This algorithm should be used for encryption that requires speed.
	private static function _setEnc_fast
	(
		string $key 		// <str> The key that you want to use for your encryption.
	,	string $encData	// <str> The data that you want to encrypt.
	): string				// RETURNS <str> The resulting encryption.
	
	// self::_setEnc_fast("myKey", "Data to encrypt");
	{
		// Make sure the mcrypt extension is valid - otherwise, use default encryption
		if(function_exists("mcrypt_encrypt"))
		{
			// Can only send the first 32 characters of the key
			$key = Security::hash($key, 32, 64);
			
			// Get the initialization vector (appends a public salt)
			$vectorSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
			$vector = mcrypt_create_iv($vectorSize, MCRYPT_DEV_RANDOM);
			
			// Encrypt the data
			$encData = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $encData, MCRYPT_MODE_CBC, $vector);
			
			return "fast|" . base64_encode($vector . $encData);
		}
		
		return self::_setEnc_default($key, $encData);
	}
	
	
/****** `open` Encryption Algorithm ******/
# Note: This algorithm does NOT provide any encryption. It is generally used for passing URL data.
	private static function _setEnc_open
	(
		string $key 		// <str> The key that you want to use for your encryption.
	,	string $encData	// <str> The data that you want to encrypt.
	): string				// RETURNS <str> The resulting encryption.
	
	// self::_setEnc_open("myKey", "Data to encrypt");
	{
		return "open|" . base64_encode($encData);
	}
	
}