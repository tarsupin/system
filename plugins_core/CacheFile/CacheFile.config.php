<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class CacheFile_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "CacheFile";
	public $title = "File Caching Plugin";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Cache chunks of text (such as HTML) into files.";
	
	public $data = array();
	
}