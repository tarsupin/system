<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the Attachment Plugin ------
-----------------------------------------

Several sites use attachments on posts, which are often shared. This plugin allows you to save attachments as ID's, and reuse them across multiple instances rather than saving each attachment individually.

In addition to reducing the amount of attachments required, it can also check if someone has already used a particular attachment. If the attachment is a duplicate, it can instead return the existing ID. This can help mitigate the issue of popular images being uploaded multiple times.


------------------------------------------
------ Examples of using this class ------
------------------------------------------

// Create the attachment
$attachment = new Attachment(Attachment::TYPE_IMAGE, "http://example.com/path/to/asset.png");

// Update the attachment's important settings
$attachment
	->setSource("http://example.com/fruit-bowl-artwork")
	->setTitle("Bowl of Fruit")
	->setDescription("Look at this impressive artwork! It has many bowl-like qualities!")
	->setData("myCustomKey", "myCustomValue");

// Provide settings that are image-specific (since this particular attachment is an image)
$attachment
	->setMobileImage("http://example.com/path/to/asset-mobile.png")
	->setTabletImage("http://example.com/path/to/asset-tablet.png")
	->setThumbnail("http://example.com/path/to/asset-thumbnail.png");

// Save the attachment into the database
$attachment->save();

// Connect the attachment to your post
$myPost['attachment_id'] = $attachment->id;


------------------------------
------ Attachment Types ------
------------------------------

	Attachment::TYPE_IMAGE			// Image Attachment (png, jpg, etc.)
	Attachment::TYPE_VIDEO			// Video Attachment
	Attachment::TYPE_FILE			// File Attachment (non-descript)
	Attachment::TYPE_LINK			// Simple URL / Link
	Attachment::TYPE_ARTICLE		// Article Attachment
	Attachment::TYPE_BLOG			// Blog Attachment
	Attachment::TYPE_CUSTOM			// Custom type that you can tailor the attachment for your site


-------------------------------
------ Methods Available ------
-------------------------------

// Attachment Constructor
$attachment = new Attachment([$type], [$assetURL]);

// Core Attachment Settings
$attachment
	->setType($type)			// The type of attachment, such as Attachment::TYPE_IMAGE
	->setAsset($assetURL)		// The URL of the attachment (such as the image or video)
	->setSource($sourceURL)			// The URL to visit if the attachment is clicked
	->setTitle($title)			// Title (can opt to display with the attachment)
	->setDescription($desc)		// Description (up to 250 characters)
	->setData($key, $value)		// Custom data to save with the attachment (some sites might want custom handling)

// Image-Specific Attachment Settings
	->setMobileImage($mobileURL)	// The URL of the mobile version of this image
	->setTabletImage($tableImage)	// The URL of the tablet version of this image
	->setThumbnail($thumbURL)		// The URL of the thumbnail for this image

// Load an existing attachment
$attachment = Attachment::get($attachmentID);

// Check if an attachment already exists
$getID = Attachment::checkIfExists($assetURL);

*/

class Attachment {
	
	
/****** Public Variables ******/
	public int $id = 0;				// <int>
	public int $type = 0;			// <int>
	public string $title = "";			// <str>
	public string $description = "";	// <str>
	public string $assetURL = "";		// <str>
	public string $sourceURL = "";		// <str>
	public array $data = array();		// <array>
	
	// Attachment Types
	const TYPE_IMAGE = 1;
	const TYPE_VIDEO = 2;
	const TYPE_FILE = 3;
	const TYPE_LINK = 4;
	const TYPE_ARTICLE = 5;
	const TYPE_BLOG = 6;
	const TYPE_COMMENT = 7;
	const TYPE_CUSTOM = 8;	// Can customize per site
	
	
/****** Constructor for an Attachment ******/
	public function __construct
	(
		int $type = 0		// <int> The type of attachment being created.
	,	string $assetURL = ""	// <str> The URL where the attachment resides.
	): int					// RETURNS <int> ID of the attachment on success, and 0 if failed.
	
