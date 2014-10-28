<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Load the widgets contained in the "UniFactionMenu" container, if applicable
$widgetList = WidgetLoader::get("UniFactionMenu");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

// Draw the Left Panel
echo '
<!-- Side Panel -->
<div id="panel">';

echo '
<div id="panel-left">';

// Load the widgets contained in the "SidePanel" container
$widgetList = WidgetLoader::get("SidePanel");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}

echo '
</div> <!-- Panel Nav -->
</div>';