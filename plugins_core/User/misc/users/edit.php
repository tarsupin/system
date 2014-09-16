<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/admin/users/edit?handle={handle}
	
	This page allows you to edit a user's role and clearance level (if you have proper clearance to edit them).
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Must be a moderator or higher to edit this page
if(Me::$clearance <= 6)
{
	header("Location: /admin/users"); exit;
}

// Get The User to Edit
if(!$user = Database::selectOne("SELECT u.uni_id, u.role, u.clearance, u.handle, u.display_name, u.date_joined, u.date_lastLogin FROM users_handles uh INNER JOIN users u ON u.uni_id=uh.uni_id WHERE u.handle=? LIMIT 1", array($_GET['handle'])))
{
	header("Location: /admin/users"); exit;
}

// Recognize Integers
$user['uni_id'] = (int) $user['uni_id'];
$user['clearance'] = (int) $user['clearance'];
$user['date_joined'] = (int) $user['date_joined'];
$user['date_lastLogin'] = (int) $user['date_lastLogin'];

// Run Form
if(Form::submitted("admin-edit-user") && $user['clearance'] < Me::$clearance)
{
	FormValidate::variable("Role", $_POST['role'], 1, 12);
	
	// Validate the level of clearance you provided
	if($_POST['clearance'] >= Me::$clearance)
	{
		Alert::error("Clearance", "You cannot increase clearance to be equal to or above your own.", 5);
	}
	
	FormValidate::number("User Clearance", $_POST['clearance'], -9, min(9, Me::$clearance - 1));
	
	if(FormValidate::pass())
	{
		$user['clearance'] = $_POST['clearance'] + 0;
		$user['role'] = $_POST['role'];
		
		Database::query("UPDATE users SET role=?, clearance=? WHERE uni_id=? LIMIT 1", array($user['role'], $user['clearance'], $user['uni_id']));
		
		Alert::success("User Update", "You have updated the user \"" . $user['handle'] . "\"!");
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Make sure this user isn't a higher clearance than you
if($user['clearance'] < Me::$clearance)
{
	// Create the Edit Form
	echo '
	<form class="uniform" action="/admin/users/edit?handle=' . $_GET['handle'] . '" method="post">' . Form::prepare("admin-edit-user") . '
		
		<h4>Details</h4>
		User: <a href="' . URL::unifaction_social() . '/' . $user['handle'] . '">' . $user['display_name'] . '</a> (<a href="' . URL::unifaction_social() . '/' . $user['handle'] . '">@' . $user['handle'] . '</a>)<br />
		Date Joined: ' . Time::fuzzy($user['date_joined']) . '<br />
		Last Login: ' . Time::fuzzy($user['date_lastLogin']) . '
		
		<br /><br />
		<h4>Role:</h4>
		<select name="role">' . str_replace('value="' . $user['role'] . '"', 'value="' . $user['role'] . '" selected', '
			<option value="">-- Select a Role --</option>
			<option value="admin">Admin</option>
			<option value="staff">Staff</option>
			<option value="mod">Moderator</option>
			<option value="vip">VIP / Trusted User</option>
			<option value="member">Member</option>
			<option value="user">User</option>
			<option value="restricted">Restricted User</option>
			<option value="banned">Banned</option>') . '
		</select>
		
		<input type="text" name="role_custom" value="" placeholder="Custom Role . . ." />
		
		<br /><br />
		<h4>Clearance:</h4>
		<select name="clearance">' . str_replace('value="' . $user['clearance'] . '"', 'value="' . $user['clearance'] . '" selected', '
			<option value="">-- Select Clearance Level --</option>
			<option value="9">9 - Superadmin, Webmaster</option>
			<option value="8">8 - Staff Administrator</option>
			<option value="7">7 - Management Staff</option>
			<option value="6">6 - Moderators &amp; Staff</option>
			<option value="5">5 - Staff</option>
			<option value="4">4 - Interns, Assistants</option>
			<option value="3">3 - VIP / Trusted Users</option>
			<option value="2">2 - Users</option>
			<option value="1">1 - Limited User</option>
			<option value="0">0 - Guest</option>
			<option value="-1">-1 - Silenced User</option>
			<option value="-2">-2 - Restricted User</option>
			<option value="-6">-6 - Temporarily Banned</option>
			<option value="-9">-9 - Permanently Banned</option>') . '
		</select>';
		
		// Show Instructions
		echo '
		<br /><br />
		<h4>Current Instructions:</h4>
		<a href="/admin/users/add-forced-url?handle=' . $user['handle'] . '">Add Forced URL Instruction</a>
		<br /><br />';
		
		if($instructions = UserInstruct::get($user['uni_id']))
		{
			echo '
			<table class="mod-table">
				<tr>
					<td><strong>Method</strong></td>
					<td><strong>Key</strong></td>
					<td><strong>Value</strong></td>
				</tr>';
			
			foreach($instructions as $ins)
			{
				echo '
				<tr>
					<td>' . $ins['plugin'] . '</td>
					<td>' . $ins['behavior'] . '</td>
					<td>' . $ins['params'] . '</td>
				</tr>';
			}
			
			echo '
			</table>';
		}
		else
		{
			echo '
			<p>None</p>';
		}
		
		echo '
		<br /><br />
		<input type="submit" name="submit" value="Update User" />
	</form>';
}
else
{
	echo '<p>This user\'s clearance level is equal to or higher than yours.</p>';
}
// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
