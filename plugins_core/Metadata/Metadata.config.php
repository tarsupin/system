<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Metadata_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Metadata";
	public $title = "Script and Data Loader";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Injects scripts and / or data into specific areas of the page, most often the META tag.";
	
	public $data = array();
	
}