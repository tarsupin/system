<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Ranking_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Ranking";
	public $title = "Ranking Algorithms";
	public $version = 0.4;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "An algorithm to determine an entry's popularity based on time spent live, votes, and user activity.";
	
	public $data = array();
	
}