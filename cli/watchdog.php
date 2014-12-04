<?php

// crontab -e
// * * * * * php /var/www/system/cli/watchdog.php

// Check on HHVM's Status
$value = shell_exec("service hhvm status");

if(!strpos($value, "is running"))
{
	passthru("service hhvm restart");
}

// Check on nginx's Status
$value = shell_exec("service nginx status");

if(!strpos($value, "is running"))
{
	passthru("service nginx restart");
}

// Check on memcached's Status
$value = shell_exec("service memcached status");

if(!strpos($value, "is running"))
{
	passthru("service memcached restart");
}
