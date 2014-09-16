<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the Site Variable, if SiteVarable plugin has been installed
$installComplete = false;

// Attempt to retrieve site configurations if the database is connected
if(Database::$database !== null)
{
	try
	{
		$installComplete = SiteVariable::load("site-configs", "install-complete");
	}
	catch (Exception $e)
	{
		// Do nothing
	}
}

// If the installation is complete, go to the completed page
if($installComplete !== false)
{
	header("Location: /install/complete"); exit;
}