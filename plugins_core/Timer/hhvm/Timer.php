<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Timer Class ------
-----------------------------------

Some code benefits from having a regularly scheduled interval of time. The timer suits this purpose, as long as the
only thing that matters is how much time passes until the next interval. If you need more advanced scheduling, such as
"run at 2 pm on the 15th of September", refer to the Schedule class. It has a much more advanced system of event
activation available to it.

The timer triggers on fixed intervals, such as every 50 seconds. It has a start time and an end time, and only
activates between those two timestamps. Leaving the end time empty results in an "infinite" timer. Leaving the start
time empty results in it starting immediately.

The timer's primary value is the "tick interval" that determines how much time passes between each activation. For
example, if the tick interval is set to 90 seconds, then the timer activates every 90 seconds.

------------------------------------------------
------ Simple examples of setting a Timer ------
------------------------------------------------

The timer has simple methods for setting start time, end time, and a variety of methods for setting the tick interval.
This example shows you how to initialize the timer and set it's values:
	
	// Set a timer to activate every 4 minutes for the next hour
	$timer = new Timer();
	
	$timer
		->start(time())
		->duration(3600)		// 3600 seconds equals 1 hour
		->everyXMinutes(4);
	
	
You can also set a timer when initializing it, like this:
	
	// Set the timer to activate every 30 seconds for the next hour
	$startTime = time();			// The current time
	$inOneHour = time() + 3600;		// One hour from now
	
	$timer = new Timer($currentTime, $inOneHour, 30);

	
----------------------------------
------ Accessing Timer Data ------
----------------------------------

A timer is only useful if you can reuse it at a later time, and only if you can identify whether or not it activated.
This class has several methods to allow you to do this, as well as allows you to save timers and reload them later.

	// Save a timer
	$timer = new Timer(time(), 3600, 25);
	$saveData = $timer->save();
	
	// Load a timer
	$timer = new Timer($saveData);
	
	// Get the timestamp of the next event that will be triggered by a timer
	$nextActivation = $timer->nextEvent();
	
	// Get the timestamp of the most recently passed trigger
	$pastActivation = $timer->previousEvent();
	

In most cases with a website, the easiest way to handle timers is to check the last time an event was triggered against
an internal pointer. The Timer class uses the $timer->timerActivation variable to track when you last attempted to
trigger the timer. When you successfully trigger the timer, it updates the internal pointer.
	
	if($timer->trigger()) { echo "The timer has triggered."; }
	
	
-------------------------------------------------------
------ Fully Functional Cross-Page Timer Example ------
-------------------------------------------------------
	
If you load this code and refresh the page every few seconds, you'll notice how it goes from saying "Timer activated!"
to "You have X more seconds until this timer activates..."

	
	// If you don't have a timer saved, create one and save it
	if(!isset($_SESSION['saveTimer']))
	{
		// The timer is set to activate every six seconds for the next 24 hours
		$timer = new Timer();
		
		$timer
			->start(time())
			->end(time() + 86400)
			->everyXSeconds(6);
	}
	else
	{
		// Load the timer from your session
		$timer = new Timer($_SESSION['saveTimer']);
	}
	
	// Attempt to trigger the timer
	if($timer->trigger())
	{
		echo "Timer activated!";
	}
	else
	{
		$next = $timer->nextEvent();
		
		echo "You have " . ($next - $timer->timestamp) . " more seconds until this timer activates...";
	}
	
	// Save the new timer (in case the internal pointer changed)
	$_SESSION['saveTimer'] = $timer->save();

	
-------------------------------
------ Methods Available ------
-------------------------------

$timer = new Timer($saveData)						// Load a timer from saved data
$timer = new Timer($tickInterval)					// Sets never-expiring timer with a specified tick interval
$timer = new Timer($start, $end, $tickInterval)		// Sets timer with start, end, and a specified tick interval

$saveData = $timer->save();						// Saves the timer setting to load later (saves as a serialized string)

$timestamp	= $timer->previousEvent()			// Gets the timestamp of the most recent activation timestamp passed
$timestamp	= $timer->nextEvent()				// Gets the next timestamp that the timer will activate
$timestamps	= $timer->getEvents([$limit])		// Gets a list of the next timestamps that the timer will activate on

$success	= $timer->trigger()					// Attempts to trigger the timer, and will update the timer if it does

