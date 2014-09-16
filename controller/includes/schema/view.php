<?php if(!defined("SCHEMA_HANDLER")) { die("No direct script access allowed."); }

/*
	Values sent from /controller/admin
		$plugin				// name of the plugin being used
		$table				// the table being viewed
		$schemaClass		// the name of the schema class
		$schema				// the schema object
*/

// Check if the view permissions on this schema table are restricted
if($schema->permissionView > 10)
{
	Alert::error("View Disabled", "This Schema View is disabled by the system - it cannot be used.");
}

// Make sure you have permissions to view this page
else if(Me::$clearance < $schema->permissionView)
{
	Alert::error("Low Clearance", "You do not have sufficient clearance to view this table.");
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display The Table (if possible)
if(!Alert::hasErrors())
{
	// Prepare the Schema
	$view = new SchemaView($table, "/admin/" . $plugin . "/" . $table);
	
	// Load the default schema for the view
	$view = $schema->view($view);
	
	// Prepare important schema values
	$showCreate = (Me::$clearance >= $schema->permissionCreate and $schema->permissionCreate < 11);
	$showSearch = (Me::$clearance >= $schema->permissionSearch and $schema->permissionSearch < 11);
	
	$view->fieldIndex = $schema->fieldIndex;
	
	if(Me::$clearance >= $schema->permissionDelete and $schema->permissionDelete < 11)
	{
		$view->allowDelete = true;
		$view->autoDelete = $schema->autoDelete;
	}
	
	if(Me::$clearance >= $schema->permissionEdit and $schema->permissionEdit < 11)
	{
		$view->allowEdit = true;
	}
	
	// Check if there were any deletions activated
	if($view->allowDelete == true and isset($_GET['delete']))
	{
		if($link = Link::clicked() and $link == "schema-delete-" . $table)
		{
			// Get the row to delete
			$sqlWhere = "";
			$sqlArray = array();
			
			// Retrieve the Editing Row
			for($a = 0, $len = count($schema->fieldIndex);$a < $len;$a++)
			{
				// Make sure the indexes are properly set
				if(!isset($_GET['val'][$a]))
				{
					Alert::error("Index Unavailable", "The index could not be read properly. Cannot edit.");
					break;
				}
				
				// Preparation
				$sqlWhere .= ($sqlWhere == "" ? "" : " AND ") . Sanitize::variable($schema->fieldIndex[$a]) . '=?';
				$sqlArray[] = $_GET['val'][$a];
			}
			
			// If the deletion index was found
			if($sqlWhere)
			{
				if($view->autoDelete == true or isset($_GET['conf']))
				{
					// Make sure you have the appropriate clearance to delete this row
					if(Me::$clearance < 6)
					{
						Alert::error("Low Clearance", "Your clearance is not high enough to delete these rows.");
					}
					else
					{
						// Delete the row
						if($success = Database::query("DELETE FROM " . Sanitize::variable($schema->tableKey) . " WHERE " . $sqlWhere, $sqlArray))
						{
							Alert::success("Deletion Successful", "You have successfully deleted the row.");
						}
						else
						{
							Alert::error("Deletion Failed", "You were not able to delete the row.");
						}
					}
				}
				else
				{
					// Confirm the deletion
					echo 'Are you sure you want to delete this row? <a href="' . $view->baseURL . "?delete=1" . Link::queryHold("val", "filters") . $view->deleteLink . $view->urlPage . '&conf=1">Yes, Delete this Row</a>';
				}
			}
		}
	}
	
	// Check if there were any search filters activated
	if($showSearch)
	{
		$view->runSearch();
	}
	
	// Display the table
	$view->display();
	
	// Show Pagination
	$paginate = $view->pagination();
	
	if($paginate->highestPage > 1)
	{
		echo '
		<style>
			.pvlink { background-color:orange; border:solid 1px black; padding:3px; border-radius:4px; }
			.pvlink:hover { background-color:red; }
		</style>
		<div style="margin-top:10px;">Pages: ';
		
		foreach($paginate->pages as $val)
		{
			echo '
			<a class="pvlink" href="' . $view->baseURL . '/view?page=' . $val . Link::queryHold("filters") . '">' . $val . '</a>';
		}
		
		echo '
		</div>';
	}
	
	// Show Create Link and Search Link
	if($showCreate or $showSearch)
	{
		echo '
		<div style="margin-top:10px;">';
		
		if($showCreate)
		{
			echo '
			<a class="button" href="' . $view->baseURL . '/create">Create New Entry</a>';
		}
		
		if($showSearch)
		{
			echo '
			<a class="button" href="' . $view->baseURL . '/search">Search</a>';
		}
		
		echo '
		</div>';
	}
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
