<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Display the viewed user's social menu
if(You::$handle)
{
	$uniMenu = '
	<li class="menu-slot social-menu"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg  . '">@' . You::$handle . '</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg . '">Unity Wall</a></li><li class="dropdown-slot"><a href="' . URL::inbox_unifaction_com() . '/to/' . You::$handle . Me::$slg . '">Send Message</a></li><li class="dropdown-slot"><a href="' . URL::blogfrog_social() . '/' . You::$handle . Me::$slg . '">BlogFrog</a></li><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_com() . '/view-wishlist/' . You::$handle . Me::$slg . '">Wish List</a></li><li class="dropdown-slot"><a href="' . URL::unicreatures_com() . '/' . You::$handle . Me::$slg . '">UC2 Visit Center</a></li></ul>';
}