$timer
	->start($time)						// Sets the start time with a timestamp or a TimeTracker object
	->end($time)						// Sets the end time with a timestamp or a TimeTracker object
	->duration($seconds)				// Changes the end time to a specific duration from the start time (in seconds)

	->everyXDays($days)					// The timer triggers every $days days.
	->everyXHours($hours)				// The timer triggers every $hours hours.
	->everyXMinutes($minutes)			// The timer triggers every $minutes minutes.
	->everyXSeconds($seconds)			// The timer triggers every $seconds seconds.

	->everyDay()						// The timer triggers once per day
	->everyHour()						// The timer triggers once per hour
	->everyMinute()						// The timer triggers once per minute

*/

class Timer {
	
	
/****** Class Variables ******/
	
	public int $timestamp = 0;			// <int> The current timestamp.
	public int $timerStart = 0;			// <int> The timestamp that the timer starts at.
	public int $timerEnd = 0;			// <int> The timestamp that the timer ends at; default is never ends.
	public int $timerActivation = 0;	// <int> The timestamp of the last activation.
	public int $tickInterval = 0;		// <int> The number of seconds between timer ticks / triggers.
	
	
/****** Class Constructor ******/
	public function __construct(
	): void				// RETURNS <void>
	
	// $timer = new Timer($saveData)						// Load a timer from saved data
	// $timer = new Timer($tickInterval)					// Sets never-expiring timer with a specified tick interval
	// $timer = new Timer($start, $end, $tickInterval)		// Sets timer with start, end, and a specified tick interval
	{
		$args = func_get_args();
		$len = count($args);
		
		if($len == 1)
		{
			// If loaded with a specified tick interval
			if(is_numeric($args[0]))
			{
				$this->tickInterval = $args[0] + 0;
			}
			
			// If loaded from previously saved data
			else if(is_string($args[0]))
			{
				$this->load($args[0]);
			}
		}
		else if($len > 1)
		{
			$this->timerStart = $args[0] + 0;
			$this->timerEnd = $args[1] + 0;
			$this->tickInterval = $args[2] + 0;
		}
		
		$this->timestamp = time();
	}
	
	
/****** Save a timer ******/
	public function save (
	): string				// RETURNS <str> JSON-minified string that can be used to restore a timer.
	
	// $saveData = $timer->save();
	{
		// To save space precious characters with JSON (serialization), we're going to rename our parameters
		$keyChanges = array(
			"timerStart"		=> "s"
		,	"timerEnd"			=> "e"
		,	"tickInterval"		=> "t"
		,	"timerActivation"	=> "a"
		);
		
		$save = Serialize::changeKeys($this, $keyChanges);
		
		unset($save->timestamp);		// We don't need to track the timestamp
		
		return Serialize::encode($save);
	}
	
	
/****** Load a schedule ******/
	private function load
	(
		string $saveData	// <str> A serialized value (minimized json) to restore a schedule.
	): void				// RETURNS <void>
	
	// $schedule->load($saveData);
	{
		// Decode the loaded string
		if(!$saveData = Serialize::decode($saveData, false))
		{
			return;
		}
		
		// If we're loading custom data (through the ->save() method)
		if(isset($saveData->t))
		{
			$keyChanges = array(
				"s"			=> "timerStart"
			,	"e"			=> "timerEnd"
			,	"t"			=> "tickInterval"
			,	"a"			=> "timerActivation"
			);
			
			$saveData = Serialize::changeKeys($saveData, $keyChanges);
		}
		
		// Load the content
		if(isset($saveData->tickInterval))
		{
			$this->timerStart = $saveData->timerStart;
			$this->timerEnd = $saveData->timerEnd;
			$this->tickInterval = $saveData->tickInterval;
			$this->timerActivation = $saveData->timerActivation;
		}
	}
	
	
/****** Specify the timer's starting time ******/
	public function start
	(
		mixed $unitData	// <mixed> The timestamp or time tracker array to start at.
	)				// RETURNS <this>
	
	// $timer->start($timestamp)
	// $timer->start($timeTracker)
	{
		if(is_numeric($unitData))
		{
			$this->timerStart = $unitData + 0;
		}
		else if(is_array($unitData))
		{
			if(!isset($unitData['Timestamp']))
			{
				$unitData = Time::tracker($timeTracker);
			}
			
			$this->timerStart = $unitData['Timestamp'];
		}
		
		return $this;
	}
	
	
/****** Specify the timer's ending time ******/
	public function end
	(
		mixed $unitData	// <mixed> The timestamp or time tracker array to start at.
	)				// RETURNS <this>
	
	// $timer->end($timestamp)
	// $timer->end($timeTracker)
	{
		if(is_numeric($unitData))
		{
			$this->timerEnd = $unitData + 0;
		}
		else if(is_array($unitData))
		{
			if(!isset($unitData['Timestamp']))
			{
				$unitData = Time::tracker($timeTracker);
			}
			
			$this->timerEnd = $unitData['Timestamp'];
		}
		
		return $this;
	}
	
	
/****** Specify the timer's ending time with a duration rather than coded timestamp ******/
	public function duration
	(
		int $seconds	// <int> The number of seconds to run the timer.
	)				// RETURNS <this>
	
