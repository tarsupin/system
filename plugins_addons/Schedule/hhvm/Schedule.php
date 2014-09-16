<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------------
------ About the Schedule Plugin ------
---------------------------------------

This plugin allows you to perform advanced scheduling techniques. You can build a schedule to run events on specific hours of the night, during special events, for automatic backups, and more.

Schedules require you to set the time units of when the schedule should activate. For example, you can set a schedule to activate only on the months of January, February, March, and October. You can also set other units, such as to only activate at 1 in the morning or only on Saturdays.

A default schedule triggers once per day at midnight, but you can modify the schedule with the methods available. This will update the trigger array, which contains the list of units to indicate when the scheduled events will trigger. Every time there is an event with an available match, the trigger can activate its scheduled event.
	
A trigger would resemble something like this:

	$triggers = array(
		'years'				=> array(2014, 2015, 2019)
		'months'			=> array("January", 2, 3, "October")
		'days_of_month'		=> array(5, 10, 15, 20, 25, 30)
		'days_of_week'		=> array("Monday", "Tuesday", 5, 6)
		'hours'				=> array(0, 2, 3, 5, 10, 20)
		'minutes'			=> array(0, 20, 40)
		'seconds'			=> array(0, 30)
	);

Note that you do not have to include every option. However, some options will include ALL options by default (such as the month and days of the month), and others will only run ONCE (such as hours, minutes, and seconds).

To build the schedule's trigger array, use the appropriate functions provided by the class.

	// For example, this schedule would FIRST activate on January 15th, 2014 at exactly midnight.
	// It would activate a total of 8 times, the final time being on October 15th, 2015 at midnight.
	$schedule = new Schedule();
	$schedule
		->setYears(2014, 2015)
		->setMonths("January", "April", "July", "October")
		->setDaysOfMonth(15);


---------------------------------------------
------ Examples of building a schedule ------
---------------------------------------------
	
#!
// Sets a Schedule for every 5 minutes on every Sunday during the month of August
// Note that every hour has to be set here (otherwise it defaults to just midnight)
$schedule = new Schedule();
$schedule->setMonths("August")
		->setDaysOfWeek("Sunday")
		->setHours(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23)
		->setMinutes(0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55);
##!

#!
// Sets a schedule for 11 pm on the 3rd of each month
// Note that every hour has to be set here (otherwise it defaults to just midnight)
$schedule = new Schedule();
$schedule->setDaysOfMonth(3)
		->setHours("11 pm")
		->setMinutes(0);
##!


------------------------------------------
------ Saving and Loading Schedules ------
------------------------------------------

Schedules won't matter unless you can keep track of them. For this reason, there are functions provided to save schedules and load them at a later time.
	
The $schedule->save() method will return a serialized value that you can store. You can load this later when initializing the plugin.
	
	// Save the Schedule for later
	$saveData = $schedule->save();
	
	// Load an earlier Schedule that you saved
	$oldSchedule = new Schedule($saveData);


-------------------------------
------ Methods Available ------
-------------------------------

// Creates a new schedule that you can edit
$schedule = new Schedule()

// Restores a schedule that you saved earlier
$schedule = new Schedule($saveData)

// Saves a schedule as a serialized value
$saveData = $schedule->save()

$schedule
	->startTime($timestamp)		// Schedule can't start before this
	->endTime($timestamp)		// Schedule won't run after this

// Set the years, months, etc, that a schedule will activate on.
	->setYears($year, $year, $year...)
	->setMonths($month, $month, $month...)
	->setDaysOfMonth($day, $day, $day...)
	->setDaysOfWeek($day, $day, $day...)
	->setHours($hour, $hour, $hour...)
	->setMinutes($minute, $minute, $minute...)
	->setSeconds($second, $second, $second...)
	
// Notes:
	->setMonths() can recognize words, e.g. "March"
	->setDaysOfWeek() can recognize words, e.g. "Thursday"
	->setHours() can recognize "am" and "pm", e.g. "3 pm", etc.
	->setHours() defaults to "0" (midnight)
	->setMinutes() defaults to "0" (start of the hour)
	->seetSeconds() defaults to "0" (start of the minute)

*/

class Schedule {
	
	
/****** Class Variables ******/
	
