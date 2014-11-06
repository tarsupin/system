<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Currency {
	
	
/****** Check how much of a currency the user has ******/
	public static function check
	(
		$uniID		// <int> The Uni-ID to check.
	)				// RETURNS <mixed> The amount of currency the user has, FALSE on error.
	
	// $amount = Currency::check("Joe");	// Returns the amount of currency Joe has
	{
		// Gather the currency data
		// If nothing was recovered, create the user's currency row
		if(!$fetchCurrency = Database::selectOne("SELECT `amount` FROM `currency` WHERE uni_id=? LIMIT 1", array($uniID)))
		{
			// Create a row for the user
			$success = Database::query("INSERT INTO `currency` (`uni_id`, `amount`) VALUES (?, ?)", array($uniID, 0));
			
			if($success === false) { return false; }
			
			$fetchCurrency = array("amount" => 0.00);
		}
		
		// Return the amount that the user possesses
		return (float) number_format((float) $fetchCurrency['amount'], 2, ".", "");
	}
	
	
/****** Add currency to the user (from the system) ******/
	public static function add
	(
		$uniID				// <int> or <str> The ID or username of the user to give currency to.
	,	$amount				// <float> Sets how much currency to add to the user.
	,	$desc = ""			// <str> The description of the transaction, if applicable.
	,	&$errorStr = false	// <var> A variable to return error strings with.
	)						// RETURNS <mixed> The new balance, or FALSE on error.
	
	// $balance = Currency::add($uniID, 150, "Found Treasure");
	{
		// Reject if the amount isn't a positive value
		if($amount <= 0)
		{
			$errorStr = "Must provide a positive amount to send.";
			return false;
		}
		
		// Get the current amount (also confirms the user exists & creates the row)
		$currentAmount = self::check($uniID);
		
		if($currentAmount === false)
		{
			$errorStr = "User's balance encountered errors while processing.";
			return false;
		}
		
		// Add the currency - if not successful, return false
		Database::startTransaction();
		
		$success = Database::query("UPDATE currency SET amount=amount+? WHERE uni_id=? LIMIT 1", array($amount, $uniID));
		
		if(!$success)
		{
			$errorStr = "Couldn't process the transaction.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Get the new balance
		$currentAmount += $amount;
		
		// Record the Transaction
		$success = self::record($uniID, 0, $amount, $currentAmount, $desc);
		
		if(!$success)
		{
			$errorStr = "Couldn't record the transaction.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Commit or Rollback the transaction
		Database::endTransaction();
		
		return number_format($currentAmount, 2);
	}
	
	
/****** Subtract credits from the user (from the system) ******/
	public static function subtract
	(
		$uniID				// <int> The Uni-Account to subtract credits from.
	,	$amount				// <float> Sets how many credits to subtract to the user.
	,	$desc = ""			// <str> The description of the transaction, if applicable.
	,	&$errorStr = false	// <var> A variable to return error strings with.
	)						// RETURNS <mixed> The new balance, or FALSE on error.
	
	// $balance = Currency::subtract(Me::$id, 125, "Bought Gold Necklace");
	{
		// Reject if the amount isn't a positive value (to subtract)
		if($amount <= 0)
		{
			$errorStr = "Must provide a positive amount to subtract.";
			return false;
		}
		
		// Get the current amount (also confirms the user exists & creates the row)
		$currentAmount = self::check($uniID);
		
		if($currentAmount === false)
		{
			$errorStr = "User's balance encountered errors while processing.";
			return false;
		}
		
		// If the user doesn't have that much currency, reject the subtraction
		if($currentAmount < $amount)
		{
			$errorStr = "User does not have enough credits available.";
			return false;
		}
		
		// Subtract the currency - if not successful, return false
		Database::startTransaction();
		
		$success = Database::query("UPDATE currency SET amount=amount-? WHERE uni_id=? LIMIT 1", array($amount, $uniID));
		
		if(!$success)
		{
			$errorStr = "Couldn't process the transaction.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Get the new balance
		$currentAmount -= $amount;
		
		// Record the Transaction
		$success = self::record($uniID, 0, 0 - $amount, $currentAmount, $desc);
		
		if(!$success)
		{
			$errorStr = "Couldn't record the transaction.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Commit the transaction
		Database::endTransaction();
		
		return number_format($currentAmount, 2);
	}
	
	
/****** Exchange currency between two users ******/
	public static function exchange
	(
		$uniIDFrom			// <int> The Uni-Account of the user sending currency.
	,	$uniIDTo			// <int> The Uni-Account receiving currency.
	,	$amount				// <float> Sets how much currency to exchange.
	,	$desc = ""			// <str> The description of the transaction, if applicable.
	,	&$errorStr = false	// <var> A variable to return error strings with.
	)						// RETURNS <mixed> The balances of each user, or FALSE on error.
	
	// list($fromBalance, $toBalance) = Currency::exchange($joeID, $bobID, 2.50, "Sent Bob 2.5 coins.", $errorStr);
	{
		// Reject if the amount isn't a positive value
		if($amount <= 0)
		{
			$errorStr = "Must send a positive amount.";
			return false;
		}
		
		// Make sure the user isn't sending to themselves
		if($uniIDTo == $uniIDFrom)
		{
			$errorStr = "Cannot send credits to yourself.";
			return false;
		}
		
		// Get the current amounts of each user (also confirms the users exist & creates their rows)
		$toBalance = self::check($uniIDTo);
		$fromBalance = self::check($uniIDFrom);
		
		if($toBalance === false)
		{
			$errorStr = "Recipient's balance encountered errors while processing.";
			return false;
		}
		else if($fromBalance === false)
		{
			$errorStr = "Sender's balance encountered errors while processing.";
			return false;
		}
		
		// Make sure the sender has the appropriate amount
		if($fromBalance < $amount)
		{
			$errorStr = "Sender doesn't have enough credits.";
			return false;
		}
		
		// Add the credits - if not successful, return false
		Database::startTransaction();
		
		$success1 = Database::query("UPDATE currency SET amount=amount+? WHERE uni_id=? LIMIT 1", array($amount, $uniIDTo));
		$success2 = Database::query("UPDATE currency SET amount=amount-? WHERE uni_id=? LIMIT 1", array($amount, $uniIDFrom));
		
		if(!$success1 or !$success2)
		{
			$errorStr = "Couldn't process the transaction.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Get the new balance
		$toBalance += $amount;
		$fromBalance -= $amount;
		
		// Record the Transaction
		$success = self::record($uniIDTo, $uniIDFrom, $amount, $toBalance, $desc);
		
		if(!$success)
		{
			$errorStr = "Recording transaction was unsuccessful.";
			
			Database::endTransaction(false);
			return false;
		}
		
		// Commit or Rollback the transaction
		Database::endTransaction();
		
		return array(number_format($fromBalance, 2), number_format($toBalance, 2));
	}
	
	
/****** Records a transaction ******/
	public static function record
	(
		$uniID		// <int> The Uni-Account to send currency.
	,	$uniIDOther	// <int> The Uni-Account to receive currency (0 for the server).
	,	$amount		// <float> How much currency was added to the recipient.
	,	$balance	// <float> The balance the sender currently has after this transaction occurs.
	,	$desc = ""	// <str> A brief description about the transaction's purpose.
	)				// RETURNS <bool> TRUE on success, or FALSE on error.
	
	// Currency::record($user['id'], $otherUser['id'], 100, 5125, "Giftbox Sale");	// Records transaction between Joe and Bob
	{
		if($uniID === false or $uniIDOther === false) { return false; }
		
		// Prepare Values
		$timestamp = time();
		$pass2 = true;
		
		// Run the record keeping
		$pass1 = Database::query("INSERT INTO `currency_records` (`description`, `uni_id`, `other_id`, `amount`, `running_total`, `date_exchange`) VALUES (?, ?, ?, ?, ?, ?)", array(Sanitize::safeword($desc), $uniID, $uniIDOther, $amount, $balance, $timestamp));
		
		if($uniIDOther !== 0)
		{
			$balance = self::check($uniIDOther);
			$pass2 = Database::query("INSERT INTO `currency_records` (`description`, `uni_id`, `other_id`, `amount`, `running_total`, `date_exchange`) VALUES (?, ?, ?, ?, ?, ?)", array(Sanitize::safeword($desc), $uniIDOther, $uniID, 0 - $amount, $balance, $timestamp));
		}
		
		return ($pass1 && $pass2);
	}
}
