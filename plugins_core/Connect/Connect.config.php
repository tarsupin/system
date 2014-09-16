<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Connect_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Connect";
	public $title = "API Connection Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to connect to other phpTesla APIs.";
	
	public $data = array();
	
}