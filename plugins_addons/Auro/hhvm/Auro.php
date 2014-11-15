<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Auro {
	
	
/****** Grant auro to a user ******/
	public static function grant
	(
		int $uniID		// <int> The UniID to grant the auro to.
	,	int $auro		// <int> The amount of auro to send to the user.
	,	string $desc = ""	// <str> A description for the transaction, if desired.
	): bool				// RETURNS <bool> TRUE if the user recieved it, FALSE on failure.
	
	// $success = Auro::grant($uniID, $auro, [$desc]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id"	=> $uniID
		,	"auro"		=> $auro
		);
		
		if($desc) { $packet['desc'] = $desc; }
		
		return Connect::to("karma", "GrantAuroAPI", $packet);
	}
	
	
/****** Spend or subtract auro from the user ******/
	public static function spend
	(
		int $uniID		// <int> The UniID to spend auro from.
	,	int $auro		// <int> The amount of auro to spend from the user.
	,	string $desc = ""	// <str> A description for the transaction, if desired.
	): bool				// RETURNS <bool> TRUE on successful purchase, FALSE if the user didn't have enough auro.
	
	// $success = Auro::spend($uniID, $auro, [$desc]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id"	=> $uniID
		,	"auro"		=> $auro
		);
		
		if($desc) { $packet['desc'] = $desc; }
		
		return Connect::to("karma", "SpendAuroAPI", $packet);
	}
	
	
/****** Exchange currency between two users ******/
	public static function exchange
	(
		int $uniIDFrom		// <int> The UniID spending the auro.
	,	int $uniIDTo		// <int> The UniID receiving the auro.
	,	int $auro			// <int> The amount of auro being exchanged.
	,	string $desc = ""		// <str> A description for the transaction, if desired.
	): bool					// RETURNS <bool> TRUE on successful exchange, FALSE on failure.
	
	// $success = Auro::exchange($uniIDFrom, $uniIDTo, $auro, [$desc]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id_from"	=> $uniIDFrom
		,	"uni_id_to"		=> $uniIDTo
		,	"auro"			=> $auro
		);
		
		if($desc) { $packet['desc'] = $desc; }
		
		return Connect::to("karma", "ExchangeAuroAPI", $packet);
	}
}