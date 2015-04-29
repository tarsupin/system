<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------
------ About the Poll Plugin ------
-----------------------------------

This plugin provides functionality for creating and holding polls. Each poll consists of 1 question and multiple options. It is possible to set the number of answers a participant is allowed to give. Optionally the answers can be weighted according to priority.

(TODO: give more details)
*/

abstract class Poll {
	

/****** Retrieve a poll's question ******/
	public static function getQuestion
	(
		int $pollID		// <int> The ID of the poll.
	): array <str, mixed>				// RETURNS <str:mixed> the poll details.
	
	// Poll:getQuestion(1);
	{
		return Database::selectOne("SELECT * FROM poll_questions WHERE id=? LIMIT 1", array($pollID));
	}
	
/****** Retrieve a poll's options ******/
	public static function getOptions
	(
		int $pollID		// <int> The ID of the poll.
	): array <str, mixed>				// RETURNS <str:mixed> the poll options.
	
	// Poll:getOptions(1);
	{
		return Database::selectMultiple("SELECT * FROM poll_options WHERE question_id=?", array($pollID));
	}
	
/****** Retrieve a poll's rules ******/
	public static function getRules
	(
		array <str, mixed> $question	// <str:mixed> The poll data.
	): array <str, mixed>				// RETURNS <str:mixed> the poll rules.
	
	// Poll:getRules($question);
	{		
		return json_decode($question['rules'], true);
	}
	
/****** Retrieve a poll's standings ******/
	public static function getStandings
	(
		int $pollID		// <int> The ID of the poll.
	): array <str, mixed>				// RETURNS <str:mixed> the poll standings ordered by score and votes.
	
	// Poll:getStandings(1);
	{
		if(!self::checkStandingsPermission($pollID))
		{
			return array();
		}
		
		$standings = Database::selectMultiple("SELECT text, total_votes, total_score FROM poll_options WHERE question_id=?", array($pollID));
		
		// sort by score first, votes second
		usort($standings, function($a, $b)
		{
			if($a['total_score'] == $b['total_score'])
			{
				if($a['total_votes'] == $b['total_votes'])
				{
					return 0;
				}
				return ($a['total_votes'] < $b['total_votes']) ? 1 : -1;
			}
			return ($a['total_score'] < $b['total_score']) ? 1 : -1;
		});
		
		return $standings;
	}
	
/****** Retrieve a poll's participants ******/
	public static function getParticipants
	(
		int $pollID			// <int> The ID of the poll.
	,	int $authorID = 0	// <int> The ID of the poll author.
	): array <str, mixed>					// RETURNS <str:mixed> the poll participants.
	
	// Poll:getParticipants(1, 5);
	{
		if($authorID == 0)
		{
			if(!$question = self::getQuestion($pollID))
			{
				return array();
			}
			$authorID = (int) $question['author_id'];
		}		
		
		$level = self::checkParticipantsPermission($pollID, $authorID);
		
		switch($level)
		{
			case 2:
				return Database::selectMultiple("SELECT v.uni_id, handle, v.option_id, text, score FROM poll_votes v INNER JOIN users u ON v.uni_id=u.uni_id INNER JOIN poll_options o ON v.question_id=o.question_id AND v.option_id=o.option_id WHERE v.question_id=?", array($pollID));
				break;			
			case 1:
				return Database::selectMultiple("SELECT DISTINCT v.uni_id, handle FROM poll_votes v INNER JOIN users u ON v.uni_id=u.uni_id WHERE v.question_id=?", array($pollID));
				break;
			default:
				return array();
				break;
		}
	}
	
/****** Check whether the user has voted ******/
	public static function getVote
	(
		int $pollID		// <int> The ID of the poll.
	,	int $uniID = 0	// <int> The ID of the user. Defaults to Me::$id.
	): array <str, mixed>				// RETURNS <str:mixed> The participant's vote.
	
