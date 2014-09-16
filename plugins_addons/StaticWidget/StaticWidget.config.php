<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class StaticWidget_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "widget";
	public $pluginName = "StaticWidget";
	public $title = "Simple HTML Widget";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a static widget that outputs HTML that you enter.";
	
	public $data = array();
	
}