	// $attachment = new Attachment([$type], [$assetURL]);
	{
		$this->type = $type;
		$this->assetURL = $assetURL;
	}
	
	
/****** Setters ******/
	public function setType($type) { $this->type = $type; return $this; }
	public function setTitle($title) { $this->title = $title; return $this; }
	public function setDescription($description) { $this->description = $description; return $this; }
	public function setAsset($assetURL)	{ $this->assetURL = $assetURL; return $this; }
	public function setSource($sourceURL) { $this->sourceURL = $sourceURL; return $this; }
	public function setPosterHandle($handle) { $this->data['poster_handle'] = $handle; return $this; }
	public function setData($key, $val) { $this->data[$key] = $val; return $this; }
	
	
/****** Setters for Image Attachments ******/
	public function setMobileImage($mobileURL) { $this->data["mobile-url"] = $mobileURL; return $this; }
	public function setTabletImage($tabletURL) { $this->data["tablet-url"] = $tabletURL; return $this; }
	public function setThumbnail($thumbURL) { $this->data["thumb-url"] = $thumbURL; return $this; }
	
	
/****** Setters for Video Attachments ******/
	public function setEmbed($embedData) { $this->data["embed"] = $embedData; return $this; }
	
	
/****** Save the Attachment ******/
	public function save (
	): void					// RETURNS <void> SETS $this->id to the attachment ID
	
	// $attachment->save();
	{
		// Serialize the attachment's data
		$params = Serialize::encode($this->data);
		
		// Insert the Attachment
		Database::query("INSERT INTO attachment (`type`, `params`, `asset_url`, `source_url`, `title`, `description`) VALUES (?, ?, ?, ?, ?, ?)", array($this->type, $params, $this->assetURL, $this->sourceURL, $this->title, $this->description));
		
		$this->id = Database::$lastID;
	}
	
	
	
/****** Retrieve Attachment ******/
	public static function get
	(
		int $attachmentID		// <int> The ID of the attachment to retrieve data for.
	): array <str, mixed>						// RETURNS <str:mixed> data extracted from the attachment, or array() on failure.
	
	// $attachment = Attachment::get($attachmentID);
	{
		if($attachment = Database::selectOne("SELECT id, type, title, description, asset_url, source_url, params FROM attachment WHERE id=? LIMIT 1", array($attachmentID)))
		{
			$attachment['id'] = (int) $attachment['id'];
			
			// Prepare Values
			$attachment['params'] = Serialize::decode($attachment['params']);
			
			switch($attachment['type'])
			{
				// Image Attachments need special extraction
				case Attachment::TYPE_IMAGE:
				case Attachment::TYPE_ARTICLE:
				case Attachment::TYPE_BLOG:
					return self::extractImageData($attachment);
				
				// Video Attachments need special extraction
				case Attachment::TYPE_VIDEO:
					return self::extractVideoData($attachment);
			}
			
			return $attachment;
		}
		
		return array();
	}
	
	
/****** Find and retrieve the attachment ID ******/
	public static function findAttachmentID
	(
		string $assetURL		// <str> The original asset that the attachment used.
	,	string $sourceURL		// <str> The source URL that the attachment used.
	,	string $title = ""		// <str> If set, will also look by the title.
	): int					// RETURNS <int> the ID of the attachment, or 0 on failure.
	
	// $attachmentID = Attachment::findAttachmentID($assetURL, $sourceURL, [$title]);
	{
		if($title)
		{
			return (int) Database::selectValue("SELECT id FROM attachment WHERE asset_url=? AND source_url=? AND title=? ORDER BY id DESC LIMIT 1", array($assetURL, $sourceURL, $title));
		}
		
		return (int) Database::selectValue("SELECT id FROM attachment WHERE asset_url=? AND source_url=? ORDER BY id DESC LIMIT 1", array($assetURL, $sourceURL));
	}
	
	
/****** Extract Image Data from the Attachment ******/
	private static function extractImageData
	(
		array <str, str> $attachment		// <str:str> The base attachment data.
	): array <str, str>					// RETURNS <str:str> data of the attachment on success, or empty array on failure.
	
