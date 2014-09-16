<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Timer_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Timer";
	public $title = "Timer Interval and Event System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a timer that returns timestamps of triggered events based on a chosen interval.";
	
	public $data = array();
	
}