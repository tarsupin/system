<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Remove an entry from the queue
if(isset($_GET['done']))
{
	$denyID = (int) $_GET['done'];
	
	if(Content::denyQueue($denyID))
	{
		Alert::success("Queue Removed", "The content entry has been removed from the queue.");
	}
}

// Get a list of the site's queued content
$approveList = Database::selectMultiple("SELECT q.last_update, c.id, c.title, c.url_slug, c.primary_hashtag, u.handle FROM content_queue q INNER JOIN content_entries c ON q.content_id=c.id INNER JOIN users u ON c.uni_id=u.uni_id ORDER BY q.last_update DESC LIMIT 20", array());

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display Category Table
echo '
<div style="margin-bottom:22px; font-size:0.85em;">
<table class="mod-table">
	<tr>
		<td><strong>Options</strong></td>
		<td><strong>ID</strong></td>
		<td><strong>URL</strong></td>
		<td><strong>Primary Hashtag</strong></td>
		<td><strong>Title</strong></td>
		<td><strong>User</strong></td>
	</tr>';

foreach($approveList as $post)
{
	// Display Row
	echo '
	<tr>
		<td><a href="/article-write?content=' . $post['id'] . '" target="_new">Review</a> | <a href="/admin/Content/Approve Posts?done=' . $post['id'] . '">Remove From Queue</a></td>
		<td>' . $post['id'] . '</td>
		<td><a href="/' . $post['url_slug'] . '" target="_new">' . $post['url_slug'] . '</a></td>
		<td><a href="' . URL::hashtag_unifaction_com() . '/' . $post['primary_hashtag'] . '" target="_new">#' . $post['primary_hashtag'] . '</a></td>
		<td>' . $post['title'] . '</td>
		<td>' . $post['handle'] . '</td>
	</tr>';
}

echo '
</table>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");