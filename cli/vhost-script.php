<?php

/**************************
****** Set Variables ******
**************************/

$phpTeslaDirectory = "/var/www";
$nginxDirectory = "/etc/nginx";
$nginxSites = "/etc/nginx/sites-enabled";

$overwriteVirtualHosts = false;

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


/****************************
****** Get Environment ******
****************************/

$environment = "production";

echo "What environment is this for? (l = local, d = development, p = production) : ";

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

/**********************************
****** Install Virtual Hosts ******
**********************************/

if(!is_dir($nginxSites))
{
	echo 'The intended nginx directory "/etc/nginx/sites-enabled" doesn\'t exist.\n';
	
	exit;
}

// Find every configuration page on the server
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
		$domain = "";
		
		// Check for #production, #development, or #local (based on your set environment)
		if(strpos($line, "#" . $environment) !== false)
		{
			$pos = strpos($line, '= "');
			$pos2 = strpos($line, '";', $pos);
			
			$domain = substr($line, $pos + 3, $pos2 - $pos - 3);
			
			echo "\nConfiguring " . $domain . " virtual host for " . $environment . " server.";
		}
		
		// Configure the virtual host
		if($domain)
		{
			// Prepare the exact directory to use
			$uniDirectory = str_replace("/config.php", "", $configPage);
			
			// Prepare the contents to save to the virtual host directory
			$virtualHostContent = '
server {
	#listen   80; ## listen for ipv4; this line is default and implied
	#listen   [::]:80 default_server ipv6only=on; ## listen for ipv6
	
	server_name ' . $domain . ';
	root ' . $uniDirectory . ';
	index index.php;
	
	# Include HHVM configuration
	include /etc/nginx/hhvm.conf;
	
	location / {
		try_files \$uri /index.php?\$query_string;
	}
	
	location /doc/ {
			alias /usr/share/doc/;
			autoindex on;
			allow 127.0.0.1;
			allow ::1;
			deny all;
	}
}';
			
			// Make sure you're not overwriting the virtual host (unless you mean to)
			if($overwriteVirtualHosts == false && is_file($nginxSites . '/' . $domain))
			{
				echo "... (Already Exists!)";
			}
			else
			{
				passthru('echo "' . $virtualHostContent . '" > "' . $nginxSites . '/' . $domain . '"');
				
				echo "... done";
			}
		}
	}
}

echo "\n";

// Restart Nginx so that these virtual hosts take effect
passthru('service nginx restart');

exit;
