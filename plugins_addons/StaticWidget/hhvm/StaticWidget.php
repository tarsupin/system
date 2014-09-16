<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ Important note about Widgets ------
------------------------------------------

All widgets are instantiated and use "processWidget" as their constructor instead of __construct(). See "Widget" plugin for more details.


-------------------------------------------
------ About the StaticWidget Plugin ------
-------------------------------------------

This widget performs the base function of all widgets without any intelligence of its own. You tell it what HTML to load, and it loads that HTML exactly as you entered it.


-----------------------------------------------
------ Example of using the StaticWidget ------
-----------------------------------------------

// Create a new static widget
$staticWidget = new StaticWidget('<div class="static-widget">Here is some HTML content to save.</div>');

// Load the Static Widget into the WidgetLoader container
$staticWidget->load("SidePanel");

// Or, if you want to display the StaticWidget by itself:
echo $staticWidget->get();


-------------------------------
------ Methods Available ------
-------------------------------

$widget->processWidget($html);

*/


class StaticWidget extends Widget {
	
	
/****** Process the widget's behavior ******/
	public function processWidget
	(
		string $html			// <str> The HTML to load into the widget.
	): void					// RETURNS <void>
	
	// StaticWidget::processWidget($html);
	{
		$this->content = $html;
	}
	
	
}