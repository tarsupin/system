<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AutoUpdate_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "AutoUpdate";
	public $title = "AutoUpdating System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides an auto-updater for the phpTesla system, plugins, sites, etc.";
	public $dependencies = array("SiteVariable");
	
	public $data = array();
	
}