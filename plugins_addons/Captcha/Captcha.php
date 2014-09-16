<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

abstract class Captcha {

/****** Captcha Class ******
* This class handles captcha images and processing.
* 
****** Examples of using this class ******

// 1. Copy "/system/libraries/securimage/securimage_show.php" to "{APP_PATH}/assets/captcha.php"
//		* Alternatively, use our existing one. It's already fully customized.
// 2. Customize the "captcha.php" file as necessary.

// Validate the captcha submission
Captcha::validate($_POST['image_val']);

if(FormValidate::pass())
{
	echo "Your submission was successful!";
}

// Display the HTML for the Captcha
echo '
<form action="/this-page" method="post">
	<img id="captcha" src="/assets/captcha.php" alt="CAPTCHA Image" /><br />
	<input type="text" name="image_val" size="12" maxlength="7" />
	<a href="/this-page" onclick="document.getElementById(\'captcha\').src = \'/assets/captcha.php?\' + Math.random(); return false;"><img src="/assets/icons/refresh.png" alt="Refresh CAPTCHA Image" style="height:18px;" /></a>
</form>
';

****** Methods Available ******
* $success = Captcha::validate($submission);		// Test if a captcha password was validated properly
* 
*/
	
	
/****** Captcha Validation ******/
	public static function validate
	(
		$submission		// <str> The site reference (var) that is used to identify the site.
	)					// RETURNS <void> 
	
	// $success = Captcha::validate($_POST['captcha']);
	{
		// Now to run CAPTCHA
		require(SYS_PATH . '/libraries/securimage/securimage.php');
		
		$securimage = new Securimage();
		
		if($securimage->check($submission) == false)
		{
			// The CAPTCHA form was not valid
			Alert::error("Captcha", "The CAPTCHA value you entered was invalid.", 1);
		}
	}
}


