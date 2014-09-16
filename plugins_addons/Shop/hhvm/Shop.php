<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Shop {
	
	
/****** Class Variables ******/
	public static $purchaseType = "standard";		// "one-click", "standard", "cart"
	public static $percentOnSale = 40;				// The percent of things on sale at a time
	
	
/****** Generate `Shop` SQL ******/
	public static function sql()
	{
		/*
			This is the list of inventory that can be listed as active. The values here reflect the behavior of the
			item when listed, even though the inventory is dormant until it's added to `shop_listed`
			
			`quantity`				// the number of this item available (only matters if listed)
			`date_end`				// the date that this item will stop being available (only matters if listed)
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_inventory`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`url_slug`				varchar(32)					NOT NULL	DEFAULT '',
			
			`title`					varchar(32)					NOT NULL	DEFAULT '',
			`blurb`					varchar(250)				NOT NULL	DEFAULT '',
			
			`cost`					float(8,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`quantity`				smallint(5)		unsigned	NOT NULL	DEFAULT '0',
			
			`views_by_users`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`inspect_by_users`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`purchases`				mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`purchase_rate`			float(4,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`),
			UNIQUE (`url_slug`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This is the list of active items in the shop. Items only appear to the users if they are in this table.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_listed`
		(
			`category`				varchar(22)					NOT NULL	DEFAULT '',
			`inventory_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`category`, `inventory_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This is the list of items that are prepared to be randomly selected for insertion into the shop, generally
			when another item has disappeared and a new offer needs to take its place.
						
			When activated, this table attempts to trigger:
				MyShop::randomItemActivated($category, $inventoryID)
			
			`likelihood_weight`		// weighted value to choose how often this item is randomly selected for a slot
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_prepare_random`
		(
			`category`				varchar(22)					NOT NULL	DEFAULT '',
			
			`likelihood_weight`		tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			`inventory_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`category`, `inventory_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This is the list of items that are prepared to be added to the shop at specific times.
			When an item is added in this way, it generally gets added with a duration (and possibly a quantity).
			
			When activated, this table attempts to trigger:
				MyShop::timerItemActivated($category, $inventoryID)
			
			`date_add`				// the time when this item needs to be added to the shop
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_prepare_timer`
		(
			`category`				varchar(22)					NOT NULL	DEFAULT '',
			
			`date_add`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`inventory_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`category`, `inventory_id`),
			INDEX (`date_add`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This table adds a sale to items that are in the shop.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_sale`
		(
			`inventory_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`sale_percent`			float(3,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`sale_amount`			float(8,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`inventory_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This table tracks the categories (and sub-categories) in the shop. Not all shop need categories, since
			some are limited to a few items, but this provides a foundation for larger systems.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_categories`
		(
			`category`				varchar(22)					NOT NULL	DEFAULT '',
			`parent_category`		varchar(22)					NOT NULL	DEFAULT '',
			
			`url_slug`				varchar(22)					NOT NULL	DEFAULT '',
			
			`views_by_users`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`inspect_by_users`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`purchases`				smallint(6)		unsigned	NOT NULL	DEFAULT '0',
			
			`is_visible`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`is_accessible`			tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`category`),
			UNIQUE (`url_slug`),
			INDEX (`parent_category`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		/*
			This table tracks what inventory the user has viewed recently. This helps eliminate views that we don't
			want to track (for record keeping purposes).
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `shop_recent_views`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`inventory_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`date_viewed`		int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			INDEX (`uni_id`, `inventory_id`, `date_viewed`),
			INDEX (`date_viewed`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		// Show Tables
		DatabaseAdmin::showTable("shop_inventory");
		DatabaseAdmin::showTable("shop_listed");
		DatabaseAdmin::showTable("shop_prepare_random");
		DatabaseAdmin::showTable("shop_prepare_timer");
		DatabaseAdmin::showTable("shop_sale");
		DatabaseAdmin::showTable("shop_categories");
		DatabaseAdmin::showTable("shop_recent_views");
	}
	
	
/****** Create a shop category ******/
	public static function addShopCategory
	(
		string $category				// <str> The category name (and identifier).
	,	string $parentCategory = ""	// <str> The Parent Category of this category, if applicable.
	,	bool $isVisible = true		// <bool> TRUE if the category is visible (listed publicly).
	,	bool $isAccessible = true	// <bool> TRUE if the category is accessible.
	): bool							// RETURNS <bool> TRUE if created, FALSE if there was an error.
	
	// Shop::addShopCategory($category, $parentCategory, $isVisible, $isAccessible);
	{
		$urlSlug = substr(strtolower(str_replace(" ", "-", Sanitize::variable($category, " "))), 0, 22);
		
		// Check if this URL slug is already taken
		while(true)
		{
			if(!$check = Database::selectValue("SELECT url_slug FROM shop_categories WHERE url_slug=? LIMIT 1", array($urlSlug)))
			{
				break;
			}
			
			$urlSlug = substr(strtolower(str_replace(" ", "-", Sanitize::variable($title, " "))), 0, 16) . "-" . substr(base64_encode(md5(mt_rand(0, 99999999) . mt_rand(0, 99999999))), 0, 5);
		}
		
		return Database::query("INSERT INTO shop_categories (category, parent_category, url_slug, is_visible, is_accessible) VALUES (?, ?, ?, ?, ?)", array($category, $parentCategory, $urlSlug, ($isVisible ? 1 : 0), ($isAccessible ? 1 : 0)));
	}
	
	
/****** Create inventory that could be utilized in the shop ******/
	public static function createInventory
	(
		string $title				// <str> The title of the inventory.
	,	string $blurb				// <str> A quick blurb about the inventory.
	,	float $cost				// <float> The cost of the inventory (in credits).
	): bool						// RETURNS <bool> TRUE if created, FALSE if there was an error.
	
	// Shop::createInventory($title, $blurb, $cost);
	{
		$urlSlug = substr(strtolower(str_replace(" ", "-", Sanitize::variable($title, " "))), 0, 32);
		
		// Check if this URL slug is already taken
		while(true)
		{
			if(!$check = Database::selectValue("SELECT url_slug FROM shop_inventory WHERE url_slug=? LIMIT 1", array($urlSlug)))
			{
				break;
			}
			
			$urlSlug = substr(strtolower(str_replace(" ", "-", Sanitize::variable($title, " "))), 0, 26) . "-" . substr(base64_encode(md5(mt_rand(0, 99999999) . mt_rand(0, 99999999))), 0, 5);
		}
		
		return Database::query("INSERT INTO shop_inventory (url_slug, title, blurb, cost) VALUES (?, ?, ?, ?)", array($urlSlug, $title, $blurb, $cost));
	}
	
	
/****** Get Inventory Data ******/
	public static function getInventoryData
	(
		int $inventoryVar	// <int> or <str> The ID (or slug) of the inventory to get data on.
	,	string $columns = "*"	// <str> The columns to retrieve from the table.
	): array <str, mixed>					// RETURNS <str:mixed> data on the inventory.
	
	// $inventory = Shop::getInventoryData(<ID or UrlSlug>, [$columns]);
	{
		return Database::selectOne("SELECT " . Sanitize::variable($columns, " ,`*") . " FROM shop_inventory WHERE " . (is_numeric($inventoryVar) ? "id" : "url_slug") . "=? LIMIT 1", array($inventoryVar));
	}
	
	
/****** Get Inventory Data of listed items ******/
	public static function getCategoryItems
	(
		string $category		// <str> The category to get inventory data from the items in.
	,	string $columns = "*"	// <str> The columns to retrieve from the table.
	,	int $startPage = 1	// <int> The starting page of the pagination value.
	,	int $limit = 30		// <int> The number of items to return.
	): array <int, array<str, mixed>>					// RETURNS <int:[str:mixed]> data on the inventory.
	
	// $inventoryList = Shop::getCategoryItems($category, [$columns], [$startPos], [$limit]);
	{
		return Database::selectMultiple("SELECT " . Sanitize::variable($columns, " ,`*") . " FROM shop_listed sl INNER JOIN shop_inventory si ON sl.inventory_id=si.id WHERE sl.category=? LIMIT " . (($startPage - 1) * $limit) . ", " . ($limit + 0), array($category));
	}
	
	
/****** List an Inventory Item ******/
	public static function listInventoryItem
	(
		string $category				// <str> The category name (and identifier).
	,	int $inventoryID			// <int> The Inventory ID to list.
	): bool							// RETURNS <bool> TRUE if created, FALSE if there was an error.
	
	// Shop::listItem($category, $inventoryID);
	{
		// Make sure the inventory item exists
		if($inventory = Shop::getInventoryData($inventoryID, "id"))
		{
			return Database::query("INSERT INTO shop_listed (category, inventory_id) VALUES (?, ?)", array($category, $inventoryID));
		}
		
		return false;
	}
	
	
/****** List a random Inventory Item ******/
	public static function listRandomItem
	(
		string $category		// <str> The category to add a random item to.
	): bool					// RETURNS <bool> TRUE if item was added, FALSE if there was an error.
	
	// Shop::addRandomItem($category);
	{
		// Select the list of random items that can be added to this category
		if(!$inventoryID = (int) Database::selectValue("SELECT inventory_id FROM shop_prepare_random WHERE category=? ORDER BY (RAND() / likelihood_weight) LIMIT 1", array($category)))
		{
			return false;
		}
		
		// Attempt Triggers
		// MyShop::SOMEMETHODHERE($category, $inventoryID);
		
		// List the item in the shop
		return self::listInventoryItem($category, $inventoryID);
	}
	
	
/****** List timer inventories, if available ******/
	public static function listTimerItem
	(
		string $category			// <str> The category to add all timer items to.
	): bool						// RETURNS <bool> TRUE if an item was added, FALSE if not.
	
	// Shop::listTimerItems($category);
	{
		// Select the list of random items that can be added to this category
		if(!$inventoryID = (int) Database::selectValue("SELECT inventory_id FROM shop_prepare_random WHERE category=? ORDER BY (RAND() / likelihood_weight) LIMIT 1", array($category)))
		{
			return false;
		}
		
		// Attempt Triggers
		// MyShop::SOMEMETHODHERE($category, $inventoryID);
		
		// List the item in the shop
		return self::listInventoryItem($category, $inventoryID);
	}
	
	
/****** Add an inventory item to the random queue ******/
	public static function queueRandomItem
	(
		string $category				// <str> The category name (and identifier).
	,	int $inventoryID			// <int> The Inventory ID to list.
	,	int $weight = 100			// <int> The weight of the random item (100 is base).
	): bool							// RETURNS <bool> TRUE if added, FALSE otherwise.
	
	// Shop::queueRandomItem($category, $inventoryID, $weight = 100);
	{
		// Make sure the inventory item exists
		if(!$inventory = Shop::getInventoryData($inventoryID, "id"))
		{
			return false;
		}
		
		return Database::query("INSERT INTO shop_prepare_random (category, likelihood_weight, inventory_id) VALUES (?, ?, ?)", array($category, $weight, $inventoryID));
	}
	
	
/****** Add an inventory item to the timer queue ******/
	public static function queueTimerItem
	(
		string $category				// <str> The category name (and identifier).
	,	int $inventoryID			// <int> The Inventory ID to list.
	,	int $weight = 100			// <int> The weight of the random item (100 is base).
	): bool							// RETURNS <bool> TRUE if added, FALSE otherwise.
	
	// Shop::queueRandomItem($category, $inventoryID, $weight = 100);
	{
		// Make sure the inventory item exists
		if(!$inventory = Shop::getInventoryData($inventoryID, "id"))
		{
			return false;
		}
		
		return Database::query("INSERT INTO shop_prepare_random (category, likelihood_weight, inventory_id) VALUES (?, ?, ?)", array($category, $weight, $inventoryID));
	}
	
}