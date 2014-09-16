<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class TimeTracker_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "TimeTracker";
	public $title = "Time Tracking System";
	public $version = 0.8;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "An advanced time tracking class that automatically syncs dates, times, and timestamps.";
	
	public $data = array();
	
}