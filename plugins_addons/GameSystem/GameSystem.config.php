<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class GameSystem_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "GameSystem";
	public $title = "Game System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A game system plugin used for canvas (javascript), AJAX, 2d games.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`slice_y` is segments of the y axis for query optimization. In other words, we can use "IN", such as with:
				slice_y IN (125, 126, 127) AND x BETWEEN 200 and 300
				
			`object` is the object at the location. If object_id is not set, this is a generic object.
			`object_type` is the type of object at the location. If object_id is not set, this is a generic object.
			`object_id` is a specific object with specific behavior (not generic).
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `world_map`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`map_id`				mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`slice_y`				smallint(5)					NOT NULL	DEFAULT '0',
			
			`x`						float(8,2)					NOT NULL	DEFAULT '0',
			`y`						float(8,2)					NOT NULL	DEFAULT '0',
			
			`object`				varchar(22)					NOT NULL	DEFAULT '',
			`object_type`			varchar(22)					NOT NULL	DEFAULT '',
			`object_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`map_id`, `slice_y`, `x`, `y`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			`is_blueprint` indicates if the map is designed to be placed like a blueprint into other maps.
			`is_persistent` indicates if it's an instance (0) that changes every load, or is persistent (1). 
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `maps`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			`description`			varchar(128)				NOT NULL	DEFAULT '',
			
			`is_blueprint`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`is_persistent`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			`id` is the ID of the object
			`data` is the serialized data containing instructions to build the object properly
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `object_data`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			`data`					text						NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			`flag_id` is the ID of the flag object, and will use the `object_data` table for building instructions.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `zone_flags`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`map_id`				mediumint(6)	unsigned	NOT NULL	DEFAULT '0',
			`slice_y`				smallint(5)					NOT NULL	DEFAULT '0',
			
			`x`						float(8,2)					NOT NULL	DEFAULT '0',
			`y`						float(8,2)					NOT NULL	DEFAULT '0',
			
			`flag`					varchar(22)					NOT NULL	DEFAULT '',
			`flag_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`map_id`, `slice_y`, `x`, `y`)
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
		return DatabaseAdmin::columnsExist("map_world_primary", array("map_id", "slice_y"));
	}
	
}
