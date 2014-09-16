<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the DBTransfer Class ------
----------------------------------------

This class allows you to transfer rows data between tables that are identical in table structure. This is not designed
(or optimized) for cloning large sets of data. It's primary function is to backup data in multiple locations, or to 
prune data from one table and store it in another.

For example, if your forum only wants to load posts that are less than 90 days old, you can prune the old posts and
back them up into another table for safekeeping.

This system can also be used for vertical partitioning, which is used to increase performance. For example, every year
you could run an algorithm that saved the next set of data into tables like this:

	`posts`				// the current table that is actively used
	`posts_2013`		// last year's table
	`posts_2012`
	`posts_2011`
	`posts_2010`	
	...

Every year, the posts would be collect into a new logging table. They could still be retrieved, but wouldn't take up so
much room in the active table.
	
-----------------------------------------------------------
------ How to transfer database data with this class ------
-----------------------------------------------------------

This class uses SQL commands to identify which tables you would like to transfer. The method is simple to use:

	DBTransfer::move($sourceTable, $destinationTable, $sqlWhere, $sqlInput);
	
For example, if you wanted to move all old posts to a backup table, it might look like this:

	$sourceTable = "posts";
	$destinationTable = "posts_old";
	$sqlWhere = "visible = ? and date_posted < ?";
	$sqlInput = array(1, time() - (3600 * 24 * 90);
	$sqlLimit = 1000;
	
	DBTransfer::move($sourceTable, $destinationTable, $sqlWhere, $sqlInput, $sqlLimit);

This code will copy all posts that are 90+ days old into the posts_old table. It will even create the posts_old table
when run if it doesn't already exists.


---------------------------
------ COPY vs. MOVE ------
---------------------------

If you want to backup data (clone it) rather than transfer it, you can use the ::copy method instead of the ::move
method. They are functionally identical except that the ::copy method won't delete the original data from the source
table.


-------------------------------
------ Methods Available ------
-------------------------------

DBTransfer::copy($sourceTable, $destinationTable);							// copies the entire table
DBTransfer::copy($sourceTable, $destinationTable, $sqlWhere, $sqlInput);	// copies specific rows to the destination

DBTransfer::move($sourceTable, $destinationTable);							// moves the entire table
DBTransfer::move($sourceTable, $destinationTable, $sqlWhere, $sqlInput);	// moves specific rows to the destination

*/

abstract class DBTransfer {
	
	
/****** Transfer data from one table to another ******/
	public static function copy
	(
		$sourceTable		// <str> The original table that you're backing up.
	,	$destinationTable	// <str> The table name that you're backing up to.
	,	$sqlWhere = ""		// <str> The WHERE clause entry for the SQL.
	,	$sqlArray = array()	// <int:mixed> The SQL array to populate the WHERE clause.
	,	$limit = 1000		// <int> The number of rows to limit (to avoid overwhelm). 0 is unlimited.
	,	$move = false		// <bool> Set to TRUE if you want to move the data rather than copy it.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// DBTransfer::copy($sourceTable, $destinationTable, $sqlWhere, $sqlArray, [$limit], [$move]);
	{
		// Protect Tables
		if(!isSanitized::variable($destinationTable) or !isSanitized::variable($sourceTable))
		{
			return false;
		}
		
		// Make sure the backup table exists
		Database::exec("CREATE TABLE IF NOT EXISTS " . $destinationTable . " LIKE " . $sourceTable);
		
		// Begin the DBTransfer
		Database::startTransaction();
		
		// Insert Rows into DBTransfer Table
		Database::query("INSERT INTO " . $destinationTable . " SELECT * FROM " . $sourceTable . ($sqlWhere != "" ? " WHERE " . Sanitize::variable($sqlWhere, " ,`!=<>?()") : "") . ($limit ? ' LIMIT ' . ((int) $limit) : ''), $sqlArray);
		
		$newCount = Database::$rowsAffected;
		
		if($move === true)
		{
			// Delete Rows from Original Table (if applicable)
			Database::query("DELETE FROM " . $sourceTable . ($sqlWhere != "" ? " WHERE " . Sanitize::variable($sqlWhere, " ,`!=<>?()") : ""), $sqlArray);
			
			// If the number of inserts matches the number of deletions, commit the transaction
			return Database::endTransaction(($newCount == Database::$rowsAffected));
		}
		
		return Database::endTransaction();
	}
	
	
/****** Transfer data from one table to another ******/
	public static function move
	(
		$sourceTable		// <str> The original table that you're backing up.
	,	$destinationTable	// <str> The table name that you're backing up to.
	,	$sqlWhere = ""		// <str> The WHERE clause entry for the SQL.
	,	$sqlArray = array()	// <int:mixed> The SQL array to populate the WHERE clause.
	,	$limit = 1000		// <int> The number of rows to limit (to avoid overwhelm). 0 is unlimited.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// DBTransfer::move($sourceTable, $destinationTable, $sqlWhere, $sqlArray, [$limit]);
	{
		return DBTransfer::copy($sourceTable, $destinationTable, $sqlWhere, $sqlArray, $limit, true);
	}
	
}
