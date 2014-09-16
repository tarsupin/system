<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/admin/users/add-forced-url?handle={handle}
	
	This page allows you to add an instruction to a user.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Must be a moderator or higher to edit this page
if(Me::$clearance <= 6)
{
	header("Location: /admin/users"); exit;
}

// Get the user to select instructions from
if(!$user = Database::selectOne("SELECT u.uni_id, u.handle, u.clearance, u.display_name FROM users_handles uh INNER JOIN users u ON u.uni_id=uh.uni_id WHERE u.handle=? LIMIT 1", array($_GET['handle'])))
{
	header("Location: /admin/users"); exit;
}

// Recognize Integers
$user['uni_id'] = (int) $user['uni_id'];
$user['clearance'] = (int) $user['clearance'];

// Run Form
if(Form::submitted("add-ins-admin-user") && $user['clearance'] <= Me::$clearance)
{
	FormValidate::url("URL", $_POST['url'], 1, 64);
	FormValidate::number("Times Activated", $_POST['once'], 0, 1);
	
	// Get the page type
	$parseURL = parse_url($_POST['url']);
	$exp = explode("/", trim($parseURL['path'], "/"));
	
	if($exp[0] == "user-panel")
	{
		array_shift($exp);
	}
	
	if(isset($exp[0]))
	{
		$pageType = $exp[0];
	}
	else
	{
		Alert::error("URL Formatting", "The URL is invalid or too vague. Try changing it.", 1);
	}
	
	// Validate the level of clearance you provided
	if($_POST['clearance'] >= Me::$clearance)
	{
		Alert::error("Clearance", "You cannot add instructions to clearance levels above your own.", 5);
	}
	
	if(FormValidate::pass())
	{
		// Create the Instruction
		UserInstruct::create($user['uni_id'], "UserInstruct", "SendToURL", array($_POST['url'], ($_POST['once'] == 1 ? true : false)));
		
		Alert::saveSuccess("Instruction Added", "The user \"" . $user['handle'] . "\" will be forced to " . $_POST['url'] . ($_POST['once'] == 1 ? " one time" : " until released") . "!");
		
		header("Location: /admin/users"); exit;
	}
}

// Form Sanitization
$_POST['url'] = Sanitize::url($_POST['url']);
$_POST['once'] = $_POST['once'] + 0;

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Make sure this user isn't a higher clearance than you
if($user['clearance'] <= Me::$clearance)
{
	// Create the Add Instruction Form
	echo '
	<form class="uniform" action="/admin/users/add-forced-url?handle=' . $user['handle'] . '" method="post">' . Form::prepare("add-ins-admin-user") . '
		
		<br /><br />
		<h4>URL to force the user to visit:</h4>
		<input type="text" name="url" value="' . $_POST['url'] . '" placeholder="e.g. /contact-us" />
		
		<br /><br />
		<h4>Times Activated:</h4>
		<select name="once">' . str_replace('value="' . $_POST['once'] . '"', 'value="' . $_POST['once'] . '" selected', '
			<option value="0">0 - Force this page to load until trigger is removed</option>
			<option value="1">1 - Load the page once, then delete trigger</option>
			') . '
		</select>
		
		<br /><br />
		<input type="submit" name="submit" value="Add Instruction" />
	</form>';
}
else
{
	echo '<p>This user\'s clearance level higher than yours.</p>';
}
// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
