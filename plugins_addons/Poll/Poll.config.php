<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Poll_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Poll";
	public $title = "Polling System";
	public $version = 1.0;
	public $author = "Brint Paris & Pegasus";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A polling system (for asking questions and getting user's votes).";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			Only retrieve this table by it's ID.
			
			"rules" is a json value that stores the following:
				
				1. "priority_weight" can be set to true or false
				
				2. "view_standings" can be set to:
					"closed" = not allowed to view until the poll is closed
					"vote" = allowed to view once you have cast a vote
					"public" = is public to everyone
				
				3. "max_choices" can be set to a numerical value
				
				4. "randomize_display" can be set to true or false
				
				5. "requirements" is an array (or something) that can be set as needed
		*/
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `poll_questions`
		(
			`id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`author_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`title`					varchar(100)				NOT NULL	DEFAULT '',
			`description`			varchar(250)				NOT NULL	DEFAULT '',
			
			`rules`					text						NOT NULL	DEFAULT '',
			
			`date_start`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`id`) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `poll_questions_by_author`
		(
			`author_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`question_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`author_id`, `question_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`author_id`) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `poll_options`
		(
			`question_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`option_id`				tinyint(2)		unsigned	NOT NULL	DEFAULT '0',
			
			`text`					varchar(120)				NOT NULL	DEFAULT '',
			
			`sort_position`			tinyint(2)		unsigned	NOT NULL	DEFAULT '0',
			
			`total_votes`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`total_score`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`question_id`, `option_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`question_id`) PARTITIONS 23;
		");
		
		// `score` is always equal to 1, unless the user is able to choose multiple options
		Database::exec("
		CREATE TABLE IF NOT EXISTS `poll_votes`
		(
			`question_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`option_id`				tinyint(2)		unsigned	NOT NULL	DEFAULT '0',
			
			`score`					tinyint(2)		unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`question_id`, `uni_id`, `option_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`question_id`) PARTITIONS 23;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("poll_questions", array("id", "title"));
		$pass2 = DatabaseAdmin::columnsExist("poll_questions_by_author", array("author_id", "question_id"));
		$pass3 = DatabaseAdmin::columnsExist("poll_options", array("question_id", "option_id"));
		$pass4 = DatabaseAdmin::columnsExist("poll_votes", array("question_id", "uni_id"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4);
	}
	
}
