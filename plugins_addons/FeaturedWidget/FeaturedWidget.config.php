<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class FeaturedWidget_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "widget";
	public $pluginName = "FeaturedWidget";
	public $title = "Featured Widget";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a widget that shows featured content, such as special articles or people to visit.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `featured_widget`
		(
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`widget_html`			text						NOT NULL	DEFAULT '',
			`views_remaining`		mediumint(5)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`hashtag`)
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
		return DatabaseAdmin::columnsExist("featured_widget", array("hashtag", "widget_html"));
	}
	
}