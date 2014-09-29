<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ModuleSearch_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ModuleSearch";
	public $title = "Search Module";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides the standard search module for the content system.";
	public $dependencies = array("Content");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_search`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`hashtags`				varchar(255)				NOT NULL	DEFAULT '',
			
			UNIQUE (`content_id`),
			FULLTEXT (`hashtags`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This table is identical to content_search, and the one that is always being called from when working with
			the content panel.
			
			No searching is done from the draft list - that is reserved for the live version.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_search_draft`
		(
			`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`hashtags`				varchar(255)				NOT NULL	DEFAULT '',
			
			UNIQUE (`content_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			`archetype` is the article archetype ("person", "place", "object", etc)
			`filter_name` is the name of the filter ("Personality", "Description", etc)
			`filter_type` indicates what type of filter is being used (single-option, multi-option, range, etc)
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_search_filters`
		(
			`id`					smallint(5)		unsigned	NOT NULL	AUTO_INCREMENT,
			
			`archetype`				varchar(22)					NOT NULL	DEFAULT '',
			`filter_name`			varchar(22)					NOT NULL	DEFAULT '',
			
			`filter_type`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			UNIQUE (`archetype`, `filter_name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This is the table that tracks the individual options that each filter has.
			
			`keyword` is one of the available options for this filter ("high_intelligence", "charismatic", etc)
			`title` is the human-readable form of the keyword ("Highly Intelligent", "Charismatic", etc)
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `content_search_filter_opts`
		(
			`filter_id`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			
			UNIQUE (`filter_id`, `hashtag`)
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
		$pass1 = DatabaseAdmin::columnsExist("content_search", array("content_id", "hashtags"));
		$pass2 = DatabaseAdmin::columnsExist("content_search_draft", array("content_id", "hashtags"));
		$pass3 = DatabaseAdmin::columnsExist("content_search_filters", array("archetype", "filter_name"));
		$pass4 = DatabaseAdmin::columnsExist("content_search_filter_opts", array("filter_id", "hashtag"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4);
	}
	
}