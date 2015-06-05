<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Form Submission
if(Form::submitted("create-global-notification"))
{
	// Check if all of the input you sent is valid: 
	$_POST['url'] = isset($_POST['url']) ? Security::purify($_POST['url']) : '';
	if(strlen($_POST['url']) > 150)
	{
		Alert::error("URL Length", "Your URL length may not exceed 150 characters.");
	}
	$_POST['message'] = isset($_POST['message']) ? Security::purify($_POST['message']) : '';
	if(strlen($_POST['message']) < 1)
	{
		Alert::error("Message Length", "Please enter a message.");
	}
	elseif(strlen($_POST['message']) > 120)
	{
		Alert::error("Message Length", "Your message length may not exceed 120 characters.");
	}
	
	// Final Validation Test
	if(FormValidate::pass())
	{		
		if(Notifications::createGlobal($_POST['message'], $_POST['url']))
		{
			Alert::success("Notification Success", "The global notification has been created.");
		}
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the Editing Form
echo '
<h3>Create Global Notification</h3>
<form class="uniform" action="/admin/Notifications/Global Notification" method="post">' . Form::prepare("create-global-notification") . '

<p>
	<strong>URL (optional):</strong><br />
	<input type="text" name="url" value="" style="width:100%;" maxlength="150" />
</p>

<p>
	<strong>Message:</strong><br />
	<input type="text" name="message" value="" style="width:100%;" maxlength="120" />
</p>

<p><input type="submit" name="submit" value="Create Notification" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
