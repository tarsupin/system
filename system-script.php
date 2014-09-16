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
if($fileData = File::read(SYS_PATH . "/assets/fonts/icomoon.ttf"))
{
	File::write(CONF_PATH . "/assets/fonts/icomoon.ttf", $fileData);
}

if($fileData = File::read(SYS_PATH . "/assets/fonts/icomoon.css"))
{
	File::write(CONF_PATH . "/assets/css/icomoon.css", $fileData);
}

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
