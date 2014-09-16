<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Photo Plugin ------
------------------------------------

This plugin allows you to post photos in a responsive manner to accomodate mobile devices.


------------------------------------------
------ Examples of using this class ------
------------------------------------------

// Include Responsive Script (allows resposive images on the page)
Photo::prepareResponsivePage();

// Display the Image
echo '<a href="/image-link">' . Photo::responsive("http://cdn.test/assets/image-standard.jpg", "http://cdn.test/assets/image-mobile.jpg", 600, "http://cdn.test/assets/image-tablet.jpg", 1200, "myClass") . '</a>';


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
	)					// RETURNS <void>
	
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
		$imageURL			// <str> The URL of the base image
	,	$mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	$mobileBreak = 450	// <int> The breakpoint to switch from mobile and the standard image.
	,	$tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	$tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	$class = ""			// <str> The class to assign to the image.
	)						// RETURNS <str> HTML to place the photo.
	
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
		$imageURL			// <str> The directory to use for the photo.
	,	$mobileURL = ""		// <str> The URL of the mobile image, if applicable
	,	$mobileBreak = 500	// <int> The breakpoint to switch from mobile and the standard image.
	,	$tabletURL = ""		// <str> The URL of the tablet image, if applicable
	,	$tabletBreak = 900	// <int> The breakpoint to switch to a tablet image.
	,	$class = ""			// <str> The class to assign to the image.
	)						// RETURNS <str> HTML to place the photo.
	
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