	// Poll:getVote(1, 9);
	{
		if($uniID == 0)
		{
			$uniID = Me::$id;
		}
		
		$votes = Database::selectMultiple("SELECT option_id, score FROM poll_votes WHERE question_id=? AND uni_id=?", array($pollID, $uniID));
		
		// flatten array
		$result = array();
		foreach($votes as $vote)
		{
			$result[$vote['option_id']] = $vote['score'];
		}
		
		return $result;
	}
	
/****** Check whether someone is allowed to vote in the poll ******/
	private static function checkVotePermission
	(
		int $pollID		// <int> The ID of the poll.
	,	int $start = 0	// <int> The start time of the poll.
	,	int $end = 0	// <int> The end time of the poll.
	): bool				// RETURNS <bool> TRUE on allowed voting, FALSE otherwise.
	
	// Poll:checkVotePermission(1, 1423296000, 1423900800);
	{
		if($start == 0 && $end == 0)
		{
			$question = self::getQuestion($pollID);
			$start = (int) $question['date_start'];
			$end = (int) $question['date_end'];
		}
		
		// only allow voting if the poll is ongoing
		if(time() < $start || time() >= $end)
		{
			return false;
		}
		
		if(!Me::$loggedIn)
		{
			return false;
		}
		
		// allow voting if not done yet
		return !self::getVote($pollID);
	}
	
/****** Check whether someone is allowed to edit the poll ******/
	public static function checkEditPermission
	(
		int $pollID		// <int> The ID of the poll.
	): array <str, mixed>				// RETURNS <str:mixed> the poll details on allowed usage, array() otherwise.
	
	// Poll:checkEditPermission(1);
	{
		// get poll question
		if(!$question = self::getQuestion($pollID))
		{
			return array();
		}
		
		// only allow editing if the poll has not started yet
		if(time() >= $question['date_start'])
		{
			Alert::saveError("Running", "You may not edit a poll after it has started.");
			return array();
		}
		
		// only the creator and mods may edit
		if(Me::$id != $question['author_id'])
		{
			if(Me::$clearance < 6)
			{
				Alert::saveError("No Permission", "You do not have permission to edit this poll.");
				return array();
			}
		}
		
		return $question;
	}
	
/****** Check whether someone is allowed to view the poll standings ******/
	private static function checkStandingsPermission
	(
		int $pollID		// <int> The ID of the poll.
	): bool				// RETURNS <bool> TRUE on allowed usage, FALSE otherwise.
	
	// Poll:checkStandingsPermission(1);
	{
		// get poll question
		if(!$question = self::getQuestion($pollID))
		{
			return false;
		}
		
		// viewing is always allowed after the poll has ended or if the viewing user is the poll creator
		if(time() < $question['date_end'] && Me::$id != $question['author_id'])
		{
			$rules = self::getRules($question);
			
			// with this setting, everyone may view
			if($rules['view_standings'] != "public")
			{
				// allow staff to view
				if(Me::$clearance < 5)
				{
					if($rules['view_standings'] == "closed" || !self::getVote($pollID))
					{
						Alert::saveError("Not Allowed", "You may not view the standings before " . ($rules['view_standings'] == "closed" ? "the poll has ended" : "you have voted") . ".");
						return false;
					}
				}
			}
		}
		
		return true;
	}
	
/****** Check whether someone is allowed to view the poll participants ******/
	private static function checkParticipantsPermission
	(
		int $pollID		// <int> The ID of the poll.
	,	int $authorID	// <int> The ID of the poll author.
	): int				// RETURNS <int> 0 if not allowed, 1 if only allowed to view the names, 2 if allowed to view votes too
	
