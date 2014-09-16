<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the AdSponsored Plugin ------
------------------------------------------

This plugin allows a site to display and work with sponsored ads. This plugin will make use of the "Ad" plugin as its parent, thus automating several of its functions.


------------------------------------------
------ Example of using this plugin ------
------------------------------------------

To publish a sponsored advertisement on your site, you need to define an "Ad" slot.

	$ad = new AdSponsored("AwesomeAd", "awesome-ad", "This ad is pretty sweet, right?", $onMobile, $onTablet, $onDesktop);
	$ad->image(250, 250);
	
	echo $ad->adCode();

*/

class AdSponsored extends Ad {
	
	
/****** Plugin Variables ******/
	public bool $visible = false;		// <bool> TRUE if the ad is visible, FALSE otherwise.
	public int $adShown = 0;			// <int> The ID of the ad to show.
	
	
/****** Constructor ******/
	public function __construct
	(
		string $structure				// <str> The ad structure (which defines the ad type, size, and other rules)
	,	string $zone					// <str> The name of the ad zone.
	,	string $keyword = ""			// <str> The reference ID for the ad.
	,	string $description = ""		// <str> The description of the ad, so that others can identify what it's purpose is.
	,	bool $onMobile = false		// <bool> TRUE to run this ad on mobile devices.
	,	bool $onTablet = true		// <bool> TRUE to run this ad on tablet devices.
	,	bool $onDesktop = true		// <bool> TRUE to run this ad on desktop devices.
	,	bool $runAd = true			// <bool> TRUE if the ad is set to continue running (false to safely end over time).
	): void							// RETURNS <void>
	
	// $ad = new Ad($structure, $zone, [$keyword], [$description], [$onMobile], [$onTablet], [$onDesktop], [$runAd]);
	{
		// Prepare Values
		$this->structure = Sanitize::safeword($structure);
		$this->zone = Sanitize::variable($zone, "-");
		$this->keyword = Sanitize::variable($keyword, " -");
		$this->description = Sanitize::safeword($description, "?");
		
		// Set the device values (determines which devices it runs on)
		$this->onMobile = (bool) $onMobile;
		$this->onTablet = (bool) $onTablet;
		$this->onDesktop = (bool) $onDesktop;
		
		// Check if this ad is visible to the user
		switch(Me::$device)
		{
			// User is on a mobile device
			case 1:
				$this->visible = $this->onMobile ? true : false;
				break;
			
			// User is on a tablet
			case 2:
				$this->visible = $this->onTablet ? true : false;
				break;
			
			// User is on a desktop
			case 3:
				$this->visible = $this->onDesktop ? true : false;
				break;
		}
		
		// Load the advertising structure
		$this->loadStructure($this->structure);
		
		// Prepare the advertisement
		$this->prepare();
		
		// Run the ad code (so that it can be identified by the system)
		echo $this->adCode();
	}
	
	
/****** Prepare the advertisement ******/
	public function prepare (
	): bool						// RETURNS <bool> TRUE if the ad is prepared successfully, FALSE otherwise.
	
