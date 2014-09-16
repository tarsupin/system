<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Migrate_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Migrate";
	public $title = "Migration and Updating System";
	public $version = 0.4;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to update plugins or systems to new versions, including changes to the database or file system.";
	
	public $data = array();
	
}