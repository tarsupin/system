<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Cron {
	
	
/****** Generate `Cron` SQL ******/
	public static function sql()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `cron`
		(
			`id`					mediumint(8)	unsigned	NOT NULL	AUTO_INCREMENT,
			
			`title`					varchar(22)					NOT NULL	DEFAULT '',
			`method`				varchar(22)					NOT NULL	DEFAULT '',
			
			`run_cycle`				mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			`date_start`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`date_nextRun`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`date_nextRun`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		// Display SQL
		DatabaseAdmin::showTable("cron");
	}
	
	
/****** Get detailed list of Cron Tasks ******/
	public static function getList (
	): array				// RETURNS <array> the data for the cron task list, empty array on failure.
	
	// $cronList = Cron::getList();
	{
		if($results = Database::selectMultiple("SELECT * FROM cron ORDER BY date_nextRun", array()))
		{
			foreach($results as $key => $val)
			{
				$results[$key]['args'] = Site::getArgs("CronTask", (int) $val['id']);
			}
		}
		
		return $results;
	}
	
	
/****** Get data about a Cron Task ******/
	public static function getData
	(
		int $cronID		// <int> The ID of the cron task to retrieve.
	): array				// RETURNS <array> the data for the cron task, empty array on failure.
	
	// $cronData = Cron::getData($cronID);
	{
		if($results = Database::selectOne("SELECT * FROM cron WHERE id=? LIMIT 1", array($cronID)))
		{
			$results['args'] = Site::getArgs("CronTask", (int) $results['id']);
		}
		
		return $results;
	}
	
	
/****** Create a Cron Task ******/
	public static function create
	(
		string $title				// <str> The title of the task, useful only to humans.
		string $method				// <str> The MyTasks::{method} or Task::{method} to call when activated.
		array $args = array()		// <array> The array of arguments to pass to the cron method.
		int $run_cycle = 600	// <int> Seconds until you repeat the task (default = 10 minutes).
		int $date_start = 0		// <int> Timestamp of when to start the task. If 0, start now.
		int $date_end = 0		// <int> Timestamp of when to end the task. If 0, don't end. If < now, run once.
	): bool						// RETURNS <bool> TRUE if task was created, FALSE on failure.
	
	// Cron::create("Purge the Database", "purgeDatabase", array("table1", "table2"), $runCycle = 600)
	{
		// Prepare task functionality
		$method = Sanitize::variable($method);
		
		if(!method_exists("MyTasks", $method) && !method_exists("Task", $method))
		{
			Alert::error("Tasks", "That method cannot be called by Cron.", 1);
		}
		
		// Return FALSE if we've had any errors so far
		if(Alert::hasErrors()) { return false; }
		
		// Quick Sanitizing & Preparation
		$title = Sanitize::safeword($title);
		
		// Prepare the activation time
		$date_start = ($date_start < time() ? $date_start = time() : $date_start);
		$date_nextRun = $date_start + $run_cycle;
		
		// Create the Task
		Database::startTransaction();
		
		$pass1 = Database::query("INSERT INTO `cron` (`title`, `method`, `run_cycle`, `date_start`, `date_end`, `date_nextRun`) VALUES (?, ?, ?, ?, ?, ?)", array($title, $method, $run_cycle, $date_start, $date_end, $date_nextRun));
		
		$cronID = Database::$lastID;
		$pass2 = true;
		
		// Add Arguments to this cron task
		foreach($args as $key => $val)
		{
			if(!$pass2 = Site::setArg("CronTask", $cronID, $key, $val))
			{
				break;
			}
		}
		
		return Database::endTransaction(($pass1 && $pass2));
	}
	
	
/****** Edit an existing Cron Task ******/
	public static function edit
	(
		int $taskID				// <int> The ID of the task to edit.
		string $title				// <str> The title of the task, useful only to humans.
		string $method				// <str> The MyTasks::{method} or Task::{method} to call when activated.
		array $args = array()		// <array> The array of arguments to pass to the cron method.
		int $run_cycle = 600	// <int> Seconds until you repeat the task (default = 10 minutes).
		int $date_start = 0		// <int> Timestamp of when to start the task. If 0, start now.
		int $date_end = 0		// <int> Timestamp of when to end the task. If 0, don't end. If < now, run once.
	): bool						// RETURNS <bool> TRUE if task was created, FALSE on failure.
	
	// Cron::edit($taskID, "Purge the Database", "purgeDatabase", array("table1", "table2"), $runCycle = 600)
	{
		// Prepare task functionality
		$method = Sanitize::variable($method);
		
		if(!method_exists("MyTasks", $method) and !method_exists("Task", $method))
		{
			Alert::error("Tasks", "That method cannot be called by Cron.", 1);
		}
		
		// Return FALSE if we've had any errors so far
		if(Alert::hasErrors()) { return false; }
		
		// Quick Sanitizing & Preparation
		$title = Sanitize::safeword($title);
		
		// Prepare the activation time
		$date_start = ($date_start < time() ? $date_start = time() : $date_start);
		$date_nextRun = $date_start + $run_cycle;
		
		// Create the Task
		Database::startTransaction();
		
		$pass1 = Database::query("UPDATE cron SET title=?, method=?, run_cycle=?, date_start=?, date_end=?, date_nextRun=? WHERE id=? LIMIT 1", array($title, $method, $run_cycle, $date_start, $date_end, $date_nextRun, $taskID));
		
		$pass2 = true;
		
		$pass3 = Site::deleteArgs("CronTask", $taskID);
		
		// Add Cron Arguments
		foreach($args as $key => $val)
		{
			if(!$pass2 = Site::setArg("CronTask", $taskID, $key, $val))
			{
				break;
			}
		}
		
		return Database::endTransaction(($pass1 && $pass2 && $pass3));
	}
	
	
/****** Create a One-Time Cron Task ******/
	public static function once
	(
		string $title				// <str> The title of the task, useful only to humans.
		string $method				// <str> The MyTasks::{method} or Task::{method} to call when activated.
		array $args = array()		// <array> The array of arguments to pass to the task's function.
		int $date_start = 0		// <int> Timestamp of when to run the task. If 0, run it right away.
	): bool						// RETURNS <bool> : TRUE if created successfully, FALSE if not.
	
	// Cron::once("Purge the Database", "purgeDatabase", array("table1", "table2"), time() + 3600)
	{
		return Cron::create($title, $method, $args, 0, $date_start, 10);
	}
	
	
/****** Delete Cron Task ******/
	public static function delete
	(
		int $id		// <int> The Cron ID to delete.
	): bool			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cron::delete(153)
	{
		return Database::query("DELETE FROM `cron` WHERE id=?", array($id));
	}
	
	
/****** Process all Cron Jobs in the Queue ******/
	public static function runQueue (
	): bool			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cron::runQueue()
	{
		// Retrieve the next jobs in the list
		$cronList = Database::selectMultiple("SELECT * FROM `cron` WHERE date_nextRun <= ?", array(time()));
		
		// Cycle through the tasks provided
		foreach($cronList as $task)
		{
			// Run the task
			self::process($task);
		}
		
		return true;
	}
	
	
/****** Run Cron Task ******/
	public static function run
	(
		int $id				// <int> The ID of the cron task you want to run.
		bool $reset = true	// <bool> TRUE to reset the task activation afterward, FALSE to not reset.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cron::run($id)
	{
		// Retrieve the task
		$task = Database::selectOne("SELECT * FROM `cron` WHERE id=? LIMIT 1", array($id));
		
		// Activate the task
		return self::process($task, $reset);
	}
	
	
/****** Process Cron Job (from Data) ******/
	private static function process
	(
		array $task			// <array> The cron task data.
		bool $reset = true	// <bool> TRUE if you want to reset the task activation, FALSE to not reset.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cron::process($task, [$reset]);
	{
		if(isset($task['method']))
		{
			// Run the site-specific MyTasks::{method} if it's available
			if(method_exists("MyTasks", $task['method']))
			{
				if($reset == true)
				{
					self::reset($task['id'], $task['date_nextRun'], $task['run_cycle'], $task['date_start'], $task['date_end']);
				}
				
				$args = Site::getArgs("CronTask", $task['id']);
				
				return call_user_func_array(array("MyTasks", $task['method']), $args);
			}
			
			// Run the system-wide Task::{method} as a fallback
			if(method_exists("Task", $task['method']))
			{
				if($reset == true)
				{
					self::reset($task['id'], $task['date_nextRun'], $task['run_cycle'], $task['date_start'], $task['date_end']);
				}
				
				$args = Site::getArgs("CronTask", $task['id']);
				
				return call_user_func_array(array("Task", $task['method']), $args);
			}
			
		}
		
		return false;
	}
	
	
/****** Reset Cron Task ******
Run this function when you need to set the next activation timer for the cron task. If the task is old and shouldn't be
used again, this function will remove it from the list.
	private static function reset
	(
		int $id				// <int> The ID of the cron task that you want to reset.
		int $date_nextRun	// <int> The next activation timer for the job.
		int $run_cycle		// <int> The number of seconds to pass until you reset.
		int $date_start		// <int> The timestamp to start at.
		int $date_end		// <int> The timestamp to end at.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Cron::reset($cronID, $date_nextRun, $run_cycle, $date_start, $date_end);
	{
		$currentTime = time();
		
		// Delete the job if it's old
		if($date_end > 0 && $currentTime > $date_end)
		{
			self::delete($id);
			
			return true;
		}
		
		// Make sure that next activation time isn't in the future
		if($date_nextRun > $currentTime) { return false; }
		
		// Prepare minimum activation timer
		$date_nextRun = max($date_start, $date_nextRun, $currentTime) + $run_cycle;
		
		// Update the job
		return Database::query("UPDATE jobs SET date_nextRun=? WHERE id=? LIMIT 1", array($date_nextRun, $id));
	}
}