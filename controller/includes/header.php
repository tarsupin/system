<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Display the Navigation Bar
echo '
<body>
<div id="container">
<div id="header-wrap">
	<a href="' . URL::unifaction_com() . '"><img id="nav-logo" src="' . CDN . '/images/unifaction-logo.png" /></a>
	<div class="viewport-wrap">
		<nav id="nav-right">
			' . Search::searchEngineBar() . '
			<ul id="nav-list">';
			
			/*
				<li class="nav-link" id="nav-menu">
					<a href="">More &#x25BC;</a>
					<ul>
						<li><a href="#">Photoshop</a></li>
						<li><a href="#">Illustrator</a></li>
						<li><a href="#">Web Design</a></li>
					</ul>
				</li>
			*/
			//<li class="nav-icon"><a href=""><span class="icon-image"></span></a></li>
			
			// See the person that you're viewing
			if(You::$id && You::$id != Me::$id)
			{
				echo '
				<li id="viewing-user">
					<img id="viewuser-avi" class="circimg-small" src="' . ProfilePic::image(You::$id, "small") . '" /> <div id="viewuser-text"><span style="font-size:13px;">Viewing</span><br /><span style="font-size:13px;">' . You::$name . 'manguything</span></div>
				</li>';
			}
			
			// If you're logged in
			if(Me::$loggedIn)
			{
				echo '
				<li id="nav-user"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(Me::$id, "small") . '" /></a>
					<ul style="line-height:22px; min-width:150px;">
						<li><a href="/login?logAct=switch">Switch User</a></li>
						<li><a href="/logout">Log Out</a></li>
					</ul>
				</li>';
			}
			
			// If you're a guest
			else
			{
				echo '
				<li id="nav-user"><a href="#"><img id="nav-propic" class="circimg-small" src="' . ProfilePic::image(0, "small") . '" /></a>
					<ul style="line-height:22px; min-width:150px;">
						<li><a href="/login">Log In</a></li>
					</ul>
				</li>';
			}
			
			echo '
			</ul>
		</nav>
	</div>
</div>
<div class="spacer-giant"></div>

<div id="content-wrap"><div id="viewport-wrap">';