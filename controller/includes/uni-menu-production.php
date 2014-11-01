<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// UniFaction Dropdown Menu
WidgetLoader::add("UniFactionMenu", 10, '
<div class="menu-wrap hide-600">
	<ul class="menu">
		<li class="menu-slot"><a href="' . URL::unifaction_com() . Me::$slg  . '">Uni</a><ul><li class="dropdown-slot"><a href="' . URL::unifaction_com() . '/discover' . Me::$slg . '">Discover Uni</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_social() . '/friends' . Me::$slg . '">Friends</a></li><li class="dropdown-slot"><a href="' . URL::unifaction_com() . '/feed' . Me::$slg . '">My Feed</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::unifaction_community() . Me::$slg  . '">Communities</a><ul><li class="dropdown-slot"><a href="' . URL::avatar_unifaction_com() . Me::$slg  . '">Avatar</a></li><li class="dropdown-slot"><a href="' . URL::chat_unifaction_com() . Me::$slg  . '">Chat System</a></li></ul>
		
		<li class="menu-slot show-tablet"><a href="' . URL::unn_today() . Me::$slg  . '">News</a><ul><li class="dropdown-slot"><a href="' . URL::unn_today() . Me::$slg  . '">World News</a></li><li class="dropdown-slot"><a href="' . URL::unn_today() . '/USA' . Me::$slg . '">US News</a></li></ul>
		
		<li class="menu-slot"><a href="' . URL::entertainment_unifaction_com() . Me::$slg  . '">Entertainment</a><ul><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Books' . Me::$slg . '">Books</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Gaming' . Me::$slg . '">Gaming</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Movies' . Me::$slg . '">Movies</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Music' . Me::$slg . '">Music</a></li><li class="dropdown-slot"><a href="' . URL::entertainment_unifaction_com() . '/Shows' . Me::$slg . '">Shows</a></li><li class="dropdown-slot"><a href="' . URL::thenooch_org() . Me::$slg  . '">The Nooch</a></li></ul>
		
		</li><li class="menu-slot hide-800"><a href="' . URL::sports_unifaction_com() . Me::$slg  . '">Sports</a><ul><li class="dropdown-slot"><a href="' . URL::gotrek_today() . Me::$slg  . '">GoTrek</a></li></ul>
		
		</li><li class="menu-slot hide-800"><a href="' . URL::tech_unifaction_com() . Me::$slg  . '">Tech</a>
		</li><li class="menu-slot hide-1000"><a href="http://' . URL::science_unifaction_com() . Me::$slg  . '">Science</a>
		</li><li class="menu-slot hide-1000"><a href="' . URL::design4_today() . Me::$slg  . '">Design4</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::fashion_unifaction_com() . Me::$slg  . '">Fashion</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::travel_unifaction_com() . Me::$slg  . '">Travel</a>
		</li><li class="menu-slot hide-1200"><a href="' . URL::food_unifaction_com() . Me::$slg  . '">Food</a>
		
		</li><li class="menu-slot show-1200"><a href="' . URL::sports_unifaction_com() . Me::$slg  . '">More</a><ul><li class="dropdown-slot show-800"><a href="' . URL::sports_unifaction_com() . Me::$slg  . '">Sports</a></li><li class="dropdown-slot show-800"><a href="' . URL::gotrek_today() . Me::$slg  . '">GoTrek</a></li><li class="dropdown-slot show-800"><a href="' . URL::tech_unifaction_com() . Me::$slg  . '">Tech</a></li><li class="dropdown-slot show-1000"><a href="' . URL::science_unifaction_com() . Me::$slg  . '">Science</a></li><li class="dropdown-slot show-1000"><a href="' . URL::design4_today() . Me::$slg  . '">Design4</a></li><li class="dropdown-slot"><a href="' . URL::fashion_unifaction_com() . Me::$slg  . '">Fashion</a></li><li class="dropdown-slot"><a href="' . URL::travel_unifaction_com() . Me::$slg  . '">Travel</a></li><li class="dropdown-slot"><a href="' . URL::food_unifaction_com() . Me::$slg  . '">Food</a></li></ul>
		
	</ul>
</div>');

/*
Sports: Sports News, Sports Forums, Sports Galleries, More...
Tech: Tech Articles, Tech Forums, Gadgets, Programming, More...
Science: Health
Communities: Art Universe, Avatar, Chat System, Pet Competition, "Cool Stuff", Roleplaying, More...
*/