<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Confirm_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Confirm";
	public $title = "Confirmation Checker";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Creates confirmation links (for email, coupon codes, etc) and validates whether or not a confirmation link is valid.";
	
	public $data = array();
	
}