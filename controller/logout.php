<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// This script logs the user out of the system and redirects them to your home page.
Me::logout();

header("Location: " . URL::unifaction_com() . "/logout"); exit;