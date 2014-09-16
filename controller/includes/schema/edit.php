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
$editRow = array();

// Check if the creation permissions on this schema table are restricted
if($schema->permissionEdit > 10)
{
	Alert::error("Edit Disabled", "Editing entries on this table is disabled by the system - it cannot be used.");
	$runForm = false;
}

// Make sure you have permissions to create on this page
else if(Me::$clearance < $schema->permissionCreate)
{
	Alert::error("Low Clearance", "You do not have sufficient clearance to edit entries on this table.");
	$runForm = false;
}

// Run all necessary editing proceedures before the display
if($runForm)
{
	// Prepare the Schema
	$form = new SchemaForm($table, "/admin/" . $plugin . "/" . $table);
	$form = $schema->edit($form);
	$form->mode = "edit";
	
	$sqlWhere = "";
	$sqlArray = array();
	
	// Retrieve the Editing Row
	for($a = 0, $len = count($schema->fieldIndex);$a < $len;$a++)
	{
		// Retrieve the appropriate indexes required
		if(isset($_GET['val'][$a]))
		{
			$form->indexVals[$schema->fieldIndex[$a]] = $_GET['val'][$a];
		}
		
		// If it didn't identify the appropriate values for the indexes required
		else
		{
			Alert::error("Index Unavailable", "The index could not be read properly. Cannot edit.");
			$runForm = false;
			break;
		}
		
		// Preparation
		$sqlWhere .= ($sqlWhere == "" ? "" : " AND ") . $schema->fieldIndex[$a] . '=?';
		$sqlArray[] = $form->indexVals[$schema->fieldIndex[$a]];
	}
	
	// If the editing form is still valid, continue with retrieving the editing row and the submission
	if($runForm)
	{
		// Run the Submission, if applicable
		if($form->validate())
		{
			// Prepare Values
			$subList = "";
			$subWhere = "";
			$subArray = array();
			
			// Cycle through the list of fields for this form
			foreach($form->formPrep as $slot)
			{
				$subList .= ($subList == "" ? "" : ", ") . '`' . Sanitize::variable($slot['field_key']) . '`=?';
				$subArray[] = $_POST[$slot['field_key']];
			}
			
			// Cycle through the field indexes
			foreach($schema->fieldIndex as $index)
			{
				$subWhere .= ($subWhere == "" ? "" : " AND ") . '`' . Sanitize::variable($index) . '`=?';
				$subArray[] = $form->indexVals[$index];
			}
			
			// Attempt to update the row
			if($success = Database::query("UPDATE IGNORE `" . Sanitize::variable($form->table) . "` SET " . $subList . " WHERE " . $subWhere, $subArray))
			{
				Alert::saveSuccess("Update Successful", "You have successfully updated the row.");
				
				header("Location: " . $form->baseURL . "/view"); exit;
			}
			
			Alert::error("Update Failed", "There was an error with updating this row.");
		}
		
		// Get the Editing Row Data
		$editRow = Database::selectOne("SELECT * FROM " . Sanitize::variable($schema->tableKey) . " WHERE " . $sqlWhere, $sqlArray);
		
		foreach($form->formPrep as $slot)
		{
			if(!isset($_POST[$slot['field_key']]))
			{
				$_POST[$slot['field_key']] = $editRow[$slot['field_key']];
			}
		}
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the form, if possible
if($editRow)
{
	// Run the Submission
	$form->build();
	
	// Editing-Specific Instructions
	$extraURL = "?edit=1";
	$a = 0;
	
	foreach($_GET['val'] as $val)
	{
		$extraURL .= "&val[" . $a . "]=" . urlencode($val);
		$a++;
	}
	
	// Display the Form
	echo '
	<form class="uniform" action="' . $form->baseURL . '/edit?edit=1' . $extraURL . '" method="post">' . Form::prepare('schema-' . $form->table);
	
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
