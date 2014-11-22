<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo '
<div style="padding-top:60px;"></div>
</div> <!-- End "viewport-wrap" -->
</div> <!-- End "content-wrap" -->';


// Display the Mobile Menu
echo '
<!-- Mobile Menu -->
<div id="mobile-menu" class="modal-bg" onclick="toggleMenu()" style="position:absolute; top:0px; left:0px; display:none; width:100%; z-index:500; height:100%;"><div style="padding:10px; z-index:600; margin-bottom:80px;">';

$widgetList = WidgetLoader::get("MobilePanel");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
</div></div>';

$uniCom = URL::unifaction_com();

// Footer
echo '
<!-- Standard Footer -->
<div id="footer">
	<ul id="footer-left">
		<li class="mobile-link"><a href="javascript:toggleMenu();">Menu</a></li>
		<li class="mobile-link"><a href="' . URL::unifaction_com() . '/mobile-menu' . Me::$slg . '">All Sites</a></li>
	</ul>
	<div id="footer-middle">
		<a href="' . $uniCom . '/contact' . Me::$slg . '">Contact</a> | <a href="' . $uniCom . '/faqs' . Me::$slg . '">FAQs</a> | <a href="' . $uniCom . '/privacy' . Me::$slg . '">Privacy</a> | <a href="' . $uniCom . '/user-panel/reports' . Me::$slg . '">Report</a> | <a href="' . $uniCom . '/acknowledgements' . Me::$slg . '">Thanks</a> | <a href="' . $uniCom . '/tos' . Me::$slg . '">TOS</a>
	</div>
	<div id="footer-right">
		<div id="ftbutton">
			<a id="friend-button" href="javascript:toggleFriends();"><span id="friend-count">0</span> <span class="icon-group foot-icon"></span></a>
			<a id="notif-button" href="javascript:toggleNotifications();"><span id="notif-count">0</span> <span class="icon-circle-exclaim foot-icon"></span></a>
			<a id="notif-button" href="javascript:toggleMyDisplay();"><span class="icon-settings foot-icon"></span></a>
		</div>
	</div>
</div>

<div id="footer-panel">
	<div id="notif-box" class="footer-display"></div>
	<div id="friend-box" class="footer-display"></div>
	<div id="myDisplay-box" class="footer-display"></div>
</div>
</div> <!-- End "container" -->';

echo Metadata::JSChat() . Metadata::footer() . '
<script src="' . CDN . '/scripts/unifaction.js" async></script>

</body>
</html>';