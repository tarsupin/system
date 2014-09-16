<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo '
</div> <!-- End "viewport-wrap content-wrap" -->

<div id="mobile-footer">
	<ul id="mn-list">
		<li class="mn-link">
			<a href="javascript:toggleMenu();">Menu</a>
		</li>
	</ul>
</div>

' . Metadata::footer() . '

<script src="' . CDN . '/scripts/ajax.js" async></script>
<script src="' . CDN . '/scripts/unifaction.js" async></script>

</body>
</html>';