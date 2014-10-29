<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// For installations, we need to make sure that we're using the 3col CSS system
Metadata::$headerData = array('<link rel="stylesheet" href="' . CDN . '/css/unifaction-3col.css" />');

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