<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Feed_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Feed";
	public $title = "Feed System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows sites to add content to a feed - only works on trusted sites.";
	
	public $data = array();
	
}