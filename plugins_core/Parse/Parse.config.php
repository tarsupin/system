<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Parse_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Parse";
	public $title = "Parsing System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Tools for parsing through content to retrieve certain strings.";
	
	public $data = array();
	
}