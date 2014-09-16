<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Paypal_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "Paypal";
	public $title = "Paypal System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Handles Paypal transactions and APIs.";
	
	public $data = array();
	
}