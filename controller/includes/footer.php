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
			<a href="' . URL::unifaction_com() . '/mobile-menu">All</a>
		</li>
		<li class="mn-link mobile-strict">
			<a href="' . URL::unifaction_social() . '">Social</a>
		</li>
	</ul>
</div>';

$uniCom = URL::unifaction_com();

echo '
<!-- Standard Footer -->
<div class="spacer-giant"></div>
<div id="footer">
	<div style="padding-top:8px;">
		<a href="' . $uniCom . '/faqs">FAQs</a> | <a href="' . $uniCom . '/tos">TOS</a> | <a href="' . $uniCom . '/privacy">Privacy</a> | <a href="' . $uniCom . '/acknowledgements">Thanks</a> | <a href="' . $uniCom . '/contact">Contact</a> | <a href="/user-panel/reports">Report</a> | <a href="' . URL::promote_unifaction_com() . '">Advertise</a>
	</div>
</div>

</div> <!-- End "container" -->

' . Metadata::footer() . '
<script src="' . CDN . '/scripts/ajax.js" async></script>
<script src="' . CDN . '/scripts/unifaction.js" async></script>

</body>
</html>';