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
	)			// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		// Prepare a UniqueID
		return UniqueID::newCounter("benchmark-cycle");
	}
	
}