	// Poll:checkParticipantsPermission(1, 5);
	{			
		// allow mods to view votes
		if(Me::$clearance >= 6)
		{
			return 2;
		}
		
		if(Me::$id == $authorID)
		{
			return 1;
		}
		
		return 0;
	}
	
/****** Create a poll ******/
	public static function create
	(
		string $title						// <str> The poll title.
	,	string $description				// <str> The question.
	,	int $date_start					// <int> The start time.
	,	int $date_end					// <int> The end time.
	,	int $max_choices = 1			// <int> The max number of options a participant may pick.
	,	bool $priority_weight = false	// <bool> Whether to weight options according to priority.
	,	string $view_standings = "closed"	// <str> "closed", "vote" or "public", whether participants may see the standings in an ongoing poll.
	,	bool $randomize_display = false	// <bool> Whether to show the options in random order while voting.
	,	mixed $requirements = array()		// <mixed> An array of any requirements for participation in the poll.
	): int								// RETURNS <int> The ID of the new poll.
	
	// Poll::create("It's Summer!", "Which ice cream flavor do you like best?", 1423296000, 1423900800, 1, false, "public", true);
	{
		$pollID = 0;
		
		// validate parameters
		if(!in_array($view_standings, array("closed", "vote", "public")))
		{
			return 0;
		}
		if($date_start <= time() || $date_end <= $date_start)
		{
			Alert::error("Invalid Time", "The poll cannot start in the past or have a negative duration.");
			return 0;
		}
		$title = trim($title);
		$description = trim($description);
		FormValidate::text("Title", $title, 1, 100, "/~");
		FormValidate::text("Question", $description, 1, 250, "/~");
		
		if(FormValidate::pass())
		{
			Database::startTransaction();
			
			// create an ID for the poll
			if(!$pollID = UniqueID::get("poll"))
			{
				UniqueID::newCounter("poll");
				$pollID = 1;
			}
			
			// save question
			if($pass = Database::query("INSERT INTO poll_questions VALUES (?, ?, ?, ?, ?, ?, ?)", array($pollID, Me::$id, $title, $description, json_encode(array("priority_weight" => $priority_weight, "view_standings" => $view_standings, "max_choices" => $max_choices, "randomize_display" => $randomize_display, "requirements" => $requirements)), $date_start, $date_end)))
			{
				// save "by author" for optimized lookup
				$pass = Database::query("INSERT INTO poll_questions_by_author VALUES (?, ?)", array(Me::$id, $pollID));
			}
			
			// complete if everything checks out
			Database::endTransaction($pass);
			if(!$pass)
			{
				return 0;
			}		
		}	
		
		return $pollID;
	}
	
/****** Add an option ******/
	public static function addOption
	(
		int $pollID						// <int> The ID of the poll.
	,	string $text						// <str> The option text to add.
	): bool								// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Poll::addOption(1, "Something Else");
	{
		if(!self::checkEditPermission($pollID))
		{
			return false;
		}
		
		$text = trim($text);
		FormValidate::text("Option", $text, 1, 120, "/~");
		
		if(FormValidate::pass())
		{
			// find number to insert with
			$optionID = (int) Database::selectValue("SELECT option_id FROM poll_options WHERE question_id=? ORDER BY option_id DESC LIMIT 1", array($pollID));
			$optionID++;
			
			if($optionID >= 100)
			{
				Alert::error("Too Many Options", "You have too many options.");
				return false;
			}
		
			return Database::query("INSERT INTO poll_options (question_id, option_id, text, sort_position) VALUES (?, ?, ?, ?)", array($pollID, $optionID, $text, $optionID));
		}
		
		return false;
	}
	
/****** Delete an option ******/
	public static function deleteOption
	(
		int $pollID						// <int> The ID of the poll.
	,	int $optionID					// <int> The option being removed.
	): bool								// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Poll::deleteOption(1, 2);
	{
		if(!$question = self::checkEditPermission($pollID))
		{
			return false;
		}
		
		if(!$pos = Database::selectValue("SELECT sort_position FROM poll_options WHERE question_id=? AND option_id=? LIMIT 1", array($pollID, $optionID)))
		{
			return false;
		}
		
		Database::startTransaction();
		if($pass = Database::query("DELETE FROM poll_options WHERE question_id=? AND option_id=? LIMIT 1", array($pollID, $optionID)))
		{
			// deleting options is only allowed before the poll starts, so there is no need to delete anything from poll_votes
			
			// adjust sort positions so that no problems occur when adding or moving options later
			$pass = Database::query("UPDATE poll_options SET sort_position=sort_position-1 WHERE question_id=? AND sort_position>?", array($pollID, (int) $pos));
			
			if($pass)
			{
				// adjust options IDs so that no problems occur when adding or moving options later
				$pass = Database::query("UPDATE poll_options SET option_id=option_id-1 WHERE question_id=? AND option_id>?", array($pollID, $optionID));
			}
		}
		Database::endTransaction($pass);
		
		return $pass;
	}
	
/***** Move an option *****/
	public static function moveOption
	(
		int $pollID						// <int> The ID of the poll.
	,	int $optionID					// <int> The option being moved.
	,	bool $direction					// <bool> TRUE for up, FALSE for down
	): bool								// RETURNS <bool> TRUE on success, FALSE otherwise