	// echo $ad->prepare();
	{
		// If this ad isn't visible to this user (such as not on the right device to view it), prepare fails.
		if(!$this->visible)
		{
			return false;
		}
		
		// Set the ad to invisible so that we can determine later if the ad should be visible (if ad was detected).
		$this->visible = false;
		
		// Prepare Values
		$audienceType = "all";
		
		// If the site has already determined that this is a premium view, set it to premium
		// This might happen, for example, with an ad being loaded on a blog by a user that is subscribed to it.
		if($this->premium)
		{
			$audienceType = "premium";
		}
		
		// Run a standard ad test to determine if the user is premium / highly targeted or qualified
		else if(Me::$loggedIn)
		{
			// Check if the user is a premium target based on the following conditions:
			// 1. If the premium mode is set to required, this automatically become a premium ad.
			// 1. If the user is actively following this reference ID (the category / section this ad is targeting)
			// 2. If the user is following the entire site, thus automatically containing the reference ID.
			if(isset(Me::$vals['follow'][$this->keyword]) or ($this->keyword == "" and isset(Me::$vals['follow_site'])))
			{
				$audienceType = "premium";
			}
			else
			{
				$audienceType = "user";
			}
		}
		
		// Find the optimal advertisement to run
		
		// Prepare Values
		$doLookup = false;
		
		// Run the ad cache check so that we can retrieve the data quickly
		$adHash = self::hash(SITE_HANDLE, $this->zone, $this->keyword, $audienceType);
		
		// Retrieve the cached ad (if one is cached)
		if($cachedAd = Database::selectOne("SELECT ad_id, views_remaining, COUNT(*) as totalnum FROM ads_sponsored_cache WHERE ad_hash=? LIMIT 1", array($adHash)))
		{
			// Recognize Integers
			$cachedAd['ad_id'] = (int) $cachedAd['ad_id'];
			$cachedAd['views_remaining'] = (int) $cachedAd['views_remaining'];
			$cachedAd['totalnum'] = (int) $cachedAd['totalnum'];
			
			// Clear the cache if we've run out of views available
			if($cachedAd['views_remaining'] <= 0)
			{
				// If we're clearing the cache and the total ads available are less than three, request more
				if($cachedAd['totalnum'] < 3)
				{
					$doLookup = true;
				}
				
				// Clear the cache
				Database::query("DELETE FROM ads_sponsored_cache WHERE ad_hash=? LIMIT 1", array($adHash));
				
				$cachedAd = array();
			}
		}
		
		// If we can't locate a cached ad, we need to provide one
		if(!$cachedAd or $doLookup)
		{
			// Prepare the SQL Checks
			$sqlZone = array($this->zone);
			$sqlKeyword = $this->keyword ? array($this->keyword, "") : array("");
			
			if($audienceType == "premium")
			{
				$sqlAudience = array("premium", "user", "all");
			}
			else if($audienceType == "user")
			{
				$sqlAudience = array("user", "all");
			}
			else
			{
				$sqlAudience = array("all");
			}
			
			list($sqlWhere, $sqlArray) = Database::sqlFilters(array("zone" => array($this->zone), "keyword" => $sqlKeyword, "audience_type" => $sqlAudience));
			
			// Retrieve any ads that can fit the criteria provided
			if($doLookup or !$adCacheList = Database::selectMultiple("SELECT * FROM ads_sponsored WHERE " . $sqlWhere . " ORDER BY bid_cpm DESC LIMIT 6", $sqlArray))
			{
				// There are no ads available on the site
				
				// Prepare the API packet that will identify which sponsored ads to pull
				$packet = array(
					'site'			=> SITE_HANDLE		// The site handle that is requesting sponsored ads.
				,	'zone'			=> $this->zone		// The ad zone that ads are being requested for.
				,	'keyword'		=> $this->keyword	// The keyword associated with the ad to check for.
				,	'audience'		=> $audienceType	// The audience type to retrieve ads for ("all", "user", "premium", etc).
				);
				
				// Pull available ads for this site (connect to ad system)
				if($sponsoredAds = Connect::to("promote", "SponsoredPull", $packet))
				{
					// Loop through ads and insert them into the system
					foreach($sponsoredAds as $ad)
					{
						Database::query("REPLACE INTO ads_sponsored (id, structure, zone, keyword, audience_type, ad_url, ad_image_url, ad_title, ad_body, bid_cpm, views_remaining) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($ad['id'], $this->structure, $this->zone, $ad['keyword'], $audienceType, $ad['ad_url'], $ad['ad_image_url'], $ad['ad_title'], $ad['ad_body'], $ad['bid_cpm'], $ad['views_remaining']));
					}
				}
				
				// Attempt to pull the ad cache list again (after the pull from the ad system)
				$adCacheList = Database::selectMultiple("SELECT * FROM ads_sponsored WHERE " . $sqlWhere . " ORDER BY bid_cpm DESC LIMIT 6", $sqlArray);
				
				// If there are still no ads, we need to load a unifaction ad
				if(!$adCacheList)
				{
					// Load a UniFaction Ad here
					
					
					
				}
			}
			
			// Load the available ads
			if($adCacheList)
			{
				$maxNum = count($adCacheList) - 1;
				$choose = mt_rand(0, mt_rand(floor($maxNum / 2), $maxNum));
				
				// Select the ad with a weighted value
				$adID = (int) $adCacheList[$choose]['id'];
				
				// Get 100 views, or as many as possible
				$viewCount = min(100, (int) $adCacheList[$choose]['views_remaining']);
				
				Database::startTransaction();
				
				// Remove the views remaining from the ad
				if($pass = Database::query("UPDATE ads_sponsored SET views_remaining=views_remaining-? WHERE id=? LIMIT 1", array($viewCount, $adID)))
				{
					// Set the Cached Ad into the database
					$pass = Database::query("REPLACE INTO ads_sponsored_cache (ad_hash, ad_id, views_remaining) VALUES (?, ?, ?)", array($adHash, $adID, $viewCount));
				}
				
				if(!Database::endTransaction($pass))
				{
					// Return empty if the necessary updates failed
					return false;
				}
				
				// Prepare the Cached Ad Variable
				$cachedAd = array(
					"ad_id"				=> (int) $adID
				,	"views_remaining"	=> (int) $viewCount
				);
			}
			
			// If no ad list was found, this preparation failed
			else
			{
				return false;
			}
		}
		
		// An ad was successfully detected, so we can set the ad to visible again
		$this->visible = true;
		$this->adShown = $cachedAd['ad_id'];
		
		return true;
	}
	
	
/****** Run the advertisement ******/
	public function run (
	): string						// RETURNS <str> The HTML for the advertisement.
	
	// echo $ad->run();
	{
		// If this ad is not visible, return only the ad code
		if(!$this->adShown) { return ""; }
		
		// Get the Data for the advertisement being displayed
		$adData = Database::selectOne("SELECT id, structure, ad_url, ad_image_url, ad_title, ad_body FROM ads_sponsored WHERE id=? LIMIT 1", array($this->adShown));
		
		// Recognize Integers
		$adData['id'] = (int) $adData['id'];
		
		// Update the cached views
		Database::query("UPDATE ads_sponsored_cache SET views_remaining=views_remaining-1 WHERE ad_id=? LIMIT 1", array($this->adShown));
		
		// Retrieve the structure of this ad
		$struct = Adstructure::load($adData['structure']);
		
		// Load the appropriate type of advertisement
		switch($this->type)
		{
			case "image":		return $this->runImageAd($adData, $struct);		break;
			case "text":		return $this->runTextAd($adData, $struct);		break;
		}
		
		// This ad didn't find an appropriate type - just return nothing
		return "";
	}
	
	
/****** Generate the URL that an advertisement is going to use ******/
	public function generateURL
	(
		int $adID		// <int> The ID of the advertisement.
	,	string $url		// <str> The URL that the advertisement is destined to arrive at.
	): string				// RETURNS <str> The URL to load the promote site.
	
	// $ad->generateURL($adID, $url);
	{
		return URL::promote_unifaction_com() . '/sponsored/go?d=' . urlencode(Encrypt::run("adURL", json_encode(array($adID, $url, Me::$id)), "fast"));
	}
	
	
/****** Return a full ad placement code ******/
	public function adCode (
	): string						// RETURNS <str> The ad code for this ad.
	
	// echo $ad->adCode();
	{
		// Only show this code if it was requested
		if(!isset($_GET['ruacd']))
		{
			return "";
		}
		
		$adShow = "";
		
		// Prepare Ad Code for Keyword
		if($this->keyword)
		{
			$adData = array(
				"structure"		=> $this->structure
			,	"site"			=> SITE_HANDLE
			,	"zone"			=> $this->zone
			,	"key"			=> $this->keyword
			,	"desc"			=> $this->description
			);
			
			$adShow = '<!--UniAd:' . json_encode($adData) . '-->';
		}
		
		// Show Ad Code for Full Site Options (not tailored to a keyword)
		$adData = array(
			"structure"		=> $this->structure
		,	"site"			=> SITE_HANDLE
		,	"zone"			=> $this->zone
		,	"desc"			=> $this->description
		);
		
		$adShow .= ' <!--UniAd:' . json_encode($adData) . '-->';
		
		// Display the Placement Code
		return $adShow;
	}
	
	
/****** Return a hash locator for ads ******/
	public static function hash
	(
		string $siteHandle			// <str> The site handle.
	,	string $zone				// <str> The ad zone.
	,	string $keyword = ""		// <str> The targeted keyword.
	,	string $audience = ""		// <str> The audience to target ("all", "user", "premium", etc).
	): string						// RETURNS <str> The ad hash to use for locating advertisements.
	
	// $adHash = Ad::hash($siteHandle, $zone, [$keyword], [$audience]);
	{
		return Security::hash($siteHandle . $zone . $keyword . $audience, 12, 62);
	}
	
	
/****** Run an image advertisement ******/
	private function runImageAd
	(
		array <str, mixed> $adData			// <str:mixed> An array that contains the data about this ad.
	,	array <str, mixed> $structure		// <str:mixed> An array that contains the structure data for the ad.
	): string					// RETURNS <str> The HTML for the advertisement.
	
	// echo $this->runImageAd($adData, $struct);
	{
		return '<a href="' . $this->generateURL($adData['id'], $adData['ad_url']). '" rel="nofollow"><img src="' . $adData['ad_image_url'] . '" style="width:' . $structure['img_width'] . 'px; height:' . $structure['img_height'] . 'px;"></a>';
	}
	
	
/****** Run a text advertisement ******/
	private function runTextAd
	(
		array <str, mixed> $adData			// <str:mixed> An array that contains the data about this ad.
	,	array <str, mixed> $structure		// <str:mixed> An array that contains the structure data for the ad.
	): string					// RETURNS <str> The HTML for the advertisement.
	
	// echo $this->runTextAd($adData, $struct);
	{
		return '<a href="' . $this->generateURL($adData['id'], $adData['ad_url']) . '" rel="nofollow" style="display:block; width:' . $structure['width'] . 'px; height:' . $structure['height'] . 'px;"><span style="display:block; color:#555555 !important; width:' . $structure['width'] . 'px; height:' . $structure['height'] . 'px; overflow:hidden; background-color:#ffffff; border:solid 1px #888888; border-radius:2px; line-height:120%; position:relative;"><div style="padding:5px;"><div style="color:#333333; font-weight:bold; margin-bottom:4px;">' . $adData['ad_title'] . '</div><div style="color:#555555; font-size:14px;">' . $adData['ad_body'] . '</div><div style="position:absolute; right:5px; bottom:2px; text-align:right; font-size:12px;">Follow this URL</div></div></span></a>';
	}
}