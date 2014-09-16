<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Text_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Text";
	public $title = "Text Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows text conversion to HTML, as well as provides security measures against unsafe text.";
	
	public $data = array();
	
}