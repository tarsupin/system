<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

$xQ = (Me::$loggedIn ? "?slg=" . Me::$id : "");

echo str_replace("is-" . SITE_HANDLE, "is-" . SITE_HANDLE . " core-active", '
<div id="panel-core">
	<div id="core-nav">
		<ul id="core-list">
			<li class="core-link is-auth"><a href="http://auth.unifaction.cool"><span class="icon-home core-icon"></span><span class="core-txt">Dashboard</span></a></li>
			<li class="core-link is-feed"><a href="http://feed.unifaction.me/' . $xQ . '"><span class="icon-document core-icon"></span><span class="core-txt">My Feed</span></a></li>
			
			<!-- My Uni -->
			
			<li class="core-link is-social"><a href="http://unifaction.social/' . $xQ . '"><span class="icon-group core-icon"></span><span class="core-txt">Social</span></a></li>
			<li class="core-link is-fastchat"><a href="http://fastchat.social/' . $xQ . '"><span class="icon-comments core-icon"></span><span class="core-txt">FastChat</span></a></li>
			<li class="core-link is-hashtag"><a href="http://hashtag.unifaction.com/' . $xQ . '"><span class="icon-tag core-icon"></span><span class="core-txt">Hashtags</span></a></li>
			
			<!-- MicroFaction -->
			
			<li class="core-link is-blogfrog"><a href="http://blogfrog.social/' . $xQ . '"><span class="icon-pen core-icon"></span><span class="core-txt">BlogFrog</span></a></li>
			<li class="core-link is-credits"><a href="http://unijoule.com/' . $xQ . '"><span class="icon-coin core-icon"></span><span class="core-txt">UniJoule</span></a></li>
			
			<!-- Avatar -->
			<!-- Games -->
			
		</ul>
	</div>
</div>');

/*
<li class="core-link is-auth"><a href="http://auth.unifaction.cool"><span class="icon-home core-icon"></span><span class="core-txt">Dashboard</span></a></li>
			<li class="core-link is-feed"><a href="http://feed.unifaction.me/' . $xQ . '"><span class="icon-document core-icon"></span><span class="core-txt">My Feed</span></a></li>';
			
			//<li class="core-link is-my_uni"><a href="http://unifaction.me/' . $xQ . '"><span class="icon-star core-icon"></span><span class="core-txt">My Uni</span></a></li>
			
			echo '
			<li class="core-link is-social"><a href="http://unifaction.social/' . $xQ . '"><span class="icon-group core-icon"></span><span class="core-txt">Social</span></a></li>
			<li class="core-link is-fastchat"><a href="http://fastchat.social/' . $xQ . '"><span class="icon-comments core-icon"></span><span class="core-txt">FastChat</span></a></li>
			<li class="core-link is-hashtag"><a href="http://hashtag.unifaction.com/' . $xQ . '"><span class="icon-tag core-icon"></span><span class="core-txt">Hashtags</span></a></li>';
			
			//<li class="core-link is-microfaction"><a href="http://unifaction.com/microfactions' . $xQ . '"><span class="icon-newspaper core-icon"></span><span class="core-txt">MicroFactions</span></a></li>
			
			echo '
			<li class="core-link is-blogfrog"><a href="http://blogfrog.social/' . $xQ . '"><span class="icon-pen core-icon"></span><span class="core-txt">BlogFrog</span></a></li>
			<li class="core-link is-credits"><a href="http://unijoule.com/' . $xQ . '"><span class="icon-coin core-icon"></span><span class="core-txt">UniJoule</span></a></li>';
			
			//<li class="core-link is-avatar"><a href="http://avatar.unifaction.com/' . $xQ . '"><span class="icon-user core-icon"></span><span class="core-txt">Avatar</span></a></li>
			//<li class="core-link"><a href="http://unifaction.com/games' . $xQ . '"><span class="icon-gamepad core-icon"></span><span class="core-txt">Games</span></a></li>
*/