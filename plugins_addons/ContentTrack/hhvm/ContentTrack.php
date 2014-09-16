<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

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
	public int $contentID = 0;			// <int> The ID of the content entry.
	public array <str, mixed> $trackerData = array();	// <str:mixed> The tracker data for this content entry.
	
	public int $uniID = 0;				// <int> The UniID to be tracking.
	public bool $userTracker = false;	// <bool> TRUE if the user has viewed this page, and has tracker data.
	public int $userVote = 0;			// <int> The vote of the user.
	public bool $userShared = false;		// <bool> TRUE if the user shared this content.
	
	
/****** Plugin Constructor ******/
	public function __construct
	(
		int $contentID		// <int> The ID of the content entry to construct with this plugin.
	,	int $uniID = 0		// <int> The UniID that is loading this (0 if guest).
	): void					// RETURNS <void>
	
	// $contentTrack = new ContentTrack($contentID, [$uniID]);
	{
		// Prepare Values
		$this->contentID = $contentID + 0;
		$this->uniID = $uniID + 0;
		
		// Attempt to retrieve the existing tracking data for this entry
		if(!$this->trackerData = Database::selectOne("SELECT content_id, rating, views, shared, comments, votes_up, votes_down, tipped_times, tipped_amount, flagged FROM content_tracking WHERE content_id=? LIMIT 1", array($this->contentID)))
		{
			// If we couldn't retrieve an existing row, create one
			if(Database::query("INSERT INTO content_tracking (content_id) VALUES (?)", array($this->contentID)))
			{
				// After creating the row initially, retrieve it
				$this->trackerData = Database::selectOne("SELECT content_id, rating, views, shared, comments, votes_up, votes_down, tipped_times, tipped_amount, flagged FROM content_tracking WHERE content_id=? LIMIT 1", array($this->contentID));
			}
		}
		
		// If there was an error retrieving the tracker data, end now
		if(!$this->trackerData)
		{
			return;
		}
		
		// Recognize Integers
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		$this->trackerData['rating'] = (int) $this->trackerData['rating'];
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		$this->trackerData['content_id'] = (int) $this->trackerData['content_id'];
		
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
				$results['vote'] = (int) $result['vote'];
				
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
	): bool						// RETURNS <bool> TRUE if the rating was updated properly, FALSE on failure.
	
	// $contentTrack->updateRating();
	{
		// Run the algorithms to determine the ratings
		$rating = Ranking::contentRatings($this->trackerData['views'], $this->trackerData['votes_up'], $this->trackerData['votes_down'], $this->trackerData['comments'], $this->trackerData['shared'], $this->trackerData['tipped_amount']);
		
		// Update the tracker with the new rating
		return Database::query("UPDATE content_tracking SET rating=? WHERE content_id=? LIMIT 1", array($rating, $this->contentID));
	}
	
	
/****** Vote a content entry up ******/
	public function voteUp (
	): bool				// RETURNS <bool> TRUE if the vote was properly set, FALSE on failure.
	
	// $contentTrack->voteUp();
	{
		// Check if we need to void the vote
		if($this->userVote == 1)
		{
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
		}
		
		return $pass;
	}
	
	
/****** Vote a content entry down ******/
	public function voteDown (
	): bool				// RETURNS <bool> TRUE if the vote was properly set, FALSE on failure.
	
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
		}
		
		return $pass;
	}
	
	
/****** Void an earlier vote ******/
	public function voteVoid (
	): bool				// RETURNS <bool> TRUE if the vote was properly set, FALSE on failure.
	
	// $contentTrack->voteDown();
	{
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking_users SET vote=? WHERE uni_id=? AND content_id=? LIMIT 1", array(0, $this->uniID, $this->contentID)))
		{
			// If the user's vote was set to "up", decrease the number of "up votes" in the content tracker
			if($this->userVote == 1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
			
			// If the user's vote was set to "down", decrease the number of "down votes" in the content tracker
			if($this->userVote == -1)
			{
				$pass = Database::query("UPDATE content_tracking SET votes_up=votes_up-? WHERE content_id=? LIMIT 1", array(1, $this->contentID));
			}
		}
		
		if(Database::endTransaction($pass))
		{
			$this->userVote = 0;
			$this->updateRating();
		}
		
		return $pass;
	}
	
	
/****** Set user tracking for a content entry ******/
	public function share (
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
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
		
		$this->trackerData['flagged'] += 1;
		
		// Update the number of flags on this content
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_tracking SET flagged=flagged+? WHERE content_id=? LIMIT 1", array(1, $this->contentID)))
		{
			// Check how many times the content has been flagged, and set the importance based on this value
			$importance = min(9, $this->trackerData['flagged']);
			
			// Create a report
			$pass = Database::query("INSERT INTO site_reports (submitter_id, uni_id, importance_level, url, timestamp) VALUES (?, ?, ?, ?, ?)", array(Me::$id, $contentData['uni_id'], $importance, SITE_URL . "/" . $contentData['url_slug'], time()));
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Track a tip for this content entry ******/
	public function tip
	(
		float $amount		// <float> The amount to tip.
	): bool				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentTrack->tip($amount);
	{
		if(Database::query("UPDATE content_tracking SET tipped_times=tipped_times+?, tipped_amount=tipped_amount+? WHERE content_id=? LIMIT 1", array(1, $amount, $this->contentID)))
		{
			$this->updateRating(); return true;
		}
		
		return false;
	}
}