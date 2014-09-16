<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Dir_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Dir";
	public $title = "Directory Handling";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to interact with directories, such as retrieving file lists.";
	
	public $data = array();
	
}