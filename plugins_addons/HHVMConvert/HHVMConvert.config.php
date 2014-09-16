<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class HHVMConvert_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "HHVMConvert";
	public $title = "HHVM Conversion Tool";
	public $version = 0.9;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows conversion of plugins from standard PHP to HHVM automatically.";
	
	public $data = array();
	
}