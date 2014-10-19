<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the Ranking Plugin ------
--------------------------------------

This plugin is used to determine what "rank" something gets, such as how high it should be ordered on a list.


-------------------------------
------ Methods Available ------
-------------------------------

// Moves content quickly
$rating = Ranking::fast($votesUp, $votesDown, $actions, $timePassed);

// Assigns a rating to content entries
$rating = Ranking::contentRatings($views, $votesUp, $votesDown, $comments, $shared, $tippedAmount, $timePassed);

*/

abstract class Ranking {

/****** About the Ranking Class ******
This class is used to determine what "rank" something gets.
 
****** Methods Available ******
* $rating = Ranking::fast($votesUp, $votesDown, $actions, $timePassed);		// Moves content quickly
* + $rating = Ranking::slow($votesUp, $votesDown, $actions, $timePassed);	// Moves content slowly
* 
*/
	
	
/****** Calculate Rank (Fast Algorithm: Moves content quickly) ******/
# The higher the ranking, the better.
	public static function fast
	(
		$votesUp		// <int> The number of positive votes received.
	,	$votesDown		// <int> The number of negative votes received.
	,	$actions		// <int> The number of actions performed (comments, shares).
	,	$timePassed		// <int> The amount of time that has passed (in seconds).
	)					// RETURNS <float> weight of the rank.
	
	// $rating = Ranking::fast($votesUp, $votesDown, $actions, $timePassed);
	{
		// There are two time degrades; a one-hour bonus burst of opportunity, which decays after the hour
		// The slow decay is perpetual
		$minutesPassed = floor($timePassed / 60);
		
		// InitialDegrade subtracts 5 from the total ranking every minute, up to a total of 300 (in 60 minutes).
		$initialDegrade = min(300, $minutesPassed * 5);
		
		// FastDegrade subtracts 5 from the total ranking every minute.
		$fastDegrade = $minutesPassed * 5;
		
		// The percentage of interest in the topic has a moderate impact at first, but may be negligible later.
		// Every percent that is positive is a +10 value.
		$percentBoost = ($votesUp / ($votesDown + 1)) * 100 * 10;
		
		// Up votes have a strong impact, while down votes have a lesser impact
		$upvote = $votesUp * 25;
		$downvote = $votesDown * 16;
		
		// Actions such as comments will increase the amount of attention drawn
		$actionBoost = $actions * 5;
		
		// Calculate all of the boosts (values > 0)
		$boosts = $percentBoost + $upvote + $actionBoost;
		
		// As time passes, we need to run a final degrade that cuts the value of the boosts.
		// The minimum multiplier of the boosts is x0.25
		$multiplier = (100 - min(75, floor($minutesPassed / 20))) / 100;
		$boosts = $boosts * $multiplier;
		
		// Determine the rating value
		$rating = 100000 - $initialDegrade - $fastDegrade - $downvote + $boosts;
		
		return (float) number_format($rating / 1000, 4);
	}
	
	
/****** Calculate Rank of Content Entries ******/
	public static function contentRatings
	(
		$views			// <int> The number of views the content has received.
	,	$votesUp		// <int> The number of positive votes received.
	,	$votesDown		// <int> The number of negative votes received.
	,	$comments		// <int> The number of comments created.
	,	$shared			// <int> The number of times the entry has been shared.
	,	$tippedAmount	// <float> The amount in total that has been tipped to the entry.
	)					// RETURNS <float> weight of the rank.
	
	// $rating = Ranking::contentRatings($views, $votesUp, $votesDown, $comments, $shared, $tippedAmount);
	{
		$rating = 0;
		
		// Provide basic rating values
		$rating += $views;
		$rating += $shared * 350;
		$rating += $votesUp * 10;
		$rating -= $votesDown * 5;
		$rating += $comments * 40;
		$rating += round($tippedAmount) * 250;
		
		// Also adjust based on the percentage of votes
		// Ratings with high votes (90%+) gain a much higher boost, since this is an exponential curve.
		// The balanced value is 70%, where there is no positive or negative modifier.
		// Anything below 50% would have major penalties to it.
		$votesTotal = $votesUp + $votesDown;
		
		if($votesTotal >= 25)
		{
			$votesPer = $votesUp / $votesTotal;
			
			// Multiply the percentage by itself (so we can identify exponential curve)
			$voteMult = $votesPer * $votesPer;
			
			// Divide the multiplier by 70 x 70 (so that we can balance at exactly 70% upvote rating)
			$voteMult = $voteMult / 4700;
			
			// Update the rating to multiply at the new value
			$rating = round($rating * $voteMult);
		}
		
		return (float) $rating;
	}
	
}
