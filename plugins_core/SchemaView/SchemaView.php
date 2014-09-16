<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the SchemaView Plugin ------
-----------------------------------------

This plugin 


-------------------------------
------ Methods Available ------
-------------------------------


*/

class SchemaView {
	
	
/****** Plugin Variables ******/
	public $table = '';					// <str> The table to pull for this schema view.
	public $filters = array();			// <int:[int:str]> The "where" filters to restrict the row results.
	public $sorts = array();			// <int:[int:str]> The "order by" sort options.
	
	public $currentPage = 1;			// <int> The current page of the schema view.
	public $rowsToShow = 30;			// <int> The number of rows to show.
	
	public $sqlCount = "";				// <str> The SQL command to run to get the row count.
	public $sql = "";					// <str> The SQL command to run to return the visible rows.
	public $sqlArray = array();			// <int:mixed> The SQL array to enter into the selection query.
	
	public $structure = array();		// <array> The full structure of all field entries in our schema.
	public $prepColumns = array();		// <array> The columns (and data) pulled for this schema view.
	public $saveData = array();			// <array> Extra data to save, such as uni_ids.
	
	public $baseURL = "";				// <str> The base URL to this page.
	public $urlPage = "";				// <str> The URL query string for the current page.
	public $editLink = "";				// <str> The preparation link for editing.
	public $deleteLink = "";			// <str> The preparation link for deleting.
	
	public $fieldIndex = "";			// <str> The unique field to use for editing and deleting rows.
	public $allowEdit = false;			// <bool> Whether or not to allow editing of rows.
	public $allowDelete = false;		// <bool> Whether or not to allow deletion of rows.
	public $autoDelete = false;			// <bool> TRUE allows instant deletions, FALSE requires confirmation.
	
	
/****** Construct the SchemaView class ******/
	public function __construct
	(
		$table		// <str> The table to use for this schema view.
	,	$baseURL	// <str> The base URL for this schema view.
	)				// RETURNS <void>
	
	// $form = new SchemaView($table);
	{
		$this->table = Sanitize::variable($table);
		
		$results = Database::selectMultiple("SELECT * FROM schema_fields WHERE table_key=?", array($this->table));
		
		foreach($results as $res)
		{
			$this->structure[$res['field_key']] = $res;
		}
		
		// Prepare Values
		$this->baseURL = $baseURL;
		
		$this->editLink = "&" . Link::prepare("schema-edit-" . $this->table);
		$this->deleteLink = "&" . Link::prepare("schema-delete-" . $this->table);
		
		if(isset($_GET['page']))
		{
			$this->currentPage = (int) $_GET['page'];
			$this->urlPage = "&page=" . $this->currentPage;
		}
	}
	
	
/****** Run search script if any were activated ******/
	public function runSearch (
	)		// RETURNS <void>
	
	// $schemaView->runSearch();
	{
		// Check if there are any submissions posted:
		if(isset($_GET['filters']))
		{
			$searchFilters = Serialize::decode($_GET['filters']);
			
			foreach($searchFilters as $filter)
			{
				// Switch the comparision values
				switch($filter[1])
				{
					case "e":		$filter[1] = "=";		break;
					case "ne":		$filter[1] = "!=";		break;
					case "lt":		$filter[1] = "<";		break;
					case "gt":		$filter[1] = ">";		break;
					
					case "like":
						$filter[1] = "LIKE";
						$filter[2] = "%" . $filter[2] . "%";
						break;
					
					case "unlike":
						$filter[1] = "NOT LIKE";
						$filter[2] = "%" . $filter[2] . "%";
						break;
					
					default:		$filter[1] = "=";		break;
				}
				
				$this->filter(Sanitize::variable($filter[0]), $filter[1], $filter[2]);
			}
		}
	}
	
	
/****** Add fields to this view ******/
	public function addFields
	(
			// (args)
	)		// RETURNS <void>
	
