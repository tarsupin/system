<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SiteReport_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "SiteReport";
	public $title = "Site Reporting Tools";
	public $version = 0.5;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a suite of reporting tools for the site, particularly important for moderators.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		// This table tracks warnings assigned to users, and associates it with a relevant report.
		Database::exec("
		CREATE TABLE IF NOT EXISTS `site_user_warnings`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`warning_level`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`report_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`, `warning_level`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(uni_id) PARTITIONS 3;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass = DatabaseAdmin::columnsExist("site_reports", array("id", "action", "url", "timestamp"));
		$pass2 = DatabaseAdmin::columnsExist("site_user_warnings", array("uni_id", "warning_level"));
		
		return ($pass and $pass2);
	}
	
}