<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Photo Plugin ------
------------------------------------

This plugin allows you to display photos in a responsive manner to accommodate mobile devices.

The primary method of this plugin, ::responsive(), will allow you to provide a photo to be used for desktops, tablets, and mobile devices. You can set the breakpoints of the screen size where these responsive photos should change.

For example, if you wanted to have a desktop image and a mobile image that gets used once the screen width has changed to 450 pixels or less, you could use the following command:
	
	// Provide a mobile image for <= 450 pixels
	echo Photo::responsive("/images/desktop.jpg", "/images/mobile.jpg", 450);

Since these images are being generated through a plugin, you must assign the CSS class through the $class parameter. To set the image above with the "resp-article" class, the command would change to the following:
	
	// Provide a mobile image for <= 450 pixels; apply the "resp-article" class
	echo Photo::responsive("/images/desktop.jpg", "/images/mobile.jpg", 450, "", 0, "resp-article");

-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Include Responsive Script (allows responsive images on the page)
Photo::prepareResponsivePage();

// Display the Image
echo '<a href="/image-link">' . Photo::responsive("http://cdn.test/assets/image-standard.jpg", "http://cdn.test/assets/image-mobile.jpg", 600, "http://cdn.test/assets/image-tablet.jpg", 1200, "resp-article") . '</a>';


-------------------------------
------ Methods Available ------
-------------------------------

// Prepares the page for responsive images
Photo::prepareResponsivePage();

// Loads an image for the appropriate device
Photo::responsive($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);

*/

abstract class Photo {
	
	
/****** Prepare a page for responsive photos ******/
	public static function prepareResponsivePage (
	): void					// RETURNS <void>
	
	// Photo::prepareResponsivePage();
	{
		Metadata::addHeader('
			<script>document.createElement("picture");</script>
			<script src="' . CDN . '/scripts/picturefill_v1.js" async></script>'
		);
	}
	
	
/****** Display a Responsive Photo ******/
	public static function responsive
	(
		string $imageURL			// <str> The URL of the base image
	,	string $mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	int $mobileBreak = 450	// <int> The breakpoint to switch from mobile and the standard image.
	,	string $tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	int $tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	string $class = ""			// <str> The class to assign to the image.
	): string						// RETURNS <str> HTML to place the photo.
	
	// echo Photo::responsive($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);
	{
		// If only the main image is available
		if($mobileURL == "" and $tabletURL == "")
		{
			return '<img' . ($class == "" ? "" : ' class="' . $class . '"') . ' src="' . $imageURL . '" />';
		}
		
		// Prepare Class
		$class = ($class == "" ? "" : ' data-class="' . $class . '"');
		
		// Prepare the responsive image
		return '
		<span class="responsive-photo" data-picture data-alt="">' .
			($mobileURL != "" ? '<span data-src="' . $mobileURL . '"' . $class . '></span>' : '') .
			($tabletURL != "" ? '<span data-src="' . $tabletURL . '"' . $class . ' data-media="(min-width: ' . $mobileBreak . 'px)"></span>' : '') . '
			<span data-src="' . $imageURL . '"' . $class . ' data-media="(min-width: ' . max($mobileBreak, $tabletBreak) . 'px)"></span>
			
			<!-- Fallback -->
			<noscript><img src="' . $imageURL . '" /></noscript>
		</span>';
	}
	
	
/****** Display a Photo with Responsive Options (version 2.0 using the picture element) ******/
# Note: since this causes JS disabled browsers not to work, this is considered deprecated for now.
	public static function responsive2_0
	(
		string $imageURL			// <str> The directory to use for the photo.
	,	string $mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	int $mobileBreak = 500	// <int> The breakpoint to switch from mobile and the standard image.
	,	string $tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	int $tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	string $class = ""			// <str> The class to assign to the image.
	): string						// RETURNS <str> HTML to place the photo.
	
	// echo Photo::responsive($imageURL, [$mobileURL], [$mobileBreak], [$tabletURL], [$tabletBreak], [$class]);
	{
		// Prepare Class
		$class = ($class == "" ? "" : ' class="' . $class . '"');
		
		// picturefill.js solution
		// <script src="' . CDN . '/scripts/picturefill_v2.min.js"></script>
		
		return '
		<picture>' .
			($mobileURL != "" ? '<source srcset="' . $mobileURL . '">' : '') .
			($tabletURL != "" ? '<source srcset="' . $tabletURL . '" media="(min-width: ' . $mobileBreak . 'px)>' : '') . '
			<source srcset="' . $imageURL . '" media="(min-width: ' . max($mobileBreak, $tabletBreak) . 'px)">
			<img' . $class . ' srcset="' . $imageURL . '">
		</picture>';
	}
		
}