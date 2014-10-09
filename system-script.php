<?php if(!defined("CONF_PATH") or !defined("PROTECTED")) { die("No direct script access allowed."); }  /*

-------------------------------------
------ About the System Script ------
-------------------------------------

File Path: {SYS_PATH}/system-script.php

This script is designed to hold instructions that ALL SITES on this server should be updating. Though each site has to have this action triggered independently, the script exists for the purpose of re-usability across multiple sites.

This page can be activated from the site's admin panel, or can be triggered by a command from Auth. This script is designed to be particularly helpful for localhost development, but can also serve practical purposes for production environments when used for appropriate tasks.

Due to the increased need for security of this page, it cannot be accessed directly, and can only be accessed through pages that also define the "PROTECTED" constant, specifically to enable this script.

*/

// Load administrative database privileges
Database::initRoot();

// Prepare your script(s) below:
if(DatabaseAdmin::tableExists("notifications"))
{
	DatabaseAdmin::renameColumn("notifications", "category", "note_type");
	DatabaseAdmin::addColumn("users", "date_notes", "int(10) unsigned NOT NULL", 0);
}

if(DatabaseAdmin::tableExists("content_block_video"))
{
	DatabaseAdmin::renameColumn("content_block_video", "class", "video_class");
	DatabaseAdmin::renameColumn("content_block_video", "caption", "video_caption");
}

if(DatabaseAdmin::tableExists("content_block_image"))
{
	DatabaseAdmin::renameColumn("content_block_image", "class", "img_class");
}

if(DatabaseAdmin::tableExists("users_friends"))
{
	DatabaseAdmin::dropTable("users_friends");
}

/*
if(DatabaseAdmin::tableExists("content_block_text"))
{
	DatabaseAdmin::dropColumn("content_block_text", "title");
}

if(DatabaseAdmin::tableExists("content_block_image"))
{
	DatabaseAdmin::renameColumn("content_block_image", "title", "caption");
	DatabaseAdmin::addColumn("content_block_image", "credits", "varchar(64) not null", "");
	DatabaseAdmin::dropColumn("content_block_image", "body");
}

if(DatabaseAdmin::tableExists("content_tracking_users"))
{
	DatabaseAdmin::addColumn("content_tracking_users", "nooch", "tinyint(1) unsigned not null", "0");
}

if(DatabaseAdmin::tableExists("content_tracking"))
{
	DatabaseAdmin::addColumn("content_tracking", "nooch", "mediumint(6) unsigned not null", "0");
}

if(DatabaseAdmin::tableExists("content_entries"))
{
	DatabaseAdmin::addColumn("content_entries", "description", "varchar(255) not null", "");
	DatabaseAdmin::addColumn("content_entries", "url", "varchar(64) not null", "");
}

if(DatabaseAdmin::tableExists("content_search"))
{
	DatabaseAdmin::renameColumn("content_search", "keywords", "hashtags");
}

if(DatabaseAdmin::tableExists("content_search_draft"))
{
	DatabaseAdmin::renameColumn("content_search_draft", "keywords", "hashtags");
}

if(DatabaseAdmin::tableExists("content_search_filter_opts"))
{
	DatabaseAdmin::renameColumn("content_search_filter_opts", "keyword", "hashtag");
}

if(DatabaseAdmin::tableExists("content_agg_entries"))
{
	DatabaseAdmin::renameColumn("content_agg_entries", "blurb", "description");
	DatabaseAdmin::renameColumn("content_agg_entries", "image_url", "thumbnail");
	DatabaseAdmin::dropColumn("content_agg_entries", "mobile_url");
}

if(DatabaseAdmin::tableExists("feed_data"))
{
	DatabaseAdmin::renameColumn("feed_data", "blurb", "description");
	DatabaseAdmin::renameColumn("feed_data", "image_url", "thumbnail");
}

if(DatabaseAdmin::tableExists("feed_data_old"))
{
	DatabaseAdmin::renameColumn("feed_data_old", "blurb", "description");
	DatabaseAdmin::renameColumn("feed_data_old", "image_url", "thumbnail");
}

if(strpos(SITE_HANDLE, "article") !== false)
{
	DatabaseAdmin::dropTable("aggregate_sites");
	
	Database::exec("
	CREATE TABLE IF NOT EXISTS `aggregate_sites`
	(
		`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
		`date_checked`			int(10)			unsigned	NOT NULL	DEFAULT '0',
		
		UNIQUE (`site_handle`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	");
}
*/

//*
if(DatabaseAdmin::tableExists("ads_sponsored"))
{
	//DatabaseAdmin::renameColumn("ads_sponsored", "reference_id", "targ_keyword");
	
	//DatabaseAdmin::addColumn("content_entries", "comments", "tinyint(1) not null", 0);
	//DatabaseAdmin::addColumn("content_entries", "thumbnail", "varchar(72) not null", "");
	
	/*
	Database::exec("
	CREATE TABLE IF NOT EXISTS `content_hashtags`
	(
		`content_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
		`hashtag`				varchar(22)					NOT NULL	DEFAULT '',
		`submitted`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
		
		UNIQUE (`content_id`, `hashtag`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(content_id) PARTITIONS 3;
	");
	//*/
}

// Copy the icomoon file
/*
if($fileData = File::read(SYS_PATH . "/assets/fonts/icomoon.ttf"))
{
	File::write(CONF_PATH . "/assets/fonts/icomoon.ttf", $fileData);
}

if($fileData = File::read(SYS_PATH . "/assets/fonts/icomoon.css"))
{
	File::write(CONF_PATH . "/assets/css/icomoon.css", $fileData);
}
*/

/*
DatabaseAdmin::dropTable("site_variables");

Database::exec("
CREATE TABLE IF NOT EXISTS `site_variables`
(
	`key_group`				varchar(22)					NOT NULL	DEFAULT '',
	`key_name`				varchar(32)					NOT NULL	DEFAULT '',
	
	`value`					text						NOT NULL	DEFAULT '',
	
	UNIQUE (`key_group`, `key_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
");

//*/
