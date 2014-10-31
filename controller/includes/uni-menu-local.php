<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// UniFaction Dropdown Menu
WidgetLoader::add("UniFactionMenu", 10, '
<div class="menu-wrap hide-600">
	<ul class="menu">
		<li class="menu-slot"><a href="' . URL::unifaction_com() . '">Uni</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_com() . '/discover">Discover Uni</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/friends">Friends</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_com() . '/feed">My Feed</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::unifaction_community() . '">Communities</a><ul><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_com() . '">Avatar</a></li><li class="dropdown-slot"><a href="' . URL::chat_unifaction_com() . '">Chat System</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::entertainment_unifaction_com() . '">Entertainment</a><ul><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Books">Books</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Gaming">Gaming</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Movies">Movies</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Music">Music</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Shows">Shows</a></li><li class="dropdown-slot"><a href="' . URL::thenooch_org() . '">The Nooch</a></li></ul>
		
		<li class="menu-slot show-tablet"><a href="' . URL::unn_today() . '">News</a><ul><li class="dropdown-slot"><a href="' . URL::unn_today() . '">World News</a></li><li class="dropdown-slot"><a href="' . URL::unn_today() . '/USA">US News</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::fashion_unifaction_com() . '">Life</a><ul><li class="dropdown-slot"><a href="' . URL::design4_today() . '">Design</a></li><li class="dropdown-slot"><a href="' . URL::fashion_unifaction_com() . '">Fashion</a></li><li class="dropdown-slot"><a href="' . URL::food_unifaction_com() . '">Food &amp; Recipes</a></li><li class="dropdown-slot"><a href="' . URL::travel_unifaction_com() . '">Travel</a></li></ul>
		
		<li class="menu-slot hide-800"><a href="' . URL::sports_unifaction_com() . '">Sports</a><ul><li class="dropdown-slot"><a href="' . URL::gotrek_today() . '">GoTrek</a></li></ul>
		
		<li class="menu-slot hide-1000"><a href="' . URL::tech_unifaction_com() . '">Tech</a></li><li class="menu-slot hide-1000"><a href="http://' . URL::science_unifaction_com() . '">Science</a></li>
	</ul>
</div>');

/*
Sports: Sports News, Sports Forums, Sports Galleries, More...
Tech: Tech Articles, Tech Forums, Gadgets, Programming, More...
Science: Health
Communities: Art Universe, Avatar, Chat System, Pet Competition, "Cool Stuff", Roleplaying, More...
*/