	// Poll::moveOption(1, 2, -1);
	{
		if(!self::checkEditPermission($pollID))
		{
			return false;
		}
		
		// get old and new position
		$fromSort = Database::selectOne("SELECT option_id, sort_position FROM poll_options WHERE question_id=? AND option_id=? LIMIT 1", array($pollID, $optionID));
		$toSort = Database::selectOne("SELECT option_id, sort_position FROM poll_options WHERE question_id=? AND sort_position" . ($direction ? "<" : ">") . "? ORDER BY sort_position" . ($direction ? " DESC" : "") . " LIMIT 1", array($pollID, $fromSort['sort_position']));
		
		if(!$fromSort || !$toSort)
		{
			return false;
		}
		
		// exchange positions
		if($fromSort['sort_position'] != $toSort['sort_position'])
		{
			Database::startTransaction();
			$pass1 = Database::query("UPDATE poll_options SET sort_position=? WHERE question_id=? AND option_id=? LIMIT 1", array((int) $toSort['sort_position'], $pollID, (int) $fromSort['option_id']));
			$pass2 = Database::query("UPDATE poll_options SET sort_position=? WHERE question_id=? AND option_id=? LIMIT 1", array((int) $fromSort['sort_position'], $pollID, (int) $toSort['option_id']));
			Database::endTransaction($pass1 && $pass2);
			return ($pass1 && $pass2);
		}
		
		return false;
	}
	
/***** Edit an option *****/
	public static function editOption
	(
		int $pollID						// <int> The ID of the poll.
	,	int $optionID					// <int> The option being edited.
	,	string $text						// <str> The new option text.
	): bool								// RETURNS <bool> TRUE on success, FALSE otherwise

