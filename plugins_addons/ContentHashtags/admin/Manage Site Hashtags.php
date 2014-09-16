<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the current hashtag being edited
$hashtagData = array();

if($activeHashtag = (isset($_GET['hashtag']) ? $_GET['hashtag'] : ''))
{
	$hashtagData = Database::selectOne("SELECT * FROM content_site_hashtags WHERE hashtag=? LIMIT 1", array($activeHashtag));
}

// If there is a deletion being run
else if(isset($_GET['delete']))
{
	if(Database::query("DELETE FROM content_site_hashtags WHERE hashtag=? LIMIT 1", array($_GET['delete'])))
	{
		Alert::success("Hashtag Deleted", "You have successfully deleted the hashtag!");
	}
}

// Get a list of the site's hashtags
$hashtagList = Database::selectMultiple("SELECT * FROM content_site_hashtags ORDER BY hashtag", array());

// Sanitize & Prepare Data
$_POST['hashtag'] = (isset($_POST['hashtag']) ? Sanitize::variable($_POST['hashtag']) : '');
$_POST['title'] = (isset($_POST['title']) ? Sanitize::safeword($_POST['title']) : '');

// Form Submission
if(Form::submitted("manage-hash-art"))
{
	if($activeHashtag)
	{
		if(Database::query("UPDATE content_site_hashtags SET hashtag=?, title=? WHERE hashtag=? LIMIT 1", array($_POST['hashtag'], $_POST['title'], $activeHashtag)))
		{
			Alert::saveSuccess("Hashtag Updated", "You have successfully updated the hashtag.");
			
			header("Location: /admin/ContentHashtags/Manage Site Hashtags"); exit;
		}
		else
		{
			Alert::error("Hashtag Error", "There was an error while trying to update this hashtag.");
		}
	}
	else
	{
		if(Database::query("REPLACE INTO content_site_hashtags (hashtag, title) VALUES (?, ?)", array($_POST['hashtag'], $_POST['title'])))
		{
			Alert::saveSuccess("Hashtag Created", "You have successfully created the hashtag.");
			
			header("Location: /admin/ContentHashtags/Manage Site Hashtags"); exit;
		}
		else
		{
			Alert::error("Hashtag Error", "There was an error while trying to create this hashtag.");
		}
	}
}

// Prepare Defaults
if($hashtagData)
{
	if(!$_POST['hashtag']) { $_POST['hashtag'] = $hashtagData['hashtag']; }
	if(!$_POST['title']) { $_POST['title'] = $hashtagData['title']; }
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display Hashtag Table
echo '
<div style="margin-bottom:22px; font-size:0.85em;">
<table class="mod-table">
	<tr>
		<td><strong>Edit</strong></td>
		<td><strong>Hashtag</strong></td>
		<td><strong>Title</strong></td>
	</tr>';

foreach($hashtagList as $hash)
{
	echo '
	<tr>
		<td>
			<a href="/admin/ContentHashtags/Manage Site Hashtags?hashtag=' . $hash['hashtag'] . '">Edit</a>
			| <a href="/admin/ContentHashtags/Manage Site Hashtags?delete=' . $hash['hashtag'] . '">Delete</a>
		</td>
		<td>' . $hash['hashtag'] . '</td>
		<td>' . $hash['title'] . '</td>
	</tr>';
}

echo '
</table>
</div>';

// Display the Form
echo '
<h2>' . ($hashtagData ? 'Edit Hashtag: ' . $hashtagData['title'] : 'Create New Hashtag') . '</h2>
<form class="uniform" action="/admin/ContentHashtags/Manage Site Hashtags' . ($activeHashtag ? '?hashtag=' . $activeHashtag : "") . '" method="post">' . Form::prepare("manage-hash-art") . '
	<p>Hashtag: <input type="text" name="hashtag" value="' . $_POST['hashtag'] . '" tabindex="5" /> (e.g. #MadisonWI, #GBPackers)</p>
	<p>Title: <input type="text" name="title" value="' . $_POST['title'] . '" tabindex="10" /> (e.g. "Madison, Wisconsin", "Green Bay Packers")</p>
	<p><input type="submit" name="submit" value="' . ($activeHashtag ? 'Update Hashtag' : 'Create Hashtag') . '" tabindex="30" /></p>
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");