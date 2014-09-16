<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the Device Plugin ------
-------------------------------------

This plugin allows UniFaction to identify what device the user is browsing on. This can be used to help us identify what elements should be displayed (without having to use JavaScript).

[[ Credit for this Plugin ]]

This plugin is derived from the work of Anthony Hand, but modified for our commenting structure (such as to adapt it to HHVM).

Details about Anthony's work can be found here:
Project: "Mobile ESP"
Email: anthony.hand@gmail.com
Web Site: http://www.mobileesp.com
Source Files: http://code.google.com/p/mobileesp/


-------------------------------
------ Methods Available ------
-------------------------------

$device = new Device();

$device->isMobilePhone;		// Our most important check with this class

*/

class Device {
	
	
/****** Plugin Variables ******/
	public $useragent = "";			// <str>
	public $httpaccept = "";			// <str>
	
	// Standardized values for true and false.
	public $true = 1;
	public $false = 0;
	
	// Stores values for quick optimization purposes.
	public $initCompleted = 0;			// <int>
	public $isWebkit = 0;				// <int>
	public $isMobilePhone = 0;			// <int>
	public $isIphone = 0;				// <int>
	public $isAndroid = 0;				// <int>
	public $isAndroidPhone = 0;			// <int>
	public $isTierTablet = 0;			// <int>
	public $isTierIphone = 0;			// <int>
	public $isTierRichCss = 0;			// <int>
	public $isTierGenericMobile = 0;	// <int>
	
	// Initialize some initial smart phone string variables.
	public $engineWebKit = 'webkit';				// <str>
	
	public $deviceIphone = 'iphone';				// <str>
	public $deviceIpod = 'ipod';					// <str>
	public $deviceIpad = 'ipad';					// <str>
	public $deviceMacPpc = 'macintosh';				// <str>
	
	public $deviceAndroid = 'android';				// <str>
	public $deviceGoogleTV = 'googletv';			// <str>
	public $deviceHtcFlyer = 'htc_flyer';			// <str>
	
	public $deviceWinPhone7 = 'windows phone os 7';	// <str>
	public $deviceWinPhone8 = 'windows phone 8';		// <str>
	public $deviceWinMob = 'windows ce';				// <str>
	public $deviceWindows = 'windows';					// <str>
	public $deviceIeMob = 'iemobile';					// <str>
	public $devicePpc = 'ppc';							// <str> Stands for PocketPC
	public $enginePie = 'wm5 pie';						// <str> An old Windows Mobile
	
	public $deviceBB = 'blackberry';					// <str>
	public $deviceBB10 = 'bb10';						// <str> For the new BB 10 OS
	public $vndRIM = 'vnd.rim';							// <str> Detectable when BB devices emulate IE or Firefox
	public $deviceBBStorm = 'blackberry95';				// <str> Storm 1 and 2
	public $deviceBBBold = 'blackberry97';				// <str> Bold 97x0 (non-touch)
	public $deviceBBBoldTouch = 'blackberry 99';		// <str> Bold 99x0 (touchscreen)
	public $deviceBBTour = 'blackberry96';				// <str> Tour
	public $deviceBBCurve = 'blackberry89';				// <str> Curve2
	public $deviceBBCurveTouch = 'blackberry 938';		// <str> Curve Touch
	public $deviceBBTorch = 'blackberry 98';			// <str> Torch
	public $deviceBBPlaybook = 'playbook';				// <str> PlayBook tablet
	
	public $deviceSymbian = 'symbian';			// <str>
	public $deviceS60 = 'series60';				// <str>
	public $deviceS70 = 'series70';				// <str>
	public $deviceS80 = 'series80';				// <str>
	public $deviceS90 = 'series90';				// <str>
	
	public $devicePalm = 'palm';				// <str>
	public $deviceWebOS = 'webos';				// <str> For Palm's line of WebOS devices
	public $deviceWebOShp = 'hpwos';			// <str> For HP's line of WebOS devices
	public $engineBlazer = 'blazer';			// <str> Old Palm browser
	public $engineXiino = 'xiino';				// <str> Another old Palm
	
