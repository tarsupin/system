<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the ContentForm Plugin ------
------------------------------------------

The ContentForm Plugin allows the user to create content, such as articles, blogs, wiki pages, site pages, and so forth.

To load the plugin and provide the form interpreter and behavior, run these lines:
	
	// Prepare the Class and Values
	$contentForm = new ContentForm((int) $_GET['id']);
	$contentForm->contentType = "article";
	$contentForm->baseURL = "/write";
	
	// Prepare Essential Metadata
	Photo::prepareResponsivePage();
	Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" />');
	
	// Run the Interpreter
	$contentForm->interpret();
	
To create the form, run this line:
	
	$contentForm->draw();
	
	
------------------------------------------
------ Customizing the Content Form ------
------------------------------------------

Some sites will need to have custom tools applied in the content system. For example, the Gallery only needs images to be posted. Furthermore, the style of thumbnail on the gallery would be different than on other sites.

Customizing the form can take place in a few ways. The first way is through choosing what setting modules and segment modules you want to provide. Setting modules are modules that set the configurations of an article. Segment modules are the blocks of content (such as text and image blocks).
	
	// Choose the setting modules allowed in this content entry
	$contentForm->settings = array(
		"Hashtags"		=> true
	,	"Search"		=> true
	,	"Related"		=> true
	);
	
	// Choose the segment modules allowed in this content entry
	$contentForm->settings = array(
		"Text"			=> true
	,	"Image"			=> true
	,	"Video"			=> true
	);
	
This is a common "default" set of modules for a site, allowing a variety of functionality for the content system. There are three different segment types ("Text", "Image", and "Video") as well as several settings to affect other functions (such as having searchable functionality).

Aside from modules, there are other values that can be adjusted, such as to enable or disable comments, voting, etc.

A powerful way to update the content form system is to call an alternative plugin that extends the ContentForm class, but which overwrites or extends some of its functionality.

For example, the Gallery may use the "GalleryForm" class, which extends "ContentForm" and overwrite "updateThumbnail" so that a new style of thumbnails can be used.

The only line that changes when you run an alternative class is:
	
	$AlternateForm = new AlternateForm();
	
To create a custom behavior, interpreter, or segment form with the "Alternative Form" class, you can create one of the following methods:
	
	// Create a custom interpreter (for the "Text" module)
	$AlternateForm->customInterpretText();
	
	// Create a custom form (for the "Video" module)
	$AlternateForm->customFormVideo();
	
You can also customize the update and deletion of pages with the following methods:
	
	$AlternateForm->updateCustom();

	
----------------------------------------------
------ Examples of the ContentForm Page ------
----------------------------------------------

// Prepare the Class and Values
$contentForm = new ContentForm((int) $_GET['id']);
$contentForm->contentType = "article";
$contentForm->baseURL = "/write";
$contentForm->redirectOnError = "/";
$contentForm->urlPrefix = "";

// Run the Interpreter
$contentForm->interpret();

// Include Responsive Script
Photo::prepareResponsivePage();

Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" />');

