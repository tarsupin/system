<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Set the Installation Value to complete
SiteVariable::save("site-configs", "install-complete", 1);


// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" class="content-open">' . Alert::display();

// Display the Page
echo '
<h1>Installation Complete!</h1>

<h3>Your site is ready!</h3>
<p>Include more information here, such as information on what you should do now that the site is installed: a test to see if your code is up to date, access to our plugin and theme pages, recent news and updates, etc.</p>

<p><a class="button" href="/">Finish Installation</a></p>';

echo '
</form>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");