<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Plugin_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Plugin";
	public $title = "Plugin Handling System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides extra functionality for plugin handling, which includes dependencies, saving, and loading.";
	
	public $data = array();
	
}