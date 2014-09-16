<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class Attachment_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "standard";
	public $pluginName = "Attachment";
	public $title = "Attachment System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Associates units of content with \"attachments\" that relate to it, such as images posted with a comment.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `attachment`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`type`					tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			`title`					varchar(48)					NOT NULL	DEFAULT '',
			`description`			varchar(250)				NOT NULL	DEFAULT '',
			
			`asset_url`				varchar(96)					NOT NULL	DEFAULT '',
			`source_url`			varchar(96)					NOT NULL	DEFAULT '',
			
			`params`				text						NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`id`),
			INDEX (`asset_url`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY(id) PARTITIONS 13;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("attachment", array("id", "type", "asset_url", "params"));
	}
}