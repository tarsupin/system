<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class DBTransfer_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "DBTransfer";
	public $title = "Database Data Transferring System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to copy or move data between tables on the database.";
	
	public $data = array();
	
}