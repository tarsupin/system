<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AdSponsored_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "AdSponsored";
	public $title = "Sponsored Ads";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a set of tools to display sponsored ads on a site.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`structure` contains the setup for the ad, including size and ad type (image, text, etc).
			`zone` is the name of the ad slot to place the ad in.
			`keyword` is the targeted section / keyword that this ad is associated with.
			`audience_type` is "all", "user", "premium", etc.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ads_sponsored`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`structure`				varchar(22)					NOT NULL	DEFAULT '',
			`zone`					varchar(22)					NOT NULL	DEFAULT '',
			`keyword`				varchar(22)					NOT NULL	DEFAULT '',
			`audience_type`			varchar(12)					NOT NULL	DEFAULT '',
			
			`ad_url`				varchar(100)				NOT NULL	DEFAULT '',
			`ad_image_url`			varchar(100)				NOT NULL	DEFAULT '',
			`ad_title`				varchar(42)					NOT NULL	DEFAULT '',
			`ad_body`				varchar(200)				NOT NULL	DEFAULT '',
			
			`bid_cpm`				float(4,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`views_remaining`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`id`),
			INDEX (zone, keyword, audience_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ads_sponsored_cache`
		(
			`ad_hash`				varchar(12)					NOT NULL	DEFAULT '',
			`ad_id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			`views_remaining`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`ad_hash`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if installed, FALSE if not
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("ads_sponsored", array("id", "zone", "keyword"));
		$pass2 = DatabaseAdmin::columnsExist("ads_sponsored_cache", array("ad_hash", "ad_id"));
		
		return ($pass1 and $pass2);
	}
	
}
