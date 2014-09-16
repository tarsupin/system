<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

/****** Color Dropper Class ******
* Gets the colors in a given image. All pixels are matched against a whitelist color palette.
* 
* Note: This script, which was originally created by Joe Hoyle (which was derived from Marc Pacheco), has been modified
* to fit our class system.
* 
****** Example of Use ******

// Get Image Colors
$imageColors = new ColorDropper($imagePath);
$colors = $imageColors->getProminentColors();		// Gets main colors
$bg_colors = $imageColors->getBackgroundColor();	// Gets background colors

// Display each color located
foreach($colors as $color) { echo '<div style="width: 20px; float: left; height: 20px; background-color: ' . $color . '"></div>'; }

*/

class ColorDropper {

/****** Important Variables ******/
	var $image;
	var $height;
	var $width;
	var $precision;
	var $coinciditions;
	var $maxnumcolors;
	var $trueper;
	var $color_map = array();
	var $_palette = array();
	var $_min_percentage = 10;
	var $_excluded_colors = array( '#FFFFFF' );
	
	static $hit_pixels = 0;
	static $missed_pixels = 0;
	
/****** Class Constructor ******/
	public function __construct( $image, $precision = 10, $maxnumcolors = 5, $trueper = true ) {

		$this->image = $image;
		$this->maxnumcolors = $maxnumcolors;
		$this->trueper = $trueper;
		$this->getImageSize();
		$this->precision = $precision;

		$this->readPixels();

		$this->_excluded_colors[] = $this->getBackgroundColor();
	}
	
	
/***** Set the Minimum Percentage ******/
	public function setMinPercentage( $num ) {
		$this->_min_percentage = $num;
	}
	
	
	
/****** Get Background Colors of the Image ******/
	public function getProminentColors()
	{
		$pixels			= $this->getPercentageOfColors();
		$bg_color_hex	= $this->getBackgroundColor();
		
		foreach ($pixels as $color => $value)
		{
			if ( $value < $this->_min_percentage )
				unset( $pixels[$color] );
		}
		
		$_c = array();
		
		foreach ($pixels as $key => $value)
		{
			$_c[] = $key;
		}
		
		return $_c;
	}
	
/****** Get Background Colors of the Image ******/
	public function getBackgroundColor
	(
		bool $use_palette = true			// <bool>
	)
	
	{
		$top_left_color = imagecolorsforindex( $this->workingImage, imagecolorat( $this->workingImage, 0, 0) );
		$top_left = array( $top_left_color['red'], $top_left_color['green'], $top_left_color['blue'] );
		
		$top_right_color = imagecolorsforindex( $this->workingImage, imagecolorat( $this->workingImage, $this->width - 1, 0 ) );
		$top_right = array( $top_right_color['red'], $top_right_color['green'], $top_right_color['blue'] );
		
		$bottom_left_color = imagecolorsforindex( $this->workingImage, imagecolorat( $this->workingImage, 0, $this->height - 1 ) );
		$bottom_left = array( $bottom_left_color['red'], $bottom_left_color['green'], $bottom_left_color['blue'] );
		
		$bottom_right_color = imagecolorsforindex( $this->workingImage, imagecolorat( $this->workingImage, $this->width - 1, $this->height - 1 ) );
		$bottom_right = array( $bottom_right_color['red'], $bottom_right_color['green'], $bottom_right_color['blue'] );
		
		if ( $use_palette )
		{
			$top_left		= call_user_func_array( array( $this, 'getClosestColor' ), $top_left );
			$top_right		= call_user_func_array( array( $this, 'getClosestColor' ), $top_right );
			$bottom_right	= call_user_func_array( array( $this, 'getClosestColor' ), $bottom_right );
			$bottom_left 	= call_user_func_array( array( $this, 'getClosestColor' ), $bottom_left );
		}
		
		$colors = array( $top_left, $top_right, $bottom_left, $bottom_right);
		
		if( count( array_unique( $colors[0] ) ) == 1 )
		{
			return Image::rgbToHex( $top_left[0], $top_left[1], $top_left[2] );
		}
		
		return null;
	}
	
	
/****** Read Pixels ******/
	public function readPixels()
	{
		$image 		= $this->image;
		$width 		= $this->width;
		$height 	= $this->height;
		$arrayex 	= explode( '.', $image );
		$typeOfImage= end( $arrayex );
		
		try
		{
			switch ( $typeOfImage )
			{
				case "png": $outputimg = "imagecreatefrompng"; break;
				case "jpg": $outputimg = "imagecreatefromjpeg"; break;
				case "gif": $outputimg = "imagecreatefromgif"; break;
				case "bpm": $outputimg = "imagecreatefrombmp"; break;
					
				default: return;
			}
			
			$this->workingImage = $outputimg($image);

		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";
			exit();
		}
		
		for( $x = 0; $x < $width; $x += $this->precision ) {
			for ( $y = 0; $y < $height; $y += $this->precision ) {
				
				$index = imagecolorat($this->workingImage, $x, $y);
				$rgb = imagecolorsforindex($this->workingImage, $index);

				$color = $this->getClosestColor( $rgb["red"], $rgb["green"], $rgb["blue"] );

				$hexarray[] = Image::rgbToHex( $color[0], $color[1], $color[2] );
			}
		}
		
		$coinciditions = array_count_values($hexarray);
		$this->coinciditions = $coinciditions;

		return true;
	}
	