	// $schemaView->addFields($field, [..$field], [..$field]);
	{
		$args = func_get_args();
		
		foreach($args as $arg)
		{
			// Reject the field from being used if it doesn't exist
			if(!isset($this->structure[$arg]))
			{
				continue;
			}
			
			// Add the field to the SchemaView display
			$this->prepColumns[$arg] = $this->structure[$arg];
		}
	}
	
	
/****** Add a field to this view, with options for special parameters ******/
	public function addField
	(
		$field		// <str> The field to add to this view.
					// (args of options and modes)
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schemaView->addField($field, [..$option], [..$option]);
	// $schemaView->addField($field, array("title" => "Overwrite Title", "max_value" => 5));
	{
		// Reject the field from being used if it doesn't exist
		if(!isset($this->structure[$field]))
		{
			return false;
		}
		
		$args = func_get_args();
		$argLen = count($args);
		
		$this->prepColumns[$field] = $this->structure[$field];
		
		// Load Options into this field
		for($a = 1;$a < $argLen;$a++)
		{
			// If the options loaded are designed to change existing values in the schema
			if(is_array($args[$a]))
			{
				foreach($args[$a] as $argkey => $argval)
				{
					$this->prepColumns[$field][$argkey] = $argval;
				}
			}
			
			// If the options loaded are settings
			else
			{
				switch($args[$a])
				{
					// case "something": break;
				}
			}
		}
		
		return true;
	}
	
	
/****** Add a filter: only fields that match the requirements will show ******/
	public function filter
	(
		$field			// <str> The field to add as a filter.
	,	$comparison		// <str> The comparison string to use (e.g. "=", ">=", "<=", etc).
	,	$value			// <mixed> The value to match for this filter.
	)					// RETURNS <bool>
	
	// $schemaView->filter($field, $comparison, $value);
	{
		// Reject the field from being used if it isn't included in this schema
		if(!isset($this->prepColumns[$field])) { return false; }
		
		// Only allow certain comparison values
		if(!in_array($comparison, array("=", "<=", "<", ">", ">=", "!=", "LIKE", "NOT LIKE")))
		{
			return false;
		}
		
		// Set the sort value
		$this->filters[] = array($field, $comparison, $value);
		
		return true;
	}
	
	
/****** Add a sort value to the schema view ******/
	public function sort
	(
		$field			// <str> The field to add to the sort.
	,	$dir = "ASC"	// <str> The direction to sort.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schemaView->sort($field, [$dir]);
	{
		// Prepare Values
		$dir = strtoupper($dir);
		$dir = $dir == "ASC" ? "ASC" : "DESC";
		
		// Reject the field from being used if it isn't included in this schema
		if(!isset($this->prepColumns[$field])) { return false; }
		
		// Set the sort value
		$this->sorts[] = array($field, $dir);
		
		return true;
	}
	
	
/****** Get the SQL that builds this schema view ******/
	public function sql (
	)					// RETURNS <void>
	
	// $schemaView->sql();
	{
		// Prepare Values
		$a = 1;
		$leftJoin = "";
		$colSQL = "";
		
		foreach($this->prepColumns as $col)
		{
			// If there is a "database" pull type discovered, we need to identify it now to prepare JOIN statements
			if($col['pull_type'] == "database")
			{
				$special = Serialize::decode($col['special_instructions']);
				
				$prepTableName = Sanitize::variable(substr($special['pull_table'], 0, 3) . substr($col['field_key'], 0, 3)) . $a++;
				
				$colSQL .= ($colSQL == "" ? "" : ", ") . $prepTableName . '.' . $special['pull_value'] . ' as ' . Sanitize::variable($col['field_key']);
				
				$leftJoin .= " LEFT JOIN " . $special['pull_table'] . ' ' . $prepTableName . " ON " . $prepTableName . '.' . $special['pull_key'] . '=' . $this->table . '.' . $col['field_key'];
			}
			
			// Standard processing
			else
			{
				$colSQL .= ($colSQL == "" ? "" : ", ") . $this->table . '.' . Sanitize::variable($col['field_key']);
			}
		}
		
		// Prepare Filters (WHERE section)
		$where = "";
		
		foreach($this->filters as $filter)
		{
			$where .= $where == "" ? " WHERE " : " AND ";
			
			$where .= Sanitize::variable($filter[0]) . " " . Sanitize::whitelist($filter[1], "!<>=NOT LIKE") . " ?";
			$this->sqlArray[] = $filter[2];
		}
		
		// Prepare Sorting
		$orderBy = "";
		
		foreach($this->sorts as $sort)
		{
			$orderBy .= $orderBy == "" ? " ORDER BY " : ", ";
			
			$orderBy .= Sanitize::variable($sort[0]) . " " . ($sort[1] == "DESC" ? "DESC" : "ASC");
		}
		
		// Pagination
		$this->rowsToShow = min(50, $this->rowsToShow);
		$limitStart = (max(1, $this->currentPage) - 1) * $this->rowsToShow;
		
		// Set the SQL Strings
		$this->sqlCount = "SELECT COUNT(*) as totalNum FROM " . Sanitize::variable($this->table) . $where;
		$this->sql = "SELECT " . $colSQL . " FROM " . Sanitize::variable($this->table) . $where . $leftJoin . $orderBy . " LIMIT " . $limitStart . ", " . $this->rowsToShow;
	}
	
	
/****** Display the Schema View ******/
	public function display (
	)					// RETURNS <void>
	
	// $schemaView->display();
	{
		// Build the SQL for this schema
		$this->sql();
		
		// Retrieve the rows
		$rows = Database::selectMultiple($this->sql, $this->sqlArray);
		
		// Pre-process rows that have special field types that require pre-processing
		$preprocess = array();
		
		foreach($rows as $row)
		{
			foreach($this->prepColumns as $column)
			{
				switch($column['field_type'])
				{
					case "uni_id":
						$preprocess['uni_ids'][] = $row[$column['field_type']];
						break;
				}
			}
		}
		
		// Pre-Process the UniIDs
		if(isset($preprocess['uni_ids']))
		{
			$sqlIn = "";
			
			foreach($preprocess['uni_ids'] as $uniID)
			{
				$sqlIn .= ($sqlIn == "" ? "" : ", ") . "?";
				$sqlArray[] = $uniID;
			}
			
			// Provide a list of UniID's and corresponding data
			$uniIDList = array();
			
			if($getUniIDs = Database::selectMultiple("SELECT uni_id, handle FROM users WHERE uni_id IN (" . $sqlIn . ")", $sqlArray))
			{
				foreach($getUniIDs as $user)
				{
					$uniIDList[(int) $user['uni_id']] = $user['handle'];
				}
			}
		}
		
		// Display the Form
		echo '
		<table class="mod-table" style="font-size:0.9em;">
			<tr>';
		
		// Show Editing Header and Deletion Header, if applicable
		if($this->fieldIndex and ($this->allowEdit == true or $this->allowDelete == true))
		{
			echo '
			<td>Options</td>';
		}
		
		// Show the schema headers
		foreach($this->prepColumns as $column)
		{
			echo '
			<td>' . $column['title'] . '</td>';
		}
		
		echo '
			</tr>';
		
		// Cycle through each row and output the data
		foreach($rows as $row)
		{
			echo '
			<tr>';
			
			// Show Editing Header and Deletion Columns, if applicable
			if($this->fieldIndex and ($this->allowEdit == true or $this->allowDelete == true))
			{
				echo '
				<td>';
				
				// If you allow editing
				if($this->allowEdit == true)
				{
					$editLine = '';
					
					for($a = 0, $len = count($this->fieldIndex);$a < $len;$a++)
					{
						$editLine .= "&val[" . $a . "]=" . urlencode($row[$this->fieldIndex[$a]]);
					}
					
					echo '<a href="' . $this->baseURL . '/edit?edit=1' . $editLine . $this->editLink . '">Edit</a>';
				}
				
				// If you allow deleting
				if($this->allowDelete == true)
				{
					$deleteLine = '';
					
					for($a = 0, $len = count($this->fieldIndex);$a < $len;$a++)
					{
						$deleteLine .= "&val[" . $a . "]=" . urlencode($row[$this->fieldIndex[$a]]);
					}
					
					echo ($this->allowEdit == true ? ' | ' : '') . '<a href="' . $this->baseURL . '?delete=1' . $deleteLine . $this->deleteLink . ($this->autoDelete == true ? "&conf=1" : "") . Link::queryHold("filters") . $this->urlPage . '">Delete</a>';
				}
				
				echo '</td>';
			}
			
			// Show each column
			foreach($this->prepColumns as $column)
			{
				// If using a "Selection" pull type
				if($column['pull_type'] == "select")
				{
					// Get the equivalent value from the selection
					$row[$column['field_key']] = Database::selectValue("SELECT arg_value FROM schema_selections WHERE selection_name=? AND arg_key=? LIMIT 1", array($column['pull_from'], $row[$column['field_key']]));
				}
				
				// If using a "Method" pull type
				else if($column['pull_type'] == "method")
				{
					if(class_exists($column['table_key'] . "_schema"))
					{
						if(method_exists($column['table_key'] . "_schema", "pullMethodView_" . $column['pull_from']))
						{
							// Attempt to pull the method results
							$result = call_user_func_array(array($column['table_key'] . "_schema", "pullMethodView_" . $column['pull_from']), array($row[$column['field_key']]));
							
							$row[$column['field_key']] = $result;
						}
					}
				}
				
				// Scan through regular field types
				else
				{
					switch($column['field_type'])
					{
						case "uni_id":
							$row[$column['field_key']] = isset($uniIDList[$row[$column['field_key']]]) ? $row[$column['field_key']] . ': ' . $uniIDList[$row[$column['field_key']]] : $row[$column['field_key']];
							break;
						
						case "number":
							$row[$column['field_key']] = (int) $row[$column['field_key']];
							break;
							
						case "variable":
						case "input":
						case "filepath":
							break;
						
						case "url":
							$row[$column['field_key']] = '<a href="' . $row[$column['field_key']] . '">' . $row[$column['field_key']] . '</a>';
							break;
							
						case "timestamp":
							
							if($row[$column['field_key']] == 0)
							{
								$row[$column['field_key']] = "";
							}
							else
							{
								$row[$column['field_key']] = Time::fuzzy((int) $row[$column['field_key']]);
							}
							
						case "text":
							$row[$column['field_key']] = htmlspecialchars($row[$column['field_key']]);
							break;
					}
				}
				
				echo '
				<td>' . $row[$column['field_key']] . '</td>';
			}
			
			echo '
			</tr>';
		}
		
		echo '
		</table>';
	}
	
	
/****** Display Pagination Links ******/
	public function pagination (
	)					// RETURNS <mixed> the pagination class.
	
	// $schemaView->pagination();
	{
		$totalRows = (int) Database::selectValue($this->sqlCount, $this->sqlArray);
		
		return new Pagination($totalRows, $this->rowsToShow, $this->currentPage);
	}
}
