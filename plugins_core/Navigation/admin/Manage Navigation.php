<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Make sure that only management or higher is allowed
if(Me::$clearance < 7)
{
	header("Location: /admin"); exit;
}

// Sanitize Entries
$_POST['nav_group'] = (isset($_POST['nav_group']) ? Sanitize::variable($_POST['nav_group'], "-") : '');
$_POST['title'] = (isset($_POST['title']) ? Sanitize::safeword($_POST['title'], "'") : '');
$_POST['class'] = (isset($_POST['class']) ? Sanitize::url($_POST['class']) : '');
$_POST['url'] = (isset($_POST['url']) ? Sanitize::url($_POST['url']) : '');
$_POST['sort_order'] = (isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : '');

// Delete Entries
if(isset($_GET['delete']))
{
	$del = Serialize::decode(Decrypt::run("navmanage", $_GET['delete'], "fast"));
	
	if(Database::query("DELETE FROM navigation_loader WHERE nav_group=? AND sort_order=? AND title=? AND url=? AND class=? LIMIT 1", array($del['nav_group'], $del['sort_order'], $del['title'], $del['url'], $del['class'])))
	{
		Alert::success("Delete Success", "You have deleted the navigation entry.");
	}
}

// Update the Navigation Entry
if(Form::submitted(SITE_HANDLE . '-nav-sub'))
{
	FormValidate::variable("Nav Group", $_POST['nav_group'], 1, 22, '-');
	FormValidate::safeword("Title", $_POST['title'], 1, 22, "'");
	FormValidate::safeword("CSS Class", $_POST['class'], 1, 22);
	FormValidate::url("URL", $_POST['url'], 1, 22);
	
	if(FormValidate::pass())
	{
		Database::query("INSERT INTO navigation_loader (nav_group, title, url, class, sort_order) VALUES (?, ?, ?, ?, ?)", array($_POST['nav_group'], $_POST['title'], $_POST['class'], $_POST['class'], $_POST['sort_order']));
		
		Alert::success("Updated Link", "You have successfully updated the navigation link.");
	}
}

// Retrieve the current list of Navigation Entries
$navEntries = Database::selectMultiple("SELECT * FROM navigation_loader", array());

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Get the Navigation List
echo '
<h2 style="margin-top:20px;">Navigation: Menu</h2>
<table class="mod-table">
	<tr>
		<td>Options</td>
		<td>Nav Group</td>
		<td>Title</td>
		<td>CSS Class</td>
		<td>URL</td>
		<td>Position</td>
	</tr>';

foreach($navEntries as $entry)
{
	echo '
	<tr>
		<td><a href="/admin/Navigation/Manage Navigation?delete=' . urlencode(Encrypt::run("navmanage", Serialize::encode($entry), "fast")) . '">Delete</a></td>
		<td>' . $entry['nav_group'] . '</td>
		<td>' . $entry['title'] . '</td>
		<td>' . $entry['class'] . '</td>
		<td>' . $entry['url'] . '</td>
		<td>' . $entry['sort_order'] . '</td>
	</tr>';
}

echo '
</table>';

// Create Navigation Entry
echo '
<h2 style="margin-top:20px;">Create Navigation Link</h2>

<form class="uniform" action="/admin/Navigation/Manage Navigation" method="post">' . Form::prepare(SITE_HANDLE . '-nav-sub') . '
	<p>Group: <input type="text" name="nav_group" value="' . $_POST['nav_group'] . '" /> (e.g. "main-menu", "side-menu", etc.)</p>
	<p>Title: <input type="text" name="title" value="' . $_POST['title'] . '" /></p>
	<p>CSS Class: <input type="text" name="class" value="' . $_POST['class'] . '" /> (e.g. "icon-tag", "icon-pencil", etc.)</p>
	<p>URL: <input type="text" name="url" value="' . $_POST['url'] . '" /> (e.g. "/contact", "http://somesite.com/myPath", etc.)</p>
	<p>Position: <input type="text" name="sort_order" value="' . $_POST['sort_order'] . '" /> (e.g. 1, 5, 20, etc.)</p>
	<p><input type="submit" name="submit" value="Add Navigation Link" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
