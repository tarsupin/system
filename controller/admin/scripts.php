<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*

This page is used to run scripts on your site or server. You must have the appropriate system-wide script prepared at "SYS_PATH/system-script.php" for this page to function properly.

*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

if(SITE_HANDLE == "auth")
{
	echo '
	<a href="/admin/scripts/run-universe">Run Universal Script</a> (activates all sites on the entire universe system)<br />';
}

echo '
<a href="/admin/scripts/run-system">Run Server-Wide Script</a> (activates all sites on the server)<br />
<a href="/admin/scripts/local">Run Local Script on this site</a>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
