<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class isSanitized_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "isSanitized";
	public $title = "Sanitation Tester";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Checks if input is sanitized without sanitizing it.";
	
	public $data = array();
	
}