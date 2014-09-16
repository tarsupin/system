<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Captcha_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Captcha";
	public $title = "Captcha System";
	public $version = 0.7;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a captcha to help prove whether a user is real or not.";
	
	public $data = array();
	
}