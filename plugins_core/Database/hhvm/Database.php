<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the Database Plugin ------
---------------------------------------

This plugin provides an SQL wrapper that safely handles connections and queries with the database. For database commands that affect the structure of tables, refer to the "DatabaseAdmin" class, which requires root access.

This plugin does NOT try to oversimplify SQL with wrappers for each individual SQL function, such as ->table($table)->columns($columns)->run(). Vanilla SQL is already simple and straightforward, and rebuilding queries with additional functions requires more function calls that add an unnecessary burden. Therefore, we've opted against that method and instead use direct SQL.

There are a five primary uses of the Database class:

	1. Retrieve multiple rows of data from the database.
	2. Retrieve a single row of data from the database (such as all of a user's information).
	3. Retrieve a single item from the database (such as a username).
	4. Updating or inserting data to the database.
	5. Running database transactions.
	
	
----------------------------------------------------
------ How to retrieve data from the database ------
----------------------------------------------------

There are three methods that we use to gather data from the database:

	Database::selectOne() returns a single array of data with a <str:mixed> structure. It is most commonly used to return a single row, such as to pull a user's row from the `users` table. It is a proper practice to add "LIMIT 1" to the end of all queries done with this method.
	
	Database::selectMultiple() returns an array of arrays, with inner arrays being rows of data from the database. This is used to retrieve many rows, such as when trying to retrieve a list of friends. It would be common practie to use "LIMIT 20" (or something similar) when using this method.
	
	Database::selectValue() returns a single entry from the database, and it will ONLY return the first value listed in the query. For example, `SELECT handle FROM users_handle WHERE handle="JoeSmith" LIMIT 1` would return "JoeSmith".


All three of these methods have exactly two parameters: the SQL query, and the input array.
	
	// An example of gathering the first twenty addresses in Ohio
	$addresses = Database::selectMultiple("SELECT * FROM addresses WHERE country=? AND state=? LIMIT 20", array("US", "Ohio"));
	
	// An example of getting a username
	$username = Database::selectValue("SELECT username FROM users WHERE uni_id=? LIMIT 1", array(Me::$id));
	
	// An example of getting a single user's data
	$userData = Database::selectOne("SELECT * FROM users WHERE uni_id=? LIMIT 1", array(Me::$id));
	
If you are familiar with PDO, then this will look natural to you. Otherwise, the basics of it are pretty simple:

The first parameter is standard SQL. However, question marks are used in place of the input. The input for the query is stored in the second parameter (the input array). This allows the method to distinguish instances of user input, making it possible sanitize the input before running it through the database. This helps to protect the database from SQL injection attacks.

Though you aren't REQUIRED to use the input array, it's much safer to do so. In some cases, you'll have to put user input directly into the SQL, such as if you want to determine which table to call based on what the user chose. If you do this, you must be very certain that your data is properly sanitized.

To use the input array, just put a ? where your input would normally be, and then add the input to the input array. The order of the input must match the order of the ?'s that are meant to correspond with them.


---------------------------------------------------------
------ Inserting and updating rows in the database ------
---------------------------------------------------------

The Database::query() and Database::exec() methods can be used to run all other non-select queries on the database.

	// An example of updating a row
	Database::query("UPDATE users SET display_name=? WHERE uni_id=? LIMIT 1", array("New Display Name", Me::$id));
	
	// An example of inserting a new row
	Database::query("INSERT INTO config (name, number) VALUES (?, ?)", array("last_update", time()));
	
	
To get the last ID that was inserted after an "INSERT" query, use Database::$lastID; Note that this only works on
tables that have an auto-incrementing primary ID value.

	// Example of getting the ID of the last inserted value
	Database::query("INSERT INTO table (column, second_column) VALUES (?, ?)", array("Value1", "Value2"));
	
	$lastID = Database::$lastID;
	
	
-----------------------------------
------ Handling Transactions ------
-----------------------------------

Transactions allow you to maintain the integrity of the database by ensuring that every query that is part of the transaction must succeed. If any part of the query fails, all of them can be rolled back. If everything worked as desired, you can commit the transaction normally.

	// An example of trading items between two owners
	Database::startTransaction();
	
	if($pass = Database::query("UPDATE trade SET owner_id=? WHERE item_id=? LIMIT 1", array($ownerA, $itemB)))
	{
		$pass = Database::query("UPDATE trade SET owner_id=? WHERE item_id=? LIMIT 1", array($ownerB, $itemA));
	}
	
	Database::endTransaction($pass);
	
The Database::startTransaction() method must always be closed with a Database::endTransaction() method, even if it fails. You can wrap several transactions inside of each other, which helps maintain integrity between functions that use transactions.

The Database::endTransaction($success) method has a single parameter ($success) that indicates whether the transaction SUCCEEDED or FAILED. If the method succeeds, it will commit all of the queries that were made. If it fails, all of the queries will be "rolled back" and not succeed.


------------------------------------------------
------ Access Advanced Database Functions ------
------------------------------------------------

Some functions will not be accessible unless you are loading your SQL with root. In general, it is not advisable to run SQL as root unless you need to. It's faster to run as a user with fewer privileges, and also more secure. There's less of a chance that someone can do something dangerous (not that they can't with a regular user).

To initiate a connection with your root user, run Database::initRoot(). This will activate the root user for your database, as long as your configurations are set properly in the /global-config.php file.


-------------------------------
------ Methods Available ------
-------------------------------

Database::$lastID								// Returns the last insert ID.

Database::initRoot();							// Reinitializes the database with root connection.

Database::selectOne($query, $inputArray)		// Returns a single row as an array.
Database::selectValue($query, $inputArray)		// Returns a single column as an array.
Database::selectMultiple($query, $inputArray)	// Returns multiple rows as an array of arrays.

Database::query($query, $inputArray)			// Runs a standard query on the database.
Database::exec($query)							// Runs a static query (no preparation - must be trusted).

Database::startTransaction()					// Starts transaction: validates multiple changes at once, much faster
Database::endTransaction($commit = true)		// Confirms a transaction and commits it.

// Prepare a list of multiple SQL filters (for the input values)
list($sqlWhere, $sqlArray) = Database::sqlFilters(array($column => array($values), ...);

Database::showQuery($query, $inputArray)		// Show what the query would look like.

*/

abstract class Database {
	
	
/****** Class Variables ******/
	public static mixed $database = null;			// <mixed> The database resource
	public static string $databaseName = "";		// <str> The name of the database
	public static int $rowsAffected = 0;		// <int> The number of rows affected by an UPDATE or INSERT query
	public static int $lastID = 0;				// <int> The last ID sent by an INSERT query
	
	public static int $inTransaction = 0;		// <int> The depth of transaction that is currently being run
	public static bool $rootActive = false;		// <bool> TRUE if the admin DB user is being used
	
	
/****** Initialize the Database ******/
	public static function initialize
	(
		string $databaseName					// <str> The name of the database as stored in SQL
	,	string $databaseUser					// <str> The user that you're logging into the database with
	,	string $databasePassword				// <str> The password that you're using to log into the database with
	,	string $databaseHost = '127.0.0.1'		// <str> The host name that you're connecting to
	,	string $databaseType = 'mysql'			// <str> The type of database you're connecting to
	): bool									// RETURNS <bool> TRUE on success, FALSE otherwise
	
	// Database::initialize($name, $user, $pass, $host = "127.0.0.1", $type = "mysql");
	{
		try
		{
			/*
				http://stackoverflow.com/questions/20079320/php-pdo-mysql-returns-integer-columns-as-strings-on-ubuntu-but-as-integers-o
				http://stackoverflow.com/questions/1197005/how-to-get-numeric-types-from-mysql-using-pdo
				http://stackoverflow.com/questions/10113562/pdo-mysql-use-pdoattr-emulate-prepares-or-not
			*/
			
			// Prepare PDO Options
			$options = array(
				PDO::ATTR_ERRMODE			=>	PDO::ERRMODE_EXCEPTION
			,	PDO::ATTR_EMULATE_PREPARES	=> false		// Might optimize speed, but may also handle errors differently
			,	PDO::ATTR_STRINGIFY_FETCHES	=> false
			);
			
			if(defined("PDO::MYSQL_ATTR_FOUND_ROWS"))
			{
				$options[PDO::MYSQL_ATTR_FOUND_ROWS] = false;
			}
			else
			{
				$options[1002] = false;	// Note: 1002 is PDO::MYSQL_ATTR_FOUND_ROWS (fixing issue for some instances)
			}
			
			// Connect to the database
			self::$database = new PDO($databaseType . ":host=" . $databaseHost . ";dbname=" . $databaseName . ";charset=utf8", $databaseUser, $databasePassword, $options);
			
			self::$databaseName = $databaseName;
			
			return true;
		}
		catch (PDOException $e)
		{
			// TODO: Use the logging method here to track the exception.
		}
		
		return false;
	}
	
	
/****** Initialize Root Access to the Database ******/
	public static function initRoot (
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Database::initRoot();
	{
		if(self::$rootActive == true) { return true; }
		
		global $config;
		
		if(self::initialize($config['database']['name'], $config['database']['admin-user'], $config['database']['admin-pass'], $config['database']['host'], $config['database']['type']))
		{
			self::$rootActive = true;
		}
		
		return self::$rootActive;
	}
	
	
/****** Select a Row from the Database ******/
	public static function selectOne
	(
		string $query			// <str> The SQL for the selection query that you're going to run.
	,	array <int, mixed> $inputArray		// <int:mixed> The values that correspond to the PDO ?'s in the query.
	): array <str, mixed>					// RETURNS <str:mixed> an array of the row that was requested
	
	// $row = Database::selectOne("SELECT column FROM table WHERE username=? LIMIT 1", array("myUsername"));
	{
		$result = self::$database->prepare($query);
		$result->execute($inputArray);
		
		if($fetch = $result->fetch(PDO::FETCH_ASSOC))
		{
			return $fetch;
		}
		
		return array();
	}
	
	
/****** Select a Value from the Database ******/
	public static function selectValue
	(
		string $query			// <str> The SQL for the selection query that you're going to run
	,	array <int, mixed> $inputArray		// <int:mixed> The values that correspond to the PDO ?'s in the query
	): mixed					// RETURNS <mixed> the value of the first column
	
	// $value = Database::selectValue("SELECT column FROM table WHERE username=? LIMIT 1", array("myUsername"));
	{
		$result = self::$database->prepare($query);
		$result->execute($inputArray);
		
		$value = $result->fetch(PDO::FETCH_NUM);
		
		return ($value ? $value[0] : false);
	}
	
	
/****** Select Multiple Rows from the Database ******/
	public static function selectMultiple
	(
		string $query			// <str> The selection query to run (must start with "SELECT")
	,	array <int, mixed> $inputArray		// <int:mixed> The values that correspond to the ?'s in the query
	): mixed					// RETURNS <mixed> <int:[str:mixed]> array of arrays that contain each row you retrieved
	
	// $rows = Database::selectMultiple("SELECT column FROM table WHERE values >= ? ORDER BY col2 DESC", array(5));
	// foreach($rows as $row) { echo $row['column'] . "<br />"; }
	{
		$result = self::$database->prepare($query);
		$result->execute($inputArray);
		
		return $result->fetchAll(PDO::FETCH_ASSOC);
	}
	
	
/****** Query the Database ******/
# Queries the database and verifies success or failure. This can be used for inserts, deletes, creates, etc.
	public static function query
	(
		string $query			// <str> The SQL query to run
	,	array <int, mixed> $inputArray		// <int:mixed> The values that correspond to the ?'s in the query
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Database::query("DELETE FROM table WHERE values >= ?", array(5));
	{
		// Run the query
		$result = self::$database->prepare($query);
		
		if(!$result->execute($inputArray))
		{
			return false;
		}
		
		// Get Last ID that was inserted into the database (if applicable)
		self::$lastID = (int) self::$database->lastInsertId();
		
		// Retrieve the number of rows that were affected
		self::$rowsAffected = (int) $result->rowCount();
		
		return true;
	}
	
	
/****** Direct execution of SQL Query  ******/
# This method runs an SQL query directly as stated. This is not a prepared statement and therefore not protected
# against any form of user input. Make sure you sanitize properly before using this function with user input.
	public static function exec
	(
		string $query		// <str> The SQL statement to execute
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Database::exec("CREATE DATABASE myDatabase");	// requires CREATE privileges (likely need "root" user)
	{
		$result = self::$database->prepare($query);
		
		$success = $result->execute(array());
		
		self::$rowsAffected = $result->rowCount();
		
		return $success;
	}
	
	
/****** Start a Transaction ******/
	public static function startTransaction (
	): void				// RETURNS <void>
	
	// Database::startTransaction();
	{
		// Only run the transaction if you're on your first one
		self::$inTransaction += 1;
		
		if(self::$inTransaction == 1)
		{
			self::$database->beginTransaction();
		}
	}
	
	
/****** End a Transaction ******/
	public static function endTransaction
	(
		bool $commit = true		// <bool> Set to FALSE if the transaction should roll back instead of committing.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure
	
	// Database::endTransaction();			// Commit the transaction
	// Database::endTransaction(false);		// Roll back the transaction
	{
		// Reduce the active transactions by 1 (since we're ending one)
		self::$inTransaction -= 1;
		
		// Only the final transaction must be allowed to commit
		if(self::$inTransaction == 0)
		{
			if($commit === false)
			{
				self::$database->rollBack();
				return false;
			}
			
			self::$database->commit();
			return true;
		}
		
		return $commit;
	}
	
	
/****** Extract an SQL Filter with multiple columns ******/
# Returns a TUPLE that provides the "IN" WHERE statement for the filter, and the SQL ARRAY contents for it.
	public static function sqlFilters
	(
		array <str, array<int, mixed>> $sqlFilters 	// <str:[int:mixed]> The list of SQL Filters: array("column" => array($val, $val), ...)
	): array <int, mixed>					// RETURNS <int:mixed> the query string and array of the filter.
	
	// list($sqlWhere, $sqlArray) = Database::sqlFilters(array($column => array($values), ...);
	{
		// Return an empty SQL filter
		if($sqlFilters == array())
		{
			return "";
		}
		
		// Prepare Values
		$sqlWhere = "";
		$sqlArray = array();
		
		// Cycle through the SQL Filters and extract the filter data
		foreach($sqlFilters as $column => $values)
		{
			$len = count($values);
			$sqlList = "";
			
			// Make sure there are values here that need to be filtered
			if($len == 0) { continue; }
			
			$sqlWhere .= ($sqlWhere == "" ? "" : " AND ");
			
			if($len == 1)
			{
				$sqlWhere .= $column . "=?";
				$sqlArray = array_merge($sqlArray, $values);
				continue;
			}
			
			foreach($values as $value)
			{
				$sqlList .= ($sqlList == "" ? "" : ", ") . "?";
				$sqlArray[] = $value;
			}
			
			$sqlWhere .= $column . " IN (" . $sqlList . ")";
		}
		
		// Return the SQL Filter Data
		return array($sqlWhere, $sqlArray);
	}
	
	
/****** Show an SQL Query as text rather than running it (for debugging purposes) ******/
	public static function showQuery
	(
		string $query			// <str> The SQL query command to run.
	,	array <int, mixed> $inputArray		// <int:mixed> The values that correspond to the ?'s in the query.
	): string					// RETURNS <str> the SQL of the query.
	
	// Database::showQuery("SELECT * FROM users WHERE id=? AND value=? LIMIT 1", array($user_id, $value)
	{
		foreach($inputArray as $value)
		{
			$pos = strpos($query, "?");
			
			if(!is_numeric($value))
			{
				$value = '"' . $value . '"';
			}
			
			$query = substr_replace($query, $value, $pos, 1);
		}
		
		return $query;
	}
}

