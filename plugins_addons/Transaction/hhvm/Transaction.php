<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------
------ Transaction Class ------
-------------------------------

This plugin allows users to gift or trade (exchange) virtual goods and services. It does this by storing the functions of each virtual good (which should be provided by the site plugins) in a list that, upon all conditions being met, will all simultaneously be exchanged between all users that agreed to partake in the transaction.

For example, if Joe wants to trade 10 gold for Bob's item #100, the transaction could simultaneously run the plugins Gold::send_doTransaction("Joe", "Bob", 10) and Item::send_doTransaction("Bob", "Joe", 100) when the "approve" option has been selected by both parties. This way, it's possible to make exchanges with any functionality provided by the site. It could even be used to exchange gold for posting a public status message or friending someone (despite how odd that would be).

The plugin will only allow transactions with methods that end in "_doTransaction" for security reasons.


---------------------------------------------------
------ Example of how the local plugin looks ------
---------------------------------------------------

This is an exmample of the plugin that you'll need to create in your site's plugins:
	
	// Save the plugin at this location
	/{APP_PATH}/plugins/AppTrade.php
	
You must have the methods end in "_doTransaction" or they will not work (this is for security reasons).


abstract class AppTrade {
	
	
/****** Exchange gold between two users ******
	public static function sendGold_doTransaction
	(
		int $senderID		// <int> The UniID sending the gold.
	,	int $recipientID	// <int> The UniID receiving the gold.
	,	int $goldAmount		// <int> The amount of gold being sent.
	): bool					// RETURNS <bool> TRUE if the gold was sent, FALSE if it failed.
	
	// AppTrade::sendGold_doTransaction($senderID, $recipientID, $goldAmount);
	{
		// Do the gold transaction here
	}
	
	
/****** Exchange an item between two users ******
	public static function sendItem_doTransaction
	(
		int $senderID		// <int> The UniID sending the gold.
	,	int $recipientID	// <int> The UniID receiving the gold.
	,	int $itemID			// <int> The ID of the item that the sender is exchanging.
	): bool					// RETURNS <bool> TRUE if the item was sent, FALSE if it failed.
	
	// AppTrade::sendItem_doTransaction($senderID, $recipientID, $itemID);
	{
		// Do the item transaction here
	}
	
}

-------------------------------------------
------ Example of using Transactions ------
-------------------------------------------

// Prepare Values
$joeUniID = 1;
$bobUniID = 14;
$goldAmount = 100;
$ninjaSwordID = 155;

// Create a transaction
$transactionID = Transaction::create("Joe's Transaction");

// Add two users to the transaction
Transaction::addUser($transactionID, $joeUniID);
Transaction::addUser($transactionID, $bobUniID);

// Joe offers 100 gold to Bob
$pass = Transaction::addEntry($joeUniID, $transactionID, "AppTrade", "sendGold", array($joeUniID, $bobUniID, $goldAmount), array("image" => "gold.png", "caption" => "10 Gold", "description" => "Joe will send 10 gold to Bob."));

if(!$pass) { echo "The transaction entry could not be added successfully."; }

// Bob offers an item to Joe
$pass = Transaction::addEntry($bobUniID, $transactionID, "AppTrade", "sendItem", array($bobUniID, $joeUniID, $ninjaSwordID), array("image" => "item.png", "caption" => "Ninja Sword", "description" => "Bob sends Joe a Ninja Sword."));

if(!$pass) { echo "The transaction entry could not be added successfully."; }

// Joe approves the transaction
Transaction::approve($transactionID, $joeUniID);

// Bob approves the transaction
// Since all participants have now approved the transaction, this also processes the transaction
Transaction::approve($transactionID, $bobUniID);

// The transaction will call the following:
//		Gold::send_doTransaction($joeUniID, $bobUniID, 100);
//		Item::send_doTransaction($bobUniID, $joeUniID, "ninja_sword");

// If the transaction cannot process ALL of the entries provided, none of them will pass.


-------------------------------
------ Methods Available ------
-------------------------------

$transactionID = Transaction::create($title, [$duration])		// Creates a new transaction, returns ID
Transaction::delete($transactionID)								// Deletes a transaction

$users = Transaction::getUsers($transactionID)				// Retrieves an array of user ID's in the transaction
Transaction::addUser($transactionID, $uniID)				// Adds a user to the transaction
Transaction::removeUser($transactionID, $uniID)				// Remove a user from the transaction

Transaction::approve($transactionID, $uniID)				// Set user to approve of the transaction
Transaction::disapprove($transactionID, [$uniID])			// Set all user's to disapprove of transaction (or a specific user)

$entry = Transaction::getEntry($entryID, [$columns])		// Retrieves sql data from transaction entries

Transaction::addEntry($uniID, $transactionID, $class, $method, $parameters, $display = array())
Transaction::removeEntry($entryID)							// Removes the entry from the transaction

Transaction::process($transactionID)						// Processes the transaction
+ Transaction::prune()										// Eliminates any outdated transactions

*/

abstract class Transaction {
	
	
/****** Create Transaction ******/
	public static function create
	(
		string $title			// <str> The title of the transaction.
	,	int $expires = 0	// <int> Number of seconds before this transaction will expire (30 day default)
	): int					// RETURNS <int> The ID of the transaction created, or 0 on failure.
	
