<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AuthSync_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "AuthSync";
	public $title = "Auth Site Synchronizer";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to safely establish and syncronize your site to others.";
	
	public $data = array();
	
}