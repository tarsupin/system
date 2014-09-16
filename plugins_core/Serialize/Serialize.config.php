<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Serialize_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Serialize";
	public $title = "Serializing Tools";
	public $version = 0.7;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to read, package, unpackage, and minify serialized data.";
	
	public $data = array();
	
}