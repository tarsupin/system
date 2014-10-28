<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// UniFaction Dropdown Menu
WidgetLoader::add("UniFactionMenu", 10, '
<div class="menu-wrap">
	<ul class="menu"><li class="menu-slot"><a href="#">News</a><ul><li class="dropdown-slot"><a href="#">World News</a></li><li class="dropdown-slot"><a href="#">US News</a></li></ul></li><li class="menu-slot"><a href="#">Entertainment</a><ul><li class="dropdown-slot"><a href="#">Articles &amp; News</a></li><li class="dropdown-slot"><a href="#">Gaming</a></li><li class="dropdown-slot"><a href="#">Sports</a></li><li class="dropdown-slot"><a href="#">The Nooch</a></li><li class="dropdown-slot"><a href="#">Recreational Activites</a></li></ul></li><li class="menu-slot"><a href="#">Life</a><ul><li class="dropdown-slot"><a href="#">Design</a></li><li class="dropdown-slot"><a href="#">Food</a></li><li class="dropdown-slot"><a href="#">Fashion</a></li><li class="dropdown-slot"><a href="#">Travel</a></li></ul></li></ul>
</div>');