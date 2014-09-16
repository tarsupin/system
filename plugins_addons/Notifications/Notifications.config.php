<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Notifications_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Notifications";
	public $title = "Notification System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides a system to create and show notifications, as well as connect with UniFaction's Notification system.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		// Set the Notification Sync Tracker
		$pass1 = Sync::setTracker("Notifications", time(), 1, 120);
		
		return ($pass1);
	}
	
}