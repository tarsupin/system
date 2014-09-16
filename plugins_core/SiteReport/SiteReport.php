<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------
------ SiteReport Class ------
------------------------------

This plugin allows us to acquire site reports from users, such as if they want to flag a post as inappropriate or file a bug report.

The last report ID is saved as SiteReport::$lastReportID, which allows you to provide a URL to that report.


-------------------------------
------ Methods Available ------
-------------------------------

$reportData = SiteReport::getData($reportID);

SiteReport::create($action, $url, $submitterID, $uniID, [$details]);
SiteReport::update($reportID, $modID, $importance, $details);
SiteReport::delete($reportID);

*/

abstract class SiteReport {
	
	
/****** Class Variables ******/
	public static $lastReportID = 0;
	
	
/****** Get the Data of a Mod Report ******/
	public static function getData
	(
		$reportID		// <int> The ID of the report to retrieve data from.
	)					// RETURNS <str:mixed> data on the report, FALSE on failure.
	
	// $reportData = SiteReport::getData($reportID);
	{
		return Database::selectOne("SELECT * FROM site_reports WHERE id=? LIMIT 1", array($reportID));
	}
	
	
/****** Create a Report ******/
	public static function create
	(
		$action			// <str> The action that caused this report.
	,	$url			// <str> A relevant url, if applicable.
	,	$submitterID	// <int> The ID of the user responsible for submitting the report.
	,	$uniID = 0		// <int> The ID of the associated user (if applicable).
	,	$details = ""	// <str> The details associated with this report.
	,	$importance = 0	// <int> The level of importance of the report.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SiteReport::create($action, $url, $submitterID, $uniID, [$details], [$importance]);
	{
		if($result = Database::query("INSERT INTO site_reports (submitter_id, uni_id, action, url, importance_level, details, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)", array($submitterID, $uniID, $action, $url, $importance, $details, time())))
		{
			self::$lastReportID = Database::$lastID;
		}
		
		return $result;
	}
	
	
/****** Update a Report ******/
	public static function update
	(
		$reportID		// <int> The ID of the report to edit.
	,	$modID			// <int> The ID of the person that handled the report.
	,	$importance		// <int> The importance level of this report.
	,	$details		// <str> The details associated with this report.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SiteReport::update($reportID, $modID, $importance, $details);
	{
		return Database::query("UPDATE site_reports SET mod_id=?, importance_level=?, details=? WHERE id=? LIMIT 1", array($modID, $importance, $details, $reportID));
	}
	
	
/****** Delete a Mod Report ******/
	public static function delete
	(
		$reportID		// <int> The ID of the report to edit.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SiteReport::delete($reportID);
	{
		return Database::query("DELETE FROM site_reports WHERE id=? LIMIT 1", array($reportID));
	}
	
}
