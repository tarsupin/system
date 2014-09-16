<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/user-panel/reports/contact-mods
	
	This page is used to create a report to contact the moderators.
*/

// Form to Contact Mods
if(Form::submitted("contact-mods"))
{
	// Form Validations
	FormValidate::text("Message", $_POST['message'], 20, 3500);
	
	if(FormValidate::pass())
	{
		if(SiteReport::create("Mod Contact", "", Me::$id, 0, $_POST['message']))
		{
			Alert::saveSuccess("Contacted Mods", "Your report has been submitted! Thank you!");
			
			header("Location: /user-panel"); exit;
		}
	}
}
else
{
	// Sanitize Data
	$_POST['message'] = isset($_POST['message']) ? Sanitize::text($_POST['message']) : "";
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

// Display Form
echo '
<h2 style="margin-top:20px;">Contact Moderators</h2>

<form class="uniform" action="/user-panel/reports/contact-mods" method="post">' . Form::prepare("contact-mods") . '
	<p>
		Your Message:<br />
		<textarea name="message" style="min-width:350px;min-height:200px;">' . htmlspecialchars($_POST['message']) . '</textarea>
	</p>
	<p><input type="submit" name="submit" value="Submit Report" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");

