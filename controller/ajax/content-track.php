<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------------
------ About the content-track script ------
--------------------------------------------

This script allows the user to set content tracking values, such as to vote on content entries (e.g. "Boost").

*/

// Make sure the user is logged in and has a valid ID
if(!Me::$id)
{
	exit;
}

// Make sure the right information is gathered
if(!isset($_POST['contentID']) or !isset($_POST['type']))
{
	exit;
}

// Make sure the content exists
if($contentData = Database::selectOne("SELECT id, uni_id FROM content_entries WHERE id=? LIMIT 1", array((int) $_POST['contentID'])))
{
	// Prepare Values
	$contentID = (int) $contentData['id'];
	$authorID = (int) $contentData['uni_id'];
	
	// Run the Content Track Handler
	$contentTrack = new ContentTrack($contentID, Me::$id);
	
	switch($_POST['type'])
	{
		case "boost":
			$contentTrack->voteUp();
			break;
		
		case "nooch":
			
			$success = false;
			$noochCount = 0;
			
			if($authorID != Me::$id)
			{
				// Check if the user has already triple nooched
				$noochCount = Database::selectValue("SELECT nooch FROM content_tracking_users WHERE uni_id=? AND content_id=? LIMIT 1", array(Me::$id, $contentID));
				
				if($noochCount !== false and $noochCount < 3)
				{
					if($success = Credits::chargeInstant(Me::$id, 0.15, "Assigned a Nooch"))
					{
						$contentTrack->nooch();
						$noochCount++;
					}
				}
			}
			
			echo json_encode(array(
				"content_id" => $contentID
			,	"nooch_count" => (int) $noochCount
			,	"nooch_success" => $success
			));
			
			exit;
		
		case "tip":
			
			if(!isset($_POST['amount'])) { echo 'false'; exit; }
			
			// Prepare Values
			$_POST['amount'] = (float) $_POST['amount'];
			
			$pass = ($_POST['amount'] >= 0.1 ? true : false);
			
			// Process the UniJoule exchange
			if($pass)
			{
				$pass = Credits::exchangeInstant(Me::$id, (int) $authorID, $_POST['amount'], "Tipped by " . Me::$vals['display_name']);
			}
			
			// Track the tip
			if($pass and $success = $contentTrack->tip($_POST['amount']))
			{
				echo json_encode(array(
					"content_id" => (int) $contentID
				,	"tip_boost" => floor($_POST['amount'] * 10)
				));
			}
			else
			{
				echo 'false';
			}
			
			exit;
	}
}
