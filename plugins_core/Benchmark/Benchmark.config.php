<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Benchmark_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Benchmark";
	public $title = "Benchmarking Toolkit";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows webmasters to benchmark their scripts and algorithms to optimize performance";
	public $dependencies = array("UniqueID");
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `log_benchmark`
		(
			`benchmark_cycle`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`key_group`				varchar(22)					NOT NULL	DEFAULT '',
			`key_subgroup`			varchar(22)					NOT NULL	DEFAULT '',
			`key_name`				varchar(22)					NOT NULL	DEFAULT '',
			
			`date_logged`			int(10)						NOT NULL	DEFAULT '0',
			
			`file_call`				varchar(32)					NOT NULL	DEFAULT '',
			`benchmark`				float(8,6)					NOT NULL	DEFAULT '0',
			
			UNIQUE (`benchmark_cycle`, `file_call`),
			INDEX (`key_group`, `key_subgroup`, `key_name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		UniqueID::newCounter("benchmark-cycle");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::tableExists("log_benchmark");
	}
	
}