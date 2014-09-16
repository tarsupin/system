<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class UserActivity_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "UserActivity";
	public $title = "Activity Measuring System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to keep track of and count online users and guests.";
	public $dependencies = array("Cache");
	
	public $data = array();
	
}