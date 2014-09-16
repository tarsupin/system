<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Database_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Database";
	public $title = "Database Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to connect to and interact with the database.";
	
	public $data = array();
	
}