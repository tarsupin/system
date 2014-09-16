<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class ShoppingCart {
	
	
/****** Generate `ShoppingCart` SQL ******/
	public static function sql()
	{
		/*
			id				// Session saves this value to return to it
			auth_id			// If set to 0, we'll be relying on session alone
			uni_id			// Might be set to 0
			last_access		// This helps us purge old results
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `cart_list`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			`auth_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`last_access`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			INDEX (`last_access`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `cart_inventory`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`title`					varchar(72)					NOT NULL	DEFAULT '',
			`description`			text						NOT NULL	DEFAULT '',
			
			`cost`					float(6,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `cart_items`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			`cart_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`quantity`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 5;
		");
		
		// Show Tables
		DatabaseAdmin::showTable("cart_list");
		DatabaseAdmin::showTable("cart_items");
	}
	
	
/****** Create a Cart for a User ******/
	public static function createCart
	(
		$uniID				// <int> The UniID of the user to create a cart for.
	)						// RETURNS <mixed>
	
	// ShoppingCart::createCart($uniID);
	{
		return true;
	}
	
}
