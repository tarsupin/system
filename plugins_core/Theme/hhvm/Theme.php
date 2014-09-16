<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Theme Plugin ------
------------------------------------

This plugin allows you to change the current theme, identify the appropriate theme directory and page, etc.
	
	
	// Load a theme controller
	require(Theme::controller("page"));
	
	// Load a theme view
	require(Theme::view("page"));
	
	// Load a theme partial
	require(Theme::partial("page"));
	
	// STRUCTURE for the the /themes directory:
	

-------------------------------------------
------ The File Structure for Themes ------
-------------------------------------------
Themes must follow a specific file structure in the APP_PATH directory in order to be accessed as a theme. 

/APP_PATH
	/themes
		/THEME_NAME
			/controller
				page.php
				/directory
					page.php
			/view
				page.php
				/directory
					page.php
			/layouts
				layout.php
			/styles
				style1.css
				style2.css
			/includes
				include.php
				
				
-------------------------------
------ Methods Available ------
-------------------------------

// Sets the theme (and optionally the style)
Theme::set($theme, [$style]);

// Returns the URL of the current controller to load; used for loading files
Theme::controller($page);

// Returns the URL of the current layout to load; used for loading files
Theme::layout($page);

// Returns the URL of the current view to load; used for loading files
Theme::view($page);

// Check availability of themes and/or styles
$availableThemes = Theme::getThemeList();
$availableStyles = Theme::getStyleList($theme);

Theme::exists($theme);
Theme::hasStyle($style, [$theme]);

*/

class Theme {
	
	
/****** Plugin Variables ******/
	public static string $dir = "";				// <str> tracks the directory of the theme
	public static string $home = "/";				// <str> tracks the base home URL (when redirecting to "home")
	
	public static string $theme = "default";		// <str> the active theme being used
	public static string $style = "default";		// <str> the active style being used
	public static string $layout = "default";		// <str> the layout to load
	public static string $view = "";				// <str> the view page to load
	
	public static string $controllerPath = "";		// <str> the file path to the controller to load.
	public static string $layoutPath = "";			// <str> the file path to the layout to load.
	public static string $viewPath = "";			// <str> the file path to the view page to load.
	
	
/****** Set the active theme ******/
	public static function set
	(
		string $theme					// <str> The theme to set as active.
	,	string $style = "default"		// <str> The style that you want to set.
	,	string $layout = "default"		// <str> The layout that you want to assign.
	): void							// RETURNS <void>
	
	// Theme::set($theme, [$style], [$layout]);
	{
		self::$theme = Sanitize::variable($theme, "-");
		self::$style = Sanitize::variable($style, "-");
		self::$layout = Sanitize::variable($layout, "-");
		
		self::$dir = APP_PATH . "/themes/" . self::$theme;
		
		self::$layoutPath = self::load(self::$layout, "layouts");
	}
	
	
/****** Set the active theme controller to use ******/
	public static function setController
	(
		string $controller		// <str> The controller to load.
	,	string $plugin = ""	// <str> If set, this runs a plugin's controller.
	,	string $baseHome = ""	// <str> If set, this is the base URL of the home for this controller (used for plugins).
	): string					// RETURNS <str> The file path to the controller page.
	
	// Theme::setController($controller, [$plugin], [$baseHome]);
	{
		// Set the theme directory
		if($plugin != "")
		{
			$plugin = Sanitize::variable($plugin);
			
			Theme::$dir = Plugin::getPath($plugin);
		}
		
		// Set the base home directory
		Theme::$home = "/" . trim($baseHome, "/");
		
		// Cycle Through Plugin Controllers
		while(true)
		{
			// Check if the appropriate path exists. Load it if it does.
			if(File::exists(Theme::$dir . "/controller/" . $controller . ".php"))
			{
				self::$controllerPath = Theme::$dir . "/controller/" . $controller . ".php";
				return self::$controllerPath;
			}
			
			// Make sure there's still paths to check
			if(strpos($controller, "/") === false) { break; }
			
			$controller = substr($controller, 0, strrpos($controller, '/'));
		}
		
		// Check if the plugin's "home" page exists. Load it if it does.
		if(File::exists(Theme::$dir . "/controller/home.php"))
		{
			self::$controllerPath = Theme::$dir . "/controller/home.php";
			return self::$controllerPath;
		}
		
		return "";
	}
	
	
/****** Set the active theme layout to use ******/
	public static function setLayout
	(
		string $layout		// <str> The layout to set as active.
	): string				// RETURNS <str> The file path to the layout page.
	