	public $deviceNuvifone = 'nuvifone';		// <str> Garmin Nuvifone
	public $deviceBada = 'bada';				// <str> Samsung's Bada OS
	public $deviceTizen = 'tizen';				// <str> Tizen OS
	public $deviceMeego = 'meego';				// <str> Meego OS
	
	public $deviceKindle = 'kindle'; 			// <str> Amazon Kindle, eInk one
	public $engineSilk = 'silk-accelerated';	// <str> Amazon's accelerated Silk browser for Kindle Fire
	
	// Initialize variables for mobile-specific content.
	public $vndwap = 'vnd.wap';
	public $wml = 'wml';   
	
	//Initialize variables for other random devices and mobile browsers.
	public $deviceTablet = 'tablet';				// <str> Generic term for slate and tablet devices
	public $deviceBrew = 'brew';					// <str>
	public $deviceDanger = 'danger';				// <str>
	public $deviceHiptop = 'hiptop';				// <str>
	public $devicePlaystation = 'playstation';		// <str>
	public $devicePlaystationVita = 'vita';			// <str>
	public $deviceNintendoDs = 'nitro';				// <str>
	public $deviceNintendo = 'nintendo';			// <str>
	public $deviceWii = 'wii';						// <str>
	public $deviceXbox = 'xbox';					// <str>
	public $deviceArchos = 'archos';				// <str>
	
	public $engineOpera = 'opera';					// <str> Popular browser
	public $engineNetfront = 'netfront';			// <str> Common embedded OS browser
	public $engineUpBrowser = 'up.browser';			// <str> common on some phones
	public $engineOpenWeb = 'openweb';				// <str> Transcoding by OpenWave server
	public $deviceMidp = 'midp';					// <str> a mobile Java technology
	public $uplink = 'up.link';						// <str>
	public $engineTelecaQ = 'teleca q';				// <str> a modern feature phone browser
	public $engineObigo = 'obigo';					// <str>W 10 is a modern feature phone browser
	
	public $devicePda = 'pda';						// <str> some devices report themselves as PDAs
	public $mini = 'mini';							// <str> Some mobile browsers put 'mini' in their names.
	public $mobile = 'mobile';						// <str> Some mobile browsers put 'mobile' in their user agent strings.
	public $mobi = 'mobi';							// <str> Some mobile browsers put 'mobi' in their user agent strings.
	
	//Use Maemo, Tablet, and Linux to test for Nokia's Internet Tablets.
	public $maemo = 'maemo';						// <str>
	public $linux = 'linux';						// <str>
	public $qtembedded = 'qt embedded';				// <str> for Sony Mylo and others
	public $mylocom2 = 'com2';						// <str> for Sony Mylo also
	
	//In some UserAgents, the only clue is the manufacturer.
	public $manuSonyEricsson = "sonyericsson";		// <str>
	public $manuericsson = "ericsson";				// <str>
	public $manuSamsung1 = "sec-sgh";				// <str>
	public $manuSony = "sony";						// <str>
	public $manuHtc = "htc";						// <str>
	
	//In some UserAgents, the only clue is the operator.
	public $svcDocomo = "docomo";					// <str>
	public $svcKddi = "kddi";						// <str>
	public $svcVodafone = "vodafone";				// <str>
	
	//Disambiguation strings.
	public $disUpdate = "update";					// <str> pda vs. update
	
	
	public function __construct()
	{
		$this->useragent = isset($_SERVER['HTTP_USER_AGENT'])?strtolower($_SERVER['HTTP_USER_AGENT']):'';
		$this->httpaccept = isset($_SERVER['HTTP_ACCEPT'])?strtolower($_SERVER['HTTP_ACCEPT']):'';
		
		// Let's initialize some values to save cycles later.
		$this->InitDeviceScan();
	}

