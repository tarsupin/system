<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------
------ About the Email Plugin ------
------------------------------------

This class allows you to send emails, as well as provides handling for attachments.


-----------------------------------------
------ Example of using the plugin ------
-----------------------------------------

// Emails "someone@hotmail.com" with an important update.
Email::send("someone@hotmail.com", "Super Important", "Dude, this is incredibly important!!!");


-------------------------------
------ Methods Available ------
-------------------------------

Email::valid($email)		// Returns TRUE if the email is valid, otherwise FALSE
Email::parse($email)		// Parses an email for more detailed information about it

// Sends an email
Email::send($emailTo, $subject, $message, [$emailFrom], [$headers])

// Sends an email with an attachment
Email::sendAttachment($emailTo, $subject, $message, $filepath, $filename, [$emailFrom])

*/

abstract class Email {
	
	
/****** Sends a Simple Email ******/
	public static function valid
	(
		string $email		// <str> The email to validate.
	): bool				// RETURNS <bool> TRUE if the email is valid and properly formatted, FALSE if not.
	
	// Email::valid($email)
	{
		return ($emailData = self::parse($email)) ? true : false;
	}
	
	
/****** Sends a Simple Email ******/
	public static function parse
	(
		string $email		// <str> The email to parse.
	): array <str, str>				// RETURNS <str:str> data about the email, or array() on failure.
	
