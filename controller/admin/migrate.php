<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*
	/admin/migrate
	
	This page is used to run database migrations safely, in the proper sequence of versions.
	
	/config/migrate.php			<-- This is the file that we load migrations from. Edit this file.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Only the webmaster can access this page
if(Me::$clearance < 9)
{
	header("Location: /admin"); exit;
}

// Check if we ran the migration script
$allowMigration = false;

if($didMigrate = Link::clicked() and $didMigrate = "admin-db-migrate")
{
	$allowMigration = true;
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Run the migration if we chose to process it
if($allowMigration)
{
	if(File::exists(CONF_PATH . "/config/migrate.php"))
	{
		Database::initRoot();
		require(CONF_PATH . "/config/migrate.php");
	}
	else
	{
		echo '
		<div>A migration script does not exist for this site.</div>';
	}
}

// Ask user to confirm that they want to run the migration script
echo '
<div style="margin-top:40px;">
	<p style="color:red;">Please confirm that you would like to run the migration script:</p>
	<a class="button" href="/admin/migrate?' . Link::prepare("admin-db-migrate") . '">Run Migration</a>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
