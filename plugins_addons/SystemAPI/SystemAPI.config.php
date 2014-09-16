<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SystemAPI_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "SystemAPI";
	public $title = "System Execute API";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Executes system commands.";
	
	public $data = array();
	
}