	// The timestamps for when the schedule begins and ends
	public int $timeStart = 0;		// <int> Scheduled events cannot start prior to this timestamp
	public int $timeEnd = 0;		// <int> If set to '0', the schedule has no date that it is required to stop at
	
	// Contains all of the rules for when the schedule activates
	public array <str, array<int, mixed>> $triggers = array();		// <str:[int:mixed]> the schedule triggers (days, months, etc. to activate on)
	
	
/****** Class Constructor ******/
	public function __construct
	(
		string $saveData = false	// <str> The saved data to rebuild a schedule (either JSON or custom data).
	): void						// RETURNS <void>
	
	// $schedule = new Schedule();					// Builds a new schedule
	// $schedule = new Schedule($saveData);			// Rebuilds a schedule from saved data
	{
		// Restore Saved Data
		if($saveData !== false)
		{
			$this->load($saveData);
		}
	}
	
	
/****** Save a schedule ******/
	public function save (
	): string				// RETURNS <str> JSON-minified string that can be used to restore a schedule.
	
	// $schedule->save();
	{
		$save = new stdClass();
		
		if($this->timeStart !== 0) { $save->s = $this->timeStart; }
		if($this->timeEnd !== 0) { $save->e = $this->timeEnd; }
		
		// To save space precious characters with JSON (serialization), we're going to rename our parameters
		$keyChanges = array(
			"years"				=> "y"
		,	"months"			=> "mo"
		,	"days_of_month"		=> "d"
		,	"days_of_week"		=> "dw"
		,	"hours"				=> "h"
		,	"minutes"			=> "m"
		,	"seconds"			=> "s"
		);
		
		$save->t = $this->triggers;
		$save->t = Serialize::changeKeys($save->t, $keyChanges);
		
		// Pack the schedules into ranges where possible (helps conserve space)
		if(isset($save->t->mo)) { $save->t->mo = Serialize::numericArrayPack($save->t->mo); }
		if(isset($save->t->d)) { $save->t->d = Serialize::numericArrayPack($save->t->d); }
		if(isset($save->t->dw)) { $save->t->dw = Serialize::numericArrayPack($save->t->dw); }
		if(isset($save->t->h)) { $save->t->h = Serialize::numericArrayPack($save->t->h); }
		if(isset($save->t->m)) { $save->t->m = Serialize::numericArrayPack($save->t->m); }
		if(isset($save->t->s)) { $save->t->s = Serialize::numericArrayPack($save->t->s); }
		
		return json_encode($save);
	}
	
	
/****** Load a schedule ******/
	private function load
	(
		string $saveData	// <str> A serialized value (minimized json) to restore a schedule.
	): void				// RETURNS <void>
	
	// $schedule->load($saveData);
	{
		$saveData = json_decode($saveData);
		
		// If we're loading custom data (through the ->save() method)
		if(isset($saveData->t))
		{
			// Unpack the arrays
			if(isset($saveData->t->mo)) { $saveData->t->mo = Serialize::numericArrayUnpack($saveData->t->mo); }
			if(isset($saveData->t->d)) { $saveData->t->d = Serialize::numericArrayUnpack($saveData->t->d); }
			if(isset($saveData->t->dw)) { $saveData->t->dw = Serialize::numericArrayUnpack($saveData->t->dw); }
			if(isset($saveData->t->h)) { $saveData->t->h = Serialize::numericArrayUnpack($saveData->t->h); }
			if(isset($saveData->t->m)) { $saveData->t->m = Serialize::numericArrayUnpack($saveData->t->m); }
			if(isset($saveData->t->s)) { $saveData->t->s = Serialize::numericArrayUnpack($saveData->t->s); }
			
			// Unpack the data we serialized
			$keyChanges = array(
				"s"			=> "timeStart"
			,	"e"			=> "timeEnd"
			,	"t"			=> "triggers"
			);
			
			$saveData = Serialize::changeKeys($saveData, $keyChanges);
			
			$keyChanges = array(
				"y"			=> "years"
			,	"mo"		=> "months"
			,	"d"			=> "days_of_month"
			,	"dw"		=> "days_of_week"
			,	"h"			=> "hours"
			,	"m"			=> "minutes"
			,	"s"			=> "seconds"
			);
			
			$saveData->triggers = Serialize::changeKeys($saveData->triggers, $keyChanges);
		}
		
		// Load the content
		if(isset($saveData->triggers))
		{
			$this->timeStart = (isset($saveData->timeStart) ? $saveData->timeStart : 0);
			$this->timeEnd = (isset($saveData->timeEnd) ? $saveData->timeEnd : 0);
			
			$this->triggers = (array) $saveData->triggers;
		}
	}
	
	
/****** Specify the start time of the schedule ******/
	public function startTime
	(
		int $start		// <int> The timestamp of when to start a schedule.
	)				// RETURNS <this>
	
