<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Make sure the database gets created
if($connected = Database::initialize("", $config['database']['admin-user'], $config['database']['admin-pass'], $config['database']['host'], $config['database']['type']))
{
	Database::exec("CREATE DATABASE IF NOT EXISTS `" . Sanitize::variable($config['database']['name']) . '`');
}
else
{
	Alert::saveError("Admin Issue", "There was an issue connecting to the admin user while configuring the application.");
	
	header("Location: /install/config"); exit;
}

// Check if the database exists
if($connected = Database::initialize($config['database']['name'], $config['database']['admin-user'], $config['database']['admin-pass'], $config['database']['host'], $config['database']['type']))
{
	Alert::success("Database Creation", "The database `" . $config['database']['name'] . "` is properly setup and configured.");
}
else
{
	Alert::error("Database Creation", "There was an error with creating the database `" . $config['database']['name'] . "`. Make sure the admin user has appropriate permissions.", 5);
}

// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" class="content-open">
' . Alert::display() . '

<h1>Installation: Database Setup</h1>

<h3>Step #1 - Make sure database is connected</h3>
<p>The application requires its own database. Once you have confirmed that the database exists, you can continue to the next step in the installation process.</p>';

if(!Alert::hasErrors())
{
	echo '
	<p style="color:green;">It looks like the database has been configured properly. You can proceed with the installation.</p>
	<p><a class="button" href="/install/plugins-core">Continue to next step</a></p>';
}
else
{
	echo '
	<p style="color:red;">
		There is an issue with the database. Please perform the following tasks to continue:
		<br />
		<br /> &nbsp; &nbsp; &bull; Make sure the database `' . $config['database']['name'] . '` is created.
		<br /> &nbsp; &nbsp; &bull; Make sure the admin user exists in the global-config.php file, and has proper permissions.
		<br /> &nbsp; &nbsp; &bull; Configure ' . dirname(SYS_PATH) . '/global-config.php properly
		<br /> &nbsp; &nbsp; &bull; Configure ' . APP_PATH . '/config.php properly
	</p>
	<p><a class="button" href="/install/setup-database">Re-test Database</a></p>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");