	private function getPercentageOfColors(){
	
		$coinciditions = $this->coinciditions;

		$total = 0;
		
		foreach ($coinciditions as $color => $cuantity) {

			if ( in_array( $color, $this->_excluded_colors ) )
				unset( $coinciditions[$color] );

			else
				$total += $cuantity;
		}

		foreach ($coinciditions as $color => $cuantity) {
			$percentage = (($cuantity/$total)*100);
			$finallyarray["$color"] = $percentage;
		}

		if ( ! $coinciditions )
			return array();
		
		asort($finallyarray);
		array_keys($finallyarray);
		$outputarray = array_slice(array_reverse($finallyarray), 0, $this->maxnumcolors);
	
		$trueper = $this->trueper;
	
		if( $trueper && $outputarray ) {
		
			   $total = 0;
			   foreach ($outputarray as $color => $cuantity) {
				   $total += $cuantity;
			   }
			   foreach ($outputarray as $color => $cuantity) {
				   $percentage = (($cuantity/$total)*100);
				   $finallyarrayp["$color"] = $percentage;
			   }
			   return $finallyarrayp;

		} else {
		
		   return $outputarray;
		}
	}
	
	public function getImageSize() {
	
		$imgsize 	= getimagesize($this->image);
		$height 	= $imgsize[1];
		$width 		= $imgsize[0];
		$this->height = $height;
		$this->width = $width;

		return "x= ".$width."y= ".$height;
	}
	
	
	// Function used for testing, will return a map pof all colors to their matching color counter part
	public function getColorMap() {

		$image 		= $this->image;
		$width 		= $this->width;
		$height 	= $this->height;
		$arrayex 	= explode( '.', $image );
		$typeOfImage= end( $arrayex );
		
		for( $x = 0; $x < $width; $x += $this->precision ) {
			for ( $y = 0; $y < $height; $y += $this->precision ) {
				
				$index = imagecolorat($this->workingImage, $x, $y);
				$rgb = imagecolorsforindex($this->workingImage, $index);
				
				$color = $this->getClosestColor( $rgb["red"], $rgb["green"], $rgb["blue"] );
				
				$hexarray[ Image::rgbToHex( $rgb["red"], $rgb["green"], $rgb["blue"] ) ] = Image::rgbToHex( $color[0], $color[1], $color[2] );
			}
		}

		return $hexarray;
	}
	
	private function getClosestColor($r, $g, $b){

		if ( isset( $this->color_map[Image::rgbToHex( $r, $g, $b ) ] ) ) {
			return $this->color_map[Image::rgbToHex( $r, $g, $b )];
		}

		$differencearray = array();
		$colors = $this->getPalette();

		foreach ($colors as $key => $value) {
			$value = $value['rgb'];
			$differencearray[$key] = self::getDistanceBetweenColors( $value, array( $r, $g, $b ) );
		}

		$smallest = min( $differencearray );

		$key = array_search($smallest, $differencearray);

		$color = $this->color_map[Image::rgbToHex( $r, $g, $b )] = $colors[$key]['rgb'];

		return $color;
	}

