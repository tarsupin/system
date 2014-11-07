<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Display the Navigation Bar
echo '
<body>
<div id="container">
<div id="header-wrap">
	<a href="' . URL::unifaction_com() . '"><img id="nav-logo" src="' . CDN . '/images/unifaction-logo.png" /></a>
	<ul id="header-right">' .
		Search::searchEngineBar();
	
	// See the person that you're viewing
	if(You::$id && You::$id != Me::$id)
	{
		echo '
		<li id="viewing-user">
			<img class="circimg-small" src="' . ProfilePic::image(You::$id, "small") . '" /> <div><span style="font-size:13px;">Viewing</span><br /><span style="font-size:13px;">' . You::$name . '</span></div>
		</li>';
	}
	
	// If you're logged in
	if(Me::$loggedIn)
	{
		echo '
		<li id="login-menu"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(Me::$id, "small") . '" /></a>
			<ul style="line-height:22px; min-width:150px;">
				<li><a href="' . URL::unifaction_com() . '/multi-accounts' . Me::$slg . '">Change User</a></li>
				<li><a href="' . URL::profilepic_unifaction_com() . '/' . Me::$slg . '">Update Image</a></li>
				<li><a href="' . URL::unifaction_com() . '/user-panel' . Me::$slg . '">Settings</a></li>
				<li><a href="' . URL::unifaction_com() . '/logout">Log Out</a></li>
			</ul>
		</li>';
	}
	
	// If you're a guest
	else
	{
		echo '
		<li id="login-menu"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(0, "small") . '" /></a>
			<ul style="line-height:22px; min-width:150px;">
				<li><a href="/login">Log In</a></li>
				<li><a href="' . URL::unifaction_com() . '/register">Sign Up</a></li>
			</ul>
		</li>';
	}
	
	echo '
	</ul>
</div>';

// Load the Core Navigation Panel
require(SYS_PATH . "/controller/includes/core_panel_" . ENVIRONMENT . ".php");

echo '
<div id="content-wrap">
	<div style="padding-top:60px;">';

// Load the widgets contained in the "UniFactionMenu" container, if applicable
$widgetList = WidgetLoader::get("UniFactionMenu");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
	<div id="viewport-wrap">';