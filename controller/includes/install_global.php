<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

$installPage = (isset($url[1]) ? $url[1] : "");

// Main Installation Navigation
WidgetLoader::add("SidePanel", 10, '
<div class="panel-box">
	<ul class="panel-slots">
		<li class="nav-slot"><a href="/">Return Home<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "" ? " nav-active" : "") . '"><a href="/install">Welcome<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "config" ? " nav-active" : "") . '"><a href="/install/config">Site Configuration<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "connect-handle" ? " nav-active" : "") . '"><a href="/install/connect-handle">Site Admin<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "config-app" ? " nav-active" : "") . '"><a href="/install/config-app">App Configuration<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "setup-database" ? " nav-active" : "") . '"><a href="/install/setup-database">Database Setup<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "plugins-core" ? " nav-active" : "") . '"><a href="/install/plugins-core">Install Core Plugins<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "plugins-addon" ? " nav-active" : "") . '"><a href="/install/plugins-addon">Install Addon Plugins<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "plugins-app" ? " nav-active" : "") . '"><a href="/install/plugins-app">Install App Plugins<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "connect-auth" ? " nav-active" : "") . '"><a href="/install/connect-auth">Confirm Auth Key<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "app-custom" ? " nav-active" : "") . '"><a href="/install/app-custom">Custom App Install<span class="icon-circle-right nav-arrow"></span></a></li>
		<li class="nav-slot' . ($installPage == "complete" ? " nav-active" : "") . '"><a href="/install/complete">Installation Complete!<span class="icon-circle-right nav-arrow"></span></a></li>
	</ul>
</div>');
