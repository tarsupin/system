<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Photo_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Photo";
	public $title = "Photo Delivery and Handling";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows the site to process images based on the device size (requires javascript).";
	
	public $data = array();
	
}