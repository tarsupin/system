<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Make sure that only management or higher is allowed
if(Me::$clearance < 7)
{
	header("Location: /admin"); exit;
}

if(Form::submitted("email-send-admin"))
{
	FormValidate::email($_POST['email']);
	FormValidate::input("Subject", $_POST['title'], 1, 22);
	FormValidate::text("Message", $_POST['message'], 1, 3500);
	
	if(FormValidate::pass())
	{
		Email::send($_POST['email'], $_POST['title'], $_POST['message']);
		
		Alert::saveSuccess("Email Sent", "You have successfully sent an email to " . $_POST['email']);
		
		header("Location: /admin/Email/Email List"); exit;
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

echo '
<form action="/admin/Email/Send Email" method="post">' . Form::prepare("email-send-admin") . '
	<p>Email: <input type="text" name="email" value="" /></p>
	<p>Subject: <input type="text" name="title" value="" maxlength="22" /></p>
	<p>Message: <textarea name="message"></textarea></p>
	<p><input type="submit" name="submit" value="Send Email" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
