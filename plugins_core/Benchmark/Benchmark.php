<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the Benchmark Plugin ------
----------------------------------------

This plugin provides simple application benchmarking functions. The basic functions will display and/or log the amount of time that passes between calls.


---------------------------------------------------------------------------
------ A simple example of benchmarking the duration of an algorithm ------
---------------------------------------------------------------------------

A good example of how this class is used is to identify how fast your different algorithms take, thus helping you decide which to use on your page.
#!
// Start the Benchmark
Benchmark::get();

// Run a long algorithm	
myLongFunction();

// Run the next benchmark, which tracks the duration of myLongFunction();
Benchmark::get();

// Run another long algorithm	
mySecondLongFunction();

// Tracks the duration of mySecondLongFunction();
Benchmark::get();

// Shows a graph on the screen with results of the benchmarks.
Benchmark::graph();
##!
	
The Benchmark::get() method automatically knows what the last benchmark time was, so this is the only code you need.

If the Benchmark SQL was added to your database, the benchmark will automatically be tracked in the database for review later as well.


-------------------------------------------
------ Advanced Benchmarking Options ------
-------------------------------------------

If you require more advanced benchmarking options, there are other features you can use.

	The Benchmark::get() method has a return value equal to the duration since the last Benchmark::get(). This would allow you to have custom benchmarking tracking if you like.</li>

		// Retrieves the duration since the last benchmark
		$duration = Benchmark::get()

	If you add the Benchmark SQL to your database, the benchmarks you run will automatically be added to the database. This will allow you to track many more results across more pages and in different scenarios.</li>
	
	The Benchmark::get() method accepts additional parameters for naming the benchmark and tracking modifiers that apply to it. This is very useful when combined with the benchmark database, since it allows you to identify categories of benchmarks, or indicate what values were passed.</li>


For example, if you're running benchmarks on a profile page on a live server, you could run this code:

	Benchmark::get("profile-page", "page-visited: " . $userProfile, "visitor: " . $userVisiting);
	
This would allow you to sort the results of your benchmarking tests, even filtering them by which user profiles were being visited.


-----------------------------
------ Benchmark Modes ------
-----------------------------

You can set the Benchmark Modes with the ::setMode() method. For example:

	Benchmark::setMode(Benchmark::MODE_VERBOSE, Benchmark::CLEARANCE_ADMIN);

Modes available:

	MODE_OFF			// Don't show or log benchmarks [default mode]
	MODE_VERBOSE		// Show benchmark results in the browser.
	MODE_LOG			// Logs benchmarks into the database.
	
	CLEARANCE_ADMIN		// Only processes debugging information for admins [default setting]
	CLEARANCE_STAFF		// Only processes debugging information for staff
	CLEARANCE_ALL		// Allows debugging information to be tracked on everyone


-------------------------------
------ Methods Available ------
-------------------------------

// Set the Benchmark Mode
Benchmark::setMode(...);

// Returns seconds between now and the last benchmark (or page start)
// The $group, $subgroup, and $name parameters can be logged and used for tracking purposes and averaging
Benchmark::get();
Benchmark::get([$group], [$subgroup], [$name]);

// Displays a benchmark graph with a baseline benchmark in milliseconds
Benchmark::graph($baseline = 30);

// Displays recorded benchmark graph
Benchmark::graphRecords([$group], [$subgroup], [$name]);

// Saves your benchmarks in the database (automatically activated if mode == MODE_LOG)
Benchmark::save()

*/

abstract class Benchmark {
	
	
/****** Class Variables ******/
	public static $storeBenchmarks = array();
	
	private static $storeMicrotime = 0;
	private static $isActivated = false;
	private static $verbose = false;
	private static $log = false;
	private static $clearance = 8;
	
	const MODE_OFF = 0;			// Don't show or log benchmarks [default mode]
	const MODE_VERBOSE = 1;		// Show benchmark results in the browser.
	const MODE_LOG = 2;			// Logs benchmarks into the database.
	
	const CLEARANCE_ADMIN = 3;	// Only processes debugging information for admins [default setting]
	const CLEARANCE_STAFF = 4;	// Only processes debugging information for staff
	const CLEARANCE_ALL = 5;	// Allows debugging information to be tracked on everyone
	
	
/****** Set the Benchmark Mode ******/
	public static function setMode
	(
		// (args)		// (integers) The modes to set (e.g. Benchmark::MODE_VERBOSE, Benchmark::MODE_LOG, etc.)
	)					// RETURNS <void>
	
