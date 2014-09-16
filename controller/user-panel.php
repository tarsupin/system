<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*
	This user panel can be customized by creating a page at {APP_PATH}/controller/user-panel/index.php
	
	It can modify the $userPanel array as necessary. You can also run user-related functions by providing settings
	within the user-panel directory:
		{APP_PATH}/controller/user-panel/
*/

// Make sure you're logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/user-panel");
}

// Get Value
$unifaction = URL::unifaction_com();

// Load the Default User Panel Links
$userPanel = array(
	"User Panel"		=>	array(
		"File a Report"			=>	"/user-panel/reports"
	,	"Contact Us"			=>	$unifaction . "/contact-us"
	)
	
,	"Site Documents"	=>	array(
		"Terms of Service"		=>	$unifaction . "/docs/tos"
	,	"Privacy Policy"		=>	$unifaction . "/docs/privacy-policy"
	)
);

// Load the site-specific user-panel options, if available
if(File::exists(APP_PATH . "/controller/user-panel/index.php"))
{
	require(APP_PATH . "/controller/user-panel/index.php");
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

foreach($userPanel as $headerName => $list)
{
	echo '
	<div style="margin-top:20px;">
		<h4>' . $headerName . '</h4>';
	
	foreach($list as $name => $url)
	{
		echo '
		<div><a href="' . $url . '">' . $name . '</a></div>';
	}
	
	echo '
	</div>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");
