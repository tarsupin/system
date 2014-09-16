<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure you're logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/user-panel");
}

// Make sure the user is actually banned, otherwise people might freak out if they got here
if(Me::$clearance > -3)
{
	header("Location: /user-panel"); exit;
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

echo '
This account is currently banned from accessing this site.';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");
