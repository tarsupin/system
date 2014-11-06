<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ Important note about Widgets ------
------------------------------------------

All widgets are instantiated and use "processWidget" as their constructor instead of __construct(). See "Widget" plugin for more details.


---------------------------------------------
------ About the FeaturedWidget Plugin ------
---------------------------------------------

This widget loads featured content from the Widget Sync site. It will pull data based on a designated hashtag and category that you specify - often the category is randomly chosen between several that you provide.

The hashtag that we provide with this widget is specific to the top-tier site style that you're visiting or wanting to show featured content for. For example, the NFL site would use the #NFL hashtag, thus loading all featured content related to the NFL. A roleplaying site would use the #Roleplaying hashtag, which would load featured content relating to roleplaying.

Keep in mind that we ONLY use top-tier hashtags (ones that will be associated with top-level sites). Otherwise, the amount of featured content would go drastically higher.

In addition to designating the primary hashtag, we also want to designate the categories that the featured content will show, which can include options such as "articles", "people", "purchases", "links", "sites", etc. These are fixed options.

Featured widget content appears in widgets that show something like "Interesting Articles", "Fun Articles", "Interesting People", "Fun Sites", and so forth. The "category" is the second word ("articles", "people", etc), while the "verb" is the first word ("interesting", "fun", etc). You can designate what categories to pull from.

The featured content will show two (usually) options from the category/verb combo that was selected (within the hashtag designated). So if "Interesting People" in the #NFL hashtag was the content being shown, you will see two people relating to the #NFL.


-------------------------------------------------
------ Example of using the FeaturedWidget ------
-------------------------------------------------

// Prepare the Featured Widget Data
$hashtag = "NFL";
$categories = array("articles", "people");

// Create a new featured content widget
$featuredWidget = new FeaturedWidget($hashtag, $categories);

// Load the Featured Widget into a WidgetLoader container
$featuredWidget->load("SidePanel");

// If you want to display the FeaturedWidget by itself:
echo $featuredWidget->get();


-------------------------------
------ Methods Available ------
-------------------------------

$widget->processWidget($hashtag, $categories);

*/


class FeaturedWidget extends Widget {
	
	
/****** Process the widget's behavior ******/
	public function processWidget
	(
		$hashtag				// <str> The hashtag that this widget uses to filter the appropriate content.
	,	$categories = array()	// <int:str> The categories that this featured content widget will call from.
	,	$slots = 3				// <int> The number of slots to show in featured content.
	,	$totalViews = 200		// <int> The total number of views that this widget should show before refreshing.
	)							// RETURNS <void>
	
	// FeaturedWidget::processWidget($hashtag, $categories, [$slots], [$totalViews]);
	{
		// Prepare Values
		$html = "";
		$scan = false;
		
		// Attempt to use an existing widget
		if($widgetData = Database::selectOne("SELECT widget_html, views_remaining FROM featured_widget WHERE hashtag=? LIMIT 1", array($hashtag)))
		{
			if($widgetData['views_remaining'] <= 0)
			{
				$scan = true;
			}
			else
			{
				$html = $widgetData['widget_html'];
			}
		}
		else 
		{
			$scan = true;
		}
		
		// Run the scan for new widget data if applicable
		if($scan)
		{
			// Prepare the API Packet
			$packet = array(
				"hashtag"			=> $hashtag			// The hashtag to use for filtering purposes
			,	"categories"		=> $categories		// The categories that your widget will show
			,	"view_count"		=> $totalViews		// An optional setting; number of times it will be shown
			,	"number_slots"		=> $slots			// The number of content slots to return (generally 2 or 3)
			);
			
			// Connect to the API and pull the response
			if($apiData = Connect::to("sync_widget", "FeaturedWidgetAPI", $packet))
			{
				// Prepare Values
				$widgetSync = URL::widget_sync_unifaction_com();
				
				$html .= '<div class="widget-wrap"><div class="widget-inner"><div class="widget-title">' . ucwords($apiData['verb'] . ' ' . $apiData['category']) . '</div>';
				
				foreach($apiData['widgetData'] as $widget)
				{
					$html .= '<div class="widget-featured"><div class="widget-featured-left"><a href="' . $widget['url'] . '"><img src="' . $widgetSync . '/assets/featured/' . ceil($widget['id'] / 1000) . '/' . $widget['id'] . '.jpg"></a></div><div class="widget-featured-right"><strong>' . $widget['title'] . '</strong><br />' . $widget['description'] . '</div></div>';
				}
				
				$html .= '</div></div>';
			}
			
			// Save this entry
			Database::query("REPLACE INTO featured_widget (hashtag, widget_html, views_remaining) VALUES (?, ?, ?)", array($hashtag, $html, $totalViews));
			
			// Prepare the next cycle
			$widgetData = array('widget_html' => $html, 'views_remaining' => $totalViews);
		}
		
		// Update the views remaining
		Database::query("UPDATE featured_widget SET views_remaining=views_remaining-1 WHERE hashtag=? LIMIT 1", array($hashtag));
		
		$this->content = $html;
	}
	
}
