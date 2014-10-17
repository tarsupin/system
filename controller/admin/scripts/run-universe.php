<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------------------
------ About the "System Script" Activation Page ------
-------------------------------------------------------

This script will activate the {SYS_PATH}/system-script.php file on every site that Auth knows.

*/

// Run Permissions & Header
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Prepare Values
set_time_limit(300);	// Set the timeout limit to 5 minutes.

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Run Global Script (if applicable)
if(isset($_GET['action']) and $_GET['action'] == "run")
{
	Database::initRoot();
	
	echo "Running Script:<br />";
	
	// Prepare Valuess
	define("PROTECTED", true);
	
	// Gather Sites
	$siteList = Database::selectMultiple("SELECT site_handle, site_name FROM network_data", array());
	
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
	
	echo "<br /><br />Script Complete.";
}
else
{
	echo '
	<p>Are you sure you want to have all sites run the system-wide script "{SYS_PATH}/system-script.php" on the entire universe system?</p>
	<p><a class="button" href="/admin/scripts/run-universe?action=run">Yes, run the script</a></p>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
