<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure you're logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/user-panel/reports");
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

// Retrieve APP Reports
if(File::exists(APP_PATH . "/controller/user-panel/reports/_include.php"))
{
	require(APP_PATH . "/controller/user-panel/reports/_include.php");
}

echo '
<a href="/user-panel/reports/user">Report a User (Spam, Behavior, etc.)</a><br />
<a href="/user-panel/reports/bug">Create a Bug Report</a><br />
<a href="/user-panel/reports/contact-mods">Contact Site Moderators</a>
';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");
