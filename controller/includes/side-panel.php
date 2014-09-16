<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Draw the Side Panel
echo '
<!-- Side Panel -->
<div id="panel">';

// Load the Core Navigation Panel
require(SYS_PATH . "/controller/includes/core_panel_" . ENVIRONMENT . ".php");

echo '
<div id="panel-nav">';

// Load the widgets contained in the "SidePanel" container
$widgetList = WidgetLoader::get("SidePanel");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
</div> <!-- Panel Nav -->
</div>';