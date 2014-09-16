<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Hashtag_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Hashtag";
	public $title = "Hashtag Handler";
	public $version = 0.4;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Enables comments and other taggable content to be submitted as hashtags.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
		Network::setData("hashtag", $config['name'], "http://hashtag.test", $setKey);
		
		return ($pass and $pass2 and $pass3);
		*/
		
		return true;
	}
	
}