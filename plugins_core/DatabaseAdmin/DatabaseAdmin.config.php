<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class DatabaseAdmin_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "DatabaseAdmin";
	public $title = "Administrative Database Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to perform advanced, generally administrative tasks on the database.";
	
	public $data = array();
	
}