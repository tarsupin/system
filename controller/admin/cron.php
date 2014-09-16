<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/admin/modules
	
	This page is used to manage site modules, such as hashtag trends, advertisements, or friend suggestions.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Make sure that only management or higher is allowed
if(Me::$clearance < 8)
{
	header("Location: /admin"); exit;
}

// Delete a Cron Task
if(isset($_POST['delete']))
{
	if(Cron::delete($_POST['delete']))
	{
		Alert::success("Deleted Cron Task", "You have deleted a cron task.");
	}
}

// Prepare Values
$timestamp = time();

// Get Cron List
$cronList = Cron::getList();

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Get the Navigation List
echo '
<h2 style="margin-top:20px;">Navigation: Menu</h2>
<table class="mod-table">
	<tr>
		<td>Options</td>
		<td>Title</td>
		<td>Method</td>
		<td>Run Cycle</td>
		<td>Start Date</td>
		<td>End Date</td>
		<td>Next Activation</td>
	</tr>';
	
foreach($cronList as $task)
{
	echo '
	<tr>
		<td><a href="/admin/cron/custom-task?id=' . $task['id'] . '">Edit</a>, <a href="/admin/cron?delete=' . $task['id'] . '">Delete</a></td>
		<td>' . $task['title'] . '</td>
		<td>' . $task['method'] . '</td>
		<td>' . $task['run_cycle'] . '</td>
		<td>' . Time::fuzzy($task['date_start']) . '</td>
		<td>' . ($task['date_end'] < $task['date_start'] ? '---' : Time::fuzzy($task['date_end'])) . '</td>
		<td>' . ($task['date_nextRun'] < $timestamp ? 'Next Pass' : Time::fuzzy($task['date_nextRun'])) . '</td>
	</tr>';
}

echo '
</table>

<a class="button" href="/admin/cron/create">Create New Cron Task</a>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
