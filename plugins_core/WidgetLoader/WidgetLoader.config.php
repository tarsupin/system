<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class WidgetLoader_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "WidgetLoader";
	public $title = "Widget Loader";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to load widgets and retrieve their content.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			This table does not get edited primarily by this loader, but rather by the other widgets that make use of
			it. Widgets will provide instructions (and editing) with their own configurations.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `widget_loader`
		(
			`container`				varchar(22)					NOT NULL	DEFAULT '',
			`widget`				varchar(22)					NOT NULL	DEFAULT '',
			`sort_order`			tinyint(2)		unsigned	NOT NULL	DEFAULT '99',
			
			`instructions`			text						NOT NULL	DEFAULT '',
			
			UNIQUE (`container`, `widget`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::tableExists("widget_loader");
	}
	
}