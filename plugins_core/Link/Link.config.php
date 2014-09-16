<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Link_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Link";
	public $title = "Link and URL Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides link validation, which helps prevent mis-clicks, XSS, and resists automation.";
	
	public $data = array();
	
}