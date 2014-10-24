<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo '
</div> <!-- End "viewport-wrap" -->
</div> <!-- End "content-wrap" -->

<!-- Mobile Footer -->
<div id="mobile-footer">
	<ul id="mn-list">
		<li class="mn-link">
			<a href="javascript:toggleMenu();">Menu</a>
		</li>
		<li class="mn-link mobile-strict" style="padding-left:20px; padding-right:20px;">
			<a href="' . URL::unifaction_com() . '/mobile-menu' . Me::$slg . '">All</a>
		</li>
		<li class="mn-link mobile-strict">
			<a href="' . URL::unifaction_social() . Me::$slg . '">Social</a>
		</li>
	</ul>
</div>';

$uniCom = URL::unifaction_com();

// <a href="' . URL::promote_unifaction_com() . '">Advertise</a>

echo '
<!-- Standard Footer -->
<div class="spacer-giant"></div>
<div id="footer">
	<a href="' . $uniCom . '/contact' . Me::$slg . '">Contact</a> | <a href="' . $uniCom . '/faqs' . Me::$slg . '">FAQs</a> | <a href="' . $uniCom . '/privacy' . Me::$slg . '">Privacy</a> | <a href="' . $uniCom . '/user-panel/reports' . Me::$slg . '">Report</a> | <a href="' . $uniCom . '/acknowledgements' . Me::$slg . '">Thanks</a> | <a href="' . $uniCom . '/tos' . Me::$slg . '">TOS</a>
	<div id="footer-side-bar">
		<div class="ftbutton">
			<a id="notif-button" href="javascript:toggleNotifications();" style="color:#c0c0c0;"><span id="notif-count"></span> <span class="icon-circle-exclaim" style="font-size:22px; vertical-align:-10%;"></span></a>
		</div>
	</div>
</div>

<div id="footer-panel">
	<div id="notif-box" class="footer-display"></div>
</div>

</div> <!-- End "container" -->';

// Prepare JSEncrypt value
if(Me::$loggedIn)
{
	$jsEncrypt = Security::jsEncrypt(Me::$vals['handle']);
	
	echo '
	<script>var JSUser = "' . Me::$vals['handle'] . '"; var JSEncrypt = "' . $jsEncrypt . '";</script>';
}

echo Metadata::footer() . '
<script src="' . CDN . '/scripts/ajax.js" async></script>
<script src="' . CDN . '/scripts/unifaction.js" async></script>

</body>
</html>';