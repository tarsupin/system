<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Run the Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(Theme::$dir . "/includes/metaheader.php");
require(Theme::$dir . "/includes/header.php");
require(Theme::$dir . "/includes/side-panel.php");

// Display the Page
echo '
<div id="content">' . Alert::display();

echo 'test';

echo '
</div>';

// Display the Footer
require(Theme::$dir . "/includes/footer.php");