<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Network_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Network";
	public $title = "Network System";
	public $version = 1.00;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the site to connect to APIs, phpTesla sites, and the UniFaction sites.";
	
	public $data = array();
	
}