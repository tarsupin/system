<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Check if this is my handle
$mySocialMenu = (Me::$loggedIn ? ((You::$handle == Me::$vals['handle']) ? true : false) : false);

// Display your own personal social menu
if($mySocialMenu)
{
	$handle = Me::$vals['handle'];
	
	$uniMenu = '
	<li class="menu-slot social-menu"><a href="' . URL::unifaction_social() . '/' . $handle . Me::$slg  . '">@' . $handle . '</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . $handle . Me::$slg . '">My Social Wall</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . $handle . Me::$slg . '">My FastChat</a></li><li class="dropdown-slot"><a href="' . URL::blogfrog_social() . '/' . $handle . Me::$slg . '">My BlogFrog</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/friends' . Me::$slg . '">My Friends</a></li><li class="dropdown-slot"><a href="' . URL::chat_unifaction_com() . '/private-join' . Me::$slg . '">My Chat Rooms</a></li><li class="dropdown-slot"><a href="' . URL::unijoule_com() . '/' . Me::$slg . '">My UniJoule</a></li></ul>';
}

// Display the viewed user's social menu
else if(You::$handle)
{
	$uniMenu = '
	<li class="menu-slot social-menu"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg  . '">@' . You::$handle . '</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg . '">Social Wall</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg . '">FastChat</a></li><li class="dropdown-slot"><a href="' . URL::blogfrog_social() . '/' . You::$handle . Me::$slg . '">BlogFrog</a></li></ul>';
}