	// $schedule->startTime($timestamp);
	{
		$this->timeStart = $start + 0;
		return $this;
	}
	
	
/****** Specify the end time of the schedule ******/
	public function endTime
	(
		int $end		// <int> The timestamp of when to end a schedule.
	)				// RETURNS <this>
	
	// $schedule->endTime($timestamp);
	{
		$this->timeEnd = $end + 0;
		return $this;
	}
	
	
/****** Specify YEARS that the schedule can operate in ******/
	public function setYears
	(
		// (args of int) - allowed years.
	)						// RETURNS <this>
	
	// $schedule->setYears($year, $year, $year, ...)
	{
		$currentYear = date('Y') + 0;
		$args = func_get_args();
		
		$yearList = array();
		
		foreach($args as $year)
		{
			// Prevent illegal years
			if($year < ($currentYear - 250) or $year > ($currentYear + 250)) { continue; }
			
			$yearList[] = $year + 0;
		}
		
		$this->triggers['years'] = $yearList;
		
		return $this;
	}
	
	
/****** Specify MONTHS that the schedule can operate in ******/
	public function setMonths
	(
		// (args of mixed) - allowed months (numbers or month names accepted).
	)						// RETURNS <this>
	
	// $schedule->setMonths($month, $month, $month, ...)
	{
		// Run the Function
		$args = func_get_args();
		
		$monthList = array();
		
		foreach($args as $month)
		{
			// If the argument is a string (e.g. "January"), change it to a number.
			if(!$month = Time::convertMonthToNumber($month))
			{
				continue;
			}
			
			$monthList[] = $month + 0;
		}
		
		$this->triggers['months'] = $monthList;
		
		return $this;
	}
	
	
/****** Specify DAYS OF MONTH that the schedule can operate in ******/
	public function setDaysOfMonth
	(
		// (args of int) - allowed days of the month.
	)					// RETURNS <this>
	
	// $schedule->setDaysOfMonth($day, $day, $day, ...)
	{
		$args = func_get_args();
		
		$dayList = array();
		
		foreach($args as $day)
		{
			// Prevent illegal days of the month
			if($day < 1 or $day > 31) { continue; }
			
			$dayList[] = $day + 0;
		}
		
		$this->triggers['days_of_month'] = $dayList;
		
		return $this;
	}
	
	
/****** Specify DAYS OF WEEK that the schedule can operate in ******/
	public function setDaysOfWeek
	(
		// (args of mixed) - allowed days of the week (numbers or day name accepted).
	)					// RETURNS <this>
	
	// $schedule->setDaysOfWeek($day, $day, $day, ...)
	{
		// Run the Function
		$args = func_get_args();
		
		$dayList = array();
		
		foreach($args as $day)
		{
			// If the argument is a string (e.g. "Monday"), change it to a number.
			if(!$day = Time::convertDayOfWeekToNumber($day))
			{
				continue;
			}
			
			$dayList[] = $day + 0;
		}
		
		$this->triggers['days_of_week'] = $dayList;
		
		return $this;
	}
	
	
/****** Specify HOURS that the schedule can operate in ******/
	public function setHours
	(
		// (args of mixed) - allowed hours (military time [numbers] or time with "am" and "pm" accepted).
	)						// RETURNS <this>
	
	// $schedule->setHours($day, $day, $day, ...)
	{
		$args = func_get_args();
		
		$hourList = array();
		
		foreach($args as $hour)
		{
			// If the argument is a string (e.g. "3 pm", "1 am"), change it to a number.
			$hour = Time::convertHourToNumber("10 pm");
			
			$hourList[] = $hour + 0;
		}
		
		$this->triggers['hours'] = $hourList;
		
		return $this;
	}
	
	
/****** Specify MINUTES that the schedule can operate in ******/
	public function setMinutes
	(
		// (args of int) - allowed minutes.
	)					// RETURNS <this>
	
