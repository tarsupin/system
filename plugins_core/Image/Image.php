<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Image Plugin ------
------------------------------------

This plugin provides several methods for manipulating images.


----------------------------
------ Example of Use ------
----------------------------

// Display an image using special effects
$image = new Image(APP_PATH . "/images/image.png");
$image->multiply(APP_PATH . "/images/image2.jpg", 50, 50, 50, 50, 150, 150);
$image->thumb(150, 200);
$image->changeHue(80);
$image->display();

// Copy the image to a new location
$image = new Image(APP_PATH . "/images/image.png");
$image->save(APP_PATH . "/images/new_image.png");


-------------------------------
------ Methods Available ------
-------------------------------

$image = new Image($filePath)						// Creates an image resource from an image path.
$image = new Image($mime, $width, $height)		// Creates a blank image resource based on custom parameters.

$image->width 		// The width of the image
$image->height		// The height of the image

$image->paste("/path/to/image.png", !!!);		// Pastes a layer on top.
$image->darken("/path/to/image.png", !!!);		// Use darken layering effect.
$image->lighten("/path/to/image.png", !!!);		// Use lighten layering effect.
$image->multiply("/path/to/image.png", !!!);	// Use multiply layering effect.
$image->screen("/path/to/image.png", !!!);		// Use screen layering effect.
$image->overlay("/path/to/image.png", !!!);		// Use overlay layering effect.
$image->difference("/path/to/image.png", !!!);	// Use difference layering effect.

$image->layer("/path/to/image.png", !!!);				// Places another layer on top of the image (transparency).
$image->shadow("/path/to/image.png", !!!);				// Places a shadow layer above the image (50% transparency).
$image->blend("/path/to/image.png", !!!);				// Places a blended layer above the image.
$image->overlay_original("/path/to/image.png", !!!);	// Places a overlay layer on an image. Original version.
$image->multiply_original("/path/to/image.png", !!!);	// Places a multiply layer on an image. Original version.

$image->crop($x, y, $toX, $toY)					// Crops an image based on the dimensions provided
$image->autoCrop($width, $height)				// Automatically crops & centers an image
$image->autoWidth($width, $maxHeight = 0)		// Crops to forced width and flexible height.
$image->autoHeight($height, $maxWidth = 0)		// Crops to forced height and flexible width.
$image->thumb($width = 100, $height = 100)		// Generates a thumbnail from an image.
$image->trimTransparency()						// Crops off any transparent edges
$image->scale($newWidth, $newHeight, $x, $y, $x2, $y2);	// Scales the image.

$image->colorize(150, 20, 20, 0);				// Uses the colorize effect on an image.
$image->pixelate(3);							// Uses the pixelate effect on an image.

Image::hexToRGB($hexValue)						// Changes a hex color value to RGB (array)
Image::rgbToHex($red, $green, $blue)			// Changes an rgb color value to hex.

$image->swapColors($swapFrom, $swapTo)			// Switch colors from one to another on an image.
$image->changeHue($angle)						// Changes the hue of the image up to 360 degrees.

$image->display(&$image)						// Displays the image directly to the screen
$image->save($filename)							// Saves the Image to the filename provided

*/

class Image {
	
	
/****** Class Variables ******/
	public $mime = "";			// <str> The mime-type of the image.
	public $width = 0;			// <int> The width of the image.
	public $height = 0;			// <int> The height of the image.
	public $resource = null;	// <mixed> the image resource.
	
	
/****** Constructor ******/
	public function __construct
	(
		$imagePath = ""		// <str> The filepath of the base image to create from, or empty for a blank image.
	,	$width = 0			// <int> The width of the image (if applicable)
	,	$height = 0			// <int> The height of the image (if applicable)
	,	$extType = ""		// <str> The type of extension (if applicable)
	)						// RETURNS <void>
	
