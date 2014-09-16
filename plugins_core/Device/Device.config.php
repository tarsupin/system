<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/*
	Note: This plugin was derived from the work of Anthony Hand's "Mobile ESP"
	http://www.mobileesp.com
	
	More details in the plugin page.
*/

class Device_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Device";
	public $title = "Device Detection";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the identification of the device currently being used to browse UniFaction.";
	
	public $data = array();
	
}