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
		
		/*
			submitter_id		// the user that submitted this report
			mod_id				// the ID of the mod handling this report
			uni_id				// the uniID being targeted (if relevant)
			
			importance_level	// the level of importance of this report (0 = still open)
			
			action				// the action of this report, e.g. "Stickied Thread #1029"
			url					// the url to link to
			details				// any details that need to be added to the report
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `site_reports`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`submitter_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`mod_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`importance_level`		tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`action`				varchar(32)					NOT NULL	DEFAULT '',
			`url`					varchar(128)				NOT NULL	DEFAULT '',
			`details`				text						NOT NULL	DEFAULT '',
			
			`timestamp`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("site_reports", array("id", "action", "url", "timestamp"));
		$pass2 = DatabaseAdmin::columnsExist("site_user_warnings", array("uni_id", "warning_level"));
		
		return ($pass1 and $pass2);
	}
	
}