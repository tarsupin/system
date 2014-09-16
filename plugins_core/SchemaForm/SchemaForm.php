<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the SchemaForm Plugin ------
-----------------------------------------

This plugin 


-------------------------------
------ Methods Available ------
-------------------------------


*/

class SchemaForm {
	
	
/****** Plugin Variables ******/
	public $table = '';					// <str> The table to use for this form.
	public $baseURL = "";				// <str> The base URL to use for the links in this form.
	
	public $structure = array();		// <str:mixed> Stores schema data for all of the available fields.
	public $formPrep = array();			// <str:mixed> Stores schema data for fields used in the form.
	public $formSlots = array();		// <int:str> Stores HTML of each input built with the form.
	
	
/****** Construct the SchemaForm class ******/
	public function __construct
	(
		$table		// <str> The table to use for this form.
	,	$baseURL	// <str> The base URL to use for links with this form.
	)				// RETURNS <void>
	
	// $form = new SchemaForm($table, $baseURL);
	{
		// Prepare Values
		$this->table = Sanitize::variable($table);
		$this->baseURL = Sanitize::filepath($baseURL);
		
		// Load the available schema fields for this table
		$results = Database::selectMultiple("SELECT * FROM schema_fields WHERE table_key=?", array($this->table));
		
		foreach($results as $res)
		{
			$this->structure[$res['field_key']] = $res;
		}
	}
	
	
/****** Validate a form submission ******/
	public function validate
	(
					// (args of options and modes)
	)				// RETURNS <bool> TRUE if the submission will pass, FALSE on failure.
	