	private static function getDistanceBetweenColors( $col1, $col2 ) {

		$xyz1 = self::rgb_to_xyz( $col1 );
		$xyz2 = self::rgb_to_xyz( $col2 );

		$lab1 = self::xyz_to_lab( $xyz1 );
		$lab2 = self::xyz_to_lab( $xyz2 );

		return self::ciede2000( $lab1, $lab2 );
	} 
	
	
/****** Get the available palette used ******/
	private function getPalette()
	{
		if ( ! empty( $this->_palette ) )
			return $this->_palette;
		
		$str = '["#660000", "#990000", "#cc0000", "#cc3333", "#ea4c88", "#993399", "#663399", "#333399", "#0066cc", "#0099cc", "#66cccc", "#77cc33", "#669900", "#336600", "#666600", "#999900", "#cccc33", "#ffff00", "#ffcc33", "#ff9900", "#ff6600", "#cc6633", "#996633", "#663300", "#000000", "#999999", "#cccccc", "#ffffff", "#E7D8B1", "#FDADC7", "#424153", "#ABBCDA", "#F5DD01"]';
		
		$hexs = json_decode( $str );
		
		foreach ( $hexs as $hex )
			$this->_palette[] = array( 'rgb' => Image::hexToRGB( $hex ), 'hex' => $hex );

		return $this->_palette;
	}
	
	
	private static function xyz_to_lab( $xyz ){
		 $x = $xyz[0];
		 $y = $xyz[1];
		 $z = $xyz[2];
		 $_x = $x/95.047;
		 $_y = $y/100;
		 $_z = $z/108.883;
		 if($_x>0.008856){
			  $_x = pow($_x,1/3);
		 }
		 else{
			  $_x = 7.787*$_x + 16/116;
		 }
		 if($_y>0.008856){
			  $_y = pow($_y,1/3);
		 }
		 else{
			  $_y = (7.787*$_y) + (16/116);
		 }
		 if($_z>0.008856){
			  $_z = pow($_z,1/3);
		 }
		 else{
			  $_z = 7.787*$_z + 16/116;
		 }
		 $l= 116*$_y -16;
		 $a= 500*($_x-$_y);
		 $b= 200*($_y-$_z);
		 
		 return(array( 'l' => $l, 'a' => $a, 'b' => $b));
	}
	
	
	private static function rgb_to_xyz( $rgb ) {
		$red = $rgb[0];
		$green = $rgb[1];
		$blue = $rgb[2]; 
		$_red = $red/255;
		$_green = $green/255;
		$_blue = $blue/255;
		
		if ( $_red > 0.04045 ) {
			$_red = ($_red+0.055)/1.055;
			$_red = pow($_red,2.4);
		} else{
			$_red = $_red/12.92;
		}
		
		if ( $_green > 0.04045 ) {
		  $_green = ($_green+0.055)/1.055;
		  $_green = pow($_green,2.4);     
		} else{
		  $_green = $_green/12.92;
		}
		
		if ( $_blue>0.04045 ) {
		  $_blue = ($_blue+0.055)/1.055;
		  $_blue = pow($_blue,2.4);     
		} else {
		  $_blue = $_blue/12.92;
		}
		
		$_red *= 100;
		$_green *= 100;
		$_blue *= 100;
		
		$x = $_red * 0.4124 + $_green * 0.3576 + $_blue * 0.1805;
		$y = $_red * 0.2126 + $_green * 0.7152 + $_blue * 0.0722;
		$z = $_red * 0.0193 + $_green * 0.1192 + $_blue * 0.9505;
		
		return(array( $x, $y, $z ));
	}
	
	
/****** The CIEDE2000 Color-Difference Formula ******
Implementation Notes, Supplementary Test Data, and Mathematical Observations"
by Gaurav Sharma, Wencheng Wu and Edul N. Dalal.
*/
	private static function ciede2000($c1,$c2)
	{
		// Get L,a,b values for color 1
		$L1 = $c1['l'];
		$a1 = $c1['a'];
		$b1 = $c1['b'];
		
		// Get L,a,b values for color 2
		$L2 = $c2['l'];
		$a2 = $c2['a'];
		$b2 = $c2['b'];
		
		// Weight factors
		$kL = 1;
		$kC = 1;
		$kH = 1;
		
		// Step 1: Calculate C1p, C2p, h1p, h2p
		$C1 = sqrt(pow($a1, 2) + pow($b1, 2));
		$C2 = sqrt(pow($a2, 2) + pow($b2, 2));
		
		$a_C1_C2 = ($C1+$C2)/2.0;
		
		$G = 0.5 * (1 - sqrt(pow($a_C1_C2 , 7.0) / (pow($a_C1_C2, 7.0) + pow(25.0, 7.0))));
		
		$a1p = (1.0 + $G) * $a1;
		$a2p = (1.0 + $G) * $a2;
		
		$C1p = sqrt(pow($a1p, 2) + pow($b1, 2));
		$C2p = sqrt(pow($a2p, 2) + pow($b2, 2));
		
		$h1p = self::hp_f($b1, $a1p);
		$h2p = self::hp_f($b2, $a2p);
		
		// Step 2: Calculate dLp, dCp, dHp
		$dLp = $L2 - $L1;
		$dCp = $C2p - $C1p;
		
		$dhp = self::dhp_f($C1,$C2, $h1p, $h2p);
		$dHp = 2*sqrt($C1p*$C2p)*sin(self::radians($dhp)/2.0);
		
		//Step 3: Calculate CIEDE2000 Color-Difference
		$a_L = ($L1 + $L2) / 2.0;
		$a_Cp = ($C1p + $C2p) / 2.0;

		$a_hp = self::a_hp_f($C1,$C2,$h1p,$h2p);
		
		$T = 1-0.17*cos(self::radians($a_hp-30))+0.24*cos(self::radians(2*$a_hp))+0.32*cos(self::radians(3*$a_hp+6))-0.20*cos(self::radians(4*$a_hp-63));
		$d_ro = 30 * exp(-(pow(($a_hp-275)/25,2)));
		$RC = sqrt((pow($a_Cp, 7.0)) / (pow($a_Cp, 7.0) + pow(25.0, 7.0)));
		$SL = 1 + ((0.015 * pow($a_L - 50, 2)) / sqrt(20 + pow($a_L - 50, 2.0)));
		$SC = 1 + 0.045 * $a_Cp;
		$SH = 1 + 0.015 * $a_Cp * $T;
		$RT = -2 * $RC * sin(self::radians(2 * $d_ro));
		$dE = sqrt(pow($dLp /($SL * $kL), 2) + pow($dCp /($SC * $kC), 2) + pow($dHp /($SH * $kH), 2) + $RT * ($dCp /($SC * $kC)) * ($dHp / ($SH * $kH)));
		
		return $dE;
	}
	
