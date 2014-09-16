<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class WorldDataCities_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "WorldDataCities";
	public $title = "World City Data";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Compiles a list of world city data and allows interaction with it.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `world_data_cities`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`country_code`			char(2)						NOT NULL	DEFAULT '',
			`region_code`			tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			`city`					varchar(60)					NOT NULL	DEFAULT '',
			`city_accents`			varchar(100)				NOT NULL	DEFAULT '',
			
			`population`			int(8)			unsigned	NOT NULL	DEFAULT '0',
			
			`postal_code`			varchar(20)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Need country and region for fulltext? Add these in first?
		// Set FULLTEXT on `city`
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `world_data_geolocation`
		(
			`city_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`latitude_round`		smallint(3)					NOT NULL	DEFAULT '0',
			`latitude`				float(6,3)					NOT NULL	DEFAULT '0.000',
			`longitude_round`		smallint(3)					NOT NULL	DEFAULT '0',
			`longitude`				float(6,3)					NOT NULL	DEFAULT '0.000'
			
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Set UNIQUE on `city_id` afterward
		// Set INDEX on `latitude_round`, `longitude_round`, `latitude`, `longitude`
		
		// Check if this installation was successful
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin is installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("world_data_cities", array("country_code", "city"));
	}
	
}
