<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class IsSiteConnected_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "IsSiteConnected";
	public $title = "Site Connection Validator";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Simple tool to test if a site connection has been established successfully.";
	
	public $data = array();
	
}