// Run Global Script
require(CONF_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display the Page
echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display();

$contentForm->draw();

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");

*/

class ContentForm {
	
	
/****** Plugin Variables ******/
	public int $contentID = 0;			// <int> The ID of the content entry being worked on.
	
	// Allowed Setting Modules
	public array <str, bool> $settings = array(		// <str:bool> A list of setting modules to load.
		"Hashtags"		=> true
	,	"Author"		=> true
	,	"Search"		=> true
	,	"Related"		=> true
	);
	
	// Allowed Segment Modules
	public array <str, bool> $segments = array(		// <str:bool> A list of segment modules to load.
		"Text"			=> true
	,	"Image"			=> true
	,	"Video"			=> true
	);
	
	// Form Settings (set these on every form page)
	public string $contentType = "blog";	// <str> The type of content entry (blog, article, etc)
	public string $baseURL = "";			// <str> The base URL of the Content Form
	public string $redirectOnError = "";	// <str> The page to redirect to if there's an error.
	
	public string $urlPrefix = "";			// <str> Set this value to indicate the prefix to a URL slug.
	public string $urlFixed = "";			// <str> If set, this forces the URL to be a specific value.
	
	public bool $guestPosts = true;		// <bool> TRUE if there are guest posts on this site.
	public bool $openPost = false;		// <bool> TRUE if guests can post officially on this site.
	public bool $privatePosts = false;	// <bool> TRUE if there are private posts allowed on this site.
	
	public int $comments = 4;			// <int> The level of commenting allowed.
	public int $voting = 4;				// <int> The level of voting allowance.
	
	// Values that the system sets
	public int $urlLength = 42;			// <int> The maximum length allowed with the URL.
	public bool $urlUpdate = true;		// <bool> TRUE if you can update the URL, FALSE if not.
	public bool $hashtagsAllow = false;	// <bool> Sets to TRUE once you're allowed to add hashtags.
	
	
/****** Content Form Constructor ******/
	public function __construct
	(
		int $contentID		// <int> The ID of the content entry being worked on.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm = new ContentForm($contentID);
	{
		$this->contentID = $contentID;
		
		// Get the Content Data
		if(!$this->contentData = Content::get($contentID))
		{
			header("Location: /"); exit;
		}
		
		// Recognize Integers
		$this->contentData['id'] = (int) $this->contentData['id'];
		$this->contentData['uni_id'] = (int) $this->contentData['uni_id'];
		$this->contentData['status'] = (int) $this->contentData['status'];
		$this->contentData['comments'] = (int) $this->contentData['comments'];
		$this->contentData['voting'] = (int) $this->contentData['voting'];
		$this->contentData['date_posted'] = (int) $this->contentData['date_posted'];
		
		// Prepare Values
		$this->urlLength = 42 - (strlen($this->urlPrefix));
		$this->contentData['url_slug'] = str_replace($this->urlPrefix, "", $this->contentData['url_slug']);
		
		// Prevent any updates to the URL slug if one has been set, and status is higher than DRAFT
		if($this->contentData['url_slug'] and $this->contentData['status'] > Content::STATUS_DRAFT)
		{
			$this->urlUpdate = false;
		}
		
		// Include Responsive Script
		Photo::prepareResponsivePage();
		
		Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-system.css" />');
	}
	
	
/****** Verify access to modifying this content entry ******/
	public function redirect (
	): void					// RETURNS <void> redirects to the appropriate page.
	
	// $contentForm->redirect($redirectTo);
	{
		if($this->redirectOnError)
		{
			header("Location: " . $this->redirectOnError); exit;
		}
		
		header("Location: /"); exit;
	}
	
	
/****** Verify access to modifying this content entry ******/
	public function verifyAccess (
	): void					// RETURNS <void> redirects if user is not allowed editing access.
	
	// $contentForm->verifyAccess();
	{
		if(Me::$clearance >= 6) { return; }
		
		// Check if guest users are allowed to post on this site
		if(!$this->guestPosts) { $this->redirect(); }
		
		// Make sure you own this content
		if($this->contentData['uni_id'] != Me::$id)
		{
			Alert::error("Invalid User", "You do not have permissions to edit this content.", 7);
			
			$this->redirect();
		}
		
		// If this entry is set to official, guest submitters cannot change it any longer
		if($this->contentData['status'] >= Content::STATUS_OFFICIAL)
		{
			Alert::saveInfo("Editing Disabled", "This entry is now an official live post, and cannot be edited.");
			
			$this->redirect();
		}
	}
	
	
/****** Interpreter ******/
	public function interpret (
	): void					// RETURNS <void>
	
	// $contentForm->interpret();
	{
		if(Form::submitted(SITE_HANDLE . "-content-" . $this->contentID))
		{
			// If the content entry is being deleted
			if(isset($_POST['deletePost']))
			{
				if($this->delete())
				{
					Alert::saveSuccess("Delete Successful", "The content entry has been successfully deleted.");
					
					$this->redirect();
				}
			}
			
			// Run the Core Interpreters
			$this->updateSettings();
			$this->updateHashtags();
			$this->updateURL();
			$this->updateSegments();
			$this->updateCustom();
			
			// Interpret Setting Modules
			foreach($this->settings as $module => $bool)
			{
				if(method_exists("Module" . $module, "interpret"))
				{
					call_user_func(array("Module" . $module, "interpret"), $this);
				}
			}
			
			// If the thumbnail was updated
			if(isset($_FILES['thumb_image']) and $_FILES['thumb_image']['tmp_name'])
			{
				$this->updateThumbnail();
			}
			
			// Update Blocks
			$this->updateBlocks();
			
			// If a thumbnail is not selected, try to generate one
			if(!$this->contentData['thumbnail'])
			{
				$this->updateThumbnailAuto();
			}
		}
		else
		{
			// Interpret Setting Modules
			foreach($this->settings as $module => $bool)
			{
				if(method_exists("Module" . $module, "behavior"))
				{
					call_user_func(array("Module" . $module, "behavior"), $this);
				}
			}
		}
	}
	
	
/****** Update a Content Entry ******/
	public function updateSettings (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateSettings();
	{
		$settingUpdate = false;
		
		// Title of the Post
		if(isset($_POST['title']) and $_POST['title'] != $this->contentData['title'])
		{
			$this->contentData['title'] = Sanitize::safeword($_POST['title'], "'\"?");
			$settingUpdate = true;
		}
		
		// Post Status
		if(isset($_POST['save_official']) and $this->contentData['status'] < Content::STATUS_OFFICIAL and Me::$clearance >= 6)
		{
			$this->contentData['status'] = Content::STATUS_OFFICIAL;
			$settingUpdate = true;
		}
		
		else if(isset($_POST['save_publish']) and $this->contentData['status'] < Content::STATUS_GUEST)
		{
			$this->contentData['status'] = Content::STATUS_GUEST;
			$settingUpdate = true;
			
			// Add the entry to the queue
			Content::queue($this->contentID);
		}
		
		else if(isset($_POST['save_guest']) and $this->contentData['status'] > Content::STATUS_GUEST and Me::$clearance >= 6)
		{
			$this->contentData['status'] = Content::STATUS_GUEST;
			$settingUpdate = true;
		}
		
		// Date Posted
		if($this->contentData['status'] >= Content::STATUS_GUEST and $this->contentData['date_posted'] == 0)
		{
			$this->contentData['date_posted'] = time();
			$settingUpdate = true;
		}
		
		// Primary Hashtag
		if(isset($_POST['primary_hashtag']) and $_POST['primary_hashtag'] != $this->contentData['primary_hashtag'])
		{
			$this->contentData['primary_hashtag'] = $_POST['primary_hashtag'];
			$settingUpdate = true;
		}
		
		if(Me::$clearance >= 6)
		{
			// Comments
			if(isset($_POST['comments']) and $_POST['comments'] != $this->contentData['comments'])
			{
				$this->contentData['comments'] = (int) $_POST['comments'];
				$settingUpdate = true;
			}
			
			// Voting
			if(isset($_POST['voting']) and $_POST['voting'] != $this->contentData['voting'])
			{
				$this->contentData['voting'] = (int) $_POST['voting'];
				$settingUpdate = true;
			}
		}
		
		if(!$settingUpdate)
		{
			return true;
		}
		
		// Process the Update
		return Database::query("UPDATE IGNORE content_entries SET title=?, primary_hashtag=?, status=?, comments=?, voting=?, date_posted=? WHERE id=? LIMIT 1", array($this->contentData['title'], $this->contentData['primary_hashtag'], $this->contentData['status'], $this->contentData['comments'], $this->contentData['voting'], $this->contentData['date_posted'], $this->contentID));
	}
	
	
/****** Update the Hashtags ******/
	public function updateHashtags (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateHashtags();
	{
		// Make sure we can update hashtags
		if(!$this->contentData['url_slug'])
		{
			return false;
		}
		
		if($this->contentData['status'] <= Content::STATUS_GUEST)
		{
			return false;
		}
		
		// Allow Hashtags to be updated
		$this->hashtagsAllow = true;
	}
	
	
/****** Update a Content Entry ******/
	public function updateURL (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateURL();
	{
		// Make sure you're allowed to update the URL
		if(!$this->urlUpdate)
		{
			return false;
		}
		
		// Make sure a URL Slug was set
		if(!isset($_POST['url_slug']) or $_POST['url_slug'] == $this->contentData['url_slug'])
		{
			return true;
		}
		
		// Validate the URL Slug
		FormValidate::url("URL", $_POST['url_slug'], 10, 42, "-");
		
		if(strpos($_POST['url_slug'], "-") === false)
		{
			Alert::error("URL", "The URL must contain at least one \"-\" dash.");
		}
		
		// Update the URL
		if(FormValidate::pass("URL"))
		{
			Database::startTransaction();
			
			if($pass = Database::query("DELETE FROM content_by_url WHERE url_slug=? LIMIT 1", array($this->contentData['url_slug'])))
			{
				if($pass = Database::query("UPDATE IGNORE content_entries SET url_slug=? WHERE id=? LIMIT 1", array($_POST['url_slug'], $this->contentID)))
				{
					$pass = Database::query("INSERT INTO content_by_url (url_slug, content_id) VALUES (?, ?)", array($_POST['url_slug'], $this->contentID));
				}
			}
			
			// Update Values
			$this->contentData['url_slug'] = $_POST['url_slug'];
			
			// Finalize the update
			if(Database::endTransaction($pass))
			{
				$this->urlUpdate = true;
				
				return true;
			}
		}
		
		return false;
	}
	
	
/****** Update Segments ******/
	public function updateSegments (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateSegments();
	{
		// Create New Module Segments
		if(isset($_POST['add_module']) and is_array($_POST['add_module']))
		{
			foreach($_POST['add_module'] as $module => $bool)
			{
				call_user_func(array("Module" . $module, "create"), $this->contentID);
			}
		}
		
		// If any of the blocks were moved
		if(isset($_POST['moveUp']) and is_array($_POST['moveUp']))
		{
			reset($_POST['moveUp']);
			
			if($sortOrder = (int) key($_POST['moveUp']))
			{
				$this->moveUp($sortOrder);
			}
		}
		
		// If any of the blocks were deleted
		else if(isset($_POST['deleteBlock']) and is_array($_POST['deleteBlock']))
		{
			reset($_POST['deleteBlock']);
			
			if($sortOrder = (int) key($_POST['deleteBlock']))
			{
				if($blockData = Database::selectOne("SELECT type, block_id FROM content_block_segment WHERE content_id=? AND sort_order=? LIMIT 1", array($this->contentID, $sortOrder)))
				{
					call_user_func(array("Module" . $blockData['type'], "purgeBlock"), (int) $blockData['block_id'], $this->contentID);
				}
			}
		}
	}
	
	
/****** Update Blocks ******/
	public function updateBlocks (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateBlocks();
	{
		// Prepare Values
		$runCache = false;
		
		// Cycle through all of the modules for interpretation
		$segments = Database::selectMultiple("SELECT * FROM content_block_segment WHERE content_id=? ORDER BY sort_order", array($this->contentID));
		
		foreach($segments as $segment)
		{
			// Check if there is a custom interpreter to load
			if(method_exists($this, "customInterpret" . $segment['type']))
			{
				$funcName = "customInterpret" . $segment['type'];
				
				$this->$funcName();
			}
			
			// Run the module interpreter
			else if(method_exists("Module" . $segment['type'], "interpret"))
			{
				// Prepare Variables
				$runInterpreter = false;
				$blockID = (int) $segment['block_id'];
				
				// For images, we need to see if there was an image set
				if($segment['type'] == "Image" and isset($_FILES['image']) and $_FILES['image']['tmp_name'][$blockID] != "")
				{
					$runInterpreter = true;
				}
				
				// Check each of the block values against the related $_POST values
				$moduleData = Database::selectOne("SELECT * FROM content_block_" . strtolower($segment['type']) . " WHERE id=? LIMIT 1", array($blockID));
				
				foreach($moduleData as $blockKey => $blockVal)
				{
					if(!isset($_POST[$blockKey][$blockID]) or $blockVal == $_POST[$blockKey][$blockID])
					{
						continue;
					}
					
					$runInterpreter = true;
				}
				
				if($runInterpreter)
				{
					$runCache = true;
					
					call_user_func(array("Module" . $segment['type'], "interpret"), $this->contentID, $blockID);
				}
			}
		}
		
		// Update the cache, if applicable
		if($runCache)
		{
			$this->updateCache();
		}
	}
	
	
/****** Custom Update Handler ******/
# This is a dummy function, but can be updated by an extended class.
	public function updateCustom (
	): bool						// RETURNS <bool> TRUE if the update is successful, FALSE if there is a failure.
	
	// $contentForm->updateCustom();
	{
		return true;
	}
	
	
/****** Output the Content Form ******/
	public function draw (
	): void					// RETURNS <void>
	
	// $contentForm->draw();
	{
		// Draw the appropriate form block
		echo '
		<form class="uniform" action="' . $this->baseURL . '?id=' . $this->contentID . '" enctype="multipart/form-data" method="post">' . Form::prepare(SITE_HANDLE . "-content-" . $this->contentID);
		
		$this->drawSettings();
		$this->drawBlocks();
		$this->drawFooter();
		
		echo '
		</form>';
	}
	
	
/****** Output the Content Form Settings ******/
	public function drawSettings (
	): void					// RETURNS <void>
	
	// $contentForm->drawSettings();
	{
		echo '
		<input type="text" name="title" style="width:97%; height:38px; font-size:32px; font-weight:bold; margin-bottom:10px;" placeholder="Article Title . . ." value="' . $this->contentData['title'] . '" maxlength="72" />
		
		<div style="border:solid 1px #bbbbbb; font-size:18px; height:38px; margin-bottom:10px; width:97%; padding-left:14px;">
			' . SITE_URL . '/' . ($this->urlPrefix ? trim($this->urlPrefix, "/") . '/' : "") . '
			<input type="text" name="url_slug" style="border:none; color:#5abcde; height:26px; font-size:18px; font-weight:bold; padding-left:0px; min-width:280px;" value="' . str_replace($this->urlPrefix, "", $this->contentData['url_slug']) . '" maxlength="72" placeholder="enter-desired-url-here..." maxlength="' . $this->urlLength . '"' . ($this->urlUpdate ? "" : " disabled") . ' />
		</div>';
		
		// Display the Metadata Block
		echo '
		<div id="setting-box" style="margin-top:22px;"><fieldset style="border:solid 1px #bbbbbb; overflow:hidden;"><legend style="font-size:1.2em;">Article Settings</legend>';
		
		// Show the Thumbnail, if applicable
		if($this->contentData['thumbnail'])
		{
			echo '
			<span style="font-weight:bold;">Current Thumbnail:</span>
			<div style="position:relative; height:200px;">
				<img src="' . $this->contentData['thumbnail'] . '?cache=' . time() . '" style="position:absolute; left:0px; top:0px;" />
				<img src="' . CDN . '/images/upload-thumb.png" style="position:absolute; left:0px; top:0px;" />
				<div style="position:absolute; left:0px; top:0px; height:60px; width:100px;">
					<input type="file" name="thumb_image" value="" style="height:60px; width:100px; overflow:hidden; opacity:0; cursor:pointer;" />
				</div>
			</div>';
		}
		
		// Display the hashtag dropdown
		if(Me::$clearance >= 6 or $this->openPost)
		{
			if($dropHTML = ContentHashtags::hashtagFormDropdown($this->contentData['id'], $this->contentData['primary_hashtag']))
			{
				echo '
				<div>
				<select name="primary_hashtag">
					<option value="">-- Select Primary Hashtag --</option>
					' . $dropHTML . '
				</select>
				</div>';
			}
		}
		
		// Show several options that only editors are allowed to use
		if(Me::$clearance >= 6)
		{
			echo '<p>';
			
			// Show the comment dropdown, if allowed
			if($this->comments > 0)
			{
				echo '
				<select name="comments">' . str_replace('value="' . $this->contentData['comments'] . '"', 'value="' . $this->contentData['comments'] . '" selected', '
					<option value="' . Content::COMMENTS_STANDARD . '">Standard Comments</option>
					<option value="' . Content::COMMENTS_MODERATE . '">Comments Need Approval</option>
					<option value="' . Content::COMMENTS_NO_POSTING . '">Freeze Comments</option>
					<option value="' . Content::COMMENTS_DISABLED . '">Don\'t Show Comments</option>
				</select>');
			}
			
			// Show the voting dropdown, if allowed
			if($this->voting > 0)
			{
				echo '
				<select name="voting">' . str_replace('value="' . $this->contentData['voting'] . '"', 'value="' . $this->contentData['voting'] . '" selected', '
					<option value="' . Content::VOTING_STANDARD . '">Standard Voting</option>
					<option value="' . Content::VOTING_FREEZE . '">Freeze Voting</option>
					<option value="' . Content::VOTING_DISABLED . '">No Voting</option>
				</select>');
			}
			
			echo '
			</p>';
		}
		
		// Show the setting modules
		foreach($this->settings as $module => $bool)
		{
			if(method_exists("Module" . $module, "draw"))
			{
				call_user_func(array("Module" . $module, "draw"), $this);
			}
		}
		
		echo '
		</div>
		
		<script>
			var setbox = document.getElementById("setting-box");
			
			setbox.insertAdjacentHTML("beforebegin", "<div><a href=\'javascript:void(0)\' onclick=\'showSettingBox();\' style=\'border:solid 1px #bbbbbb; border-radius:5px; padding:8px; margin-top:2px; margin-bottom:8px; display:inline-block;\'><span class=\'icon-settings\'></span> Article Settings</a></div>");
			
			function showSettingBox()
			{
				if(setbox.style.display == "none")
				{
					setbox.style.display = "inline";
				}
				else
				{
					setbox.style.display = "none";
				}
			}
			
			showSettingBox();
		</script>';
	}
	
	
/****** Output the Content Form Block Content ******/
	public function drawBlocks (
	): void					// RETURNS <void>
	
	// $contentForm->drawBlocks();
	{
		// Get the list of content segments
		$segments = Database::selectMultiple("SELECT * FROM content_block_segment WHERE content_id=? ORDER BY sort_order", array($this->contentID));
		
		foreach($segments as $segment)
		{
			// Recognize Integers
			$blockID = (int) $segment['block_id'];
			$sortOrder = (int) $segment['sort_order'];
			
			// Draw the appropriate form block
			echo '<div id="block-' . $sortOrder . '" class="content-form"><fieldset style="border:solid 1px #bbbbbb; overflow:hidden;"><legend>' . $segment['type'] . ' Block</legend>';
			
			echo '
			<div style="float:right;">
				<input type="submit" name="moveUp[' . $sortOrder . ']" value="Move Up" />
				<input type="submit" name="deleteBlock[' . $sortOrder . ']" value="Delete" />
			</div>';
			
			// Check if there is a custom form
			if(method_exists($this, "customForm" . $segment['type']))
			{
				$funcName = "customForm" . $segment['type'];
				
				$this->$funcName();
			}
			
			// Draw the Module's Form
			else if(method_exists("Module" . $segment['type'], "draw"))
			{
				call_user_func(array("Module" . $segment['type'], "draw"), $blockID);
			}
			
			echo '</fieldset></div>';
		}
	}
	
	
/****** Output the Content Form Footer ******/
	public function drawFooter (
	): void					// RETURNS <void>
	
	// $contentForm->drawFooter();
	{
		// Show Segment-Creation Modules
		foreach($this->segments as $module => $bool)
		{
			switch($module)
			{
				case "Text":
					$icon = "newspaper";
					break;
				
				case "Image":
					$icon = "image";
					break;
				
				case "Video":
					$icon = "video";
					break;
				
				default:
					$icon = "circle-exclaim";
					break;
			}
			
			echo '
			<div class="newsegment-wrap">
				<div class="newsegment"><span class="newsegment-text">Add<br /><span class="icon-' . $icon . '" style="font-size:32px;"></span><br />' . $module . ' Block</span></div>
				<input class="newsegment-sub" type="submit" name="add_module[' . $module . ']" value="" style="background:none;" />
			</div>';
		}
		
		echo '
		<hr class="separate-div"/>';
		
		// Display Submission Options
		echo '
		<p>';
		
		// Make official post
		if($this->contentData['status'] < Content::STATUS_OFFICIAL and Me::$clearance >= 6)
		{
			echo '
			<input type="submit" name="save_official" value="Save and Make Official Post" style="background-color:#56ccc8;" />';
		}
		
		// Save and Publish option
		if($this->contentData['status'] < Content::STATUS_GUEST)
		{
			echo '
			<input type="submit" name="save_publish" value="Save and Publish" style="background-color:#56ccc8;" />';
		}
		
		// Official Post Option
		if($this->contentData['status'] >= Content::STATUS_OFFICIAL and Me::$clearance >= 6)
		{
			echo '
			<input type="submit" name="save_standard" value="Live Update" style="background-color:#56ccc8;" />
			<input type="submit" name="save_guest" value="Set as Guest Post" style="background-color:#aa2222;" />';
		}
		else
		{
			// Save / Update Option
			echo '
			<input type="submit" name="save_standard" value="Save and Update" />';
		}
		
		// Display the Submit Button
		echo '
			<input type="submit" name="deletePost" value="Delete Post" onclick="return confirm(\'Are you sure you want to delete this post?\');" />
		</p>';
	}
	
	
/****** Move a block up in the sort order of a segment list ******/
	public function moveUp
	(
		int $sortOrder		// <int> The sort order of the block associated with this segment piece.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentForm->moveUp($sortOrder);
	{
		// Make sure there is upward mobility (a block higher than itself)
		if($sortOrder <= 1) { return false; }
		
		$swapOrder = $sortOrder - 1;
		
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array(0, $this->contentID, $sortOrder)))
		{
			if($pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array($sortOrder, $this->contentID, $swapOrder)))
			{
				$pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array($swapOrder, $this->contentID, 0));
			}
		}
		
		if(Database::endTransaction($pass))
		{
			$this->updateCache();
			
			header("Location: " . $this->baseURL  . "?id=" . $this->contentID); exit;
		}
		
		return false;
	}
	
	
/****** Update a Content Entry's content, but not any of its settings ******/
	public function updateCache (
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentForm->updateCache();
	{
		// Prepare the text for being cached
		$body = "";
		
		// Get the list of content segments
		$results = Database::selectMultiple("SELECT block_id, type FROM content_block_segment WHERE content_id=? ORDER BY sort_order ASC", array($this->contentID));
		
		// Pull the text from the appropriate block type
		foreach($results as $result)
		{
			if(method_exists("Module" . $result['type'], "get"))
			{
				$body .= call_user_func(array("Module" . $result['type'], "get"), (int) $result['block_id'], $this);
			}
		}
		
		// Cache the text
		return Database::query("REPLACE INTO content_cache (content_id, body) VALUES (?, ?)", array($this->contentID, $body));
	}
	
	
	
	
	
	
/****** Update the Thumbnail ******/
	public function updateThumbnail (
	): void						// RETURNS <void>
	
	// $this->updateThumbnail();
	{
		// Initialize the plugin
		$imageUpload = new ImageUpload($_FILES['thumb_image']);
		
		// Set your image requirements
		$imageUpload->maxWidth = 4200;
		$imageUpload->maxHeight = 3500;
		$imageUpload->minWidth = 320;
		$imageUpload->minHeight = 180;
		$imageUpload->maxFilesize = 1024 * 3000;	// 3 megabytes
		$imageUpload->saveMode = Upload::MODE_OVERWRITE;
		
		// Set the image directory
		$srcData = Upload::fileBucketData($this->contentID, 10000);
		$bucketDir = '/assets/content/' . $srcData['main_directory'] . '/' . $srcData['second_directory'];
		$imageDir = CONF_PATH . $bucketDir;
		
		// Save the image to a chosen path
		if($imageUpload->validate())
		{
			$image = new Image($imageUpload->tempPath, $imageUpload->width, $imageUpload->height, $imageUpload->extension);
			
			if(FormValidate::pass())
			{
				// Resize the image to the appropriate size (320 x 180)
				$image->autoCrop(320, 180);
				
				// Prepare the filename for this image
				$imageUpload->filename = $this->contentID . '-thumb';
				
				// Save the image
				$image->save($imageDir . '/' . $imageUpload->filename . '.jpg');
				
				$imageURL = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '.jpg';
				
				// Save the thumbnail
				Database::query("UPDATE content_entries SET thumbnail=? WHERE id=? LIMIT 1", array($imageURL, $this->contentID));
				
				$this->contentData['thumbnail'] = $imageURL;
			}
		}
	}
	
	
/****** Update the Thumbnail Automatically ******/
	public function updateThumbnailAuto (
	): void						// RETURNS <void>
	
	// $this->updateThumbnailAuto();
	{
		$coreData = Content::scanForCoreData($this->contentID, 5);
		
		// Make sure content entries has an appropriate image to set as a thumbnail
		if($coreData['mobile_url'] or $coreData['image_url'])
		{
			// Update the thumbnail now with the main header
			$chooseImage = ($coreData['mobile_url'] ? $coreData['mobile_url'] : $coreData['image_url']);
			
			// Set the image directory
			$srcData = Upload::fileBucketData($this->contentID, 10000);
			$bucketDir = '/assets/content/' . $srcData['main_directory'] . '/' . $srcData['second_directory'];
			$imageDir = CONF_PATH . $bucketDir;
			
			Download::get($chooseImage, $bucketDir . '/' . $this->contentID . '-thumb.jpg');
			
			// Prepare the image that will become the thumbnail
			$image = new Image($imageDir . '/' . $this->contentID . '-thumb.jpg');
			
			// Resize the image to the appropriate size (320 x 180)
			$image->autoCrop(320, 180);
			
			// If the image needs a video cover, provide it here
			if($coreData['video_thumb'] and File::exists(APP_PATH . "/assets/icons/video_icon.png"))
			{
				$image->paste(APP_PATH . "/assets/icons/video_icon.png", 5, 5, 0, 0, 0, 0);
			}
			
			// Save the thumbnail image
			$image->save($imageDir . '/' . $this->contentID . '-thumb.jpg');
			
			// Update the value immediately
			$coreData['thumbnail'] = SITE_URL . $bucketDir . '/' . $this->contentID . '-thumb.jpg';
			
			// Save the thumbnail
			Database::query("UPDATE content_entries SET thumbnail=? WHERE id=? LIMIT 1", array($coreData['thumbnail'], $this->contentID));
		}
		
		$this->contentData['thumbnail'] = $coreData['thumbnail'];
	}
	
	
/****** Create a Content Entry ******/
	public static function createEntry
	(
		int $uniID				// <int> The UniID that is creating the entry.
	,	string $title				// <str> The title of the entry.
	,	int $status = 0			// <int> The current status to assign to the entry (e.g. Content::STATUS_DRAFT)
	,	int $clearanceView = 0	// <int> The clearance required to view this entry.
	,	string $primeHashtag = ""	// <str> Assigns a primary hashtag, if applicable ("" to ignore).
	,	int $comments = 2		// <int> The type of comments for this entry (0 is disallow).
	,	int $voting = 2			// <int> The type of voting to allow for this entry (0 is disallow).
	,	string $urlSlug = ""		// <str> The URL that you want to enforce in this entry.
	): int						// RETURNS <int> The ID of the entry on success, or 0 on failure.
	
	// $contentID = ContentForm::createEntry($uniID, $title, $status, [$clearanceView], [$primeHashtag], [$comments], [$voting], [$urlSlug]);
	{
		// Prepare Values
		$urlSlug = strtolower(Sanitize::variable($urlSlug, "-/"));
		
		Database::startTransaction();
		
		// Create the Entry
		if($pass = Database::query("INSERT INTO content_entries (uni_id, url_slug, title, primary_hashtag, status, clearance_view, comments, voting) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($uniID, $urlSlug, $title, $primeHashtag, $status, $clearanceView, $comments, $voting)))
		{
			// Make sure that the entry ID was retrieved successfully.
			if($contentID = Database::$lastID)
			{
				$pass = Database::query("INSERT INTO content_by_user (uni_id, content_id) VALUES (?, ?)", array($uniID, $contentID));
			}
			else
			{
				$pass = false;
			}
			
			// Set the URL Slug if necessary
			if($pass and $urlSlug)
			{
				$pass = Database::query("INSERT INTO content_by_url (url_slug, content_id) VALUES (?, ?)", array($urlSlug, $contentID));
			}
		}
		
		Database::endTransaction($pass);
		
		return ($pass ? $contentID : 0);
	}
	
	
/****** Delete the content entry and all of the other pieces related to it ******/
	public function delete (
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ContentForm::delete($contentID);
	{
		// Prepare Values
		$contentID = $this->contentID;
		
		// Begin the deletion process
		Database::startTransaction();
		
		// Delete the main entry
		if(!Database::query("DELETE FROM content_entries WHERE id=? LIMIT 1", array($contentID)))
		{
			Alert::error("Delete Error", "There was an error deleting the main content entry.");
			return Database::endTransaction(false);
		}
		
		// Delete the URL Data
		if(!Database::query("DELETE FROM content_by_url WHERE url_slug=? LIMIT 1", array($this->contentData['url_slug'])))
		{
			Alert::error("Delete Error", "Deletion halted: the URL section could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Delete the User Data
		if(!Database::query("DELETE FROM content_by_user WHERE uni_id=? AND content_id=? LIMIT 1", array($this->contentData['uni_id'], $contentID)))
		{
			Alert::error("Delete Error", "Deletion halted: the user data could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Delete the Content Queue
		if(!Database::query("DELETE FROM content_queue WHERE content_id=? LIMIT 1", array($contentID)))
		{
			Alert::error("Delete Error", "Deletion halted: the content queue could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Loop through each module's purge functionality
		foreach($this->settings as $module => $bool)
		{
			if(method_exists("Module" . $module, "purge"))
			{
				if(!$success = call_user_func(array("Module" . $module, "purge"), $contentID))
				{
					Alert::error("Delete Error", "Deletion halted: the " . $module . " module could not be deleted properly.");
					return Database::endTransaction(false);
				}
			}
		}
		
		// Delete the Content Tracking
		if(!Database::query("DELETE FROM content_tracking WHERE content_id=? LIMIT 1", array($contentID)))
		{
			Alert::error("Delete Error", "Deletion halted: the content tracking could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Check which additional pieces need to be deleted
		$hashtag = DatabaseAdmin::tableExists("content_by_hashtag");
		$comments = DatabaseAdmin::tableExists("content_comments");
		
		// Delete the comments data
		if($comments)
		{
			// Retrieve the list of comments that need to be deleted
			if(!Database::query("DELETE c.* FROM content_comments c INNER JOIN content_comments_by_entry x ON c.id=x.comment_id WHERE x.content_id=?", array($contentID)))
			{
				Alert::error("Delete Error", "Deletion halted: the comments could not be deleted properly.");
				return Database::endTransaction(false);
			}
			
			if(!Database::query("DELETE FROM content_comments_by_entry WHERE content_id=? LIMIT 1", array($contentID)))
			{
				Alert::error("Delete Error", "Deletion halted: the comment tracker data could not be deleted properly.");
				return Database::endTransaction(false);
			}
		}
		
		// Get the list of content segments
		$results = Database::selectMultiple("SELECT type, block_id FROM content_block_segment WHERE content_id=? ORDER BY sort_order ASC", array($contentID));
		
		$pass = true;
		
		foreach($results as $result)
		{
			if(method_exists("Module" . $result['type'], "purgeBlock"))
			{
				$pass = call_user_func(array("Module" . $result['type'], "purgeBlock"), (int) $result['block_id'], $contentID);
			}
			
			if(!$pass) { break; }
		}
		
		if(!$pass)
		{
			Alert::error("Delete Error", "Deletion halted: the content blocks could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Delete the Content Segments
		if(!Database::query("DELETE FROM content_block_segment WHERE content_id=?", array($contentID)))
		{
			Alert::error("Delete Error", "Deletion halted: the content segments could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Delete the User Content Tracking
		// This is run last because it's a non-indexed value and may consume more time
		if(!Database::query("DELETE FROM content_tracking_users WHERE content_id=?", array($contentID)))
		{
			Alert::error("Delete Error", "Deletion halted: the user votes and share tracks could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Run any additional custom deletions
		if(method_exists($this, "customDelete") and !$this->customDelete())
		{
			Alert::error("Delete Error", "Deletion halted: the custom deletion process failed.");
			return Database::endTransaction(false);
		}
		
		// Finalize the deletion process
		Database::endTransaction();
		
		// Delete the thumbnail for the content entry, if applicable
		if(isset($this->contentData['thumbnail']))
		{
			$urlData = URL::parse($this->contentData['thumbnail']);
			
			if(isset($urlData['path']) and File::exists($urlData['path']))
			{
				File::delete(CONF_PATH . '/' . trim($urlData['path'], "/"));
			}
		}
		
		return true;
	}
	
	
/****** Create a Content Block Segment ******/
	public static function createSegment
	(
		int $contentID		// <int> The ID of the content entry to assign this text block to.
	,	string $type			// <str> The type of segment being created (e.g. TYPE_TEXT, TYPE_IMAGE, etc.)
	,	int $blockID		// <int> The ID of the block that needs to be created.
	): int					// RETURNS <int> the ID of the block, or 0 on failure.
	
	// $segmentID = $contentForm->createSegment($contentID, $type, $blockID);
	{
		// Get the last sort order
		if(!$sortOrder = (int) Database::selectValue("SELECT sort_order FROM content_block_segment WHERE content_id=? ORDER BY sort_order DESC LIMIT 1", array($contentID)))
		{
			$sortOrder = 1;
		}
		else
		{
			$sortOrder += 1;
		}
		
		// Content Block Segment
		if(!Database::query("INSERT INTO content_block_segment (content_id, sort_order, type, block_id) VALUES (?, ?, ?, ?)", array($contentID, $sortOrder, $type, $blockID)))
		{
			return 0;
		}
		
		return $blockID;
	}
	
	
/****** Delete a Content Segment ******/
	public static function deleteSegment
	(
		int $contentID		// <int> The ID of the content entry assigned to this segment piece.
	,	string $type = ""		// <str> The type of the segment.
	,	int $blockID = 0	// <int> The ID of the block associated with this segment piece.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ContentForm::deleteSegment($contentID, $type, $blockID);
	{
		if(!$sortOrder = (int) Database::selectValue("SELECT sort_order FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, $type, $blockID)))
		{
			return false;
		}
		
		Database::startTransaction();
		
		if($pass = Database::query("DELETE FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, $type, $blockID)))
		{
			$pass = Database::query("UPDATE IGNORE content_block_segment SET sort_order = sort_order - 1 WHERE content_id=? AND sort_order >= ?", array($contentID, $sortOrder));
		}
		
		return Database::endTransaction($pass);
	}
	
}