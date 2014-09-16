<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Paypal {

/****** Paypal Class ******
* This class handles paypal transactions.
* 
****** Methods Available ******
* $result = Paypal::runTransaction($postData);
* $txnData = Paypal::getTransaction($txnID, $columns = "*");
* 
* $customData = Paypal::prepareCustomField($uniID);
* $customData = Paypal::getCustomData($customValue);
* 
* 
* Paypal::transactionCompleted($postData);
* Paypal::transactionPending($postData);
* Paypal::transactionCancel($postData);
* 
* Paypal::insertTransaction($postData);
* Paypal::updateStatus($txnID, $status);
* Paypal::creditUser($postData, [$txnID]);
*/
	
	
/****** Class Variables ******/
	public static $paypalEmail = "unifaction@gmail.com";
	
	
/****** Generate `Paypal` SQL ******/
	public static function sql()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `paypal_transactions`
		(
			`txn_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`auth_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`amount_paid`			float(7,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`user_received`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`status`				varchar(20)					NOT NULL	DEFAULT '',
			`email`					varchar(64)					NOT NULL	DEFAULT '',
			
			`date_paid`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`txn_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Display SQL
		DatabaseAdmin::showTable("paypal_transactions");
	}
	
	
/****** Run a Transaction ******/
	public static function runTransaction
	(
		$postData		// <array> The Post Data of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $result = Paypal::runTransaction($postData);
	{
		switch($postData['payment_status'])
		{
			case "Completed":
				return self::transactionCompleted($postData);
			break;
			
			case "Pending":
			case "In-Progress";
				return self::transactionPending($postData);
			break;
			
			case "Refunded":
			case "Reversed":
			case "Cancelled_Reversed":
			case "Voided":
			case "Denied":
				return self::transactionCancel($postData);
			break;
		}
		
		return false;
	}
	
	
/****** Get Data from a Transaction ******/
	public static function getTransaction
	(
		$txnID			// <int> The Transaction ID of the transaction.
		$columns = "*"	// <str> The columns to retrieve from the transaction.
	)					// RETURNS <str:mixed> the transaction data
	
	// $txnData = Paypal::getTransaction($txnID, $columns = "*");
	{
		return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,*`") . " FROM paypal_transactions WHERE txn_id=? LIMIT 1", array($txnID));
	}
	
	
/****** Prepare the UniID of the user for the custom paypal value ******/
	public static function prepareCustomField
	(
		$uniID		// <int> The UniID to pass to paypal.
	)				// RETURNS <str> an encrypted value to pass as the custom value.
	
	// $customData = Paypal::prepareCustomField($uniID);
	{
		return Encrypt::run("paypal-custom-field", json_encode(array("uni_id" => $uniID)));
	}
	
	
/****** Get the UniID of the user from the custom paypal value ******/
	public static function getCustomData
	(
		$customValue	// <str> The custom value that was sent to paypal to identify the UniID.
	)					// RETURNS <int> the user's UniID, or 0 on failure.
	
	// $customData = Paypal::getCustomData($customValue);
	{
		if(!$decrypted = Decrypt::run("paypal-custom-field", $customValue))
		{
			return 0;
		}
		
		if(!$value = json_decode($decrypted, true))
		{
			return 0;
		}
		
		return $value;
	}
	
	
	
	
	
	
	
	
/****** Run a Completed Transaction ******/
	private static function transactionCompleted
	(
		$postData		// <array> The Post Data of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Paypal::transactionCompleted($postData);
	{
		if(!isset($postData['txn_id'])) { return false; }
		
		// Check if the transaction already exists
		if($getTransaction = self::getTransaction($postData['txn_id'], "txn_id, auth_id, uni_id, amount_paid, status"))
		{
			// Check if we need to do anything with this transaction
			if($getTransaction['status'] == "Completed")
			{
				return false;
			}
			
			// Process this completion
			Database::startTransaction();
			
			// Must update the transaction rather than insert a new one
			if($pass = Paypal::updateStatus($postData['txn_id'], "Completed"))
			{
				// Get the user of this transaction
				$uniID = (int) Database::selectValue("SELECT uni_id FROM paypal_transactions WHERE txn_id=? LIMIT 1", array($postData['txn_id']));
				
				// Send Credits to the User
				self::creditUser($postData, $getTransaction);
			}
			
			return Database::endTransaction($pass);
		}
		
		// Process this completion
		Database::startTransaction();
		
		if($pass = self::insertTransaction($postData))
		{
			// Send Credits to the User
			self::creditUser($postData);
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Run a "Pending" Transaction ******/
	private static function transactionPending
	(
		$postData		// <array> The Post Data of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Paypal::transactionPending($postData);
	{
		if(!isset($postData['txn_id'])) { return false; }
		
		// Check if the transaction already exists
		if($getTransaction = self::getTransaction($postData['txn_id'], "txn_id, uni_id, status"))
		{
			// Since this transaction already exists, "pending" doesn't matter. End here.
			return false;
		}
		
		// Process this pending transaction
		// Note: We may want to use this section to notify the user
		return self::insertTransaction($postData);
	}
	
	
/****** Run a "Cancel" Transaction ******/
	private static function transactionCancel
	(
		$postData		// <array> The Post Data of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Paypal::transactionCancel($postData);
	{
		if(!isset($postData['txn_id'])) { return false; }
		
		// Check if the transaction already exists
		if($getTransaction = self::getTransaction($postData['txn_id'], "txn_id, uni_id, status"))
		{
			// Check if there's no updates needed here
			if($getTransaction['status'] == "Cancelled") { return false; }
			
			// Cancel this transaction
			Database::startTransaction();
			
			// Must update the transaction rather than insert a new one
			if($pass = Paypal::updateStatus($postData['txn_id'], "Cancelled"))
			{
				// Check if we need to remove the credits that were purchased
				if($getTransaction['status'] == "Completed")
				{
					// Identify the User
					$uniID = Paypal::getCustomData($postData['custom']);
					
					// Remove credits from the user
					
				}
			}
			
			return Database::endTransaction($pass);
		}
		
		// Since this transaction doesn't exist, "cancel" doesn't matter. End here.
		return false;
	}
	
	
	
	
	
	
	
/****** Insert a Transaction ******/
	private static function insertTransaction
	(
		$postData		// <array> The Post Data of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Paypal::insertTransaction($postData);
	{
		$customData = self::getCustomData($postData['custom']);
		
		// Get the User's Auth ID
		if(isset($customData['uni_id']))
		{
			$userData = User::get($customData['uni_id'], "uni_id, auth_id");
		}
		
		if(!isset($userData['uni_id']))
		{
			$userData['auth_id'] = 0;
			$userData['uni_id'] = 0;
		}
		
		return Database::query("INSERT INTO paypal_transactions (txn_id, auth_id, uni_id, amount_paid, status, email, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?)", array($postData['txn_id'], $userData['auth_id'], $userData['uni_id'], $postData['mc_gross'], $postData['payment_status'], $postData['payer_email'], time()));
	}
	
	
/****** Update a Transaction Status ******/
	private static function updateStatus
	(
		$txnID		// <int> The ID of the transaction.
	,	$status		// <str> The status to set the transaction to.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Paypal::updateStatus($txnID, $status);
	{
		return Database::query("UPDATE paypal_transactions SET status=? WHERE txn_id=? LIMIT 1", array($status, $txnID));
	}
	
	
/****** Credit the User's Account ******/
	private static function creditUser
	(
		$postData			// <array> The post data of the paypal purchase.
	,	$txnData = array()	// <array> Data that was already sent with an earlier transaction.
	)						// RETURNS <bool> TRUE if user was credited, FALSE if not.
	
	// Paypal::creditUser($postData, [$txnID]);
	{
		// Prepare Values
		$authID = 0;
		$uniID = 0;
		$amount = 0.00;
		
		// Identify the User
		if(isset($txnData['uni_id']) and isset($txnData['auth_id']) and isset($txnData['amount_paid']))
		{
			$authID = $txnData['auth_id'] + 0;
			$uniID = $txnData['uni_id'] + 0;
			$amount = $txnData['amount_paid'] + 0.00;
		}
		else
		{
			$customData = Paypal::getCustomData($postData['custom']);
			
			// Get UniID and AuthID
			if(isset($customData['uni_id']))
			{
				$uniID = $customData['uni_id'] + 0;
				
				$userData = User::get($uniID, "auth_id");
				
				$authID = $userData['auth_id'] + 0;
			}
			
			$amount = $postData['mc_gross'] + 0.00;
		}
		
		if($uniID !== 0 and $authID !== 0)
		{
			// Give credits to the User
			if(MyCredits::add($authID, $uniID, $amount, "Purchased Credits", $errorStr))
			{
				// Confirm that the user received the credits
				return Database::query("UPDATE paypal_transactions SET user_received=? WHERE txn_id=? LIMIT 1", array(1, $postData['txn_id']));
			}
		}
		
		// The user wasn't included here. Send appropriate alerts, since they didn't get their credits.
		return false;
	}
}

