<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Widget_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Widget";
	public $title = "Widget Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "The parent plugin for all widgets. Extends the core widget functionality.";
	
	public $data = array();
	
}