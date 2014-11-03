<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

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
		mixed $hashtags			// <mixed> The hashtag (or hashtags) this widget uses to filter the appropriate content.
	,	array <int, str> $categories			// <int:str> The categories that this featured content widget will call from.
	,	int $slots = 3			// <int> The number of slots to show in featured content.
	,	int $totalViews = 200	// <int> The total number of views that this widget should show before refreshing.
	): void						// RETURNS <void>
	
	// FeaturedWidget::processWidget($hashtags, $categories, [$slots], [$totalViews]);
	{
		// Prepare Values
		$featPull = "widgets-featured-" . mt_rand(1, 3);
		$scanWidget = false;
		
		// Make sure the hashtag is an array
		if(!is_array($hashtags))
		{
			$hashtags = array($hashtags);
		}
		
		// Attempt to load the site's featured content data
		if($wfData = SiteVariable::load($featPull))
		{
			if(!isset($wfData['remaining-views']) or !$wfData['remaining-views'])
			{
				$scanWidget = true;
			}
			else
			{
				SiteVariable::save($featPull, "remaining-views", ($wfData['remaining-views'] - 1));
				
				$this->content = isset($wfData['html-content']) ? $wfData['html-content'] : '';
			}
		}
		else
		{
			$scanWidget = true;
		}
		
		if($scanWidget)
		{
			// There is no saved data, so we need to pull it from the widget sync
			$html = "";
			
			// Prepare the API Packet
			$packet = array(
				"hashtags"			=> $hashtags		// The top-tier hashtags to use for filtering purposes
			,	"categories"		=> $categories		// The categories that your widget will show
			,	"view_count"		=> $totalViews		// An optional setting; number of times it will be shown
			,	"number_slots"		=> $slots			// The number of entries to return (generally 2 or 3)
			);
			
			// Connect to the API and pull the response
			if($apiData = Connect::to("sync_widget", "FeaturedWidgetAPI", $packet))
			{
				// Prepare Values
				$widgetSync = URL::widget_sync_unifaction_com();
				
				$html .= '<div class="widget-wrap"><div class="widget-inner"><div class="widget-title">' . ucwords($apiData['verb'] . ' ' . $apiData['category']) . '</div>';
				
				foreach($apiData['widgetData'] as $widget)
				{
					$html .= '<div class="widget-featured"><div class="widget-featured-left"><a href="' . $widget['url'] . '"><img src="' . $widgetSync . '/assets/featured/' . $widget['id'] . '.jpg"></a></div><div class="widget-featured-right"><strong>' . $widget['title'] . '</strong><br />' . $widget['description'] . '</div></div>';
				}
				
				$html .= '</div></div>';
			}
			
			// Set the widget data's HTML content
			SiteVariable::save($featPull, "html-content", $html);
			
			// Set the widget data's count value (even if the pull failed)
			SiteVariable::save($featPull, "remaining-views", $totalViews);
			
			$this->content = $html;
		}
	}
	
	
}