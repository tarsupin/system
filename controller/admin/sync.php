<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*

This page is used to force the sync to run.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Run the Sync
Sync::run();

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display Page
var_dump(time());

$results = Database::selectMultiple("SELECT * FROM sync_tracker", array());

echo '
<table class="mod-table">
<tr>
	<td>Plugin</td>
	<td>Tracker</td>
	<td>Last Sync Time</td>
	<td>Delay</td>
</tr>';

foreach($results as $syncData)
{
	echo '
	<tr>
		<td>' . $syncData['plugin'] . '</td>
		<td>' . Time::fuzzy((int) $syncData['tracker_time']) . '</td>
		<td>' . Time::fuzzy((int) $syncData['sync_time']) . '</td>
		<td>' . $syncData['delay'] . '</td>
	</tr>';
}

echo '
</table>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
