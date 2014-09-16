<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SchemaView_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "SchemaView";
	public $title = "Schema View Handler";
	public $version = 0.1;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to view schemas.";
	public $dependencies = array("SchemaDefine");
	
	public $data = array();
	
}