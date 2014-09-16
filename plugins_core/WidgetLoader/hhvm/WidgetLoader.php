<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the WidgetLoader Plugin ------
-------------------------------------------

This plugin prepares a container mechanism for other widgets to be added to and loaded in a designated order (generally the order that they were loaded by).

It also functions as a way to pull widgets from the database if you don't want to add the widgets in code. This can be advantageous for sites where the site administrators can change and move the widget placements around with the admin panel.


------------------------------------
------ Using the WidgetLoader ------
------------------------------------

The WidgetLoader has save and load any number of containers for the page. Widgets will insert themselves into a container with a syntax similar to the following:
	
	MyWidget::add($containerName);
	
This will add the widget into the designated container, where it can be loaded by the WidgetLoader.

There are two ways to load the WidgetLoader's contents:

	// Load the WidgetLoader's Contents by autogenerating the display
	echo Widget::display($containerName);
	
	// Load the WidgetLoader's Contents by looping with your own structure
	$widgetSlots = WidgetLoader::get($containerName);
	
	foreach($widgetSlots as $widgetContent)
	{
		echo "<div style="widget-class">" . $widgetContent . "</div>";
	}
	
If you want to load widgets from the database into the WidgetLoader, run the following line:
	
	WidgetLoader::loadWidgetsFromDatabase([$container]);
	
The optional $container parameter, if set, will mean that ONLY widgets from the $container container will be loaded. The rest will be untouched.


-----------------------------------------------
------ Loading Widgets from the Database ------
-----------------------------------------------

Some admins will want to have control over the widgets they put onto their site. If they aren't web developers and/or don't work with the code on the site, they cannot do this unless widgets are stored in the database so that they can interact with them.

Each widget may handle its interactions with the database and admin panel differently, but the general goal of the WidgetLoader when working with the database is to allow the admins to move the widgets into whatever order they want and to run the widgets with their own configurations like can be done in code.

Note that running modules through the database is not as efficient as coding them into the site (this only matters for very heavy-traffic sites), but it allows for much greater flexibility.

To load a set of widgets from the database, run the following command:

	WidgetLoader::loadWidgetsFromDatabase([$container]);
	
The above code will load any widgets that were set to the designated container. You can then retrieve the container's contents as usual.

Note: loading widgets from the database will NOT overwrite your widgets in code, so you can use both functions together.


-----------------------------------------------
------ Example of using the WidgetLoader ------
-----------------------------------------------

// Create three StaticWidget widgets and add them into the "SidePanel" container
$widget = new StaticWidget('<div>This widget will load IN THE MIDDLE.</div>');
$widget->load("SidePanel", 30);

$widget = new StaticWidget('<div>This widget will load FIRST.</div>');
$widget->load("SidePanel", 10);

$widget = new StaticWidget('<div>This widget will load LAST.</div>');
$widget->load("SidePanel", 50);

// Load the widgets contained in the "SidePanel" container
$widgetList = WidgetLoader::get("SidePanel");

foreach($widgetList as $widgetContent)
{
	echo $widgetContent;
}


----------------------------------------------
------ Add Static Entry to WidgetLoader ------
----------------------------------------------

If you are running a heavy traffic site that needs to squeeze every possible opportunity for optimization, you can load widgets statically into the WidgetLoader without instantiating a new widget. It works like this:
	
	WidgetLoader::add($container, $sortOrder, $html);
	
	
This is identical to instantiating a new StaticWidget and loading it, as done in the standard example.
	
	$widget = new StaticWidget($html);
	$widget->load($container, $sortOrder);
	
	
-------------------------------
------ Methods Available ------
-------------------------------

WidgetLoader::loadWidgetsFromDatabase([$container]);

WidgetLoader::add($container, $sortOrder, $html);

$widgetData = WidgetLoader::get($container);

*/

abstract class WidgetLoader {
	
	
/****** Plugin Variables ******/
	public static array <str, array<int, array>> $slots = array();		// <str:[int:array]> Stores widget data in a designated container.
	
	
/****** Load widgets from the database ******/
	public static function loadWidgetsFromDatabase
	(
		string $container = ""		// <str> If set, only load widgets for the designated container.
	): void						// RETURNS <void>
	
	// WidgetLoader::loadWidgetsFromDatabase([$container]);
	{
		// Load the container's widgets from the database if one was specified
		if($container !== "")
		{
			$widgetList = Database::selectMultiple("SELECT * FROM widget_loader WHERE container=?", array($container));
		}
		
		// Load all widgets in the database if there is no container specified
		else
		{
			$widgetList = Database::selectMultiple("SELECT * FROM widget_loader", array($container));
		}
		
		// Attempts to find the widget plugin being loaded
		foreach($widgetList as $widget)
		{
			// Make sure the widget's class exists
			if(!class_exists($widget['widget']))
			{
				continue;
			}
			
			// Make sure this is a valid widget
			if(get_parent_class($widgetClass) != "Widget")
			{
				continue;
			}
			
			// Use the JSON value provided to insert arguments that the widget uses
			$instructions = Serialize::decode($widget['instructions']);
			
			// Make sure the instructions come from a valid array
			if(!is_array($instructions)) { continue; }
			
			// Instantiate $widgetClass
			$widgetClass = new $widget['widget']($instructions);
			
			// Add the widget to the container
			self::$slots[$container][(int) $widget['sort_order']][] = $widgetClass->content;
		}
		
	}
	
	
/****** Add a static entry into the Widget Loader ******/
	public static function add
	(
		string $container		// <str> The name of the container to load a static widget into.
	,	int $sortOrder		// <int> The sort order (position) to load the static widget into.
	,	string $html			// <str> The block of HTML to add as a widget into the loader.
	): void					// RETURNS <void>
	
	// WidgetLoader::add($container, $sortOrder, $html);
	{
		WidgetLoader::$slots[$container][$sortOrder][] = $html;
	}
	
	
/****** Return a list of widgets in a container ******/
	public static function get
	(
		string $container		// <str> The name of the container to load widgets from.
	): array <int, str>					// RETURNS <int:str> a list of widget content stored in the container.
	
	// $widgetData = WidgetLoader::get($container);
	{
		// If the container doesn't exist, return empty array
		if(!isset(self::$slots[$container]))
		{
			return array();
		}
		
		// Retrieve the contents from this widget container
		$widgetContent = array();
		$contents = self::$slots[$container];
		
		ksort($contents);
		
		foreach($contents as $slotSet)
		{
			foreach($slotSet as $slotData)
			{
				$widgetContent[] = $slotData;
			}
		}
		
		return $widgetContent;
	}
}