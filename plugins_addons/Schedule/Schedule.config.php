<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Schedule_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Schedule";
	public $title = "Scheduling System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A powerful scheduling tool that returns timestamps based on requested dates, days, times, etc.";
	
	public $data = array();
	
}