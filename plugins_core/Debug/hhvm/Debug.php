<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Debug Plugin ------
------------------------------------

This plugin provides quick and easy debugging tools. If you LOG the result 


-----------------------------------
------ Using the Debug Tools ------
-----------------------------------

The Debug::log($value) method will log whatever value you provide it. If Debug::$verbose is TRUE, you will see the information displayed in the browser screen. If Debug::$log is TRUE, it will be logged into the database.

Using this plugin can allow you to track down unpleasant bugs, or to observe where other users are getting stuck by logging the results.

To send debugging information to a file, you can use Debug::file($value), which will save it to the application's base directory.


-------------------------------
------ Methods Available ------
-------------------------------

// Dump a debug value
Debug::dump($debugValue);

// Run the debugger (automatically runs in VERBOSE mode)
Debug::run();								// Display the debugging information in the browser.

// Save debugging information into a file
Debug::file([$filename], $textToSave);

*/

abstract class Debug {
	
	
/****** Class Variables ******/
	public static $debugList = array();
	public static $adminDisplay = false;
	public static $verbose = false;
	public static $log = false;
	public static $minClearance = 6;
	
	
/****** Dump a value for debugging purposes ******/
	public static function dump
	(
		mixed $debugValue = ""	// <mixed> The variable that you want to announce at this spot.
	): string						// RETURNS <str> a text dump of useful information about that variable.
	
	// Debug::dump($debugValue);
	{
		// If we're on a live system, run checks to see if you can run the debug.
		if(ENVIRONMENT == "production")
		{
			// Exit this function if there is no debug mode active
			if(self::$verbose == false and self::$log == false) { return ""; }
			
			// Exit this function if the clearance level is too low
			if(self::$minClearance > Me::$clearance) { return ""; }
		}
		
		// Prepare Values
		$backtrace = debug_backtrace();
		
		$origin = $backtrace[0];
		
		// Prepare Values
		$function = (isset($origin['class']) ? $origin['class'] . $origin['type'] : "") . (isset($origin['function']) ? $origin['function'] : "");
		$params = isset($origin['args']) ? StringUtils::convertArrayToArgumentString($origin['args']) : "";
		
		$file = str_replace(dirname(SYS_PATH), "", $origin['file']);
		
		$debugValue = Serialize::encode($debugValue);
		
		// Add entry to the debug list
		$debugArray = array(
			"value"		=> $debugValue
		,	"call"		=> $function . "(" . $params . ")"
		,	"file"		=> $file
		,	"line"		=> $origin["line"]
		);
		
		self::$debugList[] = $debugArray;
		
		// If we're in verbose mode, show the result in the browser when the page ends
		if(self::$verbose == true and self::$adminDisplay == false)
		{
			self::$adminDisplay = true;
			register_shutdown_function(array('Debug', 'run'));
		}
		
		// If MODE_LOG is set to true, we need to log this data
		if(self::$log == true OR ENVIRONMENT != "production")
		{
			Database::query("INSERT INTO log_debug (uni_id, date_logged, function_call, file_path, file_line, url_path, content) VALUES (?, ?, ?, ?, ?, ?, ?)", array(Me::$id, time(), $debugArray['call'], $file, $debugArray['line'], $_SERVER['REQUEST_URI'], $debugValue));
		}
		
		// Return the value so that the user can choose to echo it or not
		return "<pre>" . print_r($debugArray, true) . "</pre>";
	}
	
	
/****** Process a script error that the system causes ******/
	public static function scriptError
	(
		string $errorStr		// <str> The error message
	,	string $class			// <str> The class that produced the error.
	,	string $function		// <str> The function (or method) that produced the error.
	,	string $argString		// <str> The argument string that was used when the error was produced.
	,	string $filePath		// <str> The file that was loaded prior to the error being activated.
	,	int $fileLine		// <int> The line in the file that activated the error.
	,	string $filePathNext	// <str> The next file path in the stack trace.
	,	int $fileLineNext	// <int> The next file line in the stack trace.
	): string					// RETURNS <str>
	
	// Debug::scriptError($errorStr, $class, $function, $argString, $filePath, $fileLine, $filePathNext, $fileLineNext);
	{
		// Add entry to the debug list
		$debugArray = array(
			"value"			=> $errorStr
		,	"call"			=> ($class ? $class . "::" : "") . $function . "(" . $argString . ")"
		,	"file"			=> $filePath
		,	"line"			=> $fileLine
		,	"file_next"		=> $filePathNext
		,	"line_next"		=> $fileLineNext
		);
		
		self::$debugList[] = $debugArray;
		
		// If we're in verbose mode, show the result in the browser when the page ends
		if(self::$verbose == true and self::$adminDisplay == false)
		{
			self::$adminDisplay = true;
			register_shutdown_function(array('Debug', 'run'));
		}
		
		// Return the value so that the user can choose to echo it or not
		return "<pre>" . print_r($debugArray, true) . "</pre>";
		
	}
	
	
/****** Display the Debugging Section ******/
	public static function run (
	): void					// RETURNS <void> OUTPUTS an HTML block of debugging information gathered this page.
	
	// Debug::run();	// Activates automatically if the Debug plugin is running in verbose mode
	{
		// Prevent loading the debug if you're in a live production server with low clearance levels
		if(ENVIRONMENT == "production" and self::$minClearance < 5) { return; }
		
		// Begin output of debugging info
		echo '
		<div style="height:35%;width:100%;">&nbsp;</div>
		<div style="position:relative; text-align:left; background-color:white; font-family:courier; z-index:9999999; padding:8px; border:solid black 2px; max-height:35%; overflow:auto; width:95%;">';
		
		// Output the Error List, if applicable
		if(count(Alert::$debuggingInfo) > 0)
		{
			echo '
			********* &nbsp; Debugging Alert Warnings &nbsp; *********<br /><br />';
			
			echo self::showAlerts();
			
			echo '
			<br /><br />';
		}
		
		// Output the Variable List, if applicable
		if(count(self::$debugList) > 0)
		{
			echo '
			********* &nbsp; Debugging Dump List &nbsp; *********<br /><br />';
			
			self::showVariables();
		}
		
		echo '
		</div>';
	}
	
	
/****** Show alerts that were listed as severity 1 or higher ******/
	public static function showAlerts (
	): void				// RETURNS <void>
	
