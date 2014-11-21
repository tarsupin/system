<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo str_replace("is-" . SITE_HANDLE, "is-" . SITE_HANDLE . " core-active", '
<div id="panel-core">
	<div id="core-nav">
	<div id="core-uni" class="hide-600"></div>
		<ul id="core-list">
			<li class="core-link is-universe"><a href="http://unifaction.test/' . Me::$slg . '"><span class="icon-globe core-icon"></span><span class="core-txt">Universe</span></a></li>
			<li class="core-link is-social"><a href="http://unifaction.social.test/' . Me::$slg . '"><span class="icon-group core-icon"></span><span class="core-txt">Unity</span></a></li>
			<li class="core-link is-my_uni"><a href="http://unifaction.me.test/' . Me::$slg . '"><span class="icon-globe core-icon"></span><span class="core-txt">My Uni</span></a></li>
			<li class="core-link is-hashtag"><a href="http://hashtag.test/' . Me::$slg . '"><span class="icon-tag core-icon"></span><span class="core-txt">Hashtags</span></a></li>
			<li class="core-link is-microfaction"><a href="http://community.microfaction.test/' . Me::$slg . '"><span class="icon-newspaper core-icon"></span><span class="core-txt">MicroFactions</span></a></li>
			<li class="core-link is-my_uni"><a href="http://chat.test/' . Me::$slg . '"><span class="icon-quote core-icon"></span><span class="core-txt">Chat Rooms</span></a></li>
			<li class="core-link is-blogfrog"><a href="http://blogfrog.social.test/' . Me::$slg . '"><span class="icon-pen core-icon"></span><span class="core-txt">BlogFrog</span></a></li>
			<li class="core-link is-credits"><a href="http://unijoule.test/' . Me::$slg . '"><span class="icon-coin core-icon"></span><span class="core-txt">UniJoule</span></a></li>
			<li class="core-link is-avatar"><a href="http://avatar.test/' . Me::$slg . '"><span class="icon-user core-icon"></span><span class="core-txt">Avatar</span></a></li>
			<li class="core-link"><a href="http://unifaction.test/games' . Me::$slg . '"><span class="icon-gamepad core-icon"></span><span class="core-txt">Games</span></a></li>
		</ul>
	</div>
</div>');
