<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

if(!isset($extraColumns))
{
	$extraColumns = "";;
}

// Make sure a handle is provided
if(!You::$handle)
{
	You::$handle = isset(Me::$vals['handle']) ? Me::$vals['handle'] : '';
}

// Display the social menu
if(You::$handle)
{
	// UniFaction Dropdown Menu
	WidgetLoader::add("UniFactionMenu", 10, '
	<div class="menu-wrap hide-600">
		<ul class="menu">
			<li class="menu-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg  . '">@' . You::$handle . '</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/' . You::$handle . Me::$slg . '">Social Wall</a></li><li class="dropdown-slot"><a href="' . URL::fastchat_social() . '/' . You::$handle . Me::$slg . '">FastChat</a></li><li class="dropdown-slot"><a href="' . URL::blogfrog_social() . '/' . You::$handle . Me::$slg . '">BlogFrog</a></li></ul>
			
			' . $extraColumns . '
		</ul>
	</div>');
}