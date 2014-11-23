<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Display the Navigation Bar
echo '
<body>

<!-- Content here gets displayed in the right panel, even with a dynamic AJAX loader -->
<div id="move-content-wrapper" style="display:none;"><script type="text/javascript" src="http://ap.lijit.com/www/delivery/fpi.js?z=272446&u=unifaction&width=300&height=250" async></script></div>

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
			<ul style="line-height:22px; min-width:180px;">
				<li><a href="' . URL::unifaction_social() . '/' . Me::$vals['handle'] . Me::$slg . '">My Unity Wall</a></li>
				<li><a href="' . URL::blogfrog_social() . '/' . Me::$vals['handle'] . Me::$slg . '">My BlogFrog</a></li>
				<li><a href="' . URL::unifaction_social() . '/friends' . Me::$slg . '">My Friends</a></li>
				<li><a href="' . URL::unijoule_com() . Me::$slg . '">My UniJoule</a></li>
				<li><a href="' . URL::inbox_unifaction_com() . Me::$slg . '">My Inbox</a></li>
				<li><a href="' . URL::profilepic_unifaction_com() . '/' . Me::$slg . '">Update Profile Pic</a></li>
				<li><a href="' . URL::unifaction_com() . '/user-panel">My Settings</a></li>
				<li><a href="' . URL::unifaction_com() . '/multi-accounts">Switch User</a></li>
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
	<div style="padding-top:60px;"></div>';

// Load the widgets contained in the "UniFactionMenu" container, if applicable
$widgetList = WidgetLoader::get("UniFactionMenu");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
	<div id="viewport-wrap">';