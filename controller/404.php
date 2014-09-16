<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Send the 404 Header
header("HTTP/1.1 404 Not Found");

// If there is an 404 page provided by the theme, run that
if(is_file(Theme::$dir . "/controller/404.php"))
{
	require(Theme::$dir . "/controller/404.php"); exit;
}

// If the default theme provides a 404 (but the active theme didn't), run the default 404
if(is_file(APP_PATH . "/themes/default/controller/404.php"))
{
	require(APP_PATH . "/themes/default/controller/404.php"); exit;
}

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Include a side panel (if available)
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display the Page
echo '
<div id="content" class="content-open">
' . Alert::display();

echo '
<h3>Page Not Found</h3>
<p>Oops! Looks like you\'ve discovered an inactive page. Not to worry! You can always <a href="/">find your way back home</a>!</p>';

echo '
</div>';

// Display Footer
require(SYS_PATH . "/controller/includes/footer.php");