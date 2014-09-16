<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------------------
------ About the "System Script" Activation Page ------
-------------------------------------------------------

File Path to System Script: {SYS_PATH}/system-script

This page will run a system script for each of the sites on the current server simultaneously. It does this by activating the "system-script" page on every site.

The server must have the "{SYS_PATH}/system-script" script prepared for this to function as expected.

*/

// Run Permissions & Header
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Only allow this function on AUTH
if(SITE_HANDLE != "auth")
{
	die("You must be on Auth to run this function.");
}

// Run Global Script (if applicable)
if(isset($_GET['action']) and $_GET['action'] == "run")
{
	Database::initRoot();
	
	echo "Running Script:<br />";
	
	// Prepare Valuess
	define("PROTECTED", true);
	$configList = array();
	$siteHandle = array();
	
	// Begin tracking sites on this server (by capturing their config file)
	foreach(glob(dirname(SYS_PATH) . "/*/config.php") as $filename)
	{
		$configList[] = $filename;
	}
	
	// Begin tracking sub-sites on the server (by capturing their config file)
	foreach(glob(dirname(SYS_PATH) . "/*/*/config.php") as $filename)
	{
		$configList[] = $filename;
	}
	
	// Capture each of the site handles in the config files
	foreach($configList as $file)
	{
		$fileContents = File::read($file);
		
		$siteHandle[] = Parse::between($fileContents, 'define("SITE_HANDLE", "', "\")");
	}
	
	// Make sure the system was able to collect the appropriate site handles
	if($siteHandle != array())
	{
		// Prepare the SQL for the Network Filter
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("site_handle" => $siteHandle));
		
		// Gather Sites
		$siteList = Database::selectMultiple("SELECT site_handle, site_name FROM network_data WHERE " . $sqlWhere, $sqlArray);
		
		foreach($siteList as $site)
		{
			$siteData = Network::get($site['site_handle']);
			$success = Connect::call($siteData['site_url'] . "/api/AuthCommand", "system-script", $siteData['site_key']);
			
			if($success)
			{
				echo "<br /><span style='color:green;'>" . $site['site_name'] . " run their instructions SUCCESSFULLY!</span>";
			}
			else
			{
				echo "<br /><span style='color:red;'>" . $site['site_name'] . " FAILED! Some or all of the instructions did not run as desired.</span>";
			}
		}
	}
	
	echo "<br /><br />Script Complete.";
}
else
{
	echo '
	<p>Are you sure you want to have all sites run the system-wide script "{SYS_PATH}/system-script.php" on the server?</p>
	<p><a class="button" href="/admin/scripts/run-system?action=run">Yes, run the script</a></p>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
