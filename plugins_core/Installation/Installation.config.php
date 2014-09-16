<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Installation_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Installation";
	public $title = "Installation System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides functionality that is required during the installation process.";
	
	public $data = array();
	
}