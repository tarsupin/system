<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Testing_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Testing";
	public $title = "Integrity Testing System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Run integrity tests to ensure that plugins are operating the way you expect them to.";
	
	public $data = array();
	
}