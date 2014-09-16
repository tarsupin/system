<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Sanitize_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Sanitize";
	public $title = "Sanitize Data and User Input";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Sanitizes data by running it through a pre-approved whitelisted set of characters.";
	
	public $data = array();
	
}