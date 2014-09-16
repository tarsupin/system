<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class You_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "You";
	public $title = "Visited User Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "An alias system for the user being actively visited, designed to quickly identify user relevancy.";
	
	public $data = array();
	
}