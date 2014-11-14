<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Auro {
	
	
/****** Grant auro to a user ******/
	public static function grant
	(
		int $uniID		// <int> The UniID to grant the auro to.
	,	int $auro		// <int> The amount of auro to send to the user.
	): bool				// RETURNS <bool> TRUE if the user recieved it, FALSE on failure.
	
	// $success = Auro::grant($uniID, $auro);
	{
		return Connect::to("karma", "GrantAuroAPI", array("uni_id" => $uniID, "auro" => $auro));
	}
	
	
/****** Spend or subtract auro from the user ******/
	public static function spend
	(
		int $uniID		// <int> The UniID to spend auro from.
	,	int $auro		// <int> The amount of auro to spend from the user.
	): bool				// RETURNS <bool> TRUE on successful purchase, FALSE if the user didn't have enough auro.
	
	// $success = Auro::spend($uniID, $auro);
	{
		return Connect::to("karma", "SpendAuroAPI", array("uni_id" => $uniID, "auro" => $auro));
	}
	
	
/****** Exchange currency between two users ******/
	public static function exchange
	(
		int $uniIDFrom		// <int> The UniID spending the auro.
	,	int $uniIDTo		// <int> The UniID receiving the auro.
	,	int $auro			// <int> The amount of auro being exchanged.
	): bool					// RETURNS <bool> TRUE on successful exchange, FALSE on failure.
	
	// $success = Auro::exchange($uniIDFrom, $uniIDTo, $auro);
	{
		return Connect::to("karma", "ExchangeAuroAPI", array("uni_id_from" => $uniIDFrom, "uni_id_to" => $uniIDTo, "auro" => $auro));
	}
}