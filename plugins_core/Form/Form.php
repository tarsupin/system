<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Form Plugin ------
-----------------------------------

This plugin provides functionality to secure forms, such as to help mitigate bot submissions, resist XSS attacks, and prevent accidental re-submissions. It is also designed to ensure that the form can only be used by the user (and computer) that was used to load the page.

NOTE: This plugin MUST be used for ALL forms that are created on the UniFaction system.

To use this plugin, you need to use the following two methods:
	
	Form::submitted($identifier);
	
	Form::prepare($identifier);
	
The Form::prepare($identifier) method is used to add special input fields to the form. It adds fields that expect very specific sessions to be set by the user. Without those sessions present, the form will fail.


-----------------------------------------------
------ Example of how to use this plugin ------
-----------------------------------------------

// Check if the form was submitted successfully
if(Form::submitted("example-form-name"))
{
	echo "Your name was submitted successfully!";
}

// Display the form
echo '
<form action="thispage.php" method="post">' . Form::prepare("example-form-name") . '
	Name: <input type="text" name="user" value="" />
	Submit: <input type="submit" name="submit" value="Submit" />
</form>';


-------------------------------
------ Methods Available ------
-------------------------------

Form::prepare($uniqueIdentifier = "", $expiresInMinutes = 300)	// Prepares hidden tags to protect a form.
Form::submitted($uniqueIdentifier = "")							// Validates if the form submission was successful.

*/

abstract class Form {
	
	
/****** Prepare Special Tags for a Form ******/
	public static function prepare
	(
		$uniqueIdentifier = ""		// <str> You can pass test a unique identifier for form validation.
	,	$expiresInMinutes = 300		// <int> Duration until the form is no longer valid. (default is 5 hours)
	)								// RETURNS <str> HTML to insert into the form.
	
	// echo Form::prepare();	// Adds honeypots, salts, and keys to the form to prevent a variety of attacks.
	{
		// Prepare Values
		$salt = Security::randHash(32, 72);
		$currentTime = time();
		
		// Test the identifier that makes forms unique to each user
		$uniqueIdentifier .= SITE_SALT;
		
		// Add User Agent
		$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
		
		// Add Auth Token
		$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
		
		// Add CSRF Token
		//$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
		
		$hash = Security::hash($uniqueIdentifier . $salt . $currentTime . $expiresInMinutes, 82, 72);
		
		// Return the HTML to insert into Form
		return '
		<input type="text" name="tos_soimportant" value="" style="display:none;" />
		<input type="hidden" name="formguard_salt" value="' . $salt . '" />
		<input type="hidden" name="formguard_key" value="' . $currentTime . "-" . $expiresInMinutes . "-" . $hash . '" />
		<input type="text" name="human_answer" value="" style="display:none;" />
		';
	}
	
	
/****** Validate a Form Submission using Special Protection ******/
	public static function submitted
	(
		$uniqueIdentifier = ""	// <str> You can specify a unique identifier that the form validation requires.
		
		/* global $data	is used, and the check includes:		
			-> formguard_salt		The random salt used when the form was created.
			-> formguard_key		The resulting hash from preparation.
			-> tos_soimportant		A honeypot. If anything is written here, it's a spam bot. Form fails.
			-> human_answer			A honeypot. If anything is added here, it's a spam bot. Form fails.
		*/
	)							// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// if(Form::submitted()) { echo "The form has been submitted successfully!"; }
	{
		// Make sure all of the right data was sent
		if(isset($_POST['formguard_key']) && isset($_POST['formguard_salt']) && isset($_POST['tos_soimportant']) && isset($_POST['human_answer']))
		{
			// Make sure the honeypots weren't tripped
			if($_POST['tos_soimportant'] != "") { return false; }
			if($_POST['human_answer'] != "") { return false; }
			
			// Get Important Data
			$keys = explode("-", $_POST['formguard_key'], 3);
			
			// Prepare identifier that will make forms unique to each user
			$uniqueIdentifier .= SITE_SALT;
			
			// Add User Agent
			$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['USER_AGENT']) ? md5($_SESSION[SITE_HANDLE]['USER_AGENT']) : "");
			
			// Add Auth Token
			$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['auth_token']) ? $_SESSION[SITE_HANDLE]['auth_token'] : "");
			
			// Add CSRF Token
			//$uniqueIdentifier .= (isset($_SESSION[SITE_HANDLE]['csrfToken']) ? $_SESSION[SITE_HANDLE]['csrfToken'] : "");
			
			// Generate the Hash
			$hash = Security::hash($uniqueIdentifier . $_POST['formguard_salt'] . $keys[0] . $keys[1], 82, 72);
			
			// Make sure the hash was valid
			if($keys[2] == $hash)
			{
				// Prevent Most Accidental Resubmissions
				$mini = substr($hash, 0, 10);
				
				if(!isset($_SESSION[SITE_HANDLE]['trackForm']))
				{
					$_SESSION[SITE_HANDLE]['trackForm'] = '';
				}
				
				if(strpos($_SESSION[SITE_HANDLE]['trackForm'], "~" . $mini) !== false)
				{
					return false;
				}
				
				$_SESSION[SITE_HANDLE]['trackForm'] = "~" . $mini . substr($_SESSION[SITE_HANDLE]['trackForm'], 0, 110);
				
				// If the submission wasn't a resubmit, post it
				return true;
			}
		}
		
		return false;
	}
}
