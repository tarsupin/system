<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Form_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Form";
	public $title = "Form Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides form tools to provide confirmation, resist automation, prevent accidental refreshes, and more.";
	
	public $data = array();
	
}