	// $transactionID = Transaction::create($title, [$expires]);
	{
		// Prepare Values
		$expires = $expires == 0 ? 86400 * 30 : $expires;
		$dateEnd = time() + $expires;
		
		// Insert the Transaction
		if(Database::query("INSERT INTO `transactions` (`title`, `date_created`, `date_end`) VALUES (?, ?, ?)", array($title, time(), $dateEnd)))
		{
			return (int) Database::$lastID;
		}
		
		return 0;
	}
	
	
/****** Delete Transaction ******/
	public static function delete
	(
		int $transactionID		// <int> The ID of the transaction.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::delete($transactionID);
	{
		Database::startTransaction();
		
		// Delete all of the entries
		if($pass = Database::query("DELETE FROM transactions_entries WHERE transaction_id=?", array($transactionID)))
		{
			// Delete all of the users
			if($pass = Database::query("DELETE FROM `transactions_users` WHERE transaction_id=?", array($transactionID)))
			{
				// Delete the transaction
				$pass = Database::query("DELETE FROM transactions WHERE id=?", array($transactionID));
			}
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Get the list of users in a Transaction ******/
	public static function getUsers
	(
		int $transactionID	// <int> The ID of the transaction.
	): array <int, int>					// RETURNS <int:int> List of user ID's.
	
	// $users = Transaction::getUsers($transactionID);
	{
		$users = array();
		$fetchUsers = Database::selectMultiple("SELECT uni_id FROM transactions_users WHERE transaction_id=?", array($transactionID));
		
		foreach($fetchUsers as $user)
		{
			$users[] = (int) $user['uni_id'];
		}
		
		return $users;
	}
	
	
/****** Add User to a Transaction ******/
	public static function addUser
	(
		int $transactionID	// <int> The ID of the transaction.
	,	int $uniID			// <int> The UniID of the user to add to the transaction.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::addUser($transactionID, $uniID);
	{
		// Invalidate the transaction approvals (since circumstances changed)
		// In other words, nobody can agree to the transaction without knowledge of a new user joining
		Transaction::disapprove($transactionID);
		
		// Add the user to the transaction
		return Database::query("INSERT INTO `transactions_users` (`uni_id`, `transaction_id`) VALUES (?, ?)", array($uniID, $transactionID));
	}
	
	
/****** Remove User from a Transaction ******/
	public static function removeUser
	(
		int $transactionID	// <int> The ID of the transaction.
	,	int $uniID			// <int> The UniID of the user to add to the transaction.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::removeUser($transactionID, $uniID);
	{
		// Invalidate the transaction approvals (since circumstances changed)
		// In other words, nobody can agree to the transaction without knowledge that one of the users dropped out
		Transaction::disapprove($transactionID);
		
		// Remove the User
		return Database::query("DELETE FROM `transactions_users` WHERE transaction_id=? AND uni_id=? VALUES (?, ?)", array($transactionID, $uniID));
	}
	
	
/****** User approves a Transaction ******/
	public static function approve
	(
		int $transactionID	// <int> The ID of the transaction.
	,	int $uniID			// <int> The UniID of the user to approve the transaction.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::approve($transactionID, $uniID);
	{
		// Set the user's transaction approval to "approve"
		Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? AND uni_id=? LIMIT 1", array(1, $transactionID, $uniID));
		
		// Check if everyone in the transaction has approved. If so, finalize the transaction.
		if($checkAgree = Database::selectMultiple("SELECT has_agreed FROM transactions_users WHERE transaction_id=?", array($transactionID)))
		{
			foreach($checkAgree as $agree)
			{
				if(!$agree['has_agreed'])
				{
					return false;
				}
			}
			
			// Process the Transaction
			return self::process($transactionID);
		}
		
		return false;
	}
	
	
/****** Invalidate the Transaction Approvals ******/
	public static function disapprove
	(
		int $transactionID	// <int> The ID of the transaction.
	,	int $uniID = 0		// <int> The UniID of the user to disapprove of the transaction.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::disapprove($transactionID)			// Sets disapproval to the entire transaction (all users).
	// Transaction::disapprove($transactionID, $uniID)	// Sets disapproval for a single user in the transaction.
	{
		// If you're not targeting a user, set all users to disapprove
		if($uniID == 0)
		{
			// Set everyone's transaction approval to "disapprove"
			return Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? LIMIT 1", array(0, $transactionID));
		}
		
		// Set the user's transaction approval to "disapprove"
		return Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? AND uni_id=? LIMIT 1", array(0, $transactionID, $uniID));
	}
	
	
/****** Process a valid Transaction ******/
	public static function process
	(
		int $transactionID	// <int> The ID of the transaction.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::process($transactionID);
	{		
		// Get Actions
		Database::startTransaction();
		$actions = Database::selectMultiple("SELECT class, process_method, process_parameters, display FROM transactions_entries WHERE transaction_id=?", array($transactionID));
		foreach($actions as $action)
		{
			// Prepare Values
			$method = str_replace("_doTransaction", "", $action['process_method']) . "_doTransaction";
			$class = $action['class'];
			
			// Make sure the function can process
			if(!method_exists($class, $method))
			{
				// Undo process
				Database::endTransaction(false);
				return false;
			}

			// Serialize Data
			$parameters = json_decode($action['process_parameters']);
			$display = json_decode($action['display']);
			
			// Execute Method
			if(!call_user_func_array(array($class, $method), $parameters))
			{
				// Undo process if something went wrong
				Database::endTransaction(false);
				return false;
			}
		}
		Database::endTransaction();
		
		return true;
	}
	
	
/****** Get a list of Transaction entries ******/
	public static function getEntry
	(
		int $entryID		// <int> The ID of the transaction entry.
	,	string $sqlData = "*"	// <str> The columns that you'd like to retrieve, comma delimited.
	): array <str, mixed>					// RETURNS <str:mixed> of the entity's data.
	
	// $entry = Transaction::getEntry(513, "transaction_id");		// Get sql data from transaction entry #513
	{
		return Database::selectOne("SELECT " . Sanitize::variable($sqlData, " *`,") . " FROM transactions_entries WHERE id=? LIMIT 1", array($entryID));
	}
	
	
/****** Add Entry to a Transaction ******/
	public static function addEntry
	(
		int $uniID				// <int> The UniID of the user to add to the transaction.
	,	int $transactionID		// <int> The ID of the transaction.
	,	string $class				// <str> The class that is being used for this transaction entry.
	,	string $method				// <str> The method used to exchange the entry.
	,	array $parameters			// <array> An array of parameters to use for the entry.
	,	array $display = array()	// <array> Stores whatever parameters you want to display the transaction.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::addEntry($uniID, $transactionID, $class, $method, $parameters, $displayParameters);
	{
		// Prepare Values
		$method = str_replace("_doTransaction", "", $method) . "_doTransaction";
		
		// Make sure the function can process
		if(!method_exists($class, $method))
		{
			return false;
		}
		
		// Serialize Data
		$parameters = json_encode($parameters);
		$display = json_encode($display);
		
		// Invalidate the transaction approvals (since circumstances changed)
		// In other words, something was added to the transaction, so everyone needs to re-agree.
		Transaction::disapprove($transactionID);
		
		// Insert the new transaction entry
		return Database::query("INSERT INTO `transactions_entries` (`transaction_id`, `uni_id`, `class`, `process_method`, `process_parameters`, `display`) VALUES (?, ?, ?, ?, ?, ?)", array($transactionID, $uniID, $class, $method, $parameters, $display));
	}
	
	
/****** Remove Entry from a Transaction ******/
	public static function removeEntry
	(
		int $entryID		// <int> The ID of the transaction entry.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::removeEntry($entryID);
	{
		// Get Entry Data
		if(!$entry = self::getEntry($entryID))
		{
			return false;
		}
		
		// Invalidate the transaction approvals (since circumstances changed)
		Transaction::disapprove($entry['transaction_id']);
		
		// Remove the Entry
		return Database::query("DELETE FROM transactions_entries WHERE id=? LIMIT 1", array($entryID));
	}
}