	//**************************
	// Initialize Key Stored Values.
	public function InitDeviceScan()
	{
		// Save these properties to speed processing
		// global $isWebkit, $isIphone, $isAndroid, $isAndroidPhone;
		$this->isWebkit = $this->DetectWebkit();
		$this->isIphone = $this->DetectIphone();
		$this->isAndroid = $this->DetectAndroid();
		$this->isAndroidPhone = $this->DetectAndroidPhone();
		
		// These tiers are the most useful for web development
		// global $isMobilePhone, $isTierTablet, $isTierIphone;
		$this->isTierTablet = $this->DetectTierTablet(); //Do first
		$this->isTierIphone = $this->DetectTierIphone(); //Do second
		$this->isMobilePhone = $this->DetectMobileQuick(); //Do third
		
		// Optional: Comment these out if you NEVER use them.
		// global $isTierRichCss, $isTierGenericMobile;
		// $this->isTierRichCss = $this->DetectTierRichCss();
		// $this->isTierGenericMobile = $this->DetectTierOtherPhones();
		
		$this->initCompleted = $this->true;
	}

	//**************************
	//Returns the contents of the User Agent value, in lower case.
	public function Get_Uagent()
	{
		return $this->useragent;
	}
	
	//**************************
	//Returns the contents of the HTTP Accept value, in lower case.
	public function Get_HttpAccept()
	{
		return $this->httpaccept;
	}
	
	
	//**************************
	// Detects if the current device is an iPhone.
	public function DetectIphone()
	{
		if($this->initCompleted == $this->true ||
		$this->isIphone == $this->true)
		return $this->isIphone;
		
		if(stripos($this->useragent, $this->deviceIphone) > -1)
		{
		//The iPad and iPod Touch say they're an iPhone. So let's disambiguate.
		if($this->DetectIpad() == $this->true ||
		$this->DetectIpod() == $this->true)
		return $this->false;
		//Yay! It's an iPhone!
		else
		return $this->true;
		}
		else
		return $this->false;
	}

	//**************************
	// Detects if the current device is an iPod Touch.
	public function DetectIpod()
	{
		if(stripos($this->useragent, $this->deviceIpod) > -1)
		return $this->true;
		else
		return $this->false;
	}

