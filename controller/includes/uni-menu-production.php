<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// UniFaction Dropdown Menu
WidgetLoader::add("UniFactionMenu", 10, '
<div class="menu-wrap hide-600">
	<ul class="menu">
		<li class="menu-slot"><a href="http://unifaction.com">Uni</a><ul><li class="dropdown-slot"><a href="http://unifaction.com/discover">Discover Uni</a></li><li class="dropdown-slot"><a href="http://unifaction.social/friends">Friends</a></li><li class="dropdown-slot"><a href="http://unifaction.com/feed">My Feed</a></li></ul>
		
		<li class="menu-slot"><a href="http://unifaction.community">Communities</a><ul><li class="dropdown-slot"><a href="http://avatar.unifaction.community">Avatar</a></li><li class="dropdown-slot"><a href="http://chat.unifaction.com">Chat System</a></li></ul>
		
		<li class="menu-slot"><a href="http://entertainment.unifaction.com">Entertainment</a><ul><li class="dropdown-slot"><a href="http://entertainment.unifaction.com/Books">Books</a></li><li class="dropdown-slot"><a href="http://entertainment.unifaction.com/Gaming">Gaming</a></li><li class="dropdown-slot"><a href="http://entertainment.unifaction.com/Movies">Movies</a></li><li class="dropdown-slot"><a href="http://entertainment.unifaction.com/Music">Music</a></li><li class="dropdown-slot"><a href="http://entertainment.unifaction.com/Shows">Shows</a></li><li class="dropdown-slot"><a href="http://thenooch.org">The Nooch</a></li></ul>
		
		<li class="menu-slot show-tablet"><a href="http://unn.today">News</a><ul><li class="dropdown-slot"><a href="http://unn.today">World News</a></li><li class="dropdown-slot"><a href="http://unn.today/USA">US News</a></li></ul>
		
		<li class="menu-slot"><a href="http://fashion.unifaction.com">Life</a><ul><li class="dropdown-slot"><a href="http://design4.today">Design</a></li><li class="dropdown-slot"><a href="http://fashion.unifaction.com">Fashion</a></li><li class="dropdown-slot"><a href="http://food.unifaction.com">Food &amp; Recipes</a></li><li class="dropdown-slot"><a href="http://travel.unifaction.com">Travel</a></li></ul>
		
		<li class="menu-slot hide-800"><a href="http://sports.unifaction.com">Sports</a><ul><li class="dropdown-slot"><a href="http://gotrek.today">GoTrek</a></li></ul>
		
		<li class="menu-slot hide-1000"><a href="http://tech.unifaction.com">Tech</a></li><li class="menu-slot hide-1000"><a href="http://science.unifaction.com">Science</a></li>
	</ul>
</div>');

/*
Sports: Sports News, Sports Forums, Sports Galleries, More...
Tech: Tech Articles, Tech Forums, Gadgets, Programming, More...
Science: Health
Communities: Art Universe, Avatar, Chat System, Pet Competition, "Cool Stuff", Roleplaying, More...
*/