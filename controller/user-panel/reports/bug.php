<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/user-panel/reports/bug
	
	This page is used to create bug reports.
*/

// Form to Post a Bug Report
if(Form::submitted("create-bug-report"))
{
	// Form Validations
	FormValidate::url("URL", $_POST['url'], 0, 128);
	FormValidate::number("Urgency of Report", $_POST['importance'], 0, min(Me::$clearance + 5, 9));
	FormValidate::text("Issue", $_POST['issue'], 20, 1500);
	FormValidate::text("Steps to Recreate", $_POST['steps'], 20, 2500);
	FormValidate::text("Additional Notes", $_POST['additional'], 0, 1500);
	
	if(FormValidate::pass())
	{
		$details = '[[[ Bug Details ]]]
' . $_POST['issue'] . '

[[[ Steps to Recreate ]]]
' . $_POST['steps'] . '

[[[ Additional Notes ]]]
' . $_POST['additional'];
		
		if(SiteReport::create("Bug Report", $_POST['url'], Me::$id, 0, $details, $_POST['importance']))
		{
			Alert::saveSuccess("Report Submitted", "Your report has been submitted! Thank you!");
			
			header("Location: /user-panel"); exit;
		}
	}
}
else
{
	// Sanitize Data
	$_POST['url'] = isset($_POST['url']) ? Sanitize::url($_POST['url']) : "";
	$_POST['importance'] = isset($_POST['importance']) ? Sanitize::number($_POST['importance'], 0, Me::$clearance + 5) : 0;
	$_POST['issue'] = isset($_POST['issue']) ? Sanitize::text($_POST['issue']) : "";
	$_POST['steps'] = isset($_POST['steps']) ? Sanitize::text($_POST['steps']) : "";
	$_POST['additional'] = isset($_POST['additional']) ? Sanitize::text($_POST['additional']) : "";
}

// Get Importance Levels
$importanceText = "";

$selectMult = Database::selectMultiple("SELECT arg_key, arg_value FROM schema_selections WHERE selection_name=? AND arg_key BETWEEN ? AND ? ORDER BY arg_key ASC", array("priority", 0, Me::$clearance + 5));

foreach($selectMult as $mult)
{
	$importanceText .= '
	<option value="' . $mult['arg_key'] . '"' . ($_POST['importance'] == $mult['arg_key'] ? ' selected' : '') . '>' . $mult['arg_value'] . '</option>';
}

// Run Header
require(SYS_PATH . "/controller/includes/user_panel_header.php");

// Display Form
echo '
<h2 style="margin-top:20px;">Create Bug Report</h2>

<form class="uniform" action="/user-panel/reports/bug" method="post">' . Form::prepare("create-bug-report") . '
	<p>Provide a URL to show us the issue, if possible:<br /><input type="text" name="url" value="' . $_POST['url'] . '" /></p>
	<p>
		Describe the issue (please elaborate):<br />
		<textarea name="issue" style="min-width:350px;min-height:120px;">' . htmlspecialchars($_POST['issue']) . '</textarea>
	</p>
	<p>
		Urgency of Report:<br />
		<select name="importance">
			' . $importanceText . '
		</select>
	</p>
	<p>
		What steps will recreate the issue?:<br />
		<textarea name="steps" style="min-width:350px;min-height:120px;">' . htmlspecialchars($_POST['steps']) . '</textarea>
	</p>
	<p>
		Additional notes:<br />
		<textarea name="additional" style="min-width:350px;min-height:120px;">' . htmlspecialchars($_POST['additional']) . '</textarea>
	</p>
	<p><input type="submit" name="submit" value="Submit Report" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/user_panel_footer.php");

