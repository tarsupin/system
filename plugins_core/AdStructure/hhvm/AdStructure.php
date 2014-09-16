<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the AdStructure Plugin ------
------------------------------------------

The structure of an ad indicates how the ad will function on the site. For example, the "RectImg" structure would indicate that the ad is a rectangular ad, with a shorter height than width. This structure is also associated with images, and would provide a dimension list, such as "RectImg-230x150".

To retrieve data from the ad structure, just pass the full structure through the ::load($structure) method.
	
	$adDetails = AdStructure::load("RectImg-230x150");

*/

abstract class AdStructure {
	
	
/****** Plugin Variables ******/
	public static array <str, mixed> $adDetails = array();			// <str:mixed> The array of ad details for this ad structure.
	
	public static array <str, mixed> $detailStruct = array(		// <str:mixed> A reset version the array <str, mixed> $adDetails array.
		"name"				=> "Advertisement"
	,	"type"				=> ""
	,	"desc"				=> ""
	,	"responsive"		=> false
	,	"bid"				=> array()
	,	"require"			=> array(
								"title"			=> false
							,	"body"			=> false
							,	"image_url"		=> false
							)
	);
	
	
/****** Generate the rules of the ad based on the structure designated ******/
	public static function load
	(
		string $structure			// <str> The name of the structure.
	): array <str, mixed>						// RETURNS <str:mixed> The details for this ad's structure.
	
	// $adDetails = AdStructure::load($structure);
	{
		// Reset the ad details before loading the next structure
		self::$adDetails = self::$detailStruct;
		
		// Extract basic information about the structure
		$struct = explode("-", $structure);
		
		if(count($struct) == 2)
		{
			$structureType = Sanitize::variable($struct[0]);
			$structureData = Sanitize::safeword($struct[1]);
			
			call_user_func(array("AdStructure", "build_" . $structureType), $structureData);
		}
		
		return self::$adDetails;
	}
	
	
/****** Define rules based on the structure provided ******/
	public static function build_RectImg
	(
		string $structureData		// <str> The data for this structure.
	): void						// RETURNS <void>
	
	// AdStructure::build_RectImg($structureData);
	{
		list($width, $height) = explode("x", $structureData);
		
		self::$adDetails['name'] = "Rectangular Image";
		self::$adDetails['type'] = "image";
		self::$adDetails['responsive'] = true;
		
		self::$adDetails['width'] = (int) $width;
		self::$adDetails['height'] = (int) $height;
		self::$adDetails['img_width'] = (int) $width;
		self::$adDetails['img_height'] = (int) $height;
		
		self::$adDetails['desc'] = "This is a rectangular image ad of " . self::$adDetails['img_width'] . "x" . self::$adDetails['img_height'] . " pixels.";
		
		self::$adDetails['bid'] = array('all' => 0.95, 'user' => 4.95, 'premium' => 9.95);
		
		self::$adDetails['require']['image_url'] = true;
	}
	
	
/****** Define rules based on the structure provided ******/
	public static function build_TextBox
	(
		string $structureData		// <str> The data for this structure.
	): void						// RETURNS <void>
	
	// AdStructure::build_TextBox($structureData);
	{
		list($boxSize, $maxLengths) = explode(":", $structureData);
		
		list($width, $height) = explode("x", $boxSize);
		list($titleLen, $bodyLen) = explode(",", $maxLengths);
		
		self::$adDetails['name'] = "Text Box";
		self::$adDetails['type'] = "text";
		self::$adDetails['responsive'] = true;
		
		self::$adDetails['width'] = (int) $width;
		self::$adDetails['height'] = min(200, (int) $height);
		
		self::$adDetails['desc'] = "This is a text ad that fits roughly within " . self::$adDetails['width'] . "x" . self::$adDetails['height'] . " pixels.";
		
		self::$adDetails['bid'] = array('all' => 0.45, 'user' => 2.95, 'premium' => 4.95);
		
		self::$adDetails['require']['title'] = true;
		self::$adDetails['require']['body'] = true;
		
		self::$adDetails['title_len'] = 25;
		self::$adDetails['body_len'] = 60;
	}
	
}