	// $image = new Image($imagePath, [$width], [$height], [$extType]);	// Creates an image from a file.
	// $image = new Image("", 300, 500, "png");							// Creates a blank image (sets size and type)
	{
		// If you're using the constructor to create an image from a file:
		if($imagePath != "" and File::exists($imagePath))
		{
			if($width == 0 or $height == 0 or $extType == "")
			{
				$info = getimagesize($imagePath);
				
				$this->width = $info[0];
				$this->height = $info[1];
				$this->mime = $info['mime'];
			}
			else
			{
				$this->width = $width;
				$this->height = $height;
				
				switch($extType)
				{
					case "jpg":		$this->mime = "image/jpeg";		break;
					case "png":		$this->mime = "image/png";		break;
					case "gif":		$this->mime = "image/gif";		break;
				}
			}
			
			// Generate Image Object
			switch($this->mime)
			{
				case "image/jpeg":	
					$this->resource = imagecreatefromjpeg($imagePath); break;
				
				case "image/png":
					$this->resource = imagecreatefrompng($imagePath); break;
				
				case "image/gif":
					$this->resource = imagecreatefromgif($imagePath); break;
			}
		}
		
		// If you're creating a blank custom image
		else if($imagePath == "" and $extType != "")
		{
			switch($extType)
			{
				case "jpg":		$this->mime = "image/jpeg";		break;
				case "png":		$this->mime = "image/png";		break;
				case "gif":		$this->mime = "image/gif";		break;
			}
			
			if($this->mime == "") { return; }
			
			$this->width	= $width;
			$this->height	= $height;
			
			$this->resource = imagecreatetruecolor($this->width, $this->height);
			$transColor = imagecolorallocatealpha($this->resource, 0, 0, 0, 127);
			imagefill($this->resource, 0, 0, $transColor);
		}
	}
	
	
/****** Paste a New Layer Above ******/
# Note: This function MUCH faster than the "layer" method (like 200x faster), so use this whenever possible.
	public function paste
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->paste("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Layer at 50, 70
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Copy the layer to the image
		imagecopy($this->resource, $draw->resource, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Darken Effect ******/
	public function darken
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>

	// $image->darken("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Shadow Layer at 50, 70
	{
		$this->bypixel("darken", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Lighten Effect ******/
	public function lighten
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>

	// $image->lighten("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Light Layer at 50, 70
	{
		$this->bypixel("lighten", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Multiply Effect ******/
	public function multiply
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>

	// $image->multiply("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Multiply Layer at 50, 70
	{
		$this->bypixel("multiply", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Screen Effect ******/
	public function screen
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>

	// $image->screen("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Screen Layer at 50, 70
	{
		$this->bypixel("screen", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Overlay Effect ******/
	public function overlay
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->overlay("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Overlay Layer at 50, 70
	{
		$this->bypixel("overlay", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Difference Effect ******/
	public function difference
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>

	// $image->difference("/path/to/image.png", 50, 70, 0, 0, 0, 0);		// Paste Difference Layer at 50, 70
	{
		$this->bypixel("difference", $layerPath, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Helper function that modifies values by pixel ******/
# Modes include: darken, lighten, multiply, screen, overlay, difference 
# Formulas found by Kevin Jensen, http://www.venture-ware.com/kevin/coding/lets-learn-math-photoshop-blend-modes/
	private function bypixel
	(
		$mode				// <str> The layering mode to use.
	,	$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	{
		if (!in_array($mode, array("darken", "lighten", "multiply", "screen", "overlay", "difference")))
			return;
	
		// Load the new layer
		$draw = new Image($layerPath);
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// deactivate alpha blending in case it isn't
		imagealphablending($draw->resource, false);
		
		// Loop through each pixel and modify appropriately
		for ($x=0; $x<$layerWidth; $x++)
		{
			for ($y=0; $y<$layerHeight; $y++)
			{
				$rgb2 	= imagecolorat($this->resource, $x+$posX, $y+$posY);
				$alpha2	= ($rgb2 >> 24) & 0xFF;
				
				if ($alpha2 != 127)
				{
					$r2		= ($rgb2 >> 16) & 0xFF;
					$g2		= ($rgb2 >> 8) & 0xFF;
					$b2		= $rgb2 & 0xFF;
				
					$rgb1 	= imagecolorat($draw->resource, $x, $y);
					$alpha1	= ($rgb1 >> 24) & 0xFF;
					$r1		= ($rgb1 >> 16) & 0xFF;
					$g1		= ($rgb1 >> 8) & 0xFF;
					$b1		= $rgb1 & 0xFF;
					
					switch($mode)
					{
						case "darken":		$rgb = imagecolorallocatealpha($draw->resource, min($r1, $r2), min($g1, $g2), min($b1, $b2), $alpha1);
											break;
						case "lighten":		$rgb = imagecolorallocatealpha($draw->resource, max($r1, $r2), max($g1, $g2), max($b1, $b2), $alpha1);
											break;
						case "multiply":	$rgb = imagecolorallocatealpha($draw->resource, $r1*$r2/255, $g1*$g2/255, $b1*$b2/255, $alpha1);
											break;
						case "screen":		$rgb = imagecolorallocatealpha($draw->resource, 255*(1-(1-$r1/255)*(1-$r2/255)), 255*(1-(1-$g1/255)*(1-$g2/255)), 255*(1-(1-$b1/255)*(1-$b2/255)), $alpha1);
											break;
						case "overlay":		
											$r1 = $r1/255;	$r2 = $r2/255;
											if ($r2 < 0.5)	$r = 2*$r1*$r2;		else	$r = 1-2*(1-$r1)*(1-$r2);	$r *= 255;
											$g1 = $g1/255;	$g2 = $g2/255;
											if ($g2 < 0.5)	$g = 2*$g1*$g2;		else	$g = 1-2*(1-$g1)*(1-$g2);	$g *= 255;
											$b1 = $b1/255;	$b2 = $b2/255;
											if ($b2 < 0.5)	$b = 2*$b1*$b2;		else	$b = 1-2*(1-$b1)*(1-$b2);	$b *= 255;
											$rgb = imagecolorallocatealpha($draw->resource, $r, $g, $b, $alpha1);
											break;
						case "difference":	$rgb = imagecolorallocatealpha($draw->resource, abs($r1-$r2), abs($g1-$g2), abs($b1-$b2), $alpha1);
											break;
					}
					
					imagesetpixel($draw->resource, $x, $y, $rgb);
				}
			}
		}
		
		// activate alpha blending in case it isn't
		imagealphablending($this->resource, true);
		
		// Copy the layer to the image
		imagecopy($this->resource, $draw->resource, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Create a New Layer ******/
# Note: this function is incredibly slow, and "paste" may operate 200x faster for you.
	public function layer
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> X position of the layer.
	,	$posY = 0			// <int> Y position of the layer.
	,	$layerX = 0			// <int> X crop-position of layer.
	,	$layerY = 0			// <int> Y crop-position of layer.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->layer("/path/to/image.png", 40, 80, 0, 0, 0, 0);		// Place transparency layer at 40, 80
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $layerWidth;$x++)
		{
			for($y = 0;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($this->resource, $x + $layerX, $y + $layerY);
				$rgb = imagecolorat($draw->resource, $x + $layerX, $y + $layerY);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($this->resource, $x + $posX, $y + $posY, imagecolorallocatealpha($draw->resource, $r, $g, $b, $alpha));
				}
			}
		}
	}
	
	
/****** Create a Shadow Layer ******/
	public function shadow
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$posX = 0			// <int> X position of the layer.
	,	$posY = 0			// <int> Y position of the layer.
	,	$layerX = 0			// <int> X crop-position of layer.
	,	$layerY = 0			// <int> Y crop-position of layer.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->shadow("/path/to/image.png", 30, 60, 0, 0, 0, 0);	// Places a shadow layer at 30, 60
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $layerWidth;$x++)
		{
			for($y = 0;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($this->resource, $x + $layerX, $y + $layerY);
				$rgb = imagecolorat($draw->resource, $x + $layerX, $y + $layerY);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				$alpha2 = ($rgb_under & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// This section will "multiply" blend the lower layers with this shadow layer.
					// It uses the formula: round(top pixel * bottom pixel / 255)
					
					$r2		= ($rgb_under >> 16) & 0xFF;
					$g2		= ($rgb_under >> 8) & 0xFF;
					$b2		= $rgb_under & 0xFF;
					
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($this->resource, $x + $posX, $y + $posY, imagecolorallocatealpha($draw->resource, round($r * $r2 / 255), round($g * $g2 / 255), round($b * $b2 / 255), round($alpha * $alpha / 255)));
				}
			}
		}
	}
	
	
/****** Blend an Image Layer ******/
	public function blend
	(
		$layerPath			// <str> A path to the image you'd like to blend.
	,	$posX = 0			// <int> X position of the layer.
	,	$posY = 0			// <int> Y position of the layer.
	,	$layerX = 0			// <int> X crop-position of layer.
	,	$layerY = 0			// <int> Y crop-position of layer.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->blend("/path/to/image.png", 35, 55, 0, 0, 0, 0);	// Places a blend layer at 35, 55
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		imagelayereffect($draw->resource, IMG_EFFECT_OVERLAY);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Copy the layer to the image
		imagecopy($draw->resource, $this->resource, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
		$this->resource = $draw->resource;
	}
	
	
/****** Overlay an Image Layer ******/
	public function overlay_original
	(
		$layerPath			// <str> A path to the image you'd like to overlay.
	,	$posX = 0			// <int> X position of the layer.
	,	$posY = 0			// <int> Y position of the layer.
	,	$layerX = 0			// <int> X crop-position of layer.
	,	$layerY = 0			// <int> Y crop-position of layer.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->overlay_original("/path/to/image.png", 55, 55, 0, 0, 0, 0);	// Places an overlay at 55, 55
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		imagelayereffect($this->resource, IMG_EFFECT_OVERLAY);
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Copy the layer to the image
		imagecopy($this->resource, $draw->resource, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
	}
	
	
/****** Multiply the Image ******/
	public function multiply_original
	(
		$layerPath				// <str> The image path of the layer to multiply.
	,	$posX = 0				// <int> X position of the image (to start multiplier).
	,	$posY = 0				// <int> Y position of the image (to start multiplier).
	,	$layerX = 0				// <int> X crop-position of layer.
	,	$layerY = 0				// <int> Y crop-position of layer.
	,	$layerWidth = 0			// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0		// <int> Height of the layer. (default is actual size)
	)							// RETURNS <void> 
	
	// Image::multiply_original($image, "/path/to/image.png", 65, 50, 0, 0, 0, 0);	// Places an overlay at 65, 50
	{
		// Load the new layer
		$draw = new Image($layerPath);
		
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $layerWidth;$x++)
		{
			for($y = 0;$y < $layerHeight;$y++)
			{
				// Retrieve the color at the current location
				$rgb_under = imagecolorat($this->resource, $x + $layerX, $y + $layerY);
				$rgb = imagecolorat($draw->resource, $x, $y);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// This section will "multiply" blend the lower layers with this shadow layer.
					// It uses the formula: round(top pixel * bottom pixel / 255)
					
					$r2		= ($rgb_under >> 16) & 0xFF;
					$g2		= ($rgb_under >> 8) & 0xFF;
					$b2		= $rgb_under & 0xFF;
					
					$r		= ($rgb >> 16) & 0xFF;
					$g		= ($rgb >> 8) & 0xFF;
					$b		= $rgb & 0xFF;
					
					imagesetpixel($this->resource, $x + $posX, $y + $posY, imagecolorallocatealpha($draw->resource, round($r * $r2 / 255), round($g * $g2 / 255), round($b * $b2 / 255), $alpha));
				}
			}
		}
	}
	
	
/****** Crop Image ******/
	public function crop
	(
		$x			// <int> The X position to start cropping at.
	,	$y			// <int> The Y position to start cropping at.
	,	$toX		// <int> The X position to stop cropping at.
	,	$toY		// <int> The Y position to stop cropping at.
	)				// RETURNS <void>
	
	// $image->crop($x, $y, $toX, $toY);
	{
		// Cropped Dimensions
		$cropWidth = abs($toX - $x);
		$cropHeight = abs($toY - $y);
		
		// Crop the Image
		$croppedImage = imagecreatetruecolor($cropWidth, $cropHeight);
		$transColor = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
		
		imagefill($croppedImage, 0, 0, $transColor);
		imagecopyresampled($croppedImage, $this->resource, 0, 0, $x, $y, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		
		// Set updated image values
		$this->resource = $croppedImage;
		$this->width = $cropWidth;
		$this->height = $cropHeight;
		
		// Clear Memory
		unset($croppedImage);
	}
	
	
/****** Auto-Crop Image ******/
# This function automatically creates a center-cropped image (of the size chosen).
	public function autoCrop
	(
		$width		// <int> The width of the cropped image.
	,	$height		// <int> The height of the cropped image.
	)				// RETURNS <void>
	
	// $image->autoCrop($width, $height);
	{
		// Prepare Values
		$imgX = 0;
		$imgY = 0;
		
		// Get Current Images Width
		$imgWidth = $this->width;
		$imgHeight = $this->height;
		
		// Determine what part of the image you can shrink
		$heightPercent = $imgHeight / $height;
		$widthPercent = $imgWidth / $width;
		
		if($heightPercent > $widthPercent)
		{
			// This means the top and bottom needs to be cropped, since width can be maxed out.
			
			// Shrink clone size until $widthPercent == 1
			$cloneHeight = $imgHeight / $widthPercent;
			$cloneWidth = $imgWidth / $widthPercent;
			
			// Now get the amount of pixel space remaining to the sides
			$extraSpace = $cloneHeight - $height;
			
			// Set the X position where it will cover the center.
			if($imgHeight > $imgWidth)
			{
				$imgY += ($extraSpace / 2) * $widthPercent;
			}
			
			$imgHeight -= $extraSpace * $widthPercent;
		}
		else
		{
			// This means the left and right need to be cropped, since height can be maxed out.
			
			// Determine how much needs to be cropped by identifying the rescale width result, then centering.
			
			// Shrink clone size until $heightPercent == 1
			// The result is what the width will be after the rescale takes effect
			$cloneHeight = $imgHeight / $heightPercent;
			$cloneWidth = $imgWidth / $heightPercent;
			
			// Now get the amount of pixel space remaining to the sides
			$extraSpace = $cloneWidth - $width;
			
			// Set the X position where it will cover the center.
			if($imgWidth > $imgHeight)
			{
				$imgX += ($extraSpace / 2) * $heightPercent;
			}
			
			$imgWidth -= $extraSpace * $heightPercent;
		}
		
		// Auto-Crop the New Image
		$croppedImage = imagecreatetruecolor($width, $height);
		$transColor = imagecolorallocatealpha($croppedImage, 0, 0, 0, 127);
		imagefill($croppedImage, 0, 0, $transColor);
		imagecopyresampled($croppedImage, $this->resource, 0, 0, $imgX, $imgY, $width, $height, $imgWidth, $imgHeight);
		
		// Set updated image values
		$this->resource = $croppedImage;
		$this->width = $width;
		$this->height = $height;
		
		// Clear Memory
		unset($croppedImage);
	}
	
	
/****** Forced-Width Image ******/
# This function forces a strict width with a flexible height, and centers the height.
	public function autoWidth
	(
		$width			// <int> The forced width of the image.
	,	$maxHeight = 0	// <int> The maximum height for the image. 0 = no maximum.
	)					// RETURNS <void>
	
	// $image->autoWidth($width, 600);
	{
		$widthRatio = $width / $this->width;
		$height = (int) floor($this->height * $widthRatio);
		
		// Restrict to a maximum size if applicable
		if($height > $maxHeight and $maxHeight > 0)
		{
			$height = $maxHeight;
		}
		
		$this->autoCrop($width, $height);
	}
	
	
/****** Forced-Height Image ******/
# This function forces a strict height with a flexible width, and centers the width.
	public function autoHeight
	(
		$height			// <int> The forced width of the image.
	,	$maxWidth = 0	// <int> The maximum width for the image. 0 = no maximum.
	)					// RETURNS <void>
	
	// $image->autoHeight($height, 800);
	{
		$heightRatio = $height / $this->height;
		$width = (int) floor($this->width * $heightRatio);
		
		// Restrict to a maximum size if applicable
		if($width > $maxWidth and $maxWidth > 0)
		{
			$width = $maxWidth;
		}
		
		$this->autoCrop($width, $height);
	}
	
	
/****** Create a Thumbnail of an Image ******/
	public function thumb
	(
		$width = 100	// <int> The width of the thumbnail.
	,	$height = 100	// <int> The height of the thumbnail.
	)					// RETURNS <void>
	
	// $image->thumb(100, 100)	// Creates a 100x100 thumbnail of your image
	{
		$this->autoCrop($width, $height);
	}
	
	
/****** Crop Transparent Edges Image ******/
	public function trimTransparency (
	)				// RETURNS <int:int>
	
	// $image->trimTransparency();
	{
		// Get dimensions of the image
		list($leftMost, $rightMost, $topMost, $bottomMost) = $this->findCropbox(0, 0, $this->width - 1, $this->height - 1, 7);
		
		// If the inner crop-check rectangle didn't detect any spots, realign to make sense
		if($leftMost < $rightMost) { $rightMost = $leftMost; }
		if($topMost < $bottomMost) { $bottomMost = $topMost; }
		
		// Create the four boxes that you'll scan through
		
		// Scan Crop Boxes
		if($leftMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = $this->findCropbox(0, 0, $leftMost, $this->height - 1, true);
			
			$leftMost = ($getLeft < $leftMost ? $getLeft : $leftMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($rightMost < $this->width - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = $this->findCropbox($rightMost, 0, $this->width - 1, $this->height - 1, true);
			
			$rightMost = ($getRight > $rightMost ? $getRight : $rightMost);
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		if($topMost > 0)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = $this->findCropbox($leftMost, 0, $rightMost, $topMost, true);
			
			$topMost = ($getTop < $topMost ? $getTop : $topMost);
		}
		
		if($bottomMost < $this->height - 1)
		{
			list($getLeft, $getRight, $getTop, $getBottom) = $this->findCropbox($leftMost, $bottomMost, $rightMost, $this->height - 1, true);
			
			$bottomMost = ($getBottom > $bottomMost ? $getBottom : $bottomMost);
		}
		
		// Trim all of the transparency from the image
		$this->crop($leftMost, $topMost, $rightMost+1, $bottomMost+1);
		
		// Returns X, Y, Width, and Height
		return array($leftMost, $topMost, $rightMost - $leftMost, $bottomMost - $topMost);
	}
	
	
/****** Find the Crop Box (used by trimTransparency()) ******/
	private function findCropbox
	(
		$posX			// <int> The x position of where to start searching on the image.
	,	$posY			// <int> The y position of where to start searching on the image.
	,	$posX2			// <int> The x position of where to stop searching.
	,	$posY2			// <int> The y position of where to stop searching.
	,	$gridSize = 5	// <int> Precision of the grid search (larger number = less precise, true = exact)
	)					// RETURNS <int:int> list($x, $y, $x2, $y2) - the rectangle of detected boxes. 
	
	// $image->findCropbox($image, $leftMost, $topMost, $rightMost, $bottomMost);
	{
		// Prepare Important Values for the Image Grid
		$leftMost = $posX2;
		$rightMost = $posX;
		$topMost = $posY2;
		$bottomMost = $posY;
		
		if($gridSize === true)
		{
			$widthIntervals = 1;
			$heightIntervals = 1;
		}
		else
		{
			$widthIntervals = ($posX2 - $posX) / ($gridSize - 1);
			$heightIntervals = ($posY2 - $posY) / ($gridSize - 1);
		}
		
		// Search every 1/Xth of the image grid to speed up the crop-checking process.
		for($x = $posX;$x <= $posX2 + 1;$x += $widthIntervals)
		{
			for($y = $posY;$y <= $posY2;$y += $heightIntervals)
			{
				// Restrict to edges if applicable
				$getX = min(ceil($x), $posX2);
				$getY = min(ceil($y), $posY2);
				
				// Retrieve the color at the current location
				$rgb = imagecolorat($this->resource, $getX, $getY);
				
				// Translate the colors to RGB values
				$alpha = ($rgb & 0x7F000000) >> 24;
				
				if($alpha != 127)
				{
					// Determine the borders of the inner rectangle that you want to crop-check.
					
					// Set Horizontal Values (Crop)
					if($getX < $leftMost) { $leftMost = $getX; }
					if($getX > $rightMost) { $rightMost = $getX; }
					
					// Set Vertical Values (Crop)
					if($getY < $topMost) { $topMost = $getY; }
					if($getY > $bottomMost) { $bottomMost = $getY; }
				}
			}
		}
		
		return array($leftMost, $rightMost, $topMost, $bottomMost);
	}
	
	
/****** Scale Image ******/
	public function scale
	(
		$newWidth		// <int> The new width of the image (scaled proportionally).
	,	$newHeight		// <int> The new height of the image (scaled proportionally).
	,	$x = 0			// <int> The upper-left X boundary for what part of your image you want to scale.
	,	$y = 0			// <int> The upper-left Y boundary for what part of your image you want to scale.
	,	$x2 = 0			// <int> The bottom-right X boundary to scale. (default is max width)
	,	$y2 = 0			// <int> The bottom-right Y boundary to scale. (default is max width)
	)					// RETURNS <void>
	
	// $image->scale($newWidth, $newHeight, $x, $y, $x2, $y2);	// Returns the scaled image
	{
		// Get dimensions of the image
		$width = ($x2 <= $x ? imagesx($this->resource) - $x : $x2 - $x);
		$height = ($y2 <= $x ? imagesy($this->resource) - $y : $y2 - $y);
		
		// Prepare New Scaled Image
		$imageNew = imagecreatetruecolor($newWidth, $newHeight);
		$transColor = imagecolorallocatealpha($imageNew, 0, 0, 0, 127);
		imagefill($imageNew, 0, 0, $transColor);
		
		// Scale Image
		$scaledImage = imagecreatetruecolor($newWidth, $newHeight);
		imagesavealpha($scaledImage, true);
		imagefill($scaledImage, 0, 0, $transColor);
		imagecopyresampled($scaledImage, $this->resource, 0, 0, $x, $y, $newWidth, $newHeight, $width, $height);
		
		// Update the image values
		$this->resource = $scaledImage;
		$this->width = $newWidth;
		$this->height = $newHeight;
		
		// Clear Memory
		unset($scaledImage);
		unset($imageNew);
	}
	
	
/****** Swap Colors in an Image ******/
	public function swapColors
	(
		$swapFrom		// <int:int> The RGB color array that you'd like to swap from.
	,	$swapTo			// <int:int> The RGB color array of what you'd like to swap to.
	)					// RETURNS <void> 
	
	// $image->swapColors(array(250, 150, 135), array(100, 140, 200));
	{
		// Get Important Variables
		$width = $this->width;
		$height = $this->height;
		
		// turn alpha blending off to set pixels correctly
		imagealphablending($this->resource, false);
		
		list($rF, $gF, $bF) = $swapFrom;
		list($rT, $gT, $bT) = $swapTo;
		
		// Cycle through every pixel in the image
		for($x = 0;$x < $width;$x++)
		{
			for($y = 0;$y < $height;$y++)
			{
				// Retrieve the color at the current location
				$rgb = imagecolorat($this->resource, $x, $y);
				
				// Translate the colors to RGB values
				$alpha	= ($rgb & 0x7F000000) >> 24;
				$r		= ($rgb >> 16) & 0xFF;
				$g		= ($rgb >> 8) & 0xFF;
				$b		= $rgb & 0xFF;
				
				if($r == $rF && $g == $gF && $b == $bF)
				{
					imagesetpixel($this->resource, $x, $y, imagecolorallocatealpha($this->resource, $rT, $gT, $bT, $alpha));
				}
			}
		}
	}
	
	
/****** Colorize the Image ******/
	public function colorize
	(
		$red			// <int> The red influence for the colorization.
	,	$green			// <int> The green influence for the colorization.
	,	$blue			// <int> The blue influence for the colorization.
	,	$alpha = 0		// <int> The alpha influence for the colorization.
	)					// RETURNS <void> 
	
	// $image->colorize(150, 20, 20, 0);
	{
		imagefilter($this->resource, IMG_FILTER_COLORIZE, $red, $green, $blue, $alpha);
	}
	
	
/****** Recolor a single flat-colored Layer ******/
	public function colorize_layer
	(
		$layerPath			// <str> A path to the image you'd like to layer on top.
	,	$color				// <str> A hex color code.
	,	$isoutline			// <bool> true if the layer is an outline.
	,	$posX = 0			// <int> Destination X position of the layer (from crop).
	,	$posY = 0			// <int> Destination Y position of the layer (from crop).
	,	$layerX = 0			// <int> Source X position.
	,	$layerY = 0			// <int> Source Y position.
	,	$layerWidth = 0		// <int> Width of the layer. (default is actual size)
	,	$layerHeight = 0	// <int> Height of the layer. (default is actual size)
	)						// RETURNS <void>
	
	// $image->colorize_layer("/path/to/image.png", "#517894", false, 50, 70);
	{
		// Load the new layer
		$draw = new Image($layerPath);
		if(!isset($draw->resource)) { return; }
		
		// Check default sizes
		if($layerHeight == 0) { $layerHeight = $draw->height; }
		if($layerWidth == 0) { $layerWidth = $draw->width; }
		
		// deactivate alpha blending for pixel setting
		imagealphablending($draw->resource, false);
		
		// convert desired color
		$rgb2 = self::hexToRGB($color);
		
		// darken if outline
		if ($isoutline)
		{
			$bright = min(max(($rgb2[0]*299 + $rgb2[1]*587 + $rgb2[2]*114) / 1000, 0), 255);
			$bright = max((255 - $bright) / 2, 25);
			for ($i=0; $i<=2; $i++)
				$rgb2[$i] = min(max($rgb2[$i] - $bright, 0), 255);
		}
		
		for ($x=0; $x<$layerWidth; $x++)
		{
			for ($y=0; $y<$layerHeight; $y++)
			{
				$rgb = imagecolorat($draw->resource, $x, $y);
				$alpha = ($rgb >> 24) & 0xFF;
				if ($alpha != 127)
				{
					$color = imagecolorallocatealpha($draw->resource, $rgb2[0], $rgb2[1], $rgb2[2], $alpha);
					imagesetpixel($draw->resource, $x, $y, $color);
				}
			}
		}
		
		// activate alpha blending in case it isn't
		imagealphablending($this->resource, true);
		
		// Copy the layer to the image
		imagecopy($this->resource, $draw->resource, $posX, $posY, $layerX, $layerY, $layerWidth, $layerHeight);
		
		imagedestroy($draw->resource);
	}
	
	
/****** Pixelate the Image ******/
	public function pixelate
	(
		$blockSize = 2			// <int> The block size you want to use for pixelation.
	,	$advanced = false		// <bool> True for advanced pixelation.
	)							// RETURNS <void> 
	
	// $image->pixelate(3);
	{
		imagefilter($this->resource, IMG_FILTER_PIXELATE, $blockSize, $advanced);
	}
	
		
/****** Change Hue of Image ******/
# Note: This was modified from an original script by Tatu Ulmanen on Stack Overflow.
	public function changeHue
	(
		$angle			// <int> The degree of hue shift you'd like to change, up to 360 degrees.
	)					// RETURNS <void>
	
	// $image->changeHue(180);
	{
		// If the hue shift is irrelevant (i.e. if it's the same image), then return the normal image
		if($angle % 360 == 0)
		{
			return;
		}
		
		// Get Important Variables
		$width = $this->width;
		$height = $this->height;
		
		// turn alpha blending off to set pixels correctly
		imagealphablending($this->resource, false);
		
		// Loop through every pixel
		for($x = 0; $x < $width; $x++)
		{
			for($y = 0; $y < $height; $y++)
			{
				// For each pixel, determine the color
				$rgb = imagecolorat($this->resource, $x, $y);
				$alpha = ($rgb >> 24) & 0xFF;
				$r     = ($rgb >> 16) & 0xFF;
				$g     = ($rgb >> 8) & 0xFF;
				$b     = $rgb & 0xFF;
				list($h, $s, $l) = $this->changeRGBtoHSL($r, $g, $b);
				
				// For each pixel, provide a new pixel with appropriate hue shift
				$h += $angle / 360;
				if($h > 1) $h--;
				list($r, $g, $b) = $this->changeHSLtoRGB($h, $s, $l);            
				imagesetpixel($this->resource, $x, $y, imagecolorallocatealpha($this->resource, $r, $g, $b, $alpha));
			}
		}
	}
	
	
/****** Change Hex Color to RGB Color ******/
	public static function hexToRGB
	(
		$hexColor		// <str> The hex color value that you want to change to rgb.
	)					// RETURNS <int:int> array($red, $green, $blue)
	
	// list($red, $green, $blue) = Image::hexToRGB("#FF0000");
	{
		$hexColor = str_replace("#", "", $hexColor);
		
		// If the hex code is 3 characters long
		if(strlen($hexColor) == 3)
		{
			$red = hexdec(substr($hexColor, 0, 1).substr($hexColor, 0, 1));
			$green = hexdec(substr($hexColor, 1, 1).substr($hexColor, 1, 1));
			$blue = hexdec(substr($hexColor, 2, 1).substr($hexColor, 2, 1));
		}
		
		// If the hex code is 6 characters long
		else
		{
			$red = hexdec(substr($hexColor, 0, 2));
			$green = hexdec(substr($hexColor, 2, 2));
			$blue = hexdec(substr($hexColor, 4, 2));
		}
		
		// Return the RGB code as an array
		return array($red, $green, $blue);
	}
	
	
/****** Change RGB Color to Hex Color ******/
	public static function rgbToHex
	(
		$red		// <int> The number to represent the red value in RGB
	,	$green		// <int> The number to represent the green value in RGB
	,	$blue		// <int> The number to represent the blue value in RGB
	)				// RETURNS <str> The resulting hex color value
	
	// $hexValue = Image::rgbToHex(255, 0, 0)
	{
		$hex = "#";
		$hex .= str_pad( dechex($red), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex($green), 2, "0", STR_PAD_LEFT );
		$hex .= str_pad( dechex($blue), 2, "0", STR_PAD_LEFT );
		
		// Return the hex value generated
		return strtoupper($hex);
	}
	
	
/****** Private Helper - Transmute RGB to HSL ******/
	private function changeRGBtoHSL
	(
		$r		// <int> Red value
	,	$g		// <int> Green value
	,	$b		// <int> Blue value
	)			// RETURNS <int:int>
	
	{
		$var_R = ($r / 255);
		$var_G = ($g / 255);
		$var_B = ($b / 255);
		
		$var_Min = min($var_R, $var_G, $var_B);
		$var_Max = max($var_R, $var_G, $var_B);
		$del_Max = $var_Max - $var_Min;
		
		$v = $var_Max;
		
		if($del_Max == 0)
		{
			$h = 0;
			$s = 0;
		}
		else
		{
			$s = $del_Max / $var_Max;

			$del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
			$del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

			if      ($var_R == $var_Max) $h = $del_B - $del_G;
			else if ($var_G == $var_Max) $h = ( 1 / 3 ) + $del_R - $del_B;
			else if ($var_B == $var_Max) $h = ( 2 / 3 ) + $del_G - $del_R;

			if ($h < 0) $h++;
			if ($h > 1) $h--;
		}
		
		return array($h, $s, $v);
	}

/****** Private Helper - Transmute HSL back to RGB ******/
	private function changeHSLtoRGB
	(
		$h		// <int>
	,	$s		// <int>
	,	$v		// <int>
	)			// RETURNS <int:int>
	
	{
		if($s == 0)
		{
			$r = $g = $B = $v * 255;
		}
		else
		{
			$var_H = $h * 6;
			$var_i = floor( $var_H );
			$var_1 = $v * ( 1 - $s );
			$var_2 = $v * ( 1 - $s * ( $var_H - $var_i ) );
			$var_3 = $v * ( 1 - $s * (1 - ( $var_H - $var_i ) ) );
			
			if       ($var_i == 0) { $var_R = $v     ; $var_G = $var_3  ; $var_B = $var_1 ; }
			else if  ($var_i == 1) { $var_R = $var_2 ; $var_G = $v      ; $var_B = $var_1 ; }
			else if  ($var_i == 2) { $var_R = $var_1 ; $var_G = $v      ; $var_B = $var_3 ; }
			else if  ($var_i == 3) { $var_R = $var_1 ; $var_G = $var_2  ; $var_B = $v     ; }
			else if  ($var_i == 4) { $var_R = $var_3 ; $var_G = $var_1  ; $var_B = $v     ; }
			else                   { $var_R = $v     ; $var_G = $var_1  ; $var_B = $var_2 ; }
			
			$r = $var_R * 255;
			$g = $var_G * 255;
			$B = $var_B * 255;
		}
		
		return array($r, $g, $B);
	}
	
	
/****** Display Image ******/
	public function display (
	)					// RETURNS <void> 
	
	// $image->display();
	{
		header("Content-Type: " . $this->mime);
		
		imagealphablending($this->resource, true);
		imagesavealpha($this->resource, true);
		
		// Generate Image Object
		switch($this->mime)
		{
			case "image/jpeg":	
				imagejpeg($this->resource); break;
			
			case "image/png":
				imagepng($this->resource); break;
			
			case "image/gif":
				imagegif($this->resource); break;
		}
		
		imagedestroy($this->resource);
	}
	
	
/****** Prepare a New Image ******/
	public function save
	(
		$file				// <str> The file where you'd like to save the image.
	,	$quality = 85		// <int> The level of quality (only affects jpg)
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $image->save($file, [$quality]);
	{
		// Allow Transparency
		imagesavealpha($this->resource, true);
		
		// If the save file is valid
		if(!isSanitized::filepath($file))
		{
			Alert::error("Image Path", "The image path is invalid.", 7);
			return false;
		}
		
		$saveInfo = pathinfo($file);
		
		if(!isset($saveInfo['basename']) or !isset($saveInfo['dirname']) or !isset($saveInfo['extension']))
		{
			Alert::error("Image Path", "The image path is not functioning properly.", 6);
			return false;
		}
		
		// Make sure the directory exists
		if(!Dir::create($saveInfo['dirname']))
		{
			Alert::error("Image Directory", "The image directory cannot be created. Please check permissions.", 4);
			return false;
		}
		
		// Save the file
		switch($saveInfo['extension'])
		{
			case "jpg":
			case "jpeg":
				return imagejpeg($this->resource, $file, $quality);
			
			case "png":
				return imagepng($this->resource, $file);
			
			case "gif":
				return imagegif($this->resource, $file);
		}
		
		return false;
	}
}
