<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Connect to database with admin user
Database::initRoot();

// Load the appropriate installation page, if necessary:


// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display the Page
echo '
<div id="content" class="content-open">
' . Alert::display() . '

<h1>Installation: Plugins</h1>

<h3>Step #1 - Custom Installation</h3>

<p>This is the custom installation page for the `' . $config['site-name'] . '` application you are setting up.</p>

<br /><br /><a class="button" href="/install/complete">Continue with Installation</a>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");