	// $schedule->setMinutes($minute, $minute, $minute, ...)
	{
		$args = func_get_args();
		
		$minuteList = array();
		
		foreach($args as $arg)
		{
			$arg = ($arg == 60 ? 0 : $arg);
			
			// Prevent illegal minutes
			if($arg < 0 or $arg > 59) { continue; }
			
			$minuteList[] = $arg + 0;
		}
		
		$this->triggers['minutes'] = $minuteList;
		
		return $this;
	}
	
	
/****** Specify SECONDS that the schedule can operate in ******/
	public function setSeconds
	(
		// (args of int) - allowed seconds.
	)					// RETURNS <this>
	
	// $schedule->setSeconds($second, $second, $second, ...)
	{
		$args = func_get_args();
		
		$secondList = array();
		
		foreach($args as $arg)
		{
			$arg = ($arg == 60 ? 0 : $arg);
			
			// Prevent illegal seconds
			if($arg < 0 or $arg > 59) { continue; }
			
			$secondList[] = $arg + 0;
		}
		
		$this->triggers['seconds'] = $secondList;
		
		return $this;
	}
	
	
/****** Compare a schedule to a timestamp ******/
	public function nextEvent
	(
		int $timestamp = 0		// <int> The timestamp to compare the schedule with (default is current time).
	): array <str, mixed>						// RETURNS <str:mixed>
	
	// $schedule->nextEvent($timestamp = {current time})
	{
		// Prepare Values
		$tracker = new TimeTracker(($timestamp === 0 ? time() : $timestamp));
		$trg = $this->triggers;
		
		// Prepare local copies of schedule
		$sYears		= (isset($trg['years'])	? $trg['years'] : array($tracker->year, $tracker->year + 1));
		$sMonths	= (isset($trg['months']) ? $trg['months'] : self::getUnitList("month"));
		$sDays		= (isset($trg['days_of_month']) ? $trg['days_of_month'] : self::getUnitList("days_of_month"));
		$sDaysWeek	= (isset($trg['days_of_week']) ? $trg['days_of_week'] : array());
		$sHours		= (isset($trg['hours']) ? $trg['hours'] : array());
		$sMinutes	= (isset($trg['minutes']) ? $trg['minutes'] : array());
		$sSeconds	= (isset($trg['seconds']) ? $trg['seconds'] : array());
		$sEveryX	= (isset($trg['every_x_ticks']) ? $trg['every_x_ticks'] : false);
		
		// Make sure we start after the schedule begins
		if($this->timeStart > 0 and $tracker->timestamp < $this->timeStart)
		{
			$tracker->setTimestamp($this->timeStart);
		}
		
		// Cycle through the list
		while(true)
		{
			// Prepare Values
			$update = false;
			
			//////////
			// Year //
			//////////
			
			// Get the next available year within the schedule's allowance
			$tracker->year = self::getTimeUnit($sYears, $tracker->year, $update);
			
			// If there are no remaining years are allowed, there are no remaining activations. End the test.
			if($tracker->year === false) { break; }
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, 1, 1, 0, 0, 0);
			}
			
			///////////
			// Month //
			///////////
			
			// Get the next available month within the schedule's allowance
			$tracker->month = self::getTimeUnit($sMonths, $tracker->month, $update, 1);
			
