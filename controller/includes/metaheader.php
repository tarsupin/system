<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

echo '
<!DOCTYPE HTML>
<html>
<head>
	<base href="' . SITE_URL . '">
	<title>' . (isset($config['pageTitle']) ? $config['pageTitle'] : $config['site-name']) . '</title>
	
	<!-- Meta Data -->
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />' .
	(isset($config['active-hashtag']) ? '<meta id="activeHashtag" name="activeHashtag" content="' . $config['active-hashtag'] . '" />' : '') . '
	<link rel="icon" type="image/gif" href="/favicon.gif">
	<link rel="canonical" href="' . (isset($config['canonical']) ? $config['canonical'] : '/' . $url_relative) . '" />
	
	<!-- Primary Stylesheet -->
	<link rel="stylesheet" href="' . CDN . '/css/unifaction-base.css" />
	<link rel="stylesheet" href="/assets/css/style.css" />
	<link rel="stylesheet" href="/assets/css/icomoon.css" />
	
	<!-- Mobile Specific Metas -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	' . Metadata::header() . '
</head>';