	// $parsedEmail = Email::parse($email)
	{
		// Make sure the email doesn't contain illegal characters
		$email = Sanitize::variable($email, "@.-+", false);
		
		if(Sanitize::$illegalChars != array())
		{
			Alert::error("Email", "The email does not allow: " . FormValidate::announceIllegalChars(Sanitize::$illegalChars), 3);
			return array();
		}
		
		// Make sure the email has an "@"
		if(strpos($email, "@") === false)
		{
			Alert::error("Email", "Email improperly formatted: doesn't include an @ character.", 3);
			return array();
		}
		
		// Prepare Values
		$emailData = array();
		$exp = explode("@", $email);
		
		$emailData['full'] = $email;
		$emailData['username'] = $exp[0];
		$emailData['domain'] = $exp[1];
		
		$lenEmail = strlen($email);
		$lenUser = strlen($emailData['username']);
		$lenDomain = strlen($emailData['domain']);
		
		// Check if the email is too long
		if($lenEmail > 72)
		{
			Alert::error("Email", "Email is over 72 characters long.", 1);
			return array();
		}
		
		// Check if the username is too long
		if($lenUser < 1 or $lenUser > 50)
		{
			Alert::error("Email", "Email username must be between 1 and 50 characters.", 2);
			return array();
		}
		
		// Check if the domain is too long
		if($lenDomain < 1 or $lenDomain > 50)
		{
			Alert::error("Email", "Email domain must be between 1 and 50 characters.", 2);
			return array();
		}
		
		// Check for valid emails with the username
		if($emailData['username'][0] == '.' or $emailData['username'][($lenUser - 1)] == '.')
		{
			Alert::error("Email", "Email username cannot start or end with a period.", 5);
			return array();
		}
		
		// Username cannot have two consecutive dots
		if(strpos($emailData['username'], "..") !== false)
		{
			Alert::error("Email", "Email username cannot contain two consecutive periods.", 5);
			return array();
		}
		
		// Check the domain for valid characters
		if(!isSanitized::variable($emailData['domain'], "-."))
		{
			Alert::error("Email", "Email domain was not properly sanitized.", 3);
			return array();
		}
		
		// Return the email data
		return $emailData;
	}
	
	
/****** Sends a Simple Email ******/
	public static function send
	(
		mixed $emailTo			// <mixed> The email(s) you're sending a message to.
	,	string $subject			// <str> The subject of the message.
	,	string $message			// <str> The content of your message.
	,	string $emailFrom = ""		// <str> An email that you would like to send from.
	,	string $headers = ""		// <str> A set of headers in a single string (use cautiously).
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Email::send(array("somebody@email.com"), "Greetings!", "This welcome message will make you feel welcome!")
	{
		global $config;
		
		// Determine the Email being sent from
		if($emailFrom == "" && isset($config['admin-email']))
		{
			$emailFrom = $config['admin-email'];
		}
		
		// Handle Email Recipients
		if(is_array($emailTo))
		{
			foreach($emailTo as $next)
			{
				if(!self::valid($next))
				{
					Alert::error("Email", "Illegal email used, cannot send email.", 3);
					return false;
				}
			}
			
			$emailTo = implode(", ", $emailTo);
		}
		else if(!self::valid($emailTo))
		{
			Alert::error("Email", "Illegal email used, cannot send email.", 3);
			return false;
		}
		
		// Handle the Email Headers
		if($headers == "")
		{
			$headers = 'From: ' . $emailFrom . "\r\n" .
			'Reply-To: ' . $emailFrom . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		}
		
		// Record this email in the database
		$primeRecipient = is_array($emailTo) ? $emailTo[0] : $emailTo;
		
		$details = array(
			"recipients"	=> $emailTo
		,	"sender"		=> $emailFrom
		);
		
		Database::query("INSERT INTO log_email (recipient, subject, message, details, date_sent) VALUES (?, ?, ?, ?, ?)", array($primeRecipient, $subject, $message, Serialize::encode($details), time()));
		
		// Localhost Versions, just edit email.html with the message
		if(ENVIRONMENT == "local")
		{
			return File::write(APP_PATH . "/email.html", "To: " . $emailTo . "
From: " . $emailFrom . "
Subject: " . $subject . "

" . $message);
		}
		
		// Send the Mail
		if(!mail($emailTo, $subject, $message, $headers))
		{
			Alert::error("Email", "Email was not sent properly", 4);
			return false;
		}
		
		return true;
	}
	
	
/****** Sends an Email with an Attachment ******/
# dqhendricks on Stack Overflow provided the "$header" code of this section.
	public static function sendAttachment
	(
		mixed $emailTo			// <mixed> The email(s) you're sending a message to.
	,	string $subject			// <str> The subject of the message.
	,	string $message			// <str> The content of your message.
	,	string $filePath			// <str> The file path to the attachment you're sending.
	,	string $filename			// <str> The name of the file as you'd like it to appear.
	,	string $emailFrom = ""		// <str> An email that you would like to send from.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Email::sendAttachment("joe@email.com", "Hi!", "Sup!?", "./assets/file.csv", "excelPage.csv"])
	// May use: $_FILES["file"]["tmp_name"] and $_FILES["file"]["name"]
	{
		global $config;
		
		// Determine the Email being sent from
		if($emailFrom == "" && isset($config['admin-email']))
		{
			$emailFrom = $config['admin-email'];
		}
		
		// Handle Email Recipients
		if(is_array($emailTo))
		{
			foreach($emailTo as $next)
			{
				if(!self::valid($next))
				{
					Alert::error("Email", "Illegal email used, cannot send email.", 3);
					return false;
				}
			}
			
			$emailTo = implode(", ", $emailTo);
		}
		else if(!self::valid($emailTo))
		{
			Alert::error("Email", "Illegal email used, cannot send email.", 3);
			return false;
		}
		
		// $filePath should include path and filename
		$filename = basename($filename);
		$file_size = filesize($filePath);
		
		$content = chunk_split(base64_encode(file_get_contents($filePath))); 
		
		$uid = md5(uniqid(time()));
		
		// Designed to prevent email injection, although we should run stricter validation if we're going to allow
		// other people to insert emails into the email.
		$emailFrom = str_replace(array("\r", "\n"), '', $emailFrom);
		
		// Prepare header
		$header = "From: ".$emailFrom."\r\n"
			."MIME-Version: 1.0\r\n"
			."Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n"
			."This is a multi-part message in MIME format.\r\n" 
			."--".$uid."\r\n"
			."Content-type:text/plain; charset=iso-8859-1\r\n"
			."Content-Transfer-Encoding: 7bit\r\n\r\n"
			.$message."\r\n\r\n"
			."--".$uid."\r\n"
			."Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"
			."Content-Transfer-Encoding: base64\r\n"
			."Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n"
			.$content."\r\n\r\n"
			."--".$uid."--";
		
		// Record this email in the database
		$primeRecipient = is_array($emailTo) ? $emailTo[0] : $emailTo;
		
		$details = array(
			"recipients"	=> $emailTo
		,	"sender"		=> $emailFrom
		,	"file"			=> $filename
		);
		
		Database::query("INSERT INTO log_email (recipient, subject, message, details, date_sent) VALUES (?, ?, ?, ?, ?)", array($primeRecipient, $subject, $message, Serialize::encode($details), time()));
		
		// Localhost Versions, just edit email.html with the message
		if(ENVIRONMENT == "local")
		{
			return File::write(APP_PATH . "/email.html", "To: " . $emailTo . "
From: " . $emailFrom . "
Subject: " . $subject . "
Attachment: " . $filename . "

" . $message);
		}
		
		// Send the email
		if(!mail($emailTo, $subject, "", $header))
		{
			Alert::error("Email", "Email was not sent properly.", 4);
			return false;
		}
		
		return true;
	}
	
}