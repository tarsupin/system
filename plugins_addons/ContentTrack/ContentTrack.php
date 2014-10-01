<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the ContentTrack Plugin ------
-------------------------------------------

This plugin allows articles, blogs, and other content entry types to keep track of important information. This includes "likes" (or votes), the number of shares it's received, tips, tip amounts, flags, etc.

Content Tracking is essential for some sites, where content will be judged so that we can determine whether it should be emphasized on the site or not, or to get an idea of user preferences of something. However, on other sites, it isn't necessary. For example, the individual pages created on a site creator may not need to know any of these details.

-------------------------------------------
------ Examples of using this plugin ------
-------------------------------------------

// Load the Content Tracker
$contentTrack = new ContentTrack($contentID, Me::$id);



*/

class ContentTrack {
	
	
/****** Plugin Variables ******/
	public $contentID = 0;			// <int> The ID of the content entry.
	
	public $uniID = 0;				// <int> The UniID to be tracking.
	public $userTracker = false;	// <bool> TRUE if the user has viewed this page, and has tracker data.
	public $userVote = 0;			// <int> The vote of the user.
	public $userShared = false;		// <bool> TRUE if the user shared this content.
	
	
/****** Plugin Constructor ******/
	public function __construct
	(
		$contentID		// <int> The ID of the content entry to construct with this plugin.
	,	$uniID = 0		// <int> The UniID that is loading this (0 if guest).
	)					// RETURNS <void>
	
