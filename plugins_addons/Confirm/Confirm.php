<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Confirm Plugin ------
--------------------------------------

This plugin generates confirmation codes, such as for password resets URLs that get sent through email.

The standard use for this plugin is to simply prove the authenticity of something, such as that they knew about a special offer (like an internet coupon) or need to prove they have access to something. It can also be used to store data that can be retrieved at a later time.


-------------------------------
------ Methods Available ------
-------------------------------

// Validate a confirmation entry
$confirm = new Confirm($confirmValue);

// Create a confirmation entry
Confirm::create($confirmVal, $confirmData, [$expires]);

*/

class Confirm {
	
	
/****** Plugin Variables ******/
	public $confirmValue = "";	// <str> The value used to validate the confirmation entry.
	
	public $passed = false;		// <bool> Gets set to TRUE once the validation passes.
	public $type = "";			// <str> The confirmation type being used (if applicable).
	public $data = array();		// <str:mixed> The data stored with the confirmation (if applicable).
	public $dateCreated = 0;	// <int> The date that the confirmation was created.
	
	
/****** Delete a Confirmation Entry ******/
	public function __construct
	(
		$confirmValue	// <str> The value being used to validate the confirmation entry.
	)					// RETURNS <bool> TRUE on validate, FALSE on failure.
	
	// $confirm = new Confirm($confirmValue);
	{
		// Prepare Values
		$this->confirmValue = $confirmValue;
		
		// Run the validation
		$this->validate();
	}
	
	
/****** Create Confirmation Entry ******/
	public static function create
	(
		$confirmVal			// <str> The confirmation value to assign.
	,	$confirmData = array()	// <str:mixed> Extra data to assign to this confirmation entry.
	,	$expires = 7200		// <int> Seconds until the confirmation expires (default: 2 hours). 0 for no expiration.
	)						// RETURNS <bool> TRUE on a successful creation, FALSE on failure.
	
	// Confirm::create($confirmVal, $confirmData, [$expires]);
	{
		// Prepare the expiration time
		$expireTime = $expires ? time() + $expires : time() + (86400 * 365 * 20);
		
		// Check if the value is already taken
		if($check = Database::selectOne("SELECT confirm_val, date_expires FROM confirm_values WHERE confirm_val=? LIMIT 1", array($confirmVal)))
		{
			if($check['date_expires'] > time())
			{
				return false;
			}
		}
		
		// Insert the confirmation entry
		return Database::query("REPLACE INTO confirm_values (confirm_val, confirm_data, date_expires) VALUES (?, ?, ?)", array($confirmVal, json_encode($confirmData), $expireTime));
	}
	
	
/****** Validate a Confirmation Entry ******/
	public function validate (
	)					// RETURNS <bool> TRUE on validate, FALSE on failure.
	
	// if($confirm->validate()) { echo "Reset confirmed!"; }
	{
		// Get the results
		if(!$results = Database::selectOne("SELECT confirm_val, confirm_data, date_expires FROM confirm_values WHERE confirm_val=? LIMIT 1", array($this->confirmValue)))
		{
			return false;
		}
		
		// Set Important Values
		if($results['confirm_val'] == $this->confirmValue)
		{
			// Check if the confirmation has expired
			if($results['date_expires'] < time())
			{
				$this->delete();
				return false;
			}
			
			// Set Values
			$this->passed = true;
			$this->data = json_decode($results['confirm_data'], true);
			$this->dateCreated = (int) $results['date_expires'];
			
			if(isset($this->data['type']))
			{
				$this->type = $this->data['type'];
			}
			
			return true;
		}
		
		return false;
	}
	
	
/****** Delete a Confirmation Entry ******/
	public function delete (
	)					// RETURNS <bool> TRUE on validate, FALSE on failure.
	
	// $confirm->delete();
	{
		return Database::query("DELETE FROM confirm_values WHERE confirm_val=? LIMIT 1", array($this->confirmValue));
	}
	
	
/****** Purge old Confirmation Entries ******/
	public function purge (
	)					// RETURNS <bool> TRUE on validate, FALSE on failure.
	
	// $confirm->purge();
	{
		return Database::query("DELETE FROM confirm_values WHERE date_expires <= ? LIMIT 1", array(time()));
	}
	
}

