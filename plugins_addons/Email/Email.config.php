<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Email_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Email";
	public $title = "Email System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "If your server can send emails, this allows you to send emails, email attachments, etc.";
	
	public $data = array();
	
}