	// $contentTrack = new ContentTrack($contentID, [$uniID]);
	{
		// Prepare Values
		$this->contentID = (int) $contentID;
		$this->uniID = (int) $uniID;
		
		// Attempt to retrieve the existing tracking data for this entry
		if(!Database::selectOne("SELECT content_id FROM content_tracking WHERE content_id=? LIMIT 1", array($this->contentID)))
		{
			// If we couldn't retrieve an existing row, create one
			Database::query("INSERT IGNORE INTO content_tracking (content_id) VALUES (?)", array($this->contentID));
		}
		
		// Run the user's tracker, if a UniFaction user is logged in
		if($this->uniID)
		{
			// Attempt to retrieve the existing tracking data for a user and a content entry
			if(!$results = Database::selectOne("SELECT shared, vote FROM content_tracking_users WHERE uni_id=? AND content_id=? LIMIT 1", array($this->uniID, $this->contentID)))
			{
				// If we couldn't find the row, create one
				if(Database::query("INSERT INTO content_tracking_users (uni_id, content_id) VALUES (?, ?)", array($this->uniID, $this->contentID)))
				{
					// After creating the row initially, retrieve it
					if($results = Database::selectOne("SELECT shared, vote FROM content_tracking_users WHERE uni_id=? AND content_id=? LIMIT 1", array($this->uniID, $this->contentID)))
					{
						// Also update the view for this page
						Database::query("UPDATE content_tracking SET views=views+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
					}
				}
			}
			
			// Pull the results gathered and save the values appropriately
			if($results)
			{
				$results['vote'] = (int) $results['vote'];
				
				$this->userTracker = true;
				$this->userShared = ($results['shared'] ? true : false);
				
				if($results['vote'] > 0)
				{
					$this->userVote = 1;
				}
				else if($results['vote'] < 0)
				{
					$this->userVote = -1;
				}
			}
		}
		
		// If the user is a guest
		else
		{
			// Check if the guest has already viewed this page recently
			if(isset($_SESSION[SITE_HANDLE]['view-track'][$this->contentID]))
			{
				return false;
			}
			
			// Set a value that indicates the user has viewed this page recently.
			$_SESSION[SITE_HANDLE]['view-track'][$this->contentID] = true;
			
			// Update the tracker with the new rating
			return Database::query("UPDATE content_tracking SET views=views+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
		}
	}
	
	
/****** Update the content rating ******/
	public function updateRating (
	)						// RETURNS <bool> TRUE if the rating was updated properly, FALSE on failure.
	
	// $contentTrack->updateRating();
	{
		// Get Tracking Data
		$trackingData = self::getData($this->contentID);
		
		if(!$trackingData) { return false; }
		
		// Run the algorithms to determine the ratings
		$rating = Ranking::contentRatings((int) $trackingData['views'], (int) $trackingData['votes_up'], (int) $trackingData['votes_down'], (int) $trackingData['comments'], (int) $trackingData['shared'], (int) $trackingData['tipped_amount']);
		
		// Update the tracker with the new rating
		return Database::query("UPDATE content_tracking SET rating=? WHERE content_id=? LIMIT 1", array($rating, $this->contentID));
	}
	
	
/****** Vote a content entry up ******/
	public function voteUp
	(
		$strict = false		// <bool> If set to TRUE, only vote up. Cannot void or vote down.
	)						// RETURNS <int> 1 if voted up, -1 if voted down, 0 on no change.
	
	// $contentTrack->voteUp([$strict]);
	{
		// Check if we need to void the vote
		if($this->userVote == 1)
		{
			if($strict) { return 1; }
			
			return $this->voteVoid();
		}
		
		// Continue with Voting Normally
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET vote=? WHERE uni_id=? AND content_id=? LIMIT 1", array(1, $this->uniID, $this->contentID)))
		{
			// If the user's vote is currently "down", then we need to also reverse the effect of the last vote
			if($this->userVote == -1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_down=votes_down-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
			
			// Update the tracker with the new rating
			if($pass)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
		}
		
		if(Database::endTransaction($pass))
		{
			$this->userVote = 1;
			$this->updateRating();
			
			return 1;
		}
		
		return 0;
	}
	
	
/****** Vote a content entry down ******/
	public function voteDown (
	)				// RETURNS <int> 1 if voted up, -1 if voted down, 0 on no change.
	
	// $contentTrack->voteDown();
	{
		// Check if we need to void the vote
		if($this->userVote == -1)
		{
			return $this->voteVoid();
		}
		
		// Continue with Voting Normally
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET vote=? WHERE uni_id=? AND content_id=? LIMIT 1", array(-1, $this->uniID, $this->contentID)))
		{
			// If the user's vote is currently "up", then we need to also reverse the effect of the last vote
			if($this->userVote == 1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
			
			// Update the tracker with the new rating
			if($pass)
			{
				$pass = Database::query("UPDATE content_tracking SET vote_down=vote_down+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
		}
		
		if(Database::endTransaction($pass))
		{
			$this->userVote = -1;
			$this->updateRating();
			
			return -1;
		}
		
		return 0;
	}
	
	
/****** Void an earlier vote ******/
	public function voteVoid (
	)				// RETURNS <int> 1 if voted up, -1 if voted down, 0 on no change.
	
	// $contentTrack->voteDown();
	{
		$returnVal = 0;
		
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET vote=? WHERE uni_id=? AND content_id=? LIMIT 1", array(0, $this->uniID, $this->contentID)))
		{
			// If the user's vote was set to "up", decrease the number of "up votes" in the content tracker
			if($this->userVote == 1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
				$returnVal = -1;
			}
			
			// If the user's vote was set to "down", decrease the number of "down votes" in the content tracker
			if($this->userVote == -1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
				$returnVal = 1;
			}
		}
		
		if(Database::endTransaction($pass))
		{
			$this->userVote = 0;
			$this->updateRating();
			
			return $returnVal;
		}
		
		return 0;
	}
	
	
/****** Track a user's nooch for this entry ******/
	public function nooch (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->nooch();
	{
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET nooch=nooch+? WHERE uni_id=? AND content_id=? LIMIT 1", array(1, $this->uniID, $this->contentID)))
		{
			$pass = Database::query("UPDATE content_tracking SET nooch=nooch+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
		}
		
		if(Database::endTransaction($pass))
		{
			// Nooches do not affect the ratings right now
			// We may want to update this later
			// $this->updateRating();
		}
		
		return $pass;
	}
	
	
/****** Track a user's share for this entry ******/
	public function share (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->share();
	{
		if($this->userShared) { return true; }
		
		$this->userShared = true;
		
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET shared=? WHERE uni_id=? AND content_id=? LIMIT 1", array(1, $this->uniID, $this->contentID)))
		{
			$pass = Database::query("UPDATE content_tracking SET shared=shared+? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
		}
		
		if(Database::endTransaction($pass))
		{
			$this->updateRating();
		}
		
		return $pass;
	}
	
	
/****** Track a comment for this content entry ******/
	public function comment (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->comment();
	{
		if(Database::query("UPDATE content_tracking SET comment=comment+? WHERE content_id=? LIMIT 1", array(1, $this->contentID)))
		{
			$this->updateRating(); return true;
		}
		
		return false;
	}
	
	
/****** Track a flag / report for this content entry ******/
	public function flag (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->flag();
	{
		// Make sure the user is logged in and valid
		if(!$this->uniID)
		{
			return false;
		}
		
		// Get the content data
		$contentData = Content::get($this->contentID);
		
		// Make sure you're not flagging your own content
		if($this->uniID == $contentData['uni_id'])
		{
			return false;
		}
		
		// Prepare Flag Count
		$flags = Database::selectValue("SELECT flagged FROM content_tracking WHERE content_id=? LIMIT 1", array($contentID));
		$flags = (int) ($flags + 1);
		
		// Update the number of flags on this content
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking SET flagged=flagged+? WHERE content_id=? LIMIT 1", array(1, $this->contentID)))
		{
			// Check how many times the content has been flagged, and set the importance based on this value
			$importance = min(9, $flags);
			
			// Create a report
			$pass = Database::query("INSERT INTO site_reports (submitter_id, uni_id, importance_level, url, timestamp) VALUES (?, ?, ?, ?, ?)", array(Me::$id, $contentData['uni_id'], $importance, SITE_URL . "/" . $contentData['url_slug'], time()));
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Track a tip for this content entry ******/
	public function tip
	(
		$amount		// <float> The amount to tip.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->tip($amount);
	{
		if(Database::query("UPDATE content_tracking SET tipped_times=tipped_times+?, tipped_amount=tipped_amount+? WHERE content_id=? LIMIT 1", array(1, $amount, $this->contentID)))
		{
			$this->updateRating(); return true;
		}
		
		return false;
	}
	
	
/****** Get the Content Tracking Data ******/
	public static function getData
	(
		$contentID		// <int> The ID of the content entry.
	,	$uniID = 0		// <int> The UniID to pull tracking data for.
	)					// RETURNS <str:mixed>
	
	// $trackingData = ContentTrack::getData($contentID, [$uniID]);
	{
		if(!$uniID)
		{
			if(!$result = Database::selectOne("SELECT * FROM content_tracking WHERE content_id=? LIMIT 1", array($contentID)))
			{
				return array();
			}
			
			$result['user_vote'] = 0;
			$result['user_nooch'] = 0;
			$result['user_shared'] = 0;
			
			return $result;
		}
		
		return Database::selectOne("SELECT t.*, u.shared as user_shared, u.vote as user_vote, u.nooch as user_nooch FROM content_tracking t LEFT JOIN content_tracking_users u ON u.uni_id=? AND u.content_id=t.content_id WHERE t.content_id=? LIMIT 1", array($uniID, $contentID));
	}
}
