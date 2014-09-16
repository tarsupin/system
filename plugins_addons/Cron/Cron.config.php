<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Cron_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Cron";
	public $title = "Cron Task Handler";
	public $version = 0.1;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A system to activate and handle automated tasks, such as ones triggered by the server's cron process.";
	
	public $data = array();
	
}