	// Poll::editOption(1, 2, "Lemon Sorbet");
	{
		if(!self::checkEditPermission($pollID))
		{
			return false;
		}
		
		$text = trim($text);
		FormValidate::text("Option", $text, 1, 120, "/~");
		
		if(FormValidate::pass())
		{
			return Database::query("UPDATE poll_options SET text=? WHERE question_id=? AND option_id=? LIMIT 1", array($text, $pollID, $optionID));
		}
		
		return false;
	}
	
/****** Edit a poll ******/
	public static function edit
	(
		int $pollID						// <int> The ID of the poll to edit.
	,	string $title						// <str> The poll title.
	,	string $description				// <str> The question.
	,	int $date_start					// <int> The start time.
	,	int $date_end					// <int> The end time.
	,	int $max_choices = 1			// <int> The max number of options a participant may pick.
	,	bool $priority_weight = false	// <bool> Whether to weight options according to priority.
	,	string $view_standings = "closed"	// <str> "closed", "vote" or "public", whether participants may see the standings in an ongoing poll.
	,	bool $randomize_display = false	// <bool> Whether to show the options in random order while voting.
	,	mixed $requirements = array()		// <mixed> An array of any requirements for participation in the poll.
	): bool								// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Poll::edit(1, "It'll soon be Summer!", "Which ice cream flavor do you like best?", 1423296000, 1423900800, 1, false, "vote", true);
	{
		// check that poll exists
		if(!self::getQuestion($pollID))
		{
			return false;
		}
		
		// validate parameters
		if(!in_array($view_standings, array("closed", "vote", "public")))
		{
			return false;
		}
		if($date_start <= time() || $date_end <= $date_start)
		{
			Alert::error("Invalid Time", "The poll cannot start in the past or have a negative duration.");
			return false;
		}

		$title = trim($title);
		$description = trim($description);
		FormValidate::text("Title", $title, 1, 100, "/~");
		FormValidate::text("Question", $description, 1, 250, "/~");
		
		if(FormValidate::pass())
		{
			Database::startTransaction();
			
			// save question
			$pass = Database::query("UPDATE poll_questions SET title=?, description=?, rules=?, date_start=?, date_end=? WHERE id=? LIMIT 1", array($title, $description, json_encode(array("priority_weight" => $priority_weight, "view_standings" => $view_standings, "max_choices" => $max_choices, "randomize_display" => $randomize_display, "requirements" => $requirements)), $date_start, $date_end, $pollID));
				
			// complete if everything checks out
			Database::endTransaction($pass);
			return $pass;
		}
		
		return false;
	}
	
/***** Output for poll *****/
	public static function displayPoll
	(
		int $pollID						// <int> The ID of the poll.
	,	bool $extra = false				// <bool> TRUE to include the title, FALSE if the script handles it
	): string								// RETURNS <str> The html for the poll or an empty string on failure.
	
