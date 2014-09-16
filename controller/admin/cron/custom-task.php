<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } 

/*
	/admin/cron/custom-task
	
	This page is used to create custom cron tasks.
*/

// Run Permissions
require(SYS_PATH . "/controller/includes/admin_perm.php");

// Make sure that administrators are allowed
if(Me::$clearance < 8)
{
	header("Location: /admin"); exit;
}

// Edit mode is active if $_POST['id'] is active and the ID exists
$editID = (isset($_POST['id']) ? $_POST['id'] + 0 : 0);

// Form to Create the Module
if(Form::submitted("cron-custom"))
{
	// Validate Data
	FormValidate::variable("Title", $_POST['title'], 1, 22, " -,.:;!?()$[]");
	FormValidate::variable("Method", $_POST['method'], 1, 22);
	
	FormValidate::number("Run Cycle", $_POST['run_cycle'], 0);
	FormValidate::number("Start Date", $_POST['date_start'],0);
	FormValidate::number("End Date", $_POST['date_end'], 0);
	
	// Still need to validate parameter data
	$args = (isset($_POST['args']) ? $_POST['args'] : array());
	
	foreach($args as $key => $val)
	{
		if($val == "")
		{
			unset($args[$key]);
		}
		else
		{
			$args[$key] = Sanitize::text($val);
		}
	}
	
	if(FormValidate::pass())
	{
		// If we're in edit mode, edit the existing task
		if($editID)
		{
			if(Cron::edit($editID, $_POST['title'], $_POST['method'], $args, $_POST['run_cycle'], $_POST['date_start'], $_POST['date_end']))
			{
				Alert::saveSuccess("Edited Task", "You have successfully edited the cron task.");
				
				header("Location: /admin/cron"); exit;
			}
		}
		
		// Create the cron task
		else if(Cron::create($_POST['title'], $_POST['method'], $args, $_POST['run_cycle'], $_POST['date_start'], $_POST['date_end']))
		{
			Alert::saveSuccess("Created Task", "You have successfully created a cron task.");
			
			header("Location: /admin/cron"); exit;
		}
	}
}
else
{
	// If we're in edit mode
	if($editID)
	{
		// Make sure the task we're editing exists (or redirect)
		if(!$cronData = Cron::getData($editID))
		{
			header("Location: /admin/cron"); exit;
		}
		
		// Set default values to the task being edited
		if(!isset($_POST['method']))
		{
			$_POST['title'] = $cronData['title'];
			$_POST['method'] = $cronData['method'];
			$_POST['run_cycle'] = $cronData['run_cycle'];
			$_POST['date_start'] = $cronData['date_start'];
			$_POST['date_end'] = $cronData['date_end'];
			$_POST['title'] = $cronData['title'];
			
			// Prepare Arguments
			if($args = Site::getArgs("CronTask", $editID))
			{
				$_POST['args'] = array();
				
				foreach($args as $key => $val)
				{
					$_POST['args'][$key] = $val;
				}
			}
		}
	}
	
	// If there are no defaults
	if(!isset($_POST['run_cycle'])) { $_POST['run_cycle'] = 3600; }
	if(!isset($_POST['date_start'])) { $_POST['date_start'] = time() ; }
	if(!isset($_POST['date_end'])) { $_POST['date_end'] = 10; }
	
	// Sanitize Values
	$_POST['title'] = Sanitize::safeword($_POST['title']);
	$_POST['method'] = Sanitize::variable($_POST['method']);
	$_POST['run_cycle'] = Sanitize::number($_POST['run_cycle'], 0);
	$_POST['date_start'] = Sanitize::number($_POST['date_start'], 0);
	$_POST['date_end'] = Sanitize::number($_POST['date_end'], 0);
	
	// Sanitize Parameters
	for($a = 0;$a <= 3;$a++)
	{
		$_POST['args'][$a] = (isset($_POST['args'][$a]) ? Sanitize::text($_POST['args'][$a]) : "");
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Get Navigation Entry
echo '
<h2 style="margin-top:20px;">' . ($editID ? 'Edit' : 'Create New') . ' Cron Task</h2>

<form class="uniform" action="/admin/cron/custom-task" method="post">' . Form::prepare("cron-custom") . '
	<p>Title: <input type="text" name="title" value="' . $_POST['title'] . '" maxlength="22" /> (only useful to humans)</p>
	<p>Method: <input type="text" name="method" value="' . $_POST['method'] . '" maxlength="22" /> (the MyTasks:: or Task:: method to call)</p>
	<p>Parameters:
		<br /><input type="text" name="args[0]" value="' . htmlspecialchars($_POST['args'][0]) . '" maxlength="250" /> (leave empty for unused)
		<br /><input type="text" name="args[1]" value="' . htmlspecialchars($_POST['args'][1]) . '" maxlength="250" /> (leave empty for unused)
		<br /><input type="text" name="args[2]" value="' . htmlspecialchars($_POST['args'][2]) . '" maxlength="250" /> (leave empty for unused)
		<br /><input type="text" name="args[3]" value="' . htmlspecialchars($_POST['args'][3]) . '" maxlength="250" /> (leave empty for unused)
	</p>
	<p>Run Cycle: <input type="text" name="run_cycle" value="' . $_POST['run_cycle'] . '" maxlength="8" /> (number of seconds until we should re-run the script)</p>
	<p>Start Time: <input type="text" name="date_start" value="' . $_POST['date_start'] . '" maxlength="8" /> (number of seconds until we should re-run the script)</p>
	<p>End Time: <input type="text" name="date_end" value="' . $_POST['date_end'] . '" maxlength="8" /> (0 runs once, < start time runs forever)</p>
	<p><input type="submit" name="submit" value="' . ($editID ? 'Edit' : 'Create New') . ' Task" /></p>
	' . ($editID ? '<input type="hidden" name="id" value="' . ($editID + 0) . '" />' : '') . '
</form>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");

