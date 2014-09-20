<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Confirm Plugin ------
--------------------------------------

This plugin generates confirmation codes, such as for password resets URLs that get sent through email.

The standard use for this plugin is to simply prove the authenticity of something, such as that they knew about a special offer (like an internet coupon) or need to prove they have access to something. It can also be used to store data that can be retrieved at a later time.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------

	// Create a Confirmation Link
	$confirmType = "reset-password";
	$uniqueIdentifier = "joe@hotmail.com";
	
	$link = Confirm::createLink($confirmType, $uniqueIdentifier, array("miscVal" => "129fda"), 10, false);
	
	echo "To reset your password, visit this url: " . $link . "<br /><br />";
	
	// Validate a Confirmation Link
	$token = substr($link, strpos($link, "?enc=") + 5);
	
	if($data = Confirm::validate($token))
	{
		echo "Congratulations, this confirmation link is still valid!";
		
		var_dump($data);
	}
	
	
-------------------------------
------ Methods Available ------
-------------------------------

// Generates a confirmation token
$confirmToken = Confirm::create($type, $uniqueIdentifier, [$extraArgs], [$expire], [$extraSalt])

// Generates a Confirmation URL
$confirmURL = Confirm::createLink($type, $uniqueIdentifier, [$extraArgs], [$expire], [$extraSalt])

// Validates a confirmation token and returns original confirmation data on success
$confirmData = Confirm::validate($confirmToken, [$extraSalt])

*/

abstract class Confirm {
	
	
/****** Create Confirmation Data ******/
	public static function create
	(
		string $type					// <str> The type of confirmation you're creating.
	,	mixed $uniqueIdentifier		// <mixed> A string to uniquely identify the user or link being generated.
	,	array <str, mixed> $extraArgs = array()	// <str:mixed> Extra arguments that you would like to pass.
	,	int $expireInHours = 24		// <int> Hours until it becomes invalid. Setting to 0 allows it forever.
	,	string $extraSalt = ""			// <str> Any extra salt that you would like to add to this confirmation.
	): string							// RETURNS <str> A JSON string that contains "type", "id", "time", "expired".
	
	// $confirmToken = Confirm::create("email-confirm", "joe@hotmail.com", array("miscKey" => "159djf"), 24, false);
	{
		// Prepare the Salt
		$salt = SITE_SALT . $extraSalt;
		
		// Prepare the arguments to pass
		$args = array(
					"type" => $type,
					"id" => $uniqueIdentifier,
					"time" => time(),
					"hrs" => abs(min(24, ceil($expireInHours)))
				);
		
		foreach($extraArgs as $key => $val)
		{
			$args[$key] = $val;
		}
		
		// Prepare Encryption
		return Encrypt::run($salt, Serialize::encode($args));
	}
	
	
/****** Validate a Confirmation Link ******/
	public static function validate
	(
		string $confirmToken		// <str> The full encryption string.
	,	string $extraSalt = ""		// <str> Any extra salt that is required to complete this confirmation.
	): mixed						// RETURNS <mixed> the confirmation data if successful, FALSE on failure.
	
	// if($confirmData = Confirm::validate($confirmToken, [$extraSalt])) { echo "Reset confirmed!"; }
	{
		// Prepare the Salt
		$salt = SITE_SALT . $extraSalt;
		
		// Decrypt the data
		if(!$data = Serialize::decode(Decrypt::run($salt, $confirmToken)))
		{
			Alert::error("Invalid Link", "That confirmation code is not valid.", 5);
			return false;
		}
		
		// Make sure the key hasn't expired
		if($data['time'] + ($data['hrs'] * 3600) < time())
		{
			Alert::error("Expired Code", "That confirmation code has expired.", 2);
			return false;
		}
		
		return $data;
	}
	
	
/****** Create Confirmation Link ******/
	public static function createLink
	(
		string $type					// <str> The type of confirmation you're creating.
	,	mixed $uniqueIdentifier		// <mixed> A string to uniquely identify the user or link being generated.
	,	array <mixed, mixed> $extraArgs = array()	// <mixed:mixed> Extra arguments that you would like to pass.
	,	int $expireInHours = 24		// <int> Hours until it becomes invalid. Setting to 0 allows it forever.
	,	string $extraSalt = ""			// <str> Any extra salt that you would like to add to this confirmation.
	): string							// RETURNS <str> valid confirmation link.
	
	// $link = Confirm::createLink("email-confirm", "joe@hotmail.com", [$extraArgs], [$expireInHours], [$extraSalt]);
	{
		return SITE_URL . "/confirm/" . $type . "?enc=" . urlencode(self::create($type, $uniqueIdentifier, $extraArgs, $expireInHours, $extraSalt));
	}
	
}
