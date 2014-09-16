<?php if(!defined("SCHEMA_HANDLER")) { die("No direct script access allowed."); }

/*
	Values sent from /controller/admin
		$plugin				// name of the plugin being used
		$table				// the table being viewed
		$schemaClass		// the name of the schema class
		$schema				// the schema object
*/

// Prepare Values
$runForm = true;

// Check if the creation permissions on this schema table are restricted
if($schema->permissionCreate > 10)
{
	Alert::error("Creation Disabled", "Creating new entries for this table is disabled by the system - it cannot be used.");
	$runForm = false;
}

// Make sure you have permissions to create on this page
else if(Me::$clearance < $schema->permissionCreate)
{
	Alert::error("Low Clearance", "You do not have sufficient clearance to create entries on this table.");
	$runForm = false;
}

// Run the form submission, if possible
if($runForm)
{
	// Prepare the Schema
	$form = new SchemaForm($table, "/admin/" . $plugin . "/" . $table);
	$form = $schema->create($form);
	
	// Prepare Default Values
	foreach($form->formPrep as $field)
	{
		if(!isset($_POST[$field['field_key']]))
		{
			$_POST[$field['field_key']] = $form->formPrep[$field['field_key']]['default_value'];
		}
	}
	
	// Validate a Submission
	if($form->validate())
	{
		// Prepare Values
		$comma = '';
		$sqlList = "";
		$sqlValues = "";
		$sqlArray = array();
		
		// Cycle through the list of fields for this form
		foreach($form->formPrep as $slot)
		{
			$sqlList .= $comma . '`' . Sanitize::variable($slot['field_key']) . '`';
			$sqlValues .= $comma . '?';
			$sqlArray[] = $_POST[$slot['field_key']];
			
			$comma = ', ';
		}
		
		if($success = Database::query("INSERT IGNORE INTO `" . Sanitize::variable($form->table) . "` (" . $sqlList . ") VALUES (" . $sqlValues . ")", $sqlArray))
		{
			Alert::saveSuccess("Creation Successful", "You have successfully entered a new row.");
			
			header("Location: " . $form->baseURL . "/view"); exit;
		}
		
		Alert::error("Creation Failed", "A unique row with these values already exists, or an error has occurred.");
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the form, if possible
if($runForm)
{
	// Run the Submission
	$form->build();
	
	// Display the Form
	echo '
	<form class="uniform" action="' . $form->baseURL . '/create" method="post">' . Form::prepare('schema-' . $form->table);
	
	foreach($form->formSlots as $slot)
	{
		echo '<div>' . $slot . '</div>';
	}
	
	echo '
	<div>
		<input type="submit" name="submit" value="Submit" />
	</div>
	</form>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