			// If no more months are allowed this year, retry with the next available year
			if($tracker->month === false)
			{
				$update = true;
				
				$tracker->year++;
				$tracker->month = 1;
			}
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, $tracker->month, 1, 0, 0, 0);
				continue;
			}
			
			//////////////////
			// Day of Month //
			//////////////////
			
			// Get the next available day of the month
			// If no more days are allowed this month, retry with next available month
			$tracker->day = self::getTimeUnit($sDays, $tracker->day, $update, 1);
			
			if($tracker->day === false)
			{
				$update = true;
				
				$tracker->month++;
				$tracker->day = 1;
			}
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, $tracker->month, $tracker->day, 0, 0, 0);
				continue;
			}
			
			/////////////////
			// Day of Week //
			/////////////////
			
			// Make sure we're running on one of the allowed days of the week, if expected to
			if($sDaysWeek)
			{
				// Prepare Values
				//$timestamp = mktime($trackHour, $trackMinute, $trackSecond, $trackMonth, $trackDay, $trackYear);
				$weekday = date("N", $tracker->timestamp) + 0;
				$monthDays = date("t", $tracker->timestamp) + 0;
				
				while($tracker->day <= $monthDays)
				{
					if(in_array($weekday % 7, $sDaysWeek))
					{
						break;
					}
					
					$weekday++;
					$tracker->day++;
				}
				
				if($tracker->day > $monthDays)
				{
					echo 'g';
					$tracker->setMultiple($tracker->year, $tracker->month, 1, 0, 0, 0);
					continue;
				}
			}
			
			//////////
			// Hour //
			//////////
			
			// Get the next available hour of the day
			// If no more hours are allowed today, retry with next available day
			$tracker->hour = self::getTimeUnit($sHours, $tracker->hour, $update, 0);
			
			if($tracker->hour === false)
			{
				$update = true;
				
				$tracker->day++;
				$tracker->hour = 0;
			}
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, $tracker->month, $tracker->day, $tracker->hour, 0, 0);
				continue;
			}
			
			////////////
			// Minute //
			////////////
			
			// Get the next available minute of the hour
			// If no more minutes are allowed this hour, retry with next available hour
			$tracker->minute = self::getTimeUnit($sMinutes, $tracker->minute, $update, 0);
			
			if($tracker->minute === false)
			{
				$update = true;
				
				$tracker->hour++;
				$tracker->minute = 0;
			}
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, $tracker->month, $tracker->day, $tracker->hour, $tracker->minute, 0);
				continue;
			}
			
			////////////
			// Second //
			////////////
			
			// Get the next available second of the minute
			// If no more minutes are allowed this hour, retry with next available hour
			$tracker->second = self::getTimeUnit($sSeconds, $tracker->second, $update, 0);
			
			if($tracker->second === false)
			{
				$update = true;
				
				$tracker->minute++;
				$tracker->second = 0;
			}
			
			if($update)
			{
				$tracker->setMultiple($tracker->year, $tracker->month, $tracker->day, $tracker->hour, $tracker->minute, $tracker->second);
				continue;
			}
			
			// Break out of the loop and return the results
			break;
		}
		
		// Check if the end time has passed
		if($this->timeEnd > 0 and $tracker->timestamp > $this->timeEnd)
		{
			return false;
		}
		
		return $tracker;
	}
	
	
	
	
/****** Return a full list of months ******/
	public static function getUnitList
	(
		string $unitType	// <str> The type of unit list to retrieve.
	): array <int, int>				// RETURNS <int:int> list of the requested unit type.
	
	// self::getUnitList($unitType)
	{
		switch($unitType)
		{
			case "month":
				return array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
			
			case "day":
			case "day_of_month":
			case "days_of_month":
				return array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);
			
			case "day_of_week":
			case "days_of_week":
				return array(1, 2, 3, 4, 5, 6, 7);
			
			case "hour":
				return array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);
		}
		
		return array();
	}
	
	
/****** Help track down the next available time unit allowed ******/
	private static function getTimeUnit
	(
		array <int, int> $timeUnitArray		// <int:int> Contains each time slot / values accepted for the time unit type.
	,	int $trackerUnit		// <int> The value of the tracker time unit (the one you're matching against).
	,	bool &$update = false	// <bool> If this gets set to true, you progressed in the time sequence.
	,	mixed $default = false	// <mixed> The default value to set if the time array is empty.
	): mixed						// RETURNS <mixed>
	
	// self::getTimeUnit($timeUnitArray, $trackerUnit, &$update, [$default])
	{
		// If there is no time unit array provided, return the default value provided
		if($timeUnitArray == array())
		{
			return $default;
		}
		
		// Sort the time unit (e.g. "days_of_month") numerically
		sort($timeUnitArray);
		
		// Cycle through the list and return the next available time unit
		foreach($timeUnitArray as $val)
		{
			if($val == $trackerUnit)
			{
				return ($val + 0);
			}
			else if($val > $trackerUnit)
			{
				$update = true;
				return ($val + 0);
			}
		}
		
		return false;
	}
	
	
}