	// Benchmark::setMode(Benchmark::MODE_VERBOSE, Benchmark::CLEARANCE_ADMIN);
	// Benchmark::setMode(Benchmark::MODE_LOG, Benchmark::CLEARANCE_ALL);
	{
		$args = func_get_args();
		
		foreach($args as $arg)
		{
			switch($arg)
			{
				case self::MODE_OFF:
					self::$verbose = false;
					self::$log = false;
					break;
				
				case self::MODE_VERBOSE;
					self::$verbose = 1;
					break;
				
				case self::MODE_LOG;
					self::$log = 1;
					break;
				
				case self::CLEARANCE_ADMIN;
					self::$clearance = 8;
					break;
				
				case self::CLEARANCE_STAFF;
					self::$clearance = 5;
					break;
				
				case self::CLEARANCE_ALL;
					self::$clearance = -10;
					break;
			}
		}
	}
	
	
/****** Retrieve Duration Since Last Benchmark ******/
	public static function get
	(
		$group = ""		// <str> Optional group name for record keeping purposes (to identify the benchmark).
	,	$subgroup = ""	// <str> Optional subgroup to indicate changes to (or parameters of) the benchmark.
	,	$name = ""		// <str> Optional name to indicate changes to (or parameters of) the benchmark.
	)					// RETURNS <float> The duration (in seconds) since the last benchmark.
	
	// Benchmark::get();
	// Benchmark::get("image_gallery", "page_" . $_POST['page'], "filter by popularity");
	{
		// If you haven't run ::get() yet, default it to the original script time:
		$difference = (self::$storeMicrotime == 0 ? microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true) - self::$storeMicrotime);
		
		// Record our last benchmark so that it can be used later
		self::$storeMicrotime = microtime(true);
		
		// Record Values
		$benchmark = number_format($difference, 6);
		
		$backtrace = debug_backtrace();
		$origin = $backtrace[1];
		
		// Get minimized file name
		$fileMin = $origin['file'];
		$fileMin = str_replace("\\", "/", $fileMin);
		$fileMin = substr($fileMin, strrpos($fileMin, "/"));
		$fileMin = substr($fileMin, 0, 20);
		
		// Store all benchmarks in list
		self::$storeBenchmarks[] = array("file_call" => $fileMin . ":" . $backtrace[0]['line'], "benchmark" => $benchmark, "group" => $group, "subgroup" => $subgroup, "name" => $name);
		
		// Set the benchmark to show when the page ends
		if(self::$isActivated == false)
		{
			self::$isActivated = true;
			
			if(self::$verbose == true)
			{
				register_shutdown_function(array('Benchmark', 'graph'));
			}
			
			register_shutdown_function(array('Benchmark', 'save'));
		}
		
		return (float) $benchmark;
	}
	
	
