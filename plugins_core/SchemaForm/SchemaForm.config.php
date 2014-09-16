<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SchemaForm_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "SchemaForm";
	public $title = "Schema Form Handler";
	public $version = 0.1;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows you to create and manage forms with the Schema system";
	public $dependencies = array("SchemaDefine");
	
	public $data = array();
	
}