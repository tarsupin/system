<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Include a side panel (if available)
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display the Page
echo '
<div id="content" class="content-open">
' . Alert::display();

// Breadcrumb List
$bbase = "/user-panel";
$blength = count($url) - 1;

$bcrumb = ($blength > 0 ? '<a href="/user-panel">User Panel</a>' : "User Panel");

for($a = 1;$a < $blength;$a++)
{
	$bbase .= '/' . $url[$a];
	
	$bcrumb .= ' &gt; <a href="' . $bbase . '">' . ucfirst($url[$a]) . '</a>';
}

if($blength > 0)
{
	$bcrumb .= ' &gt; ' . ucfirst($url[$blength]);
}

echo '
<h3>' . $bcrumb . '</h3>';

