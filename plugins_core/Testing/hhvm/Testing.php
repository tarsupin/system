<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Testing Plugin ------
--------------------------------------

This plugin allows you to test the integrity of the plugins on the system, ensuring that they are producing the appropriate and expected values.

To run integrity testing for a plugin, you can create a {PLUGIN}.testing.php class file in the /admin folder of your plugin. For example, if the name of the plugin is "MyPlugin", your testing file would be named like this:
	
	/PLUGIN_DIRECTORY/MyPlugin/admin/MyPlugin.testing.php
	

------------------------------------------
------ Example of using this plugin ------
------------------------------------------

// Run tests on the "Sanitize" plugin
$test = new Testing("Sanitize");

// Test the "whitelist" method
$test->whitelist("aaabbbcccddd!@#$", "ab!@")	->expect("aaabbb!@");			// TRUE
$test->whitelist("asdfjkl;", "_jk,.")			->expect("jk");					// TRUE

// Test the "variable" method
$test->variable("Hello, Mr. Smith!")			->expect("HelloMrSmith");		// TRUE
$test->variable("AHA! #hashtag *star!")			->expect("AHAhashtagstar");		// TRUE
$test->variable("zigzug!!!")					->expect("zigzug!!!");			// FALSE

// Error Message Examples
$test->variable("abc!@#")	->expect("!@#")	->fail("Just testing out this message.");	// Error Message

// Display the results
$test->displayTestResults();


-------------------------------
------ Methods Available ------
-------------------------------

$test = new Testing($pluginName);

$test->{method}([..$arg], [..$arg], ...);

$test->displayTestResults();

*/

class Testing {
	
	
/****** Plugin Variables ******/
	public string $plugin = "";			// <str> The name of the plugin that you're testing.
	public int $lastIndex = 0;			// <int> The last index used in the results array.
	public string $lastMethod = "";		// <str> The method used in the last integrity test.
	public mixed $lastResult = null;		// <mixed> The result of your most recent integrity test.
	public array <str, array<int, array>> $results = array();		// <str:[int:array]> The results that you're testing.
	
	
/****** Initialize the testing plugin ******/
	public function __construct
	(
		string $plugin		// <str> The name of the plugin that you intend to run integrity tests on.
	): void				// RETURNS <void>
	
	// $test = new Testing($plugin);
	{
		$this->plugin = Sanitize::variable($plugin);
		
		
	}
	
	
/****** Run an Integrity Test ******/
	public function __call
	(
		string $method		// <str> The method to call.
	,	array <int, mixed> $args		// <int:mixed> The arguments passed into the method being called.
	): mixed				// RETURNS <mixed>
	
	// $test->{method}([..$arg], [..$arg], ...)
	{
		// Make sure the plugin is set
		if($this->plugin == "") { return; }
		
		// Prepare Values
		$result = "{{--BROKEN--}}";
		
		// Check if the plugin is abstract or not
		$checkPlugin = new ReflectionClass($this->plugin);
		
		if($checkPlugin->isAbstract())
		{
			if(method_exists($this->plugin, $method))
			{
				$result = call_user_func_array(array($this->plugin, $method), $args);
			}
		}
		else if(method_exists($this->plugin, $method))
		{
			$testPlugin = new $this->plugin();
			$result = call_user_func_array(array($testPlugin, $method), $args);
		}
		
		// Prepare Values
		$this->lastMethod = $method;
		$this->lastResult = $result;
		
		if(isset($this->results[$this->lastMethod]))
		{
			$this->lastIndex = count($this->results[$this->lastMethod]);
		}
		else
		{
			$this->lastIndex = 0;
		}
		
		$this->results[$method][$this->lastIndex]["result"] = $this->lastResult;
		$this->results[$this->lastMethod][$this->lastIndex]["args"] = Serialize::encode($args);
		
		return $this;
	}
	
	
/****** Run an Integrity Test ******/
	public function expect
	(
		mixed $expected	// <mixed> The expected result of your last integrity test.
	)				// RETURNS <this>
	
	// $test->expect($expected);
	{
		$this->results[$this->lastMethod][$this->lastIndex]["expected"] = $expected;
		
		if($this->lastResult !== $expected)
		{
			$this->results[$this->lastMethod][$this->lastIndex]["failed"] = true;
		}
		else
		{
			$this->results[$this->lastMethod][$this->lastIndex]["success"] = true;
		}
		
		return $this;
	}
	
	
/****** Set an error message if the test fails ******/
	public function fail
	(
		string $message	// <str> The message to announce if the test fails.
	)				// RETURNS <this>
	
	// $test->fail($message);
	{
		if(isset($this->results[$this->lastMethod][$this->lastIndex]["failed"]))
		{
			$this->results[$this->lastMethod][$this->lastIndex]["message"] = Sanitize::safeword($message);
		}
		
		return $this;
	}
	
	
/****** Display the test results ******/
	public function displayTestResults (
	): void				// RETURNS <void>
	
	// $test->displayTestResults();
	{
		echo '
		<style>
			.integrity { margin-bottom:14px; font-weight:bold; }
		</style>';
		
		foreach($this->results as $method => $testList)
		{
			foreach($testList as $round)
			{
				// Prepare Values
				$args = Serialize::decode($round['args']);
				$argString = StringUtils::convertArrayToArgumentString($args);
				
				$success = isset($round['failed']) ? false : true;
				
				echo '
				<div class="integrity" style="color:' . ($success ? "green" : "red") . '">' . $this->plugin . '::' . $method . '(' . $argString . ')';
				
				if(!$success)
				{
					echo '<pre>&nbsp; &bull; Result   : ' . $round['result'] . '
&nbsp; &bull; Expected : ' . $round['expected'];
					
					if(isset($round['message']))
					{
						echo '
&nbsp; &bull; Message  : ' . $round['message'];
					}
					
					echo '</pre>';
				}
				
				echo '
				</div>';
			}
		}
	}
	
	
}