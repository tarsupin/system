<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the Serialize Plugin ------
----------------------------------------

This plugin is a wrapper for JSON, plus a few additional features. JSON is a "serializer", which is used to pack objects and arrays into strings that can be retrieved later.

For example, let's say you wanted to save an array into a database. How would you go about doing that?

	// Create the example array
	$array = array("Hello", "World!");
	
	// The first step is to change the array into a string
	$string = Serialize::encode($array);
	
	// See what the string looks like
	echo $string;		// Outputs: ["Hello","World!"]
	
	// Let's turn it back into an array
	$array = Serialize::decode($string);
	
	// And see what the result is
	var_dump($array);
	
	
-------------------
------ Notes ------
-------------------
Despite it's primary use for serializing arrays and objects, the Serialize plugin can serialize other variables too. This includes numbers, strings, booleans, etc. They are treated the same way:

	Serialize::encode("Hello world!");	// Encodes a string
	Serialize::encode(true);			// Encodes a boolean
	Serialize::encode(1022);			// Encodes a number


-------------------------------
------ Methods Available ------
-------------------------------

// Changing an object or array into a serialized value
$serializedString = Serialize::encode($value);
Serialize::encodeFile($filename, $value);

// Changing a serialized value back into an array (or object)
$value = Serialize::decode($serialized)
$value = Serialize::decodeFile($filename)

// Change the keys on an array or object (to reduce serialized storage size)
$array = Serialize::changeKeys($array, $keyChanges);
$object = Serialize::changeKeys($object, $keyChanges);

// Pack and unpack numeric ranges (to reduce serialized storage size)
$numericArray = Serialize::numericArrayPack($numericArray);
$numericArray = Serialize::numericArrayUnpack($numericArray);

*/

abstract class Serialize {
	
	
/****** Encode Serialized Text ******/
	public static function encode
	(
		mixed $value		// <mixed> The value to serialize.
	)				// RETURNS <string> the serialized string.
	
	// Serialize::encode($value);
	{
		return json_encode($value);
	}
	
	
/****** Encode Serialized Text into a File ******/
	public static function encodeFile
	(
		string $filepath		// <str> The path to save the serialized data.
	,	mixed $value			// <mixed> The value to serialize.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Serialize::encodeFile($filepath, $value);
	{
		return File::write($filepath, json_encode($value));
	}
	
	
/****** Load Value from Serialized Data ******/
	public static function decode
	(
		string $serializedData		// <str> The serialized data to load
	,	bool $toArray = true		// <bool> FALSE if you want to change it to an object instead of an array.
	): mixed						// RETURNS <mixed> the data in its original form, or NULL if not a serialized value
	
	// $value = Serialize::decode($serializedData, [$toArray]);
	{
		return json_decode($serializedData, $toArray);
	}
	
	
/****** Load Value from a Serialized File ******/
	public static function decodeFile
	(
		string $filepath			// <str> The path to the serialized data to decode.
	,	bool $toArray = true		// <bool> FALSE if you want to change it to an object instead of an array.
	): mixed						// RETURNS <mixed> the data in its original form, or NULL if not a serialized value
	
	// $value = Serialize::decodeFile($filepath, [$toArray]);
	{
		// Attempt to retrieve the file content
		if($serializedData = File::read($filepath))
		{
			return self::decode($serializedData, $toArray);
		}
	}
	
	
/****** Rename an object's keys, generally for minification purposes ******/
	public static function changeKeys
	(
		mixed $object			// <mixed> The object or array whose keys you're going to modify.
	,	array <str, str> $keyChanges		// <str:str> KEY = key to rename, VAL = the new key name.
	): mixed					// RETURNS <mixed> the original object or array, but with modified keys.
	
	// $array = Serialize::changeKeys($array, $keyChanges);
	// $object = Serialize::changeKeys($object, $keyChanges);
	{
		// Change the properties if an object
		if(is_object($object))
		{
			foreach($keyChanges as $key => $val)
			{
				if(isset($object->$key))
				{
					$object->$val = $object->$key;
					
					unset($object->$key);
				}
			}
			
			return $object;
		}
		
		// Change the object
		foreach($keyChanges as $key => $val)
		{
			if(isset($object[$key]))
			{
				$object[$val] = $object[$key];
				
				unset($object[$key]);
			}
		}
		
		return $object;
	}
	
	
/****** Minimize a numeric array into ranges ******/
	public static function numericArrayPack
	(
		array <int, int> $numericArray	// <int:int> An array of numbers that might be in a range.
	): array <int, mixed>					// RETURNS <int:mixed> an array with range minifiers, if applicable.
	
	// $numericArray = Serialize::numericArrayPack($numericArray);
	{
		// Prepare Values
		$lastPos = -2;
		$rangeSequence = 0;
		
		$ranges = array();
		
		// Loop through array and determine any available ranges
		foreach($numericArray as $val)
		{
			// Check if the range is broken
			if($lastPos !== $val - 1)
			{
				$rangeSequence++;
			}
			
			$ranges[$rangeSequence][] = $val;
			
			$lastPos = $val;
		}
		
		// Prepare the new minified value
		$newArray = array();
		
		foreach($ranges as $rng)
		{
			if(count($rng) > 2)
			{
				$start = $rng[0];
				$end = $rng[count($rng) - 1];
				
				$newArray[] = $start . "-" . $end;
			}
			else
			{
				foreach($rng as $val)
				{
					$newArray[] = $val;
				}
			}
		}
		
		return $newArray;
	}
	
	
/****** Unpack a minimized numeric array ******/
	public static function numericArrayUnpack
	(
		array <int, mixed> $numericArray	// <int:mixed> An array that was minimized.
	): array <int, int>					// RETURNS <int:int> the unpacked array.
	
	// $numericArray = Serialize::numericArrayUnpack($numericArray);
	{
		// Prepare Variables
		$newArray = array();
		
		// Loop through each entry
		foreach($numericArray as $value)
		{
			if(is_string($value))
			{
				$exp = explode("-", $value);
				
				$start = $exp[0] + 0;
				$end = $exp[1] + 0;
				
				for($a = $start;$a <= $end;$a++)
				{
					$newArray[] = $a;
				}
			}
			else
			{
				$newArray[] = $value;
			}
		}
		
		return $newArray;
	}
}
