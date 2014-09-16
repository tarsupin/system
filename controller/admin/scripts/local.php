<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Run Global Script (if applicable)
if(isset($_GET['action']) and $_GET['action'] == "run")
{
	// Provide the appropriate 
	define("PROTECTED", true);
	
	// Run the local script
	require(SYS_PATH . "/system-script.php");
	
	echo "You have run the system script locally.";
}
else
{
	echo '
	<p>Are you sure you want to run the system script for this site? This will run the "{SYS_PATH}/system-script" script on this site.</p>
	<p><a class="button" href="/admin/scripts/local?action=run">Yes, run the script</a></p>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
