<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Cookie_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Cookie";
	public $title = "Cookie Handling";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Creates and loads cookies to retain information across site visits, such as for \"Remember Me\" logins.";
	
	public $data = array();
	
}