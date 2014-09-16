<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/user-panel/reports/user
	
	This page is used to create site reports about users (flag as spam, offensive, etc).
*/

// Form to Submit User Report
if(Form::submitted("create-user-report"))
{
	// Form Validations
	FormValidate::variable("Reason for Report", $_POST['action'], 1, 22, " ");
	FormValidate::number("Importance of Report", $_POST['importance'], 0, min(Me::$clearance + 4, 9));
	FormValidate::variable("User", $_POST['userHandleInput'], 1, 22);
	FormValidate::url("URL", $_POST['url'], 1, 128);
	FormValidate::text("Description (Details)", $_POST['details'], 1, 3500);
	
	// Get the User
	if(!$uniID = User::getIDByHandle($_POST['userHandleInput']))
	{
		Alert::error("Invalid User", "That user is not registered on this site.", 1);
	}
	
	if(FormValidate::pass())
	{
		if(SiteReport::create($_POST['action'], $_POST['url'], Me::$id, $uniID, $_POST['details'], $_POST['importance']))
		{
			Alert::saveSuccess("Report Submitted", "Your report has been submitted! Thank you!");
			
			header("Location: /user-panel"); exit;
		}
	}
}
else
{
	// Sanitize Data
	$_POST['action'] = isset($_POST['action']) ? Sanitize::variable($_POST['action'], " ") : "";
	$_POST['importance'] = isset($_POST['importance']) ? Sanitize::number($_POST['importance'], 0, min(Me::$clearance + 4, 9)) : 0;
	$_POST['url'] = isset($_POST['url']) ? Sanitize::url($_POST['url']) : "";
	$_POST['details'] = isset($_POST['details']) ? Sanitize::text($_POST['details']) : "";
	$_POST['userHandleInput'] = isset($_POST['userHandleInput']) ? Sanitize::variable($_POST['userHandleInput']) : "";
}

// Get Importance Levels
$importanceText = "";

$selectMult = Database::selectMultiple("SELECT arg_key, arg_value FROM schema_selections WHERE selection_name=? AND arg_key BETWEEN ? AND ? ORDER BY arg_key ASC", array("priority", 0, min(Me::$clearance + 4, 9)));

foreach($selectMult as $mult)
{
	$importanceText .= '
	<option value="' . $mult['arg_key'] . '"' . ($_POST['importance'] == $mult['arg_key'] ? ' selected' : '') . '>' . $mult['arg_value'] . '</option>';
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

// Display Form
echo '
<h2 style="margin-top:20px;">Create User Report</h2>

<form class="uniform" action="/user-panel/reports/user" method="post">' . Form::prepare("create-user-report") . '
	<p>
		Reason for Report:<br />
		<select name="action">' . str_replace('value="' . $_POST['action'] . '"', 'value="' . $_POST['action'] . '" selected', '
			<option value="">-- Please select an option --</option>
			<option value="User Spamming">Spammer</option>
			<option value="Offensive User">Offensive Behavior</option>
			<option value="Other">Other Reason</option>') . '
		</select>
	</p>
	<p>
		Importance of Report:<br />
		<select name="importance">
			' . $importanceText . '
		</select>
	</p>
	<p>Provide the user\'s handle, if applicable:<br />' . str_replace('value=""', 'value="' . $_POST['userHandleInput'] . '"', Search::searchBarUserHandle()) . '</p>
	<p>Provide a URL to show us the issue, if possible:<br /><input type="text" name="url" value="' . $_POST['url'] . '" /></p>
	<p>
		Details of Report (please be specific):<br />
		<textarea name="details" style="min-width:350px;min-height:120px;">' . htmlspecialchars($_POST['details']) . '</textarea>
	</p>
	<p><input type="submit" name="submit" value="Submit Report" /></p>
</form>';

echo '
<script>
function UserHandle(handle)
{
	var x = document.getElementById("userHandleInputID");
	x.value = handle;
	
	return false;
}
</script>';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");

