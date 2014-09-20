<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure you're logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/notifications");
}

// Get Notifications
$globalNotes = Notifications::getGlobal();
$standardNotes = Notifications::get(Me::$id, true);

// Set User's Notifications to 0
if(Database::query("UPDATE users SET has_notifications=? WHERE uni_id=? AND has_notifications > ? LIMIT 1", array(0, Me::$id, 0)))
{
	// Remove the cache for the notification widget
	Cache::delete("noti:" . Me::$id);
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

echo '
<style>
.notices>h2 { padding:0px; margin-bottom:4px; }
.notices>h2:nth-child(n+2) { padding-top:24px; }
.notices>p { margin:0px; padding:5px 0 0 0; }
.notices>p:nth-child(even) { background-color:#c2d3e4; }
.notices>p:nth-child(odd) { background-color:#ddeeff; }
</style>

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
	if(!isset($standardNotes[$key - 1]) or $standardNotes[$key - 1]['category'] != $note['category'])
	{
		echo '
		<h2>' . $note['category'] . '</h2>';
	}
	
	if($note['url'] != "")
	{
		$note['message'] = '<a href="' . $note['url'] . '">' . $note['message'] . '</a>';
	}
	
	echo '
		<p>' . $note['message'] . ' (' . Time::fuzzy($note['date_created']) . ')</p>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");
