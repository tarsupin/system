<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Auro {
	
	
/****** Grant auro to a user ******/
	public static function grant
	(
		$uniID			// <int> The UniID to grant the auro to.
	,	$auro			// <int> The amount of auro to send to the user.
	,	$desc = ""		// <str> A description for the transaction, if desired.
	,	$siteName = ""	// <str> The name of the site, if you're recording a transaction.
	)					// RETURNS <bool> TRUE if the user received it, FALSE on failure.
	
	// $success = Auro::grant($uniID, $auro, [$desc], [$siteName]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id"	=> $uniID
		,	"auro"		=> $auro
		);
		
		if($desc)
		{
			$packet['desc'] = $desc;
			$packet['site_name'] = $siteName;
		}
		
		return Connect::to("karma", "GrantAuroAPI", $packet);
	}
	
	
/****** Spend or subtract auro from the user ******/
	public static function spend
	(
		$uniID			// <int> The UniID to spend auro from.
	,	$auro			// <int> The amount of auro to spend from the user.
	,	$desc = ""		// <str> A description for the transaction, if desired.
	,	$siteName = ""	// <str> The name of the site, if you're recording a transaction.
	)					// RETURNS <bool> TRUE on successful purchase, FALSE if the user didn't have enough auro.
	
	// $success = Auro::spend($uniID, $auro, [$desc], [$siteName]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id"	=> $uniID
		,	"auro"		=> $auro
		);
		
		if($desc)
		{
			$packet['desc'] = $desc;
			$packet['site_name'] = $siteName;
		}
		
		return Connect::to("karma", "SpendAuroAPI", $packet);
	}
	
	
/****** Exchange currency between two users ******/
	public static function exchange
	(
		$uniIDFrom		// <int> The UniID spending the auro.
	,	$uniIDTo		// <int> The UniID receiving the auro.
	,	$auro			// <int> The amount of auro being exchanged.
	,	$desc = ""		// <str> A description for the transaction, if desired.
	,	$siteName = ""	// <str> The name of the site, if you're recording a transaction.
	)					// RETURNS <bool> TRUE on successful exchange, FALSE on failure.
	
	// $success = Auro::exchange($uniIDFrom, $uniIDTo, $auro, [$desc], [$siteName]);
	{
		// Prepare the Packet
		$packet = array(
			"uni_id_from"	=> $uniIDFrom
		,	"uni_id_to"		=> $uniIDTo
		,	"auro"			=> $auro
		);
		
		if($desc)
		{
			$packet['desc'] = $desc;
			$packet['site_name'] = $siteName;
		}
		
		return Connect::to("karma", "ExchangeAuroAPI", $packet);
	}
}
