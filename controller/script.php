<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the "Script" page ------
-------------------------------------

This page is to run local scripts on the site.

*/

// Prepare Values
$getPass = "uni6scriptadm";

// Make sure the code is valid
if(!isset($_GET[$getPass]))
{
	exit;
}

// Prepare Values
$environment = "production";
$domainList = array();

// Load the script for the application
if(File::exists(APP_PATH . "/includes/script.php"))
{
	require(APP_PATH . "/includes/script.php");
}

$confList = shell_exec('find /var/www -name "config.php"');

$confList = explode("\n", trim($confList));

// Cycle through the list of configuration files detected
foreach($confList as $configPage)
{
	$configPage = trim($configPage);
	
	// Get the lines from the file
	$lines = file($configPage, FILE_IGNORE_NEW_LINES);
	
	// Cycle through the lines looking for certain markers
	foreach($lines as $line)
	{
		// Check for #production, #development, or #local (based on your set environment)
		if(strpos($line, "#" . $environment) !== false)
		{
			$pos = strpos($line, '= "');
			$pos2 = strpos($line, '";', $pos);
			
			$domainList[] = substr($line, $pos + 3, $pos2 - $pos - 3);
		}
	}
	
	sort($domainList);
}

echo '
<div style="font-size:1.1em;">';

// Loop through the domain list
foreach($domainList as $domain)
{
	echo '<a href="http://' . $domain . '/script?' . $getPass . '=1">' . $domain . '</a><br />';
}

echo '
</div>';