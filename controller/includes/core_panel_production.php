<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

$xQ = (Me::$loggedIn ? "?slg=" . Me::$id : "");

echo str_replace("is-" . SITE_HANDLE, "is-" . SITE_HANDLE . " core-active", '
<div id="panel-core">
	<div id="core-nav">
		<ul id="core-list">
			<li class="core-link is-auth"><a href="http://auth.phptesla.com"><span class="icon-user core-icon"></span><span class="core-txt">Dashboard</span></a></li>
			<li class="core-link is-social"><a href="http://social.phptesla.com/' . $xQ . '"><span class="icon-group core-icon"></span><span class="core-txt">Social</span></a></li>
			<li class="core-link is-fastchat"><a href="http://fastchat.phptesla.com/' . $xQ . '"><span class="icon-comments core-icon"></span><span class="core-txt">FastChat</span></a></li>
			<li class="core-link is-hashtag"><a href="http://hashtag.phptesla.com/' . $xQ . '"><span class="icon-tag core-icon"></span><span class="core-txt">Hashtags</span></a></li>
			<li class="core-link is-search"><a href="http://search.phptesla.com/' . $xQ . '"><span class="icon-globe core-icon"></span><span class="core-txt">Search</span></a></li>
			<li class="core-link is-inbox"><a href="http://inbox.phptesla.com/' . $xQ . '"><span class="icon-envelope core-icon"></span><span class="core-txt">Inbox</span></a></li>
			<li class="core-link is-blog_programming"><a href="http://programming.blog.phptesla.com/' . $xQ . '"><span class="icon-book core-icon"></span><span class="core-txt">BlogIt</span></a></li>
			<li class="core-link is-photo"><a href="http://photo.phptesla.com/' . $xQ . '"><span class="icon-image core-icon"></span><span class="core-txt">Photo</span></a></li>
			<li class="core-link is-culture"><a href="http://culture.phptesla.com/' . $xQ . '"><span class="icon-newspaper core-icon"></span><span class="core-txt">News</span></a></li>
			<li class="core-link is-forum_avatar"><a href="http://avatar.forum.test/' . $xQ . '"><span class="icon-newspaper core-icon"></span><span class="core-txt">Forums</span></a></li>
			<li class="core-link is-credits"><a href="http://credits.phptesla.com' . $xQ . '"><span class="icon-coin core-icon"></span><span class="core-txt">Credits</span></a></li>
			<li class="core-link is-avatar"><a href="http://avatar.test/' . $xQ . '"><span class="icon-tag core-icon"></span><span class="core-txt">Avatar</span></a></li>
			<li class="core-link"><a href="#/' . $xQ . '"><span class="icon-briefcase core-icon"></span><span class="core-txt">Jobs</span></a></li>
			<li class="core-link"><a href="#/' . $xQ . '"><span class="icon-gamepad core-icon"></span><span class="core-txt">Games</span></a></li>
		</ul>
	</div>
</div>');
