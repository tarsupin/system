<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Widget Plugin ------
-------------------------------------

This plugin extends the core functionality for widgets. All widgets extend this class as its parent.


-----------------------------------
------ Instantiating Widgets ------
-----------------------------------

It is important to know that all widgets use "processWidget" as their constructor method. For example, to instantiate "StaticWidget", run the following command:
	
	$widget = new StaticWidget("<p>Some HTML to add to this widget</p>");

If you review the StaticWidget class, you will see that it is using Widget::__construct(), which then uses StaticWidget::processWidget() as the constructor instead.

While this might seem unnecessary, this allows the database to load widgets dynamically with its own arguments by a combination of serialized data in the database and the call_user_func_array() call in the constructor. You can observe this behavior in the WidgetLoader plugin.


-----------------------------------------------
------ Structure and Behavior of Widgets ------
-----------------------------------------------

Widgets are fairly universal in their purpose. Ultimately, every widget just boils down to being a block of content (HTML) that gets displayed on the screen. To get there, it generally follows this path:

	1. The widget is instantiated and performs any necessary functions to determine what it should look like.
	
	2. The widget is added to a WidgetLoader container, e.g. $widget->load($container, [$sortOrder])
	
		a. WidgetLoader containers represent a particular location on the site, such as the side bar.
		
	3. The WidgetLoader displays the container, which shows each widget in its corresponding location (based on their sort orders).
	
	
Widgets can also be displayed directly, without needing to load them through the WidgetLoader. This can be used, for example, if there is only one widget that needs to be loaded in a certain area of the page.

For example, the forum might need a widget to show the active users on the site. If there aren't any other widgets that are going to be placed in that location, the widget could be output by itself.
	
	// Display a widget's contents directly
	echo $widget->get()

The only reason to have a WidgetLoader and containers is so that widgets can be sorted and organized more effectively, particularly if you are loading widgets from the database. It allows site admins to use the control panel to setup and position widgets in the containers designed in the code.


---------------------------
------ Using Widgets ------
---------------------------

Widgets are instantiated like any other class:
	
	$widget = new StaticWidget('<div class="static-widget">Here is some HTML content to save.</div>');
	
	
To directly modify the content of a widget, use the $content variable.
	
	$widget->content = '<div class="static-widget">I have decided to update the content of this widget.</div>';
	
	
Widgets can be added to the WidgetLoader like this:

	$containerName = "SidePanel";		// Assigns the widget to be set into the "SidePanel" container.
	$sortOrder = 10;					// Assigns the widget to be positioned higher than anything sorted at 11+
	
	$widget->load($containerName, $sortOrder);
	
	
You can load the widgets inside of a WidgetLoader like this:
	
	$containerName = "SidePanel";
	$widgetSlots = WidgetLoader::get($containerName);
	
	foreach($widgetSlots as $widgetContent)
	{
		echo "<div style="side-panel-entry">" . $widgetContent . "</div>";
	}
	
	
If you want to bypass the WidgetLoader and just retrieve the Widget output, you can run the ::get() method:
	
	echo $widget->get();
	
	
-------------------------------
------ Methods Available ------
-------------------------------

$widget->load($container, [$sortOrder]);
$content = $widget->get();

*/

class Widget {
	
	
/****** Widget Variables ******/
	public $content = "";		// <str> The HTML content of the widget.
	
	
/****** Construct the Widget ******/
# It may seem odd to construct the child widget with the "processWidget" method instead of its own constructor.
# However, this setup allows us to instantiate child widgets dynamically when loading from the database. You can see
# this behavior in action in the "WidgetLoader::loadWidgetsFromDatabase()" method.
	public function __construct (
						// (parameters that the child widget requires for building)
	)					// RETURNS <void>
	
	// $widget = new Widget([..$args], [..$args], ...);
	{
		// Make sure the child widget has the "processWidget" method
		if(method_exists($this, "processWidget"))
		{
			// Run the child widget's main processor
			call_user_func_array(array($this, "processWidget"), func_get_args());
		}
	}
	
	
/****** Load this widget into the WidgetLoader ******/
	public function load
	(
		$container			// <str> The HTML to load into the widget.
	,	$sortOrder = 99		// <int> The sort order to load this widget into.
	)						// RETURNS <void>
	
	// $widget->load($container, [$sortOrder]);
	{
		WidgetLoader::$slots[$container][$sortOrder][] = $this->content;
	}
	
	
/****** Retrieve the widget's content ******/
	public function get (
	)					// RETURNS <void>
	
	// $content = $widget->get();
	{
		return $this->content;
	}
	
	
}
