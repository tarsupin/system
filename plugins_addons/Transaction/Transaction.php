<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Transaction {

/****** Transaction Class ******
* This class allows users to gift or trade (exchange) virtual goods and services. It does this by storing the functions
* of each virtual good (which should be provided by the app) in a list that, upon all conditions being met, will all
* simultaneously be exchanged between all users that agreed to partake in the transaction.
* 
* For example, if Joe wants to trade 10 gold for Bob's item #100, the transaction could simultaneously run the plugins
* Gold::send("Joe", "Bob", 10) and Item::send("Bob", "Joe", 100) when the "approve" option has been selected by both
* parties. This way, it's possible to make exchanges with any functionality provided by the site. It could even be used
* to exchange gold for posting a public status message or friending someone (despite how odd that would be).
* 
****** Example of using the class ******



****** Methods Available ******
* $transID = Transaction::create($title, [$duration])	// Creates a new transaction, returns ID
* Transaction::delete($id)								// Deletes a transaction
* 
* $users = Transaction::getUsers($id)			// Retrieves an array of user ID's in the transaction
* Transaction::addUser($id, $user)				// Adds a user to the transaction
* Transaction::removeUser($id, $user)			// Remove a user from the transaction
* 
* Transaction::approve($id, $user)				// Set user to approve of the transaction
* Transaction::disapprove($id, [$user])			// Set all user's to disapprove of transaction (or a specific user)
* 
* $entry = Transaction::getEntry($entryID, [$columns])	// Retrieves sql data from transaction entries
* 
* Transaction::addEntry($user, $transactionID, $class, $method, $parameters, $display = array())
* Transaction::removeEntry($entryID);					// Removes the entry from the transaction
* 
* + Transaction::run($transactionID)				// Runs the transaction
* + Transaction::prune()							// Eliminates any outdated transactions
*/

/****** Generate `Transaction` SQL ******/
	public static function sql()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			
			`date_created`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions_users`
		(
			`user_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`transaction_id`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			`has_agreed`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`user_id`, `transaction_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 PARTITION BY KEY(user_id) PARTITIONS 3;
		");
		
		/****** `transactions_entries` Table Clarifications ******
		`user_id`				// This is only used to indicate which user is providing this transaction entry.
		
		`class`					// The class (or plugin) to use for this transaction.
		`process_method`		// The method that will be used for this transaction.
		`process_parameters`	// A JSON encoded array to use as the parameters for this transaction.
		
		`display`				// A JSON encoded array to pass information on how to display the transaction.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `transactions_entries`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`transaction_id`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			`user_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`class`					varchar(24)					NOT NULL	DEFAULT '',
			`process_method`		varchar(32)					NOT NULL	DEFAULT '',
			`process_parameters`	text						NOT NULL	DEFAULT '',
			
			`display`				text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			INDEX (`transaction_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
		
		// Display SQL
		DatabaseAdmin::showTable("transactions");
		DatabaseAdmin::showTable("transactions_users");
		DatabaseAdmin::showTable("transactions_entries");
	}
	
	
/****** Create Transaction ******/
	public static function create
	(
		$title		// <str> The title of the transaction.
	)				// RETURNS <int> The ID of the transaction created, or 0 on failure.
	
	// $transactionID = Transaction::create("Joe's Cool Transaction")
	{
		if(Database::query("INSERT INTO `transactions` (`title`) VALUES (?)", array($title)))
		{
			return (int) Database::$lastID;
		}
		
		return 0;
	}
	
	
/****** Delete Transaction ******/
	public static function delete
	(
		$transactionID		// <int> The ID of the transaction.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::delete(153)
	{
		Database::startTransaction();
		
		// Delete all of the entries
		Database::query("DELETE FROM transactions_entries WHERE transaction_id=?", array($transactionID));
		
		// Delete all of the users
		Database::query("DELETE FROM `transactions_users` WHERE transaction_id=? VALUES (?)", array($transactionID));
		
		// Delete the transaction
		Database::query("DELETE FROM transactions WHERE id=?", array($id));
		
		Database::endTransaction();
		
		return true;
	}
	
	
/****** Get Users from a Transaction ******/
	public static function getUsers
	(
		$transactionID	// <int> The ID of the transaction.
	)					// RETURNS <int:int> List of user ID's.
	
	// $users = Transaction::getUsers(352)	// Returns an array of user ID's from Transaction #352
	{
		$users = array();
		$fetchUsers = Database::selectMultiple("SELECT user_id FROM transactions_users WHERE transaction_id=? LIMIT 1", array($transactionID));
		
		foreach($fetchUsers as $user)
		{
			$users[] = (int) $user['user_id'];
		}
		
		return $users;
	}
	
	
/****** Add User to a Transaction ******/
	public static function addUser
	(
		$transactionID	// <int> The ID of the transaction.
	,	$user			// <int> or <str> The ID, username, or email of the user to add to the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::addUser(352, "Joe")	// Adds Joe to Transaction #352
	{
		$userID = User::toID($user);
		
		if(!$userID) { return false; }
		
		// Invalidate the transaction approvals (since circumstances changed)
		Transaction::disapprove($transactionID);
		
		// Add the user to the transaction
		return Database::query("INSERT INTO `transactions_users` (`user_id`, `transaction_id`) VALUES (?, ?)", array($userID, $transactionID));
	}
	
	
/****** Remove User from a Transaction ******/
	public static function removeUser
	(
		$transactionID	// <int> The ID of the transaction.
	,	$user			// <int> or <str> The ID, username, or email of the user to add to the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::removeUser(352, "Joe")	// Removes Joe from Transaction #352
	{
		$userID = User::toID($user);
		
		if(!$userID) { return false; }
		
		// Invalidate the transaction approvals (since circumstances changed)
		Transaction::disapprove($transactionID);
		
		// Remove the User
		return Database::query("DELETE FROM `transactions_users` WHERE transaction_id=? AND user_id=? VALUES (?, ?)", array($transactionID, $userID));
	}
	
	
/****** Invalidate the Transaction Approvals ******/
	public static function approve
	(
		$transactionID	// <int> The ID of the transaction.
	,	$user			// <int> or <str> The ID, username, or email of the user to approve the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::approve(352, "Joe")		// Joe approves the transaction
	{
		// Retrieve the user that is approving the transaction
		$userID = User::toID($user);
		
		if(!$userID) { return false; }
		
		// Set the user's transaction approval to "approve"
		Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? AND user_id=? LIMIT 1", array(0, $transactionID, $userID));
		
		// Check if everyone in the transaction has approved. If so, finalize the transaction.
		
		return false;
	}
	
	
/****** Invalidate the Transaction Approvals ******/
	public static function disapprove
	(
		$transactionID	// <int> The ID of the transaction.
	,	$user = ""		// <int> or <str> The ID, username, or email of the user to disapprove of the transaction.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::disapprove(352)			// Sets disapproval to the entire transaction (all users)
	// Transaction::disapprove(352, "Joe")	// Joe sets his approval to "disapprove"
	{
		// If you're not targeting a user, set all users to disapprove
		if($user == "")
		{
			// Set everyone's transaction approval to "disapprove"
			return Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? LIMIT 1", array(0, $transactionID));
		}
		
		// Retrieve the user that is disapproving of the transaction
		$userID = User::toID($user);
		
		if(!$userID) { return false; }
		
		// Set the user's transaction approval to "disapprove"
		return Database::query("UPDATE `transactions_users` SET has_agreed=? WHERE transaction_id=? AND user_id=? LIMIT 1", array(0, $transactionID, $userID));
	}
	
	
/****** Add Entry to a Transaction ******/
	public static function getEntry
	(
		$entryID		// <int> The ID of the transaction entry.
	,	$sqlData = "*"	// <str> The columns that you'd like to retrieve, comma delimited.
	)					// RETURNS <str:mixed> of the entity's data.
	
	// $entry = Transaction::getEntry(513, "transaction_id");		// Get sql data from transaction entry #513
	{
		return Database::selectOne("SELECT " . Sanitize::variable($sqlData, " *`,") . " FROM transactions_entries WHERE id=? LIMIT 1", array($entryID));
	}
	
	
/****** Add Entry to a Transaction ******/
	public static function addEntry
	(
		$user				// <int> or <str> The ID, username, or email of the user to add to the transaction.
	,	$transactionID		// <int> The ID of the transaction.
	,	$class				// <str> The class that is being used for this transaction entry.
	,	$method				// <str> The method used to exchange the entry.
	,	$parameters			// <array> An array of parameters to use for the entry.
	,	$display = array()	// <array> Stores whatever parameters you want to display the transaction.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Add an entry (to Transaction #352) that indicates that Joe will send 10 gold to Bob
	// Transaction::addEntry("Joe", 352, "Gold", "send", array("Joe", "Bob", 10), array("image" => "gold.png", "caption" => "10 Gold", "description" => "Joe will send 10 Gold to Bob"))
	{
		$userID = User::toID($user);
		
		if(!$userID) { return false; }
		
		// Make sure the function can process
		if(!method_exists($class, $method)) { return false; }
		
		$parameters = json_encode($parameters);
		$display = json_encode($display);
		
		// Invalidate the transaction approvals (since circumstances changed)
		Transaction::disapprove($transactionID);
		
		// Insert the new transaction entry
		return Database::query("INSERT INTO `transactions_entries` (`transaction_id`, `user_id`, `class`, `process_method`, `process_parameters`, `display`) VALUES (?, ?, ?, ?, ?, ?)", array($transactionID, $userID, $class, $method, $parameters, $display));
	}
	
	
/****** Remove Entry from a Transaction ******/
	public static function removeEntry
	(
		$entryID		// <int> The ID of the transaction entry.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Transaction::removeEntry(388);	// Removes transaction entry #388
	{
		// Get Entry Data
		$entry = self::getEntry($entryID);
		
		if(!$entry) { return false; }
		
		// Invalidate the transaction approvals (since circumstances changed)
		Transaction::disapprove($entry['transaction_id']);
		
		// Remove the Entry
		return Database::query("DELETE FROM transactions_entries WHERE id=? LIMIT 1", array($entryID));
	}
}

