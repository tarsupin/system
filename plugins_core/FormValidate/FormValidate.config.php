<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class FormValidate_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "FormValidate";
	public $title = "Form Validation Tools";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a system to verify whether or not submitted input has been properly sanitized.";
	
	public $data = array();
	
}