/****** Retrieve Duration Since Script Began ******/
	public static function sinceStart (
	)					// RETURNS <float> The duration (in seconds) since the script began.
	
	// Benchmark::sinceStart();
	{
		$benchmark = (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
		
		return (float) number_format($benchmark, 4);
	}
	
	
/****** Benchmark Graph ******/
	public static function graph
	(
		$baseline = 30		// <int> The baseline in milliseconds for precision in the graph.
	)						// RETURNS <void> OUTPUTS an HTML graph of the benchmark.
	
	// Benchmark::graph($baseline)
	{
		// Prevent loading the benchmark graph if you're in a live production server without testing enabled
		if(ENVIRONMENT == "production") { return; }
		
		// Prepare Values
		$graphData = self::$storeBenchmarks;
		$baseline = ($baseline / 100);
		$highestCost = $baseline;
		
		$files = array();
		$titles = array();
		$percents = array();
		$costs = array();
		
		// Retrieve the highest value to compare to
		foreach($graphData as $cost)
		{
			$highestCost = max($cost['benchmark'], $highestCost);
		}
		
		// Add primary benchmark
		$files[] = "";
		$titles[] = "";
		$percents[] = $baseline / $highestCost;
		$costs[] = number_format($baseline, 6);
		
		// Cycle through each of the benchmarks
		foreach($graphData as $cost)
		{
			$files[] = $cost['file_call'];
			$titles[] = (isset($cost['title']) ? $cost['title'] : "");
			$percents[] = $cost['benchmark'] / $highestCost;
			$costs[] = $cost['benchmark'];
		}
		
		// Output the benchmark graph
		echo '
		<div style="height:35%;width:100%;">&nbsp;</div>
		<div style="position:fixed;text-align:left;background-color:white;bottom:0;right:0;font-family:courier;z-index:9999999;padding:8px;border-left:solid black 2px;;border-top:solid black 2px;">
		************************************ &nbsp; Benchmark Graph &nbsp; *************************************<br /><br />';
		
		$len = (count($files) * 2) + 1;
		
		// Cycle through each of the graph lines
		for($a = 0, $loopCount = count($files);$a < $loopCount;$a++)
		{
			// Handling Titles
			if($titles[$a] != "")
			{
				echo str_repeat("&nbsp;", 32 - strlen($titles[$a])) . $titles[$a] . ": ";
			}
			else
			{
				// Determine spacing
				$leftWidth = 34 - strlen($files[$a]);
				$gap = 4;
				
				// Determine which type of line is being loaded (since there are two unique types)
				if($a > 1)
				{
					echo str_repeat("&nbsp;", $leftWidth) . $files[$a] . str_repeat("&nbsp;", $gap);
				}
				else if($a == 0)
				{
					echo str_repeat("&nbsp;", $leftWidth) . " &nbsp; Benchmark: ";
				}
				else if($a == 1)
				{
					echo str_repeat("&nbsp;", $leftWidth) . $files[$a] . str_repeat("&nbsp;", $gap) . "Initial Load - Start Point<br />";
					continue;
				}
			}
			
			// Graph the lines
			for($b = 0;$b < 50;$b++)
			{
				if($percents[$a] > 0.02 * $b)
				{
					echo "O";
				}
				else
				{
					echo "&nbsp;";
				}
			}
			
			// Display the time expense
			echo " (" . $costs[$a] . ")<br />";
		}
		
		echo '
		</div>';
	}
	
	
/****** Benchmark Graph ******/
	public static function graphRecords
	(
		$group = ""		// <str> Group that was used to identify and track the benchmark
	,	$subgroup = ""	// <str> Sub-group that was used to identify and track the benchmark
	,	$name = ""		// <str> Name that was used to identify and track the benchmark
	)					// RETURNS <void> OUTPUTS an HTML graph of the benchmark.
	
	// Benchmark::graphRecords([$group], [$subgroup], [$name]);
	{
		// Prepare Values
		self::$storeBenchmarks = array();
		
		// If the record only includes the group
		if($subgroup == "")
		{
			$results = Database::selectMultiple("SELECT key_group, key_subgroup, file_call, AVG(benchmark) as benchmark FROM log_benchmark WHERE key_group=? GROUP BY key_subgroup", array($group, $subgroup));
		}
		else
		{
			$results = Database::selectMultiple("SELECT key_group, key_subgroup, key_name, file_call, AVG(benchmark) as benchmark FROM log_benchmark WHERE key_group=? AND key_subgroup=? AND key_name=?", array($group, $subgroup, $name));
		}
		
		foreach($results as $value)
		{
			// Store all benchmarks in list
			self::$storeBenchmarks[] = array("" => $value['key_group'], "subgroup" => $value['key_subgroup'], "name" => $value['key_name'], "file_call" => $value['file_call'], "benchmark" => (float) $value['benchmark']);
		}
		
		self::graph();
	}
	
	
/****** Record the Benchmark Results in the Database ******/
	public static function save (
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Benchmark::save()
	{
		// Get the next benchmark cycle ID
		if(!$benchmarkCycle = UniqueID::get("benchmark-cycle"))
		{
			return false;
		}
		
		Database::startTransaction();
		
		// Cycle through each of the benchmarks and track them in the database
		foreach(self::$storeBenchmarks as $value)
		{
			if(!$pass = Database::query("INSERT INTO `log_benchmark` (benchmark_cycle, key_group, key_subgroup, key_name, file_call, benchmark, date_logged) VALUES (?, ?, ?, ?, ?, ?, ?)", array($benchmarkCycle, $value['group'], $value['subgroup'], $value['name'], $value['file_call'], $value['benchmark'], time())))
			{
				break;
			}
		}
		
		return Database::endTransaction($pass);
	}
	
}
