<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class APITrack_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "APITrack";
	public $title = "API Tracking System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Tracks the API usage for every API on the site.";
	public $dependencies = array("SchemaDefine");
	
	public $data = array();
	
}