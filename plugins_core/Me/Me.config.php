<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Me_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Me";
	public $title = "Me - Active User Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Loads the active user's data on each page load and performs many handling tasks for the active user.";
	
	public $data = array();
	
}