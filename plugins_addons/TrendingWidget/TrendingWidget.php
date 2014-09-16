<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ Important note about Widgets ------
------------------------------------------

All widgets are instantiated and use "processWidget" as their constructor instead of __construct(). See "Widget" plugin for more details.


---------------------------------------------
------ About the TrendingWidget Plugin -------
---------------------------------------------

This widget loads the trending hashtags from the Hashtag site, and updates it roughly every half hour or so (with some variance so that not all sites run it the same way).


-------------------------------------------------
------ Example of using the TrendingWidget -------
-------------------------------------------------

// Prepare the Trending Widget Data
$trendingCount = 6;

// Create a new widget
$trendingWidget = new TrendingWidget($trendingCount);

// Load the widget into a WidgetLoader container
$trendingWidget->load("SidePanel");

// If you want to display the widget by itself:
echo $trendingWidget->get();


-------------------------------
------ Methods Available ------
-------------------------------

$widget->processWidget($trendingCount);

*/


class TrendingWidget extends Widget {
	
	
/****** Plugin Variables ******/
	public static $refreshTrend = 1800;		// <int> The number of seconds before refreshing the trending tags.
	
	
/****** Process the widget's behavior ******/
	public function processWidget
	(
		$trendingCount		// <int> The number of trending tags to return.
	)						// RETURNS <void>
	
	// TrendingWidget::processWidget($$trendingCount);
	{
		// Prepare Values
		$scanWidget = false;
		
		// Attempt to load the site's featured content data
		if($whData = SiteVariable::load("widgets-trending"))
		{
			if(!isset($whData['update-time']) or $whData['update-time'] < (time() - self::$refreshTrend))
			{
				$scanWidget = true;
			}
			else
			{
				$this->content = isset($whData['html-content']) ? $whData['html-content'] : '';
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
			$packet = array(
				"count"		=> $trendingCount
			);
			
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
			SiteVariable::save("widgets-trending", "html-content", $trendingHTML);
			
			// Set the widget data's last update time
			SiteVariable::save("widgets-trending", "update-time", time() - mt_rand(0, round(self::$refreshTrend / 10)));
			
			$this->content = $trendingHTML;
		}
	}
	
	
}