	// Poll::displayPoll(1);
	{
		// get data
		if(!$question = self::getQuestion($pollID))
		{
			return "";
		}		
		if(!$options = self::getOptions($pollID))
		{
			return "";
		}
		
		$html = '';
		
		if($extra)
		{
			$html .= '
		<h3>' . UniMarkup::parse($question['title']) . '</h3>';
		}
		
		// output description
		$author = User::get((int) $question['author_id'], "handle");
		$html .= '
		' . UniMarkup::parse('[quote=' . $author['handle'] . '<br/>Start: ' . Time::fuzzy((int) $question['date_start']) . ', End: ' . Time::fuzzy((int) $question['date_end']) . ']' . $question['description'] . '[/quote]');
		
		$rules = self::getRules($question);
		
		// check the maximum number of choices, but not edit it in database since the author may be in the middle of editing/creating
		if($rules['max_choices'] > count($options))
		{
			$rules['max_choices'] = count($options);
		}
		
		$html .= '
		<div class="spacer"></div>
		<form class="uniform" method="post">' . Form::prepare("pollanswer");
		
		if($rules['priority_weight'] && $rules['max_choices'] > 1)
		{
			$html .= '
			Please set your order of preference by giving points from 0 (not included in your vote) to ' . $rules['max_choices'] . ' (best possible). You can include up to ' . $rules['max_choices'] . ' options in your vote. Each of those must have a different number of points.<br/>';
		}
		elseif ($rules['max_choices'] > 1)
		{
			$html .= '
			Please choose up to ' . $rules['max_choices'] . ' options.<br/>';
		}
		
		// determine order to display options in
		if($rules['randomize_display'])
		{
			// randomize order
			shuffle($options);
		}
		else
		{
			// sort by set position
			usort($options, function($a, $b)
			{
				if($a['sort_position'] == $b['sort_position'])
				{
					return 0;
				}
				return ($a['sort_position'] < $b['sort_position']) ? -1 : 1;
			});
		}
		
		// check if user has voted
		$previous = self::getVote($pollID);
		
		// output options
		foreach($options as $opt)
		{
			$html .= '
			<br/>';
			
			if($rules['priority_weight'] && $rules['max_choices'] > 1)
			{
				// normally the value version works; the innerHTML version is fallback for IE
				$html .= '<input type="range" name="optid_' . $opt['option_id'] . '" min="0" max="' . $rules['max_choices'] . '" value="' . (isset($_POST['optid_' . $opt['option_id']]) ? $_POST['optid_' . $opt['option_id']] : (isset($previous[$opt['option_id']]) ? $previous[$opt['option_id']] : '0')) . '" onchange="document.getElementById(\'valoptid_' . $opt['option_id'] . '\').innerHTML = this.value; document.getElementById(\'valoptid_' . $opt['option_id'] . '\').value = this.value;">(<output id="valoptid_' . $opt['option_id'] . '"/>' . (isset($_POST['optid_' . $opt['option_id']]) ? $_POST['optid_' . $opt['option_id']] : (isset($previous[$opt['option_id']]) ? $previous[$opt['option_id']] : '0')) . '</output> Points)';
			}
			elseif ($rules['max_choices'] > 1)
			{
				$html .= '<input type="checkbox" name="optid_' . $opt['option_id'] . '"' . ($_POST != array() && isset($_POST['optid_' . $opt['option_id']]) ? ' checked' : (isset($previous[$opt['option_id']]) ? ' checked' : '')) . '>';
			}
			else
			{
				$html .= '<input type="radio" name="selection" value="' . $opt['option_id'] . '"' . ($_POST != array() && $_POST['selection'] == $opt['option_id'] ? ' selected' : (isset($previous[$opt['option_id']]) ? ' selected' : '')) . '>';
			}
			
			$html .= ' ' .  UniMarkup::parse($opt['text']);
		}
		
		$html .= '
		<div class="spacer"></div>';
		
		// output button(s)
		if(!$previous)
		{
			if(self::checkVotePermission($pollID, (int) $question['date_start'], (int) $question['date_end']))
			{
				$html .= '
			<input type="submit" value="Vote!">';
			}
		}
		else
		{
			$html .= '
			<input type="submit" name="redo" value="Change Vote" onclick="return confirm(\'Are you sure you want to change your vote?\');">';
		}
		
		if(self::checkStandingsPermission($pollID))
		{
			$html .= '
			<input type="button" value="View Standings" onclick="window.location.href=\'/poll-standings?id=' . $pollID . '\'">';
		}
		
		$html .= '
		</form>
	</div>
</div>';
		
		return $html;
	}
	
/***** Output for standings *****/
	public static function displayStandings
	(
		array <str, mixed> $standings					// <str:mixed> The standings.
	): string								// RETURNS <str> The output html.
	
	// Poll::displayStandings(Poll::getStandings(1));
	{
		$html = '
		<table class="polltable">
			<tr><th>Option</th><th>Score</th><th>Votes</th></tr>';
		foreach($standings as $stand)
		{
			$html .= '
			<tr><td>' . UniMarkup::parse($stand['text']) . '</td><td>' . $stand['total_score'] . '</td><td>' . $stand['total_votes'] . '</td></tr>';
		}		
		$html .= '
		</table>';
		
		return $html;
	}
	
/***** Output for votes *****/
	public static function displayVotes
	(
		int $pollID						// <int> The ID of the poll.
	,	int $authorID					// <int> The ID of the poll author.
	): string								// RETURNS <str> The output html.
	
