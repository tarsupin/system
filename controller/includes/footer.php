<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo '
</div> <!-- End "viewport-wrap" -->
</div> <!-- End "content-wrap" -->';

$uniCom = URL::unifaction_com();

echo '
<!-- Standard Footer -->
<div class="spacer-giant"></div>
<div id="footer">
	<ul id="footer-left">
		<li class="mobile-link"><a href="javascript:toggleMenu();">Menu</a></li>
		<li class="mobile-link"><a href="' . URL::unifaction_com() . '/mobile-menu' . Me::$slg . '">All Sites</a></li>
		<li class="mobile-link"><a href="' . URL::unifaction_com() . Me::$slg . '">Home</a></li>
	</ul>
	<div id="footer-middle">
		<a href="' . $uniCom . '/contact' . Me::$slg . '">Contact</a> | <a href="' . $uniCom . '/faqs' . Me::$slg . '">FAQs</a> | <a href="' . $uniCom . '/privacy' . Me::$slg . '">Privacy</a> | <a href="' . $uniCom . '/user-panel/reports' . Me::$slg . '">Report</a> | <a href="' . $uniCom . '/acknowledgements' . Me::$slg . '">Thanks</a> | <a href="' . $uniCom . '/tos' . Me::$slg . '">TOS</a>
	</div>
	<div id="footer-right">
		<div class="ftbutton">
			<a id="friend-button" href="javascript:toggleFriends();"><span id="friend-count">0</span> <span class="icon-group foot-icon"></span></a>
			<a id="notif-button" href="javascript:toggleNotifications();"><span id="notif-count">0</span> <span class="icon-circle-exclaim foot-icon"></span></a>
		</div>
	</div>
</div>

<div id="footer-panel">
	<div id="notif-box" class="footer-display"></div>
	<div id="friend-box" class="footer-display"></div>
</div>

</div> <!-- End "container" -->';

// Prepare JSEncrypt value
if(Me::$loggedIn)
{
	$jsEncrypt = Security::jsEncrypt(Me::$vals['handle']);
	$jsUser = Me::$vals['handle'];
	
	echo '
	<script>var JSUser = "' . $jsUser . '"; var JSEncrypt = "' . $jsEncrypt . '"; var JSChatTime = ' . microtime(true) . '; var JSProfilePic = "' . ProfilePic::image(Me::$id, "small") . '";</script>';
}

echo Metadata::footer() . '
<script src="' . CDN . '/scripts/ajax.js" async></script>
<script src="' . CDN . '/scripts/unifaction.js" async></script>

</body>
</html>';