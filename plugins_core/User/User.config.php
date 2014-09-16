<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class User_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "User";
	public $title = "User and Account Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Handles several important functions that deal with users, including registration and login.";
	
	public $data = array();
	
}