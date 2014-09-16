<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the Navigation Plugin ------
-----------------------------------------

This plugin allows the site to create dynamically loaded navigational structures, either through code or through the database. This allows the additional flexibility to have site admins create and modify menus through the admin panel.


----------------------------------------------------
------ Example of using the Navigation Plugin ------
----------------------------------------------------

// Instantiate the navigation container
$sideMenu = new Navigation();

// Add any menus that are saved in the database under the "SideMenu" group
$sideMenu->loadFromDatabase("SideMenu");

// Add your own menus through the code
$sortOrder = 1;

$sideMenu->add("Home", "/", "home-class", $sortOrder++);
$sideMenu->add("About Us", "/about-us", "about-class", $sortOrder++);
$sideMenu->add("Contact Us", "/contact-us", "contact-class", $sortOrder++);

// Retrieve your navigation links and display them
$menuList = $sideMenu->get();

echo '<ul class="side-menu">';

foreach($menuList as $menu)
{
	echo '
	<li class="' . $menu['class'] . '"><a href="' . $menu['url'] . '">' . $menu['title'] . '</a></li>';
}

echo '</ul>';


-------------------------------
------ Methods Available ------
-------------------------------

$navigation->add($title, $url, [$className], [$sortOrder]);

$links = $navigation->get();

*/

class Navigation {
	
	
/****** Plugin Variables ******/
	public array <int, array<int, array>> $slots = array();		// <int:[int:array]> Stores links in a navigational structure.
	
	
/****** Load navigation links from the database ******/
	public function loadFromDatabase
	(
		string $group		// <str> Load the navigation links from this particular group.
	): void				// RETURNS <void>
	
	// $navigation->loadFromDatabase($group);
	{
		$linkList = Database::selectMultiple("SELECT * FROM navigation_loader WHERE nav_group=?", array($group));
		
		// Cycle through the links retrieved add them to this navigation object
		foreach($linkList as $linkData)
		{
			$this->slots[$linkData['sort_order']][] = array(
				"title"		=> $linkData['title']
			,	"url"		=> $linkData['url']
			,	"class"		=> $linkData['class']
			);
		}
	}
	
	
/****** Add a link to this navigation object ******/
	public function add
	(
		string $title				// <str> The title of the link.
	,	string $url				// <str> The URL to visit when the link is clicked.
	,	string $class				// <str> The name of the CSS class to associate with this link.
	,	int $sortOrder = 99		// <int> The sort order to position this link into.
	): void						// RETURNS <void>
	
	// $navigation->add($title, $url, $class, [$sortOrder]);
	{
		$this->slots[$sortOrder][] = array(
			"title"		=> $title
		,	"url"		=> $url
		,	"class"		=> $class
		);
	}
	
	
/****** Return a list of links from this navigation object ******/
	public function get (
	): array <int, str>					// RETURNS <int:str> a list of widget content stored in the container.
	
	// $links = $navigation->get();
	{
		$linkContent = array();
		$contents = $this->slots;
		
		ksort($contents);
		
		foreach($contents as $slotSet)
		{
			foreach($slotSet as $slotData)
			{
				$linkContent[] = $slotData;
			}
		}
		
		return $linkContent;
	}
	
	
}