	// Theme::setLayout($layout);
	{
		self::$layout = Sanitize::variable($layout, "-");
		
		self::$layoutPath = self::load(self::$layout, "layouts");
		
		return self::$layoutPath;
	}
	
	
/****** Set the active view page to load ******/
	public static function setView
	(
		string $view		// <str> The view page to load.
	): string				// RETURNS <str> The file path to the layout page.
	
	// Theme::setView($view);
	{
		self::$view = Sanitize::variable($view, "-");
		
		self::$viewPath = self::load(self::$view, "view");
		
		return self::$viewPath;
	}
	
	
/****** Load a theme file ******/
	public static function load
	(
		string $page		// <str> The layout to load.
	,	string $type		// <str> The type of page to load (controller, layout, page, etc).
	): string				// RETURNS <str> the path to the file being loaded.
	
	// Theme::load($page, $type);
	{
		// Prepare Values
		$page = ltrim(Sanitize::variable($page, "/-"), "/");
		
		// Attempt to load the active theme layout
		if(is_file($fullpath = self::$dir . "/" . $type . "/" . $page . ".php"))
		{
			return $fullpath;
		}
		
		// If the previous theme didn't load, force the default theme to load
		return APP_PATH . "/themes/default/" . $type . "/" . $page . ".php";
	}
	
	
/****** Get the list of available themes ******/
	public static function getThemeList (
	): array <int, str>				// RETURNS <int:str> the list of available themes
	
	// $availableThemes = Theme::getThemeList();
	{
		return Dir::getFolders(APP_PATH . "/themes");
	}
	
	
/****** Get the list of available themes ******/
	public static function getStyleList
	(
		string $theme		// <str> The theme to check the available styles of.
	): array <int, str>				// RETURNS <int:str> the list of available styles in the theme
	
	// $availableStyles = Theme::getStyleList($theme);
	{
		$fileList = array();
		$files = Dir::getFiles(APP_PATH . "/themes/" . Sanitize::variable($theme) . "/styles");
		
		foreach($files as $file)
		{
			$file = Sanitize::variable($file, " -.");
			
			if(strpos($file, ".css") !== false)
			{
				$fileList[] = $file;
			}
		}
		
		return $fileList;
	}
	
	
/****** Check if a theme is available (exists) on the site ******/
	public static function exists
	(
		string $theme		// <str> The theme that you want to verify exists.
	): bool				// RETURNS <bool> TRUE if the theme is available, FALSE if not.
	
	// Theme::exists($theme);
	{
		return is_dir(APP_PATH . "/themes/" . Sanitize::variable($theme, "-"));
	}
	
	
/****** Check if a theme has the indicated style ******/
	public static function hasStyle
	(
		string $style			// <str> The style that you're checking exists within the theme.
	,	string $theme = ""		// <str> The theme to look for the style in (default is your active theme).
	): bool					// RETURNS <bool> TRUE if the style is part of the theme, FALSE if not.
	
	// Theme::hasStyle($style, [$theme]);
	{
		// Prepare the default theme if nothing is selected
		if($theme == "")
		{
			$theme = self::$theme;
		}
		
		// Sanitize Values
		$theme = Sanitize::variable($theme, "-");
		$style = Sanitize::variable($style, "-");
		
		// Check if the style is available
		return is_file(APP_PATH . "/themes/" . $theme . "/styles/" . $style . ".css");
	}
}