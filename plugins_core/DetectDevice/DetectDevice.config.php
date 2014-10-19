<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*
	Note: This plugin was derived from the work of Serban Ghita's "Mobile-Detect"
	https://raw.githubusercontent.com/serbanghita/Mobile-Detect/
	
	More details in the plugin page.
*/

class DetectDevice_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "DetectDevice";
	public $title = "Device Detection";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to identify the device being used to browse UniFaction.";
	
	public $data = array();
	
}