	// Debug::showAlerts();
	{
		// Prepare values
		$lineColor = array("CCFF99", "FFFF99", "CCFFFF");
		$a = 0;
		
		// Display a table with the user errors
		echo '
		<table class="mod-table" style="font-size:0.9em; font-family:Courier;">
			<tr>
				<td>Error Origin</td>
				<td>Line</td>
				<td>Severity</td>
				<td>Message</td>
			</tr>';
		
		foreach(Alert::$debuggingInfo as $error)
		{
			$argDisp = StringUtils::convertArrayToArgumentString($error[3]);
			
			// Display the next row
			echo '
			<tr style="background-color:#' . $lineColor[$a++ % 3] . '">
				<td>' . ($error[1] ? $error[1] : "") . $error[2] . '(' . $argDisp . ')</td>
				<td style="text-align:center;">' . $error[5] . '</td>
				<td style="text-align:center;">' . $error[6] . '</td>
				<td>' . $error[0] . '</td>
			</tr>';
		}
		
		echo '
		</table>';
	}
	
	
/****** Show debug variables that we dumped ******/
	public static function showVariables (
	): void				// RETURNS <void>
	
	// Debug::showVariables();
	{
		// Prepare values
		$lineColor = array("CCFF99", "FFFF99", "CCFFFF");
		$a = 0;
		
		echo '
		<style>
			.debug-line:hover { font-size: 1.2em; }
			.debug-line .hidden-debug { display:none; }
			.debug-line:hover .hidden-debug { display:block; font-size:0.9em; }
		</style>
		<table class="mod-table" style="font-size:0.8em; font-family:Courier;">
			<tr>
				<td>Function Call</td>
				<td>File Path</td>
				<td>Line #</td>
				<td>Debug Message</td>
			</tr>';
		
		// Loop through each debug line
		foreach(self::$debugList as $debug)
		{
			echo '
			<tr class="debug-line" style="background-color:#' . $lineColor[$a++ % 3] . '">
				<td>' . $debug['call'] . '</td>
				<td>
					<div style="background-color:#eeeeee;">' . $debug['file'] . '</div>
					<div style="background-color:#bbbbbb;">' . $debug['file_next'] . '</div></td>
				<td style="text-align:center;">
					<div style="background-color:#eeeeee;">' . $debug['line'] . '</div>
					<div style="background-color:#bbbbbb;">' . $debug['line_next'] . '</div>
				</td>
				<td>' . print_r($debug['value'], true) . "</td>
			</tr>";
		}
		
		echo '
		</table>';
	}
	
	
/****** Saves a text file with debugging information ******/
	public static function file
	(
		mixed $value1			// <mixed> The content you would like to save as text.
	,	string $value2 = ""	// <str> If set, the first value equals the "debug-{filename}.txt" to save to.
	): bool					// RETURNS <bool> TRUE if the file is written, FALSE on failure.
	
	// Debug::file("Some content to save in a text file.");
	// Debug::file("filename", "Some content to save in a text file.");
	{
		// Save a generic debug text file to debug.txt
		if($value2 == "")
		{
			return File::write(APP_PATH . "/debug.txt", print_r($value1, true));
		}
		
		// Save debugging information to a specific filename
		$value2 = Sanitize::variable($value2);
		return File::write(APP_PATH . "/debug-" . $value2 . ".txt", print_r($value1, true));
	}
	
	
/****** Log an error into the database ******/
	public static function logError
	(
		int $importance		// <int> The level of importance to assign to the error being logged.
	,	string $errorType		// <str> The type of error being logged.
	,	string $class			// <str> The class that produced the error.
	,	string $function		// <str> The function (or method) that produced the error.
	,	string $argString		// <str> The argument string that was used when the error was produced.
	,	string $filePath		// <str> The file that was loaded prior to the error being activated.
	,	int $fileLine		// <int> The line in the file that activated the error.
	,	string $url			// <str> The URL that was called when the error was detected.
	,	int $uniID			// <int> The UniID that was running the page when the error was produced.
	,	string $filePathNext	// <str> The next file path step in the stack trace.
	,	int $fileLineNext	// <int> The next file line step in the stack trace.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Debug::logError($importance, $errorType, $class, $function, $argString, $filePath, $fileLine, $url, $uniID, $filePathNext, $fileLineNext);
	{
		try
		{
			// Insert the Error Log
			Database::query("INSERT INTO log_errors (date_logged, importance, error_type, class, function, arg_string, file_path, file_line, url, uni_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array(time(), $importance, $errorType, $class, $function, $argString, $filePath, $fileLine, $url, $uniID));
			
			// Prune the error log prior to 30 days ago
			if(mt_rand(0, 50) == 20)
			{
				self::pruneErrorLog((86400 * 30));
			}
		}
		catch (PDOException $e)
		{
			return false;
		}
		
		return true;
	}
	
	
/****** Prune the error log ******/
	private static function pruneErrorLog
	(
		int $pruneDuration	// <int> The duration of time to prune errors prior to now.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// self::pruneErrorLog($pruneDuration);
	{
		return Database::query("DELETE FROM log_errors WHERE date_logged < ?", array(time() - $pruneDuration));
	}
	
}
