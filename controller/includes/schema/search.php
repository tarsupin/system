<?php if(!defined("SCHEMA_HANDLER")) { die("No direct script access allowed."); }

/*
	Values sent from /controller/admin
		$plugin				// name of the plugin being used
		$table				// the table being viewed
		$schemaClass		// the name of the schema class
		$schema				// the schema object
*/

// Prepare Variables
$showFilters = true;
$baseURL = "/admin/" . $plugin . "/" . $table;

// Check if the search permissions on this schema table are restricted
if($schema->permissionSearch > 10)
{
	$showFilters = false;
	Alert::error("Search Disabled", "The search for this table is disabled by the system - it cannot be used.");
}

// Make sure you have permissions to view this page
else if(Me::$clearance < $schema->permissionSearch)
{
	$showFilters = false;
	Alert::error("Low Clearance", "You do not have sufficient clearance to search this table.");
}

// Check form submissions
else if(Form::submitted("schema-search-" . $table))
{
	$filterList = array();
	
	for($a = 0;$a < 3;$a++)
	{
		if($_POST['search'][$a] != '')
		{
			$filterList[] = array($_POST['field'][$a], $_POST['comparison'][$a], $_POST['search'][$a]);
		}
	}
	
	// If we submitted filters, return to the view page with filters provided
	if($filterList)
	{
		$filterList = Serialize::encode($filterList);
		
		header("Location: " . $baseURL . "?filters=" . urlencode($filterList)); exit;
	}
	else
	{
		Alert::error("No Filter", "You did not include any search filters.");
	}
}

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the search options, if possible
if($showFilters)
{
	// Prepare the Schema
	$view = new SchemaView($table, $baseURL);
	
	// Load the default schema for the view
	$view = $schema->search($view);
	
	$results = $view->prepColumns;
	
	echo '
	<div>
	<h2>Search Filters</h2>
	<p>Each filter you provie will refine the search results.</p>
	<form class="uniform" action="' . $view->baseURL . '/search" method="post">' . Form::prepare("schema-search-" . $table);
	
	for($a = 0;$a < 3;$a++)
	{
		// Prepare Post Values
		$_POST['field'][$a] = isset($_POST['field'][$a]) ? Sanitize::variable($_POST['field'][$a]) : "";
		$_POST['comparison'][$a] = isset($_POST['comparison'][$a]) ? Sanitize::variable($_POST['comparison'][$a]) : "";
		$_POST['search'][$a] = isset($_POST['search'][$a]) ? Sanitize::variable($_POST['search'][$a]) : "";
		
		// Field
		echo '
		<h4>Filter #' . ($a + 1) . '</h4>
		<p>
		<select name="field[' . $a . ']">';
		
		foreach($results as $column)
		{
			echo '
			<option value="' . $column['field_key'] . '"' . ($_POST['field'][$a] == $column['field_key'] ? ' selected' : '') . '>' . $column['title'] . '</option>';
		}
		
		echo '
		</select>';
		
		// Comparison
		echo '
		<select name="comparison[' . $a . ']">' . str_replace('value="' . $_POST['comparison'][$a] . '"', 'value="' . $_POST['comparison'][$a] . '" selected', '
			<option value="e">Equals</option>
			<option value="ne">Doesn\'t Equal</option>
			<option value="lt">Less Than</option>
			<option value="gt">Greater Than</option>
			<option value="like">Contains</option>
			<option value="unlike">Doesn\'t Contain</option>') . '
		</select>';
		
		// Search
		echo '
		<input type="text" name="search[' . $a . ']" value="' . $_POST['search'][$a] . '" />
		</p>';
	}
	
	echo '
		<p><input type="submit" name="submit" value="Search" /></p>
	</form>
	</div>';
}

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");