	//**************************
	// Detects if the current device is an iPad tablet.
	public function DetectIpad()
	{
	if(stripos($this->useragent, $this->deviceIpad) > -1 &&
	$this->DetectWebkit() == $this->true)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is an iPhone or iPod Touch.
	public function DetectIphoneOrIpod()
	{
	//We repeat the searches here because some iPods may report themselves as an iPhone, which would be okay.
	if($this->DetectIphone() == $this->true ||
	$this->DetectIpod() == $this->true)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects *any* iOS device: iPhone, iPod Touch, iPad.
	public function DetectIos()
	{
	if(($this->DetectIphoneOrIpod() == $this->true) ||
	($this->DetectIpad() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	
	//**************************
	// Detects *any* Android OS-based device: phone, tablet, and multi-media player.
	// Also detects Google TV.
	public function DetectAndroid()
	{
	if($this->initCompleted == $this->true ||
	$this->isAndroid == $this->true)
	return $this->isAndroid;

	if((stripos($this->useragent, $this->deviceAndroid) > -1)
	|| ($this->DetectGoogleTV() == $this->true))
	return $this->true;
	//Special check for the HTC Flyer 7" tablet
	if((stripos($this->useragent, $this->deviceHtcFlyer) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a (small-ish) Android OS-based device
	// used for calling and/or multi-media (like a Samsung Galaxy Player).
	// Google says these devices will have 'Android' AND 'mobile' in user agent.
	// Ignores tablets (Honeycomb and later).
	public function DetectAndroidPhone()
	{
	if($this->initCompleted == $this->true ||
	$this->isAndroidPhone == $this->true)
	return $this->isAndroidPhone;

	if(($this->DetectAndroid() == $this->true) &&
	      (stripos($this->useragent, $this->mobile) > -1))
	return $this->true;

	//Special check for Android phones with Opera Mobile. They should report here.
	if(($this->DetectOperaAndroidPhone() == $this->true))
	return $this->true;
	//Special check for the HTC Flyer 7" tablet. It should report here.
	if((stripos($this->useragent, $this->deviceHtcFlyer) > -1))
	return $this->true;

	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a (self-reported) Android tablet.
	// Google says these devices will have 'Android' and NOT 'mobile' in their user agent.
	public function DetectAndroidTablet()
	{
	//First, let's make sure we're on an Android device.
	if($this->DetectAndroid() == $this->false)
	return $this->false;

	//Special check for Opera Android Phones. They should NOT report here.
	if($this->DetectOperaMobile() == $this->true)
	return $this->false;
	//Special check for the HTC Flyer 7" tablet. It should NOT report here.
	if((stripos($this->useragent, $this->deviceHtcFlyer) > -1))
	return $this->false;

	//Otherwise, if it's Android and does NOT have 'mobile' in it, Google says it's a tablet.
	if(stripos($this->useragent, $this->mobile) > -1)
	return $this->false;
	else
	return $this->true;
	}

	//**************************
	// Detects if the current device is an Android OS-based device and
	//   the browser is based on WebKit.
	public function DetectAndroidWebKit()
	{
	if(($this->DetectAndroid() == $this->true) &&
	      ($this->DetectWebkit() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a GoogleTV.
	public function DetectGoogleTV()
	{
	if(stripos($this->useragent, $this->deviceGoogleTV) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is based on WebKit.
	public function DetectWebkit()
	{
	if($this->initCompleted == $this->true ||
	$this->isWebkit == $this->true)
	return $this->isWebkit;

	if(stripos($this->useragent, $this->engineWebKit) > -1)
	return $this->true;
	else
	return $this->false;
	}


	//**************************
	// Detects if the current browser is EITHER a
	// Windows Phone 7.x OR 8 device.
	public function DetectWindowsPhone()
	{
	if(($this->DetectWindowsPhone8() == $this->true)
	         || ($this->DetectWindowsPhone7() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects a Windows Phone 7.x device (in mobile browsing mode).
	public function DetectWindowsPhone7()
	{
	if(stripos($this->useragent, $this->deviceWinPhone7) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects a Windows Phone 8 device (in mobile browsing mode).
	public function DetectWindowsPhone8()
	{
	if(stripos($this->useragent, $this->deviceWinPhone8) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a Windows Mobile device.
	// Excludes Windows Phone 7 and later devices.
	// Focuses on Windows Mobile 6.xx and earlier.
	public function DetectWindowsMobile()
	{
	if($this->DetectWindowsPhone() == $this->true)
	return $this->false;

	//Most devices use 'Windows CE', but some report 'iemobile'
	//  and some older ones report as 'PIE' for Pocket IE.
	if(stripos($this->useragent, $this->deviceWinMob) > -1 ||
	stripos($this->useragent, $this->deviceIeMob) > -1 ||
	stripos($this->useragent, $this->enginePie) > -1)
	return $this->true;
	//Test for Windows Mobile PPC but not old Macintosh PowerPC.
	     if(stripos($this->useragent, $this->devicePpc) > -1
	        && !(stripos($this->useragent, $this->deviceMacPpc) > 1))
	return $this->true;
	//Test for certain Windwos Mobile-based HTC devices.
	if(stripos($this->useragent, $this->manuHtc) > -1 &&
	stripos($this->useragent, $this->deviceWindows) > -1)
	return $this->true;
	if($this->DetectWapWml() == $this->true &&
	stripos($this->useragent, $this->deviceWindows) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is any BlackBerry device.
	// Includes BB10 OS, but excludes the PlayBook.
	public function DetectBlackBerry()
	{
	if((stripos($this->useragent, $this->deviceBB) > -1) ||
	(stripos($this->httpaccept, $this->vndRIM) > -1))
	return $this->true;
	if($this->DetectBlackBerry10Phone() == $this->true)
	return $this->true;       
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a BlackBerry 10 OS phone.
	// Excludes tablets.
	public function DetectBlackBerry10Phone()
	{
	if((stripos($this->useragent, $this->deviceBB10) > -1) &&
	(stripos($this->useragent, $this->mobile) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is on a BlackBerry tablet device.
	//    Examples: PlayBook
	public function DetectBlackBerryTablet()
	{
	if((stripos($this->useragent, $this->deviceBBPlaybook) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a BlackBerry phone device AND uses a
	//    WebKit-based browser. These are signatures for the new BlackBerry OS 6.
	//    Examples: Torch. Includes the Playbook.
	public function DetectBlackBerryWebKit()
	{
	if(($this->DetectBlackBerry() == $this->true) &&
	      ($this->DetectWebkit() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a BlackBerry Touch phone device with
	//    a large screen, such as the Storm, Torch, and Bold Touch. Excludes the Playbook.
	public function DetectBlackBerryTouch()
	{  
	if((stripos($this->useragent, $this->deviceBBStorm) > -1) ||
	      (stripos($this->useragent, $this->deviceBBTorch) > -1) ||
	      (stripos($this->useragent, $this->deviceBBBoldTouch) > -1) ||
	      (stripos($this->useragent, $this->deviceBBCurveTouch) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a BlackBerry OS 5 device AND
	//    has a more capable recent browser. Excludes the Playbook.
	//    Examples, Storm, Bold, Tour, Curve2
	//    Excludes the new BlackBerry OS 6 and 7 browser!!
	public function DetectBlackBerryHigh()
	{
	//Disambiguate for BlackBerry OS 6 or 7 (WebKit) browser
	if($this->DetectBlackBerryWebKit() == $this->true)
	return $this->false;
	if($this->DetectBlackBerry() == $this->true)
	{
	if(($this->DetectBlackBerryTouch() == $this->true) ||
	stripos($this->useragent, $this->deviceBBBold) > -1 ||
	stripos($this->useragent, $this->deviceBBTour) > -1 ||
	stripos($this->useragent, $this->deviceBBCurve) > -1)
	{
	return $this->true;
	}
	else
	return $this->false;
	}
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a BlackBerry device AND
	//    has an older, less capable browser.
	//    Examples: Pearl, 8800, Curve1.
	public function DetectBlackBerryLow()
	{
	if($this->DetectBlackBerry() == $this->true)
	{
	//Assume that if it's not in the High tier, then it's Low.
	if(($this->DetectBlackBerryHigh() == $this->true) ||
	         ($this->DetectBlackBerryWebKit() == $this->true))
	return $this->false;
	else
	return $this->true;
	}
	else
	return $this->false;
	}


	//**************************
	// Detects if the current browser is the Nokia S60 Open Source Browser.
	public function DetectS60OssBrowser()
	{
	//First, test for WebKit, then make sure it's either Symbian or S60.
	if($this->DetectWebkit() == $this->true)
	{
	if(stripos($this->useragent, $this->deviceSymbian) > -1 ||
	stripos($this->useragent, $this->deviceS60) > -1)
	{
	return $this->true;
	}
	else
	return $this->false;
	}
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is any Symbian OS-based device,
	//   including older S60, Series 70, Series 80, Series 90, and UIQ,
	//   or other browsers running on these devices.
	public function DetectSymbianOS()
	{
	if(stripos($this->useragent, $this->deviceSymbian) > -1 ||
	stripos($this->useragent, $this->deviceS60) > -1 ||
	stripos($this->useragent, $this->deviceS70) > -1 ||
	stripos($this->useragent, $this->deviceS80) > -1 ||
	stripos($this->useragent, $this->deviceS90) > -1)
	return $this->true;
	else
	return $this->false;
	}


	//**************************
	// Detects if the current browser is on a PalmOS device.
	public function DetectPalmOS()
	{
	      //Make sure it's not WebOS first
	      if($this->DetectPalmWebOS() == $this->true)
	         return $this->false;

	//Most devices nowadays report as 'Palm', but some older ones reported as Blazer or Xiino.
	if(stripos($this->useragent, $this->devicePalm) > -1 ||
	stripos($this->useragent, $this->engineBlazer) > -1 ||
	stripos($this->useragent, $this->engineXiino) > -1)
	return $this->true;
	else
	return $this->false;
	}


	//**************************
	// Detects if the current browser is on a Palm device
	//   running the new WebOS.
	public function DetectPalmWebOS()
	{
	if(stripos($this->useragent, $this->deviceWebOS) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is on an HP tablet running WebOS.
	public function DetectWebOSTablet()
	{
	if((stripos($this->useragent, $this->deviceWebOShp) > -1)
	         && (stripos($this->useragent, $this->deviceTablet) > -1))
	return $this->true;
	else
	return $this->false;
	}



	//**************************
	// Detects if the current browser is Opera Mobile or Mini.
	public function DetectOperaMobile()
	{
	if(stripos($this->useragent, $this->engineOpera) > -1)
	{
	if((stripos($this->useragent, $this->mini) > -1) ||
	(stripos($this->useragent, $this->mobi) > -1))
	return $this->true;
	else
	return $this->false;
	}
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is Opera Mobile
	// running on an Android phone.
	public function DetectOperaAndroidPhone()
	{
	if((stripos($this->useragent, $this->engineOpera) > -1) &&
	(stripos($this->useragent, $this->deviceAndroid) > -1) &&
	      (stripos($this->useragent, $this->mobi) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is Opera Mobile
	// running on an Android tablet.  
	public function DetectOperaAndroidTablet()
	{
	if((stripos($this->useragent, $this->engineOpera) > -1) &&
	(stripos($this->useragent, $this->deviceAndroid) > -1) &&
	      (stripos($this->useragent, $this->deviceTablet) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is an Amazon Kindle (eInk devices only).
	// Note: For the Kindle Fire, use the normal Android methods.
	public function DetectKindle()
	{
	if(stripos($this->useragent, $this->deviceKindle) > -1 &&
	$this->DetectAndroid() == $this->false)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current Amazon device has turned on the Silk accelerated browsing feature.
	// Note: Typically used by the the Kindle Fire.
	public function DetectAmazonSilk()
	{
	if(stripos($this->useragent, $this->engineSilk) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if a Garmin Nuvifone device.
	public function DetectGarminNuvifone()
	{
	if(stripos($this->useragent, $this->deviceNuvifone) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects a device running the Bada smartphone OS from Samsung.
	public function DetectBada()
	{
	if(stripos($this->useragent, $this->deviceBada) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects a device running the Tizen smartphone OS.
	public function DetectTizen()
	{
	if(stripos($this->useragent, $this->deviceTizen) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects a device running the Meego OS.
	public function DetectMeego()
	{
	if(stripos($this->useragent, $this->deviceMeego) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects the Danger Hiptop device.
	public function DetectDangerHiptop()
	{
	if(stripos($this->useragent, $this->deviceDanger) > -1 ||
	stripos($this->useragent, $this->deviceHiptop) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current browser is a Sony Mylo device.
	public function DetectSonyMylo()
	{
	if((stripos($this->useragent, $this->manuSony) > -1) &&
	((stripos($this->useragent, $this->qtembedded) > -1) ||
	(stripos($this->useragent, $this->mylocom2) > -1)))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is on one of the Maemo-based Nokia Internet Tablets.
	public function DetectMaemoTablet()
	{
	if(stripos($this->useragent, $this->maemo) > -1)
	return $this->true;
	//For Nokia N810, must be Linux + Tablet, or else it could be something else.
	if((stripos($this->useragent, $this->linux) > -1)
	      && (stripos($this->useragent, $this->deviceTablet) > -1)
	      && ($this->DetectWebOSTablet() == $this->false)
	      && ($this->DetectAndroid() == $this->false))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is an Archos media player/Internet tablet.
	public function DetectArchos()
	{
	if(stripos($this->useragent, $this->deviceArchos) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is an Internet-capable game console.
	// Includes many handheld consoles.
	public function DetectGameConsole()
	{
	if(($this->DetectSonyPlaystation() == $this->true) ||
	       ($this->DetectNintendo() == $this->true) ||
	       ($this->DetectXbox() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a Sony Playstation.
	public function DetectSonyPlaystation()
	{
	if(stripos($this->useragent, $this->devicePlaystation) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a handheld gaming device with
	// a touchscreen and modern iPhone-class browser. Includes the Playstation Vita.
	public function DetectGamingHandheld()
	{
	if((stripos($this->useragent, $this->devicePlaystation) > -1) &&
	(stripos($this->useragent, $this->devicePlaystationVita) > -1))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a Nintendo game device.
	public function DetectNintendo()
	{
	if(stripos($this->useragent, $this->deviceNintendo) > -1 ||
	stripos($this->useragent, $this->deviceWii) > -1 ||
	stripos($this->useragent, $this->deviceNintendoDs) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device is a Microsoft Xbox.
	public function DetectXbox()
	{
	if(stripos($this->useragent, $this->deviceXbox) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects whether the device is a Brew-powered device.
	public function DetectBrewDevice()
	{
	if(stripos($this->useragent, $this->deviceBrew) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects whether the device supports WAP or WML.
	public function DetectWapWml()
	{
	if(stripos($this->httpaccept, $this->vndwap) > -1 ||
	stripos($this->httpaccept, $this->wml) > -1)
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// Detects if the current device supports MIDP, a mobile Java technology.
	public function DetectMidpCapable()
	{
	if(stripos($this->useragent, $this->deviceMidp) > -1 ||
	stripos($this->httpaccept, $this->deviceMidp) > -1)
	return $this->true;
	else
	return $this->false;
	}



	//*****************************
	// Device Classes
	//*****************************

	//**************************
	// Check to see whether the device is *any* 'smartphone'.
	//   Note: It's better to use DetectTierIphone() for modern touchscreen devices.
	public function DetectSmartphone()
	{
	//Exclude duplicates from TierIphone
	if(($this->DetectTierIphone() == $this->true)
	      || ($this->DetectS60OssBrowser() == $this->true)
	      || ($this->DetectSymbianOS() == $this->true)
	      || ($this->DetectWindowsMobile() == $this->true)
	      || ($this->DetectBlackBerry() == $this->true)
	      || ($this->DetectPalmWebOS() == $this->true))
	return $this->true;
	else
	return $this->false;
	}

	//**************************
	// The quick way to detect for a mobile device.
	//   Will probably detect most recent/current mid-tier Feature Phones
	//   as well as smartphone-class devices. Excludes Apple iPads and other modern tablets.
	public function DetectMobileQuick()
	{
		//Let's exclude tablets
		if($this->isTierTablet == $this->true)
		{
			return $this->false;
		}
		
		if($this->initCompleted == $this->true || $this->isMobilePhone == $this->true)
		{
			return $this->isMobilePhone;
		}
		
		// Most mobile browsing is done on smartphones
		if($this->DetectSmartphone() == $this->true)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->mobile) > -1)
		{
			return $this->true;
		}
		
		if(($this->DetectWapWml() == $this->true) || ($this->DetectBrewDevice() == $this->true) || ($this->DetectOperaMobile() == $this->true))
		{
			return $this->true;
		}
		
		if((stripos($this->useragent, $this->engineObigo) > -1) || (stripos($this->useragent, $this->engineNetfront) > -1) || (stripos($this->useragent, $this->engineUpBrowser) > -1) || (stripos($this->useragent, $this->engineOpenWeb) > -1))
		{
			return $this->true;
		}
		
		if(($this->DetectDangerHiptop() == $this->true) || ($this->DetectMidpCapable() == $this->true) || ($this->DetectMaemoTablet() == $this->true) || ($this->DetectArchos() == $this->true))
		{
			return $this->true;
		}
		
		if((stripos($this->useragent, $this->devicePda) > -1) && !(stripos($this->useragent, $this->disUpdate) > -1))
		{
			return $this->true;
		}
		
		// We also look for Kindle devices
		if($this->DetectKindle() == $this->true || $this->DetectAmazonSilk() == $this->true)
		{
			return $this->true;
		}
		
		return $this->false;
	}
	
	//**************************
	// The longer and more thorough way to detect for a mobile device.
	//   Will probably detect most feature phones,
	//   smartphone-class devices, Internet Tablets,
	//   Internet-enabled game consoles, etc.
	//   This ought to catch a lot of the more obscure and older devices, also --
	//   but no promises on thoroughness!
	public function DetectMobileLong()
	{
		if($this->DetectMobileQuick() == $this->true)
		{
			return $this->true;
		}
		
		if($this->DetectGameConsole() == $this->true)
		{
			return $this->true;
		}
		
		if($this->DetectSonyMylo() == $this->true)
		{
			return $this->true;
		}
		
		//Detect older phones from certain manufacturers and operators.
		if(stripos($this->useragent, $this->uplink) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->manuSonyEricsson) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->manuericsson) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->manuSamsung1) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->svcDocomo) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->svcKddi) > -1)
		{
			return $this->true;
		}
		
		if(stripos($this->useragent, $this->svcVodafone) > -1)
		{
			return $this->true;
		}
		
		return $this->false;
	}
	
	
	//*****************************
	// For Mobile Web Site Design
	//*****************************
	
	//**************************
	// The quick way to detect for a tier of devices.
	//   This method detects for the new generation of
	//   HTML 5 capable, larger screen tablets.
	//   Includes iPad, Android (e.g., Xoom), BB Playbook, WebOS, etc.
	public function DetectTierTablet()
	{
		if($this->initCompleted == $this->true || $this->isTierTablet == $this->true)
		{
			return $this->isTierTablet;
		}
		
		if(($this->DetectIpad() == $this->true) || ($this->DetectAndroidTablet() == $this->true) || ($this->DetectBlackBerryTablet() == $this->true) || ($this->DetectWebOSTablet() == $this->true))
		{
			return $this->true;
		}
		
		return $this->false;
	}
	
	
	//**************************
	// The quick way to detect for a tier of devices.
	//   This method detects for devices which can
	//   display iPhone-optimized web content.
	//   Includes iPhone, iPod Touch, Android, Windows Phone 7 and 8, BB10, WebOS, Playstation Vita, etc.
	public function DetectTierIphone()
	{
		if($this->initCompleted == $this->true || $this->isTierIphone == $this->true)
		{
			return $this->isTierIphone;
		}
		
		if(($this->DetectIphoneOrIpod() == $this->true) || ($this->DetectAndroidPhone() == $this->true) || ($this->DetectWindowsPhone() == $this->true) || ($this->DetectBlackBerry10Phone() == $this->true) || ($this->DetectPalmWebOS() == $this->true) || ($this->DetectBada() == $this->true) || ($this->DetectTizen() == $this->true) || ($this->DetectGamingHandheld() == $this->true))
		{
			return $this->true;
		}
		
		// Note: BB10 phone is in the previous paragraph
		if(($this->DetectBlackBerryWebKit() == $this->true) && ($this->DetectBlackBerryTouch() == $this->true))
		{
			return $this->true;
		}
		
		return $this->false;
	}
	
	//**************************
	// The quick way to detect for a tier of devices.
	//   This method detects for devices which are likely to be capable
	//   of viewing CSS content optimized for the iPhone,
	//   but may not necessarily support JavaScript.
	//   Excludes all iPhone Tier devices.
	public function DetectTierRichCss()
	{
		if($this->initCompleted == $this->true || $this->isTierRichCss == $this->true)
		{
			return $this->isTierRichCss;
		}
		
		if($this->DetectMobileQuick() == $this->true)
		{
			//Exclude iPhone Tier and e-Ink Kindle devices
			if(($this->DetectTierIphone() == $this->true) ||
			($this->DetectKindle() == $this->true))
			return $this->false;
			
			//The following devices are explicitly ok.
			if($this->DetectWebkit() == $this->true)
			{
				return $this->true;
			}
			
			if($this->DetectS60OssBrowser() == $this->true)
			{
				return $this->true;
			}
			
			// Note: 'High' BlackBerry devices ONLY
			if($this->DetectBlackBerryHigh() == $this->true)
			{
				return $this->true;
			}
			
			// Older Windows 'Mobile' isn't good enough for iPhone Tier.
			if($this->DetectWindowsMobile() == $this->true)
			{
				return $this->true;
			}
			
			if(stripos($this->useragent, $this->engineTelecaQ) > -1)
			{
				return $this->true;
			}
		}
		
		return $this->false;
	}
	
	
	//**************************
	// The quick way to detect for a tier of devices.
	//   This method detects for all other types of phones,
	//   but excludes the iPhone and RichCSS Tier devices.
	public function DetectTierOtherPhones()
	{
		if($this->initCompleted == $this->true || $this->isTierGenericMobile == $this->true)
		{
			return $this->isTierGenericMobile;
		}
		
		//Exclude devices in the other 2 categories
		if(($this->DetectMobileLong() == $this->true) && ($this->DetectTierIphone() == $this->false) && ($this->DetectTierRichCss() == $this->false))
		{
			return $this->true;
		}
		
		return $this->false;
	}

}
