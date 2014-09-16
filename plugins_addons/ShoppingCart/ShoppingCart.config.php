<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class ShoppingCart_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "ShoppingCart";
	public $title = "Shopping Cart System";
	public $version = 0.4;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Allows tracking of a user's purchases in a cart until they want to checkout.";
	public $dependencies = array("Shop");
	
	public $data = array();
	
}