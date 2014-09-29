<?php

/**************************
****** Set Variables ******
**************************/

$phpTeslaDirectory = "/var/www";


/**************************
****** Validate Root ******
**************************/

// Get the active user
$activeUser = trim(shell_exec('whoami'));

// Make sure the user is root, or prevent further use
if($activeUser != "root")
{
	echo "You must be root to use this script.\n";
	exit;
}


/****************************
****** Get Environment ******
****************************/

// Run through the directory
if(is_dir($phpTeslaDirectory))
{
	passthru('chown www-data:www-data ' . $phpTeslaDirectory . ' -R');
}

// Scan the folders
$folders = scandir($phpTeslaDirectory);

foreach($folders as $folder)
{
	if(!is_dir($phpTeslaDirectory . "/" . $folder) or ($folder == "." or $folder == "..")) { continue; }
	
	echo "\nPulling from Git for: " . $phpTeslaDirectory . '/' . $folder;
	
	shell_exec("cd " . $phpTeslaDirectory . '/' . $folder . ' && git fetch --all && git reset --hard origin/master');
}

echo "\n";

exit;
