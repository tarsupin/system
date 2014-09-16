<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

---------------------------------
------ About the Ad Plugin ------
---------------------------------

This plugin allows a site to display ads and promotions on their site. A good portion of the system can automate itself, but there are configurations that may need to be adjusted for each site.

To publish an advertisement on your site, you need to define an "Ad" slot.


---------------------
------ Ad Code ------
---------------------

Any ad that is being run will also provide an ad code on the location that it's being set. The ad code is an HTML comment that can be identified by scanning the URL's html for information about the ads available on the page. Each ad on the page returns a code with the following format:

	<!--UniAd:{"structure":"AdStructure", "site":"SiteHandle", "zone":"AdZoneName", "key":"TargetingKeyword", "desc":"Ad Description, such as Location on Page", "minCPM":{MIN_COSTS}}-->
	
The section after "UniAd:" is a JSON string with the appropriate data contained to return to the ad page.

The "{MIN_COSTS}" section provides an array of minimum costs for this particular ad slot.

In order to scrape these comments, the appropriate query string must be used to request them. This is done by calling "ruacd=1", or "Request UniAd Core Data". The strange acronym is to avoid any name collisions.


*/

class Ad {
	
	
/****** Plugin Variables ******/
	public $visitCPM = 0.45;		// <float> The minimum credit cost for 1000 views from a visitor.
	public $userCPM = 2.45;			// <float> The minimum credit cost for 1000 views from registered users.
	public $targetCPM = 4.95;		// <float> The minimum credit cost for 1000 views from targeted users.
	
	/*
		// Update the minimum CPM for image slots
		$this->visitCPM = 0.95;
		$this->userCPM = 4.95;
		$this->targetCPM = 9.95;
	*/
	
	public $structure = "";			// <str> The structure of ad, which defines the type of ad and its rules.
	public $type = "";				// <str> The type of ad ("text", "image", etc);
	public $zone = "";				// <str> The name of the ad zone.
	public $keyword = "";			// <str> The keyword associated with the ad (if applicable).
	public $description = "";		// <str> The description of the ad.
	
	public $premium = false;		// <bool> Set to TRUE if you've identified that this is a premium view.
	
	public $mobile = true;			// <bool> TRUE if this ad is displayed on mobile.
	public $desktop = true;			// <bool> TRUE if this ad is displayed on desktop.
	
	
/****** Load the ad structure ******/
	public function loadStructure
	(
		$structure		// <str> The structure to load.
	)					// RETURNS <void>
	
	// $ad->loadStructure($structure);
	{
		if($adDetails = AdStructure::load($structure))
		{
			$this->name = $adDetails['name'];
			$this->type = $adDetails['type'];
			$this->responsive = $adDetails['responsive'];
			
			if($this->type == "image")
			{
				$this->width = $adDetails['width'];
				$this->height = $adDetails['height'];
				$this->imgWidth = $adDetails['img_width'];
				$this->imgHeight = $adDetails['img_height'];
			}
		}
	}
}
