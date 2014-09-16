<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Translate_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Translate";
	public $title = "Translation System";
	public $version = 0.2;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows basic translation of languages, with the original language as a fallback.";
	
	public $data = array();
	
}