	// $timer->duration($seconds)
	{
		if(!isset($this->schedule['timer_start']))
		{
			$this->timerStart = time();
		}
		
		$this->timerEnd = $this->timerStart + $seconds;
		
		return $this;
	}
	
	
/****** Set the frequency of events for the timers (in days) ******/
	public function everyXDays
	(
		int $days		// <int> The number of days to cycle.
	)				// RETURNS <this>
	
	// $timer->everyXDays($days)
	{
		$this->tickInterval = $days * 60 * 60 * 24;
		return $this;
	}
	
	
/****** Set the frequency of events for the timers (in hours) ******/
	public function everyXHours
	(
		int $hours		// <int> The number of hours to cycle.
	)				// RETURNS <this>
	
	// $timer->everyXHours($hours)
	{
		$this->tickInterval = $hours * 60 * 60;
		return $this;
	}
	
	
/****** Set the frequency of events for the timers (in minutes) ******/
	public function everyXMinutes
	(
		int $minutes	// <int> The number of minutes to cycle.
	)				// RETURNS <this>
	
	// $timer->everyXMinutes($minutes)
	{
		$this->tickInterval = $minutes * 60;
		return $this;
	}
	
	
/****** Set the frequency of events for the timers (in seconds) ******/
	public function everyXSeconds
	(
		int $seconds	// <int> The number of seconds to cycle.
	)				// RETURNS <this>
	
	// $timer->everyXSeconds($seconds)
	{
		$this->tickInterval = $seconds;
		return $this;
	}
	
	/****** Set the timer to tick every X units within allowed time frames ******/
	public function everyDay()		// RETURNS <this>
		{ return $this->everyXDays(1); }
		
	public function everyHour()		// RETURNS <this>
		{ return $this->everyXHours(1); }
		
	public function everyMinute()	// RETURNS <this>
		{ return $this->everyXMinutes(1); }
	
	
/****** Attempt to trigger the timer ******/
	public function trigger (
	): bool				// RETURNS <bool> TRUE if the timer was activated, FALSE if not.
	
	// if($timer->trigger()) { echo "The timer was activated!"; }
	{
		// The activation trigger must be greater than or equal to the previous event to pass
		if($this->timerActivation >= $this->previousEvent())
		{
			return false;
		}
		
		// Update the Trigger Position
		$this->timerActivation = $this->previousEvent();
		
		return true;
	}
	
	
/****** Get the first available divisible timestamp of this timer ******/
	public function base (
	): int					// RETURNS <int> first available divisible timestamp.
	
	// $timestamp = $timer->base()
	{
		// Prepare Values
		$trackTimer = $this->timestamp;
		
		// Reset to the nearest activated time prior to this moment.
		$r = ($trackTimer - $this->timerStart) % $this->tickInterval;
		
		$trackTimer -= ($r == 0 ? $this->tickInterval : $r);
		
		// Return the next available activation
		return $trackTimer;
	}
	
	
/****** Get the timestamp of the most recent activation ******/
	public function previousEvent (
	): int					// RETURNS <int> timestamp of the previous event.
	
	// $timestamp = $timer->previousEvent()
	{
		// Prepare Values
		$trackTimer = $this->base();
		
		// Return the most recent event
		$trackTimer = ($this->timerStart >= $trackTimer ? $this->timerStart : $trackTimer);
		
		return $trackTimer;
	}
	
	
/****** Get the timestamp of the next event activation ******/
	public function nextEvent (
	): int					// RETURNS <int> timestamp of the next event.
	
	// $timestamp = $timer->nextEvent()
	{
		return $this->base() + $this->tickInterval;
	}
	
	
/****** Get an list of upcoming events ******/
	public function getEvents
	(
		int $limit = 100	// <int> The maximum number of events to return.
	): array <int, int>					// RETURNS <int:int> of timestamps, ordered by next to arrive.
	
	// $timestamps = $timer->getEvents([$limit])
	{
		// Prepare Values
		$list = array();
		$trackTimer = $this->nextEvent();
		
		// Get all remaining events (up to your limit)
		$cnt = 0;
		
		for($a = $trackTimer;$a <= $this->timerEnd;$a += $this->tickInterval)
		{
			// Stop if we have reached the limit of events to return
			if($cnt++ >= $limit) { break; }
			
			$list[] = $a;
		}
		
		return $list;
	}
	
	
}