	// Poll::displayVotes(1, 5);
	{
		$votes = self::getParticipants($pollID, $authorID);
		if(!$votes)
		{
			return "";
		}
		if(isset($votes[0]['score']))
		{
			$html = '
		<table class="polltable">
			<tr><th>User</th><th>Score</th><th>Option</th></tr>';
			foreach($votes as $vote)
			{
			$html .= '
			<tr><td><a href="poll-standings?id=' . $pollID . '&remove=' . $vote['handle'] . '" onclick="return confirm(\'Are you sure you want to remove the votes of this user?\');"><span class="icon-circle-close"></span></a> ' . $vote['handle'] . '</td><td>' . $vote['score'] . '</td><td>' . UniMarkup::parse($vote['text']) . '</td></tr>';
			}		
			$html .= '
		</table>';
		}
		else
		{
			$votes = array_map(function($p) { global $pollID; return '<a href="poll-standings?id=' . $pollID . '&remove=' . $p['handle'] . '" onclick="return confirm(\'Are you sure you want to remove the votes of this user?\');"><span class="icon-circle-close"></span></a> ' . $p['handle']; }, $votes);
			$votes = array_unique($votes);
			sort($votes);
			return implode(", ", $votes);
		}
		
		return $html;
	}
	
/***** Vote in poll *****/
	public static function vote
	(
		int $pollID						// <int> The ID of the poll.
	,	array <str, mixed> $rules						// <str:mixed> The rules of the poll.
	,	array <str, mixed> $data						// <str:mixed> The vote data.
	): bool								// RETURNS <bool> TRUE on success, FALSE on failure.

	// Poll::vote(1, $rules, $_POST);
	{
		Database::startTransaction();
		$pass = true;
		if(isset($data['redo']))
		{
			if($pass = self::removeVote($pollID, Me::$id))
			{
				unset($data['redo']);
			}
		}

		if(!self::checkVotePermission($pollID))
		{
			return false;
		}
		
		// process input data
		if(isset($data['selection']))
		{
			$votes[(int) $data['selection']] = 1;
		}
		else
		{
			foreach($data as $key => $val)
			{
				$split = explode("_", $key);
				if($split[0] == "optid")
				{
					if(!is_numeric($val))
					{
						$votes[(int) $split[1]] = 1;
					}
					elseif($val > 0)
					{
						$votes[(int) $split[1]] = (int) $val;
					}
				}
			}
		}
		
		if(!isset($votes))
		{
			return false;
		}
		
		// calculate points where still necessary
		if($rules['priority_weight'] && $rules['max_choices'] > 1)
		{
			if(count(array_unique($votes)) < count($votes))
			{
				return false;
			}
			
			arsort($votes);
			$count = $rules['max_choices'];
			foreach($votes as $key => $val)
			{
				$votes[$key] = $count;
				$count--;
			}
		}
		
		if($pass)
		{
			foreach($votes as $key => $val)
			{
				if(!$pass = Database::query("INSERT INTO poll_votes VALUES (?, ?, ?, ?)", array($pollID, Me::$id, $key, $val)))
				{
					break;
				}
				if(!$pass = Database::query("UPDATE poll_options SET total_votes=total_votes+?, total_score=total_score+? WHERE question_id=? AND option_id=? LIMIT 1", array(1, $val, $pollID, $key)))
				{
					break;
				}
			}
		}
		
		Database::endTransaction($pass);
		
		return $pass;
	}
	
/***** Remove a vote *****/
	public static function removeVote
	(
		int $pollID						// <int> The ID of the poll.
	,	int $uniID						// <int> The ID of the user whose vote gets removed.
	): bool								// RETURNS <bool> TRUE on success, FALSE on failure.

	// Poll::removeVote(1, 9);
	{
		if(!$votes = self::getVote($pollID, $uniID))
		{
			return true;
		}
		
		// if not removing own vote, check whether doing so is allowed
		// votes can be changed while the poll is ongoing; the ability to remove one's own vote (unless one owns the poll or is a mod) is not desirable
		if($uniID != Me::$id)
		{
			if(!self::checkParticipantsPermission($pollID))
			{
				return false;
			}
		}
		
		Database::startTransaction();
		$pass = Database::query("DELETE FROM poll_votes WHERE question_id=? AND uni_id=?", array($pollID, $uniID));
		if($pass)
		{
			foreach($votes as $key => $val)
			{
				if(!$pass = Database::query("UPDATE poll_options SET total_votes=total_votes-?, total_score=total_score-? WHERE question_id=? AND option_id=? LIMIT 1", array(1, $val, $pollID, $key)))
				{
					break;
				}
			}
		}
		Database::endTransaction($pass);
		
		return $pass;
	}
}