	// $schemaForm->validate();
	{
		if(Form::submitted("schema-" . $this->table))
		{
			// Cycle through the list of fields for this form
			foreach($this->formPrep as $slot)
			{
				// Validate
				
				switch($slot['field_type'])
				{
					// "UniID" Field Type
					case "uni_id":
						$searchInput = $_POST[$slot['field_key'] . '-schemaInput'];
						
						// Get the UniID from the handle posted
						if($getUser = User::getDataByHandle($searchInput, "uni_id"))
						{
							$_POST[$slot['field_key']] = (int) $getUser['uni_id'];
							FormValidate::number($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value']);
						}
						else
						{
							Alert::error("Invalid UniID", "That user does not exist.");
						}
						
						break;
					
					// "Number" Field Type
					case "number":
						if($slot['decimals'] == 0)
						{
							FormValidate::number($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value']);
						}
						else
						{
							FormValidate::number_float($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value']);
						}
						break;
					
					// "Variable" Field Type
					case "variable":
						FormValidate::variable($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value'], $slot['extra_chars']);
						break;
					
					// "Input" Field Type
					case "input":
						FormValidate::input($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value'], $slot['extra_chars']);
						break;
					
					// "Text" Field Type
					case "text":
						FormValidate::text($slot['title'], $_POST[$slot['field_key']], $slot['min_value'], $slot['max_value'], $slot['extra_chars']);
						break;
				}
			}
			
			// See if the form validations passed
			if(FormValidate::pass())
			{
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Add a field to this form ******/
	public function addFields
	(
					// (args of fields to add)
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schemaForm->addFields($field, [..$field], [..$field]);
	{
		$args = func_get_args();
		
		foreach($args as $field)
		{
			// Reject the field from being used if it doesn't exist
			if(!isset($this->structure[$field])) { return false; }
			
			$this->formPrep[$field] = $this->structure[$field];
		}
		
		return true;
	}
	
	
/****** Add a field to this form, with ability to designate options ******/
	public function addField
	(
		$field		// <str> The field to add to this form.
					// (args of options and modes)
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $schemaForm->addField($field, [..option], [..option]);
	// $schemaForm->addField($field, array("title" => "Overwrite Title", "max_value" => 5));
	{
		// Reject the field from being used if it doesn't exist
		if(!isset($this->structure[$field])) { return false; }
		
		$args = func_get_args();
		$argLen = count($args);
		
		$this->formPrep[$field] = $this->structure[$field];
		
		// Load Options into this field
		for($a = 1;$a < $argLen;$a++)
		{
			// If the options loaded are designed to change existing values in the schema
			if(is_array($args[$a]))
			{
				foreach($args[$a] as $argkey => $argval)
				{
					$this->formPrep[$field][$argkey] = $argval;
				}
			}
		}
	}
	
	
/****** Build the form ******/
	public function build (
	)				// RETURNS <void>
	
	// $schemaForm->build();
	{
		foreach($this->formPrep as $slot)
		{
			// Prepare Variables
			$prepareSlot = "";
			$readonly = $slot['is_readonly'] == 1 ? " readonly" : "";
			
			// "Selection" Pull Type was used
			if($slot['pull_type'] == "select")
			{
				// Attempt to pull the selection
				$results = Database::selectMultiple("SELECT arg_key, arg_value FROM schema_selections WHERE selection_name=?", array($slot['pull_from']));
				
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<select name="' . $slot['field_key'] . '"' . $readonly . '>';
				
				foreach($results as $select)
				{
					$prepareSlot .= '
					<option value="' . $select['arg_key'] . '"' . ($select['arg_key'] == $_POST[$slot['field_key']] ? ' selected' : '') . '>' . $select['arg_value'] . '</option>';
				}
				
				$prepareSlot .= '
				</select>';
			}
			
			// "Method" Pull Type was used
			else if($slot['pull_type'] == "method")
			{
				if(class_exists($slot['table_key'] . "_schema"))
				{
					if(method_exists($slot['table_key'] . "_schema", "pullMethodForm_" . $slot['pull_from']))
					{
						// Attempt to pull the method results
						$results = call_user_func_array(array($slot['table_key'] . "_schema", "pullMethodForm_" . $slot['pull_from']), array($_POST[$slot['field_key']]));
						
						$prepareSlot = '
						<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
						<select name="' . $slot['field_key'] . '"' . $readonly . '>';
						
						foreach($results as $key => $val)
						{
							$prepareSlot .= '
							<option value="' . $key . '"' . ($key == $_POST[$slot['field_key']] ? ' selected' : '') . '>' . $val . '</option>';
						}
						
						$prepareSlot .= '
						</select>';
					}
				}
			}
			
			// "Number" Field Type
			else if($slot['field_type'] == "number")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::number($_POST[$slot['field_key']], $slot['min_value'], $slot['max_value'], ($slot['decimals'] > 0 ? true : false)) . '" maxlength="' . (strlen((string) $slot['max_value']) + (int) $slot['decimals']) . '"
					' . $readonly . '
				/>';
			}
			
			// "Variable" Field Type
			else if($slot['field_type'] == "variable")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::variable($_POST[$slot['field_key']], $slot['extra_chars']) . '"
					' . $readonly . '
				/>';
			}
			
			// "Filepath" Field Type
			else if($slot['field_type'] == "filepath")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::filepath($_POST[$slot['field_key']], $slot['extra_chars']) . '"
					' . $readonly . '
				/>';
			}
			
			// "Input" Field Type
			else if($slot['field_type'] == "input")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::safeword($_POST[$slot['field_key']], $slot['extra_chars']) . '"
					' . $readonly . '
				/>';
			}
			
			// "Text" Field Type
			else if($slot['field_type'] == "text")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<textarea
					class="' . $slot['field_key'] . '"
					name="' . $slot['field_key'] . '"
					' . $readonly . '
				>' . htmlspecialchars(Sanitize::text($_POST[$slot['field_key']], $slot['extra_chars'])) . '</textarea>';
			}
			
			// "URL" Field Type
			else if($slot['field_type'] == "url")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::url($_POST[$slot['field_key']], $slot['extra_chars']) . '"
					' . $readonly . '
				/>';
			}
			
			// "UniID" Field Type
			else if($slot['field_type'] == "uni_id")
			{
				$prepareSlot = '
				<label for="' . $slot['field_key'] . '">' . $slot['title'] . '</label>
				<input
					id="schema-slot-' . $slot['field_key'] . '"
					class="' . $slot['field_key'] . '"
					type="text"
					name="' . $slot['field_key'] . '"
					value="' . Sanitize::url($_POST[$slot['field_key']], $slot['extra_chars']) . '"
					' . $readonly . '
					style="display:inline-block;"
				/>';
				
				// Get Base Handle Name
				$base = User::get($_POST[$slot['field_key']], "handle");
				
				// If this field is editable, provide a search bar
				if($readonly == "")
				{
					// Prepare Search Bar
					$barName = $slot['field_key'] . '-schema';
					$prepareSlot .= Search::searchBarUserHandle($barName, "getLastSearchBarID", $base['handle']);
					
					$prepareSlot .= '
					<style>
						#search-' . $barName . ' { display:inline-block; vertical-align:bottom; }
						#' . $barName . 'InputID { display:inline-block; width:200px; }
					</style>';
					
					// This section is a little complicated, but basically what it does is it allows multiple user search
					// bars to exist on the same SchemaForm page.
					if(!isset($userHandleScript))
					{
						$userHandleScript = true;
						
						$prepareSlot .= '
						<script>
							var lastSearchBar = "";
							var updateInput = null;
							
							function getLastSearchBarID(event)
							{
								lastSearchBar = event.target.id;
								updateInput = document.getElementById("schema-slot-" + lastSearchBar.substr(0, lastSearchBar.indexOf("-")));
							}
							
							function UserHandle(handle)
							{
								if(updateInput)
								{
									updateInput.value = "[set to handle]";
									
									var a = document.getElementById("' . $barName . 'InputID");
									a.value = handle;
								}
							}
						</script>';
					}
				}
				
				// If this field is read-only, default to the field being displayed with a handle
				else { /* Anything here? */ }
			}
			
			$this->formSlots[] = $prepareSlot;
		}
	}
}
