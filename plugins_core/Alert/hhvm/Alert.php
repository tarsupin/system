<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Alert Class ------
-----------------------------------

This class will track any messages, notices, or errors that you want to display throughout the page (or on a following page load). This is particularly helpful for form validation when error messages (or success messages) are very common. It can also be used for general information boxes and notices.

The alert boxes are wrapped with CSS tags, so you can customize their appearance on each site. You can run multiple alerts simultaneously, such as if there are multiple errors with a form (e.g. "short password", "must enter age", etc).


----------------------------------------------
------ Using Alerts for Form Validation ------
----------------------------------------------

Alerts play an essential role in form validation. If an error occurs during a form, such as the user not entering the proper information, then you can track this information with the Alert::error() method. If the form succeeds, you can pass along a success message to the next page load with Alert::saveSuccess().

Determining if the form has passed successfully or not can be done Alert::hasErrors(), as long as you have made sure that everything that can fail has been marked with an Alert::error() value. The FormValidate::pass() method uses the Alert class to identify if it has passed or not.


----------------------------------------
------ Quick Examples with Alerts ------
----------------------------------------
#!
// Will run any alerts that were saved on an earlier page load
echo Alert::display();

// Create some alerts
Alert::info("This page will provide you with some useful FAQs");
Alert::error("No Password", "You didn't enter a password.");
Alert::success("Lucky", "You found one of the site's easter eggs!");

// Retrieve the error by tag name and display it
echo Alert::getError("No Password");	// Will display the error

// Display all of the alerts (not just one of them)
echo Alert::display();

// Create an alert that won't load until the next page view
echo Alert::saveSuccess("Try reloading the page. You should see this appear :)");
##!

-------------------------------------------------
------ Example of using Alerts with a Form ------
-------------------------------------------------
#!
// If the form posts successfully
if(Form::submitted("this-form"))
{
	if($_POST['value'] == "YES")
	{
		// This alert will be saved and process on its next chance to display
		// That allows it to be loaded on the login-thanks page.
		Alert::saveSuccess("Said YES!", "You have successfully said YES!");
		
		header("Location: " . $_SERVER['REQUEST_URI'] . "?refresh=1"); exit;
	}
	else
	{
		Alert::error("Said Other", "Sorry, you didn't say YES. Please try again.");
	}
}

// Display any alerts on this page
echo Alert::display();

echo '
<form action="' . $_SERVER['REQUEST_URI'] . '">' . Form::prepare("this-form") . '
	<input type="text" name="value" value="YES" />
	<input type="submit" name="submit" value="Submit" />
</form>';
##!
-------------------------------
------ Methods Available ------
-------------------------------

// Display an alert on the SAME page
Alert::success([$name], $message);
Alert::error([$name], $message);
Alert::warning([$name], $message);
Alert::info([$name], $message);

// Displays an alert on the NEXT AVAILBLE alert display (often after you redirect to a new page)
Alert::saveSuccess([$name], $message);
Alert::saveError([$name], $message);
Alert::saveWarning([$name], $message);
Alert::saveInfo([$name], $message);

// Retrieve the message of a specific alert being tracked (based on the tag name)
Alert::getSuccess($name)
Alert::getError($name)
Alert::getWarning($name)

// Returns a list of messages (as an array)
Alert::getSuccesses()					
Alert::getErrors()
Alert::getWarnings()

// Returns TRUE if there are errors, FALSE if not
Alert::hasErrors()

// Displays all available alerts
// This is run on most pages, even those that don't cause alerts, since saved alerts can still process there.
Alert::display()

*/

abstract class Alert {
	
	
/****** Public Variables ******/
	
	// This is used to track debugging information that can be displayed to the admin
	public static $debuggingInfo = array();
	
	// Saves alerts in their respective array
	public static $successList = array();
	public static $warningList = array();
	public static $errorList = array();
	public static $infoList = array();
	
	
/****** Alert Methods ******/
# To avoid repetition of code, Alerts are called with dynamic names, including Alert::success(), Alert::error(), etc.
	public static function __callStatic
	(
		string $func		// <str> The name of the function being called
	,	$args		// [int:mixed] Additional arguments being passed
	): mixed				// RETURNS <mixed> value that depends on the function being called
	
	{
		switch($func)
		{
			case "error":
			case "warning":
			case "success":
			case "info":
				return self::addAlert($func, $args[0], $args[1], isset($args[2]) ? $args[2] : 0);
			
			case "saveError":
			case "saveWarning":
			case "saveSuccess":
			case "saveInfo":
				return self::addAlert(str_replace("save", "", strtolower($func)), $args[0], $args[1], isset($args[2]) ? $args[2] : 0, true);
			
			case "getSuccess":
			case "getError":
			case "getWarning":
				$func = str_replace("save", "", strtolower($func));
				$valName = $func . "List";
				return (isset(self::$valName[$args[0]]) ? self::$valName[$args[0]] : "");
		}
		
		return "";
	}
	
	
/****** Add an Alert ******/
	protected static function addAlert
	(
		string $type			// <str> The type of alert (success, error, warning, info, etc)
	,	string $key			// <str> The key name of the alert
	,	string $message		// <str> The alert message (e.g. "You successfully logged in!")
	,	int $severity = 0	// <int> Severity (0 to 10); 0 = don't track, 1 = harmless, 10 = obvious hacking attempt.
	,	bool $save = false	// <bool> TRUE will save this value for the next page load, FALSE is this page only.
	): bool					// RETURNS <bool> TRUE on success, FALSE if something goes wrong.
	
