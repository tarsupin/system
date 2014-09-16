<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class File_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "File";
	public $title = "File Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to interact with files, such as to read or create them.";
	
	public $data = array();
	
}