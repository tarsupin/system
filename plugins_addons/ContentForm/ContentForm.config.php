<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ContentForm_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ContentForm";
	public $title = "Content Form Generator";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Creates a form and interpreter for the content block system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		return true;
	}
	
}