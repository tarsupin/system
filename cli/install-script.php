<?php

/**************************
****** Set Variables ******
**************************/

$phpTeslaDirectory = "/var/www";


/******************************
****** Prepare Functions ******
******************************/

/****** Get input from the command line ******/
function getInput (
)				// RETURNS <str> a single character from input.

// $input = getInput();
{
	readline_callback_handler_remove();
	return readline();
}


/****** Get a single character input ******/
function getInputChar (
)				// RETURNS <str> a single character from input.

// $char = getInputChar();
{
	// Prepare Required Functions
	readline_callback_handler_install('', function() { });
	
	while(true)
	{
		$r = array(STDIN);
		$w = NULL;
		$e = NULL;
		$n = stream_select($r, $w, $e, 0);
		
		if($n && in_array(STDIN, $r))
		{
			$c = stream_get_contents(STDIN, 1);
			
			return $c;
		}
	}
}

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


/*******************************
****** Update Environment ******
*******************************/

// Make sure the global config file is in the appropriate location
if(!file_exists($phpTeslaDirectory . "/global-config.php"))
{
	echo "Unable to locate global-config.php. Please set the phpTeslaDirectory value to the proper directory.\n";
	exit;
}

$environment = "production";

echo "What environment is being used? (l = local, d = development, p = production) : ";

$char = getInputChar();

if($char == "d")
{
	echo "\nEnvironment: Development\n";
	$environment = "development";
}
else if($char == "l")
{
	echo "\nEnvironment: Local\n";
	$environment = "local";
}
else
{
	echo "\nEnvironment: Production\n";
}

// Get the contents of the global config file
$content = file_get_contents($phpTeslaDirectory . "/global-config.php");

// Change to the new environment
$content = str_replace('define("ENVIRONMENT", "local")', 'define("ENVIRONMENT", "' . $environment . '")', $content);
$content = str_replace('define("ENVIRONMENT", "development")', 'define("ENVIRONMENT", "' . $environment . '")', $content);
$content = str_replace('define("ENVIRONMENT", "production")', 'define("ENVIRONMENT", "' . $environment . '")', $content);

passthru('echo "' . str_replace(array('"', '$'), array('\"', '\$'), $content) . '" > "' . $phpTeslaDirectory . "/global-config.php" . '"');








exit; 

/************************
****** Create User ******
************************/

$username = "uni6user";

$contents = file_get_contents("/etc/passwd");

if(strpos($contents, $username . ":") !== false)
{
	echo 'The user "' . $username . '" already exists.\n';
}
else
{
	// Add the user
	passthru('useradd ' . $username);
	
	// Create the user's home
	passthru('mkdir /home/' . $username);
	
	// Set the user's empty SSH authorization keys
	passthru('mkdir /home/' . $username . '/.ssh');
	passthru('echo "" > /home/' . $username . '/.ssh/authorized_keys');
	
	echo 'The user "' . $username . '" has been created.\n';
}

// Add an SSH key to the user
// passthru('echo "' . $sshKey . '" >> /home/' . $username . '/.ssh/authorized_keys');


/*****************************
****** SSH Key Handling ******
*****************************/

$username = "uni6user";
$sshKey = "";

// Check if the SSH Key is already set
if(!$contents = file_get_contents('/home/' . $username . '/.ssh/authorized_keys'))
{
	passthru('echo "" > /home/' . $username . '/.ssh/authorized_keys');
}
else if(strpos($contents, $sshKey) !== false)
{
	return true;
}
