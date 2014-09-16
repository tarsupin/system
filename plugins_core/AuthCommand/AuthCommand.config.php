<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AuthCommand_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "AuthCommand";
	public $title = "Receive Commands from UniFaction";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Runs a script after being commanded by UniFaction.";
	
	public $data = array();
	
}