	// $attachment = self::extractImageData($attachment);
	{
		$attachment['mobile-url'] = isset($attachment['params']['mobile-url']) ? $attachment['params']['mobile-url'] : "";
		$attachment['thumb-url'] = isset($attachment['params']['thumb-url']) ? $attachment['params']['thumb-url'] : "";
		
		return $attachment;
	}
	
	
/****** Extract Video Data from the Attachment ******/
	private static function extractVideoData
	(
		array <str, str> $attachment		// <str:str> The base attachment data.
	): array <str, str>					// RETURNS <str:str> data of the attachment on success, or empty array on failure.
	
	// $attachment = self::extractImageData($attachment);
	{
		$attachment['embed'] = isset($attachment['params']['embed']) ? $attachment['params']['embed'] : "";
		
		return $attachment;
	}
	
	
	
/****** Prepare Video Settings from a Video URL ******/
	public static function getVideoEmbedFromURL
	(
		string $videoURL		// <str> The URL of the video to embed.
	): string					// RETURNS <str> The embed code for the video, or "" on failure.
	
	// Attachment::getVideoEmbedFromURL($videoURL);
	{
		// Prepare Values
		$embed = "";
		$parseURL = URL::parse($videoURL);
		
		// Check if the video is from YouTube
		if($parseURL['baseDomain'] == "youtu.be" and isset($parseURL['urlSegments'][0]) and $parseURL['urlSegments'][0] != "")
		{
			$embed = '<div class="video-embed-flex"><iframe width="560" height="315" src="//www.youtube.com/embed/' . Sanitize::variable($parseURL['urlSegments'][0], "-") . '" frameborder="0" allowfullscreen></iframe></div>';
		}
		else if($parseURL['baseDomain'] == "youtube.com" and isset($parseURL['queryValues']['v']))
		{
			$embed = '<div class="video-embed-flex"><iframe width="560" height="315" src="//www.youtube.com/embed/' . Sanitize::variable($parseURL['queryValues']['v'], "-") . '" frameborder="0" allowfullscreen></iframe></div>';
		}
		
		// Check if the video is from Vimeo
		else if($parseURL['baseDomain'] == "vimeo.com")
		{
			if(isset($parseURL['urlSegments']) and is_array($parseURL['urlSegments']))
			{
				if($lastSegment = $parseURL['urlSegments'][count($parseURL['urlSegments']) - 1])
				{
					$embed = '<div class="video-embed-flex"><iframe src="//player.vimeo.com/video/' . Sanitize::variable($lastSegment, "-") . '" width="500" height="281" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
				}
			}
		}
		
		// Return the embed code
		return $embed;
	}
	
	
/****** Extract Video Image from a Video URL ******/
	public static function getVideoImageFromURL
	(
		string $videoURL		// <str> The URL of the video to extract images from.
	): string					// RETURNS <str> The embed code for the video, or "" on failure.
	
	// Attachment::getVideoImageFromURL($videoURL);
	{
		// Prepare Values
		$parseURL = URL::parse($videoURL);
		
		// Extract a video image from YouTube
		// http://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api/2068371#2068371
		$youtubeValue = "";
		
		if($parseURL['baseDomain'] == "youtu.be" and isset($parseURL['urlSegments'][0]) and $parseURL['urlSegments'][0] != "")
		{
			$youtubeValue = Sanitize::variable($parseURL['urlSegments'][0], "-");
		}
		else if($parseURL['baseDomain'] == "youtube.com" and isset($parseURL['queryValues']['v']))
		{
			$youtubeValue = Sanitize::variable($parseURL['queryValues']['v'], "-");
		}
		
		if($youtubeValue)
		{
			return "http://img.youtube.com/vi/" . $youtubeValue . "/hqdefault.jpg";
		}
		
		// Extract a video image from Vimeo
		if($parseURL['baseDomain'] == "vimeo.com")
		{
			if(isset($parseURL['urlSegments']) and is_array($parseURL['urlSegments']))
			{
				if($lastSegment = $parseURL['urlSegments'][count($parseURL['urlSegments']) - 1])
				{
					$hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/" . Sanitize::variable($lastSegment, "-") . ".php"));
					
					return $hash[0]['thumbnail_large'];
				}
			}
		}
		
		// Return the embed code
		return "";
	}
	
}

