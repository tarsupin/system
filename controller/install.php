<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Destroy all cookies and sessions
Cookie::deleteAll();
session_destroy();
unset($_SESSION);
unset($_COOKIE);

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" class="content-open">' . Alert::display() . '

<h1>Installation</h1>
<p>Welcome to the phpTesla installer! This installer will walk you through the necessary steps to set up your application (your site).</p>

<h3>Helpful Notes</h3>
<p>Here are a few helpful pointers that may help you understand certain elements of installation:</p>

<p>
<strong>User Handles:</strong>
<br />A "handle" (or "user handle") is the username of one of your UniFaction users (profiles). Handles are often indicated by a "@" sign, such as "@joesmith1" or "@spiderman", and are important for referencing other users on UniFaction.

<br /><br />To access one of your user handles, log into your UniFaction account and click on one of your users. The user that you click on should tell you what your handle is.

<br /><br />The installation will prompt you for your administrator handle. The user you choose (by entering it\'s handle) will be granted admin rights to your site.

<br /><br /><strong>Site Handles:</strong>
<br />A "site handle" is different than a "user handle". You can think of it like referencing your site rather than a UniFaction user. Your site handle will allow UniFaction (and other sites connected to UniFaction) to identify your site and interact with it.

<br /><br />The installation will prompt you to choose a site handle. Your site handle must be at least seven characters long, and can only use letters, numbers, and underscores.

<br /><br /><strong>Environments:</strong>
<br />Environments are where your site is hosted. A "Production Environment" generally refers to the server that is hosting your site that everyone will see, while a "Development Environment" or "Local Environment" might refer to servers that only YOU have access to for testing and debugging purposes.

<br /><br />If you aren\'t sure why that\'s important, don\'t worry! Just leave your environment set as "Production" and you\'ll be fine :)
</p>

<p><a class="button" href="/install/config">Begin the Installation</a></p>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");

