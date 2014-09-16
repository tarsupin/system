<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Draw the Side Panel
echo '
<!-- Side Panel -->
<div id="panel">';

// Load the Core Navigation Panel
require(SYS_PATH . "/controller/includes/core_panel_" . ENVIRONMENT . ".php");

echo '
<div id="panel-nav" style="display:none;"></div>
</div>';

// Allow the content to stretch over where the side navigation bar would be
echo '
<style>#content {margin-left:0px;}</style>';