	private static function hp_f($x,$y)
	{
		if($x== 0 && $y == 0) return 0;
		
		$tmphp = self::degrees(atan2($x,$y));
		
		if($tmphp >= 0) return $tmphp;
		
		return $tmphp + 360;
	}
	
	private static function dhp_f($C1, $C2, $h1p, $h2p)
	{
		if($C1*$C2 == 0)				return 0;
		else if(abs($h2p-$h1p) <= 180)	return $h2p-$h1p;
		else if(($h2p-$h1p) > 180)		return ($h2p-$h1p)-360;
		else if(($h2p-$h1p) < -180)		return ($h2p-$h1p)+360;
		else							throw(error);
	}
	
	private static function a_hp_f($C1, $C2, $h1p, $h2p)
	{
		if($C1*$C2 == 0)                                      return $h1p+$h2p;
		else if(abs($h1p-$h2p)<= 180)                         return ($h1p+$h2p)/2.0;
		else if((abs($h1p-$h2p) > 180) && (($h1p+$h2p) < 360))  return ($h1p+$h2p+360)/2.0;
		else if((abs($h1p-$h2p) > 180) && (($h1p+$h2p) >= 360)) return ($h1p+$h2p-360)/2.0;
		else                                                throw( new Exception( 'd' ));
	}
	
	private static function degrees($n) { return $n*(180/pi()); }
	private static function radians($n) { return $n*(pi()/180); }
}