	// self::addAlert("info", "Page Navigation", "This page will teach you how to navigate!");
	// self::addAlert("error", "Filepath", "File paths do not allow null bytes.", 10);
	{
		// If the severity is greater than 0, this alert can be tracked by the system to learn more about it.
		// This may be useful for diagnosing potential threats, or seeing where problems are frequently occuring.
		if($severity > 0)
		{
			// Identify where the alert was called: the class, file, file line, etc.
			$backtrace = debug_backtrace();
			$origin = $backtrace[2];
			
			// Record the information discovered about the alert
			self::$debuggingInfo[] = array(
				$message
			,	isset($origin['class']) ? $origin['class'] . $origin['type'] : ""
			,	isset($origin['function']) ? $origin['function'] : ""
			,	isset($origin['args']) ? $origin['args'] : array()
			,	$origin['file']
			,	$backtrace[1]['line']
			,	$severity
			,	Me::$id
			);
			
			// If debug mode is verbose, display the alert information directly in the browser
			if(Debug::$verbose == 1 and Debug::$adminDisplay == false)
			{
				Debug::$adminDisplay = true;
				register_shutdown_function(array('Debug', 'run'));
			}
			
			// If ThreatTracker mode is set to logging, log these results in the database for later review
			if(ThreatTracker::$trackActivity == true and ThreatTracker::$minSeverity <= $severity)
			{
				// Prepare Values
				$function = (isset($origin['class']) ? $origin['class'] . $origin['type'] : "") . (isset($origin['function']) ? $origin['function'] : "");
				$params = isset($origin['args']) ? StringUtils::convertArrayToArgumentString($origin['args']) : "";
				
				// Log the threat
				ThreatTracker::log("activity", $severity, $message, array(), $function, $params, $origin['file'], $backtrace[1]['line']);
			}
		}
		
		// Now we can load the alert as intended for normal users:
		if($save == false)
		{
			// This alert is a regular alert, and will only load on this page:
			switch($type)
			{
				case "success":		self::$successList[$key] = $message;	break;
				case "warning":		self::$warningList[$key] = $message;	break;
				case "error":		self::$errorList[$key] = $message;		break;
				case "info":		self::$infoList[$key] = $message;		break;
				
				default: return false;
			}
			
			return true;
		}
		
		// This is a saved alert, so it will generally load on the next page (unless you don't display alerts there)
		// Filter out any invalid alert types
		if(!in_array($type, array("success", "warning", "error", "info")))
		{
			return false;
		}
		
		// Prepare the Session Variable
		if(!isset($_SESSION[SITE_HANDLE]['alert']))
		{
			$_SESSION[SITE_HANDLE]['alert'] = array($type => array());
		}
		else if(!isset($_SESSION[SITE_HANDLE]['alert'][$type]))
		{
			$_SESSION[SITE_HANDLE]['alert'][$type] = array();
		}
		
		// Save the Alert into the Session
		$_SESSION[SITE_HANDLE]['alert'][$type][$key] = $message;
		
		return true;
	}
	
	
/****** Get the full list of a desired Alert Type ******/
	public static function getSuccesses()	{ return $successList; }
	public static function getErrors()		{ return $errorList; }
	public static function getWarnings()	{ return $warningList; }
	
	
/****** Check if there are Errors ******/
# Note: This method can be used to identify if a form succeeds or not.
	public static function hasErrors (
	): bool		// RETURNS <bool> TRUE if there are error alerts, FALSE if not
	
	// if(!Alert::hasErrors()) { echo "There were no errors!"; }
	{
		return (self::$errorList == array() ? false : true);
	}
	
	
/****** Display Relevant Alerts ******/
	public static function display (
	): string		// RETURNS <str> HTML with divs that provide a simple display of relevant alert data
	
	// echo Alert::display();
	{
		// Check if there were any saved messages (passed by sesssion across pages)
		if(isset($_SESSION[SITE_HANDLE]['alert']))
		{
			$alertList = array("info", "error", "success", "warning");
			
			foreach($alertList as $alert)
			{
				if(isset($_SESSION[SITE_HANDLE]['alert'][$alert]))
				{
					foreach($_SESSION[SITE_HANDLE]['alert'][$alert] as $key => $val)
					{
						switch($alert)
						{
							case "info":		self::$infoList[$key] = $val;		break;
							case "success":		self::$successList[$key] = $val;	break;
							case "error":		self::$errorList[$key] = $val;		break;
							case "warning":		self::$warningList[$key] = $val;	break;
						}
					}
				}
			}
			
			unset($_SESSION[SITE_HANDLE]['alert']);
		}
		
		// If there are no alerts, return empty
		if(self::$errorList === array() && self::$successList === array() && self::$infoList === array() && self::$warningList === array())
		{
			return "";
		}
		
		// Display the Alert Box
		$html = '
		<div class="alert-box">';
		
		// Display Info Alerts
		foreach(self::$infoList as $key => $alert)
		{
			$html .= '
			<div class="alert-info">' . $alert . '</div>';
		}
		
		// Display Error Alerts
		foreach(self::$errorList as $key => $alert)
		{
			$html .= '
			<div class="alert-error">' . $alert . '</div>';
		}
		
		// Display Messages
		foreach(self::$successList as $key => $alert)
		{
			$html .= '
			<div class="alert-message">' . $alert . '</div>';
		}

		// Display Warnings
		foreach(self::$warningList as $key => $alert)
		{
			$html .= '
			<div class="alert-message">' . $alert . '</div>';
		}
		
		$html .= '
		</div>';
		
		return $html;
	}
}