<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ Important note about Widgets ------
------------------------------------------

All widgets are instantiated and use "processWidget" as their constructor instead of __construct(). See "Widget" plugin for more details.


---------------------------------------------
------ About the HashtagWidget Plugin -------
---------------------------------------------

This widget can load the recent comments from a specific hashtag.


-------------------------------------------------
------ Example of using the HashtagWidget -------
-------------------------------------------------

// Prepare the Hashtag Widget Data
$showTrending = true;
$showHashtag = "NFL";

// Create a new widget
$hashtagWidget = new HashtagWidget($showTrending, $showHashtag);

// Load the widget into a WidgetLoader container
$hashtagWidget->load("SidePanel");

// If you want to display the widget by itself:
echo $hashtagWidget->get();


-------------------------------
------ Methods Available ------
-------------------------------

$widget->processWidget($showTrending, $showHashtag);

*/


class HashtagWidget extends Widget {
	
	
/****** Plugin Variables ******/
	public static int $refreshTrend = 1800;		// <int> The number of seconds before refreshing the trending tags.
	
	
/****** Process the widget's behavior ******/
	public function processWidget
	(
		bool $showTrending		// <bool> TRUE if we're showing the trending tags.
	,	string $showHashtag = ""	// <str> Set to the hashtag that you'd like to review / post here.
	): void						// RETURNS <void>
	
	// HashtagWidget::processWidget($showTrending, [$showHashtag]);
	{
		// Prepare Values
		$scanWidget = false;
		
		// Attempt to load the site's featured content data
		if($whData = SiteVariable::load("widgets-hashtag"))
		{
			if(!isset($whData['update-trending']) or $whData['update-trending'] < (time() - self::$refreshTrend))
			{
				$scanWidget = true;
			}
			else
			{
				$this->content = isset($whData['html-trending']) ? $whData['html-trending'] : '';
			}
		}
		else
		{
			$scanWidget = true;
		}
		
		if($scanWidget)
		{
			// There is no saved data, so we need to pull it from the widget sync
			$trendingHTML = "";
			
			// Prepare the API Packet
			$packet = array();
			
			// Connect to the API and pull the response
			if($tagList = Connect::to("hashtag", "TrendingWidgetAPI", $packet))
			{
				// Prepare Values
				$hashtagSite = URL::hashtag_unifaction_com();
				
				$trendingHTML .= '<div class="widget-wrap"><div class="widget-inner"><div class="widget-title">Trending Hashtags</div>';
				
				foreach($tagList as $tag)
				{
					$trendingHTML .= '
					<div class="widget-line"><a href="' . $hashtagSite . '/' . $tag . '" target="_new">#' . $tag . '</a></div>';
				}
				
				$trendingHTML .= '</div></div>';
			}
			
			// Set the widget data's HTML content
			SiteVariable::save("widgets-hashtag", "html-trending", $trendingHTML);
			/// SiteVariable::save("widgets-hashtag", "html-review", $reviewHTML);
			
			// Set the widget data's last update time
			SiteVariable::save("widgets-hashtag", "update-trending", time() - mt_rand(0, round(self::$refreshTrend / 10)));
			//SiteVariable::save("widgets-hashtag", "update-review", time());
			
			$this->content = $trendingHTML;
		}
	}
	
	
}