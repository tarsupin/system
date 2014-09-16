<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Prepare Variables
$_POST['page'] = (isset($_POST['page']) ? $_POST['page'] + 0 : 1);
$usersToShow = 20;

// Get Users
$users = Database::selectMultiple("SELECT u.uni_id, u.role, u.clearance, u.handle, u.display_name FROM users_handles uh INNER JOIN users u ON u.uni_id=uh.uni_id ORDER BY uh.handle LIMIT " . (($_POST['page'] - 1) * $usersToShow) . ", " . ($usersToShow + 0), array());

// Recognize Integers
$user['uni_id'] = (int) $user['uni_id'];
$user['clearance'] = (int) $user['clearance'];

$userCount = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM users", array());

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Get a list of users
echo '
<script>
function UserHandle(handle)
{
	var x = document.getElementById("userHandleInputID");
	x.value = handle;
}

function editUser()
{
	var x = document.getElementById("userHandleInputID");
	window.location = "/admin/users/edit?handle=" + x.value;
}
</script>

<div style="display:inline-block; width:200px; margin-top:25px;">' . Search::searchBarUserHandle() . '</div>
<div><input class="button" type="submit" name="submit" value="Edit User" onclick="editUser();" /></div>';

echo '
<h2 style="margin-top:20px;">User List</h2>
<table class="mod-table">
	<tr>
		<td>Options</td>
		<td>Handle</td>
		<td>Role</td>
		<td>Clearance</td>
		<td>Display Name</td>
	</tr>';

foreach($users as $user)
{
	echo '
	<tr>
		<td><a href="/admin/users/edit?handle=' . $user['handle'] . '">Edit</a></td>
		<td>' . $user['handle'] . '</td>
		<td style="text-align:center;">' . $user['role'] . '</td>
		<td style="text-align:center;">' . $user['clearance'] . '</td>
		<td>' . $user['display_name'] . '</td>
	</tr>';
}

echo '
</table>';

// Provide Pagination for Users
$pages = new Pagination($userCount, $usersToShow, $_POST['page']);

if($pages->highestPage > 1)
{
	echo '
	<div>';
	
	foreach($pages->pages as $page)
	{
		echo '<a href="/admin/users?page=' . $page . '">' . $page . '</a>';
	}
	
	echo '
	</div>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
