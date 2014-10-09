<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure you're logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/notifications");
}

// Get Notifications
$globalNotes = Notifications::getGlobal();
$standardNotes = Notifications::getFullList(Me::$id);

// Set User's Notifications to 0
if(Database::query("UPDATE users SET has_notifications=?, date_notes=? WHERE uni_id=? AND has_notifications > ? LIMIT 1", array(0, time(), Me::$id, 0)))
{
	// Remove the cache for the notification widget
	Cache::delete("noti:" . Me::$id);
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Display Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content" class="content-open">' . Alert::display();

echo '
<style>
.notices>h2:nth-child(n+2) { padding-top:24px; }
.notices>p { margin:0px; padding:2px 6px 2px 6px; }
.notices>p:nth-child(even) { background-color:#ccddff; }
.notices>p:nth-child(odd) { background-color:#ddeeff; }
</style>

<h2 style="margin-bottom:4px;">Notifications</h2>
<div class="notices">';

// Scan through Global Notifications
if(count($globalNotes) > 0)
{
	echo '
	<h2>Global Notifications</h2>';
	
	foreach($globalNotes as $key => $note)
	{
		if($note['url'] != "")
		{
			$note['message'] = '<a href="' . $note['url'] . '">' . $note['message'] . '</a>';
		}
		
		echo '
		<p>' . $note['message'] . ' (' . Time::fuzzy($note['date_created']) . ')</p>';
	}
}

// Scan through Standard Notifications
echo '
</div>
<div class="notices">';

foreach($standardNotes as $key => $note)
{
	if($note['url'] != "")
	{
		$note['message'] = '<a href="' . $note['url'] . '">' . $note['message'] . '</a>';
	}
	
	echo '
	<p>' . $note['message'] . ' (' . Time::fuzzy((int) $note['date_created']) . ')</p>';
}

echo '
</div>
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
