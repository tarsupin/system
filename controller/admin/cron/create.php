<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/admin/cron/create
	
	This page is used to create cron tasks.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Make sure that administrators are allowed
if(Me::$clearance < 8)
{
	header("Location: /admin"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Get Navigation Entry
echo '
<h4>What type of Cron Task would you like to create?</h4>

<br />	<a href="/admin/cron/custom-task">Custom Cron Task</a>
';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");

