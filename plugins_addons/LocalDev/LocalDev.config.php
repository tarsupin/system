<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class LocalDev_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "LocalDev";
	public $title = "Local Development Tools";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a local environment for fast updating to other environments.";
	
	public $data = array();
	
}