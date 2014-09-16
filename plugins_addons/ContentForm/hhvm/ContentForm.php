<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the ContentForm Plugin ------
------------------------------------------

The ContentForm Plugin allows for the creation and modification of content, such as with articles, blogs, wiki pages, site pages, and so forth. This plugin will allow the entire form to be generated and interpreted without the need to rebuild elaborate controllers for each system.

To load the plugin and provide the form interpreter and behavior, run these lines:
	
	$contentForm = new ContentForm("/base-url", $contentID);
	$contentForm->runBehavior();
	$contentForm->runInterpreter();
	
To create the form, run this line:
	
	$contentForm->drawContent();
	
Note that the page that loads these things will require responsive photos and unique styling provided like this:
	
	// Include Important Scripts
	Photo::prepareResponsivePage();
	Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-block.css" />');
	
	
------------------------------------------
------ Customizing the Content Form ------
------------------------------------------

Some sites will need to have custom tools applied in the content system. For example, the Gallery only needs images to be posted, and even those don't need titles. Furthermore, the style of thumbnail on the gallery would be different than on other sites.

Customizing the form can take place in a few ways. The first way is through choosing what methods you want to provide, and under what circumstances to provide them. To set the modules you want in this form, you can set the $contentForm->modules value. For example:
	
	// Set the modules allowed in this content entry
	$contentForm->modules = array(
		"Text"			=> ContentForm::MODULE_TYPE_SEGMENT
	,	"Image"			=> ContentForm::MODULE_TYPE_SEGMENT
	,	"Video"			=> ContentForm::MODULE_TYPE_SEGMENT
	,	"Author"		=> ContentForm::MODULE_TYPE_META
	,	"Hashtags"		=> ContentForm::MODULE_TYPE_META
	,	"Search"		=> ContentForm::MODULE_TYPE_META
	,	"Related"		=> ContentForm::MODULE_TYPE_META
	);
	
This is a common "default" set of modules for a site, allowing a variety of functionality for the content system. There are three different segment types ("Text", "Image", and "Video") as well as several meta-functions to affect other functions (such as having searchable functionality).

Aside from modules, there are other values that can be adjusted, such as to enable or disable comments, voting, etc.

A major way to update the form system is to call an alternative form type that extends the ContentForm class, but which overwrites or extends some of the functionality.

For example, the Gallery may use the "GalleryForm" class, which extends "ContentForm" and overwrite "updateThumbnail" so that a new style of thumbnails can be used.

The only line that changes when you run an alternative class is:
	
	$AlternateForm = new AlternateForm("/base-url", $contentID);
	
To create a custom behavior, interpreter, or segment form with the "Alternative Form" class, you can create one of the following methods:
	
	// Create a custom behavior (for the "Image" module)
	$AlternateForm->customBehaviorImage();
	
	// Create a custom interpreter (for the "Text" module)
	$AlternateForm->customInterpretText();
	
	// Create a custom form (for the "Video" module)
	$AlternateForm->customFormVideo();
	
You can also customize the update and deletion of pages with the following methods:
	
	$AlternateForm->customUpdate();
	$AlternateForm->customDelete();

	
----------------------------------------------
------ Examples of the ContentForm Page ------
----------------------------------------------

// Prepare Form
$contentForm = new ContentForm("/base-url", $contentID);

// Set the modules allowed in this content entry
$contentForm->modules = array(
	"Text"			=> self::MODULE_TYPE_SEGMENT
,	"Image"			=> self::MODULE_TYPE_SEGMENT
,	"Video"			=> self::MODULE_TYPE_SEGMENT
,	"Author"		=> self::MODULE_TYPE_META
,	"Hashtags"		=> self::MODULE_TYPE_META
,	"Search"		=> self::MODULE_TYPE_META
,	"Related"		=> self::MODULE_TYPE_META
);

// Prepare Settings
ContentForm::$contentType = 'article';		// Set the content entry type.

$contentForm->guestPosts = true;			// Allows guest submissions on the site.
$contentForm->privatePosts = true;			// Allows private submissions on the site.
$contentForm->maxStatus = 4;				// Sets the maximum allowed status for a user to set.

// $contentForm->urlPrefix = Me::$vals['handle'] . '/';		// Used in the blog
// $contentForm->urlFixed = "";								// If set, forces the URL to be a specific value
// $contentForm->urlClearance = ContentForm::URL_ALLOW;		// Allows the writer to update his own URL
// $contentForm->urlClearance = ContentForm::URL_ONCE;		// Allows the writer to update his own URL when published
// $contentForm->urlClearance = ContentForm::URL_DENY;		// Prevents the writer from updating his own URL

$contentForm->useComments = true;			// Allows comments
$contentForm->useVoting = true;				// Allows voting
$contentForm->useDeletion = true;			// Allows deletion

// Make sure you have permissions to edit this form
$contentForm->verifyAccess("/my-articles");

// Run Behaviors and Interpreters
$contentForm->runBehavior();
$contentForm->runInterpreter();

// Include Responsive Script
Photo::prepareResponsivePage();
Metadata::addHeader('<link rel="stylesheet" href="' . CDN . '/css/content-block.css" />');

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '<div id="content">' . Alert::display();

$contentForm->drawEditingBox();
$contentForm->drawContent();

echo '</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");


-------------------------------
------ Methods Available ------
-------------------------------

$contentForm = new ContentForm("/this-page");
$contentForm->runBehavior();
$contentForm->runInterpreter();

Content::output($contentID);

*/

class ContentForm {
	
	
/****** Plugin Variables ******/
	public string $baseURL = "";			// <str> The base URL of the Content Form
	public int $contentID = 0;			// <int> The ID of the content entry being worked on.
	public array <str, mixed> $contentData = array();	// <str:mixed> The data of the content entry.
	public int $blockID = 0;			// <int> The ID of the active content block being modified.
	public string $action = "";			// <str> The action / behavior to run.
	public string $type = "";				// <str> The active module type.
	
	// Default Modules
	public array <str, bool> $modules = array(			// <str:bool> A list of modules to load into this content form.
		"Hashtags"		=> self::MODULE_TYPE_META
	,	"Text"			=> self::MODULE_TYPE_SEGMENT
	,	"Image"			=> self::MODULE_TYPE_SEGMENT
	,	"Video"			=> self::MODULE_TYPE_SEGMENT
	,	"Author"		=> self::MODULE_TYPE_META
	,	"Search"		=> self::MODULE_TYPE_META
	,	"Related"		=> self::MODULE_TYPE_META
	);
	
	// Form Settings (set these on every form page)
	public static string $contentType = "blog";	// <str> The type of content entry (blog, article, etc)
	
	public bool $guestPosts = true;		// <bool> TRUE if there are guest posts on this site.
	public bool $privatePosts = false;	// <bool> TRUE if there are private posts allowed on this site.
	public int $maxStatus = 4;			// <int> The maximum status level (e.g. Content::STATUS_GUEST)
	
	public string $urlPrefix = "";			// <str> Set this value to indicate the prefix to a URL slug.
	public string $urlFixed = "";			// <str> If set, this forces the URL to be a specific value.
	public int $urlClearance = 0;		// <int> The level of clearance the writer has regarding the URL.
	public bool $urlReassign = false;	// <bool> TRUE if you are allowed to reassign URL's after making them public.
	
	public int $blockLimit = 25;		// <int> The total number of blocks that this form is limited to.
	public int $pagination = 1;			// <int> The total number of pages that this content can allow.
	
	public bool $useHashtags = false;	// <bool> TRUE to allow hashtags.
	public bool $useComments = false;	// <bool> TRUE to allow comments.
	public bool $useVoting = false;		// <bool> TRUE to allow voting.
	
	// Important Constants
	const MODULE_TYPE_SEGMENT = 1;		// <int> This type is a segment (a content block).
	const MODULE_TYPE_META = 2;			// <int> This type is metadata, or a menu option.
	
	const URL_DENY = 0;			// Prevents the writer from updating his own URL
	const URL_ONCE = 1;			// Allows the writer to update the URL one time only (when publishing)
	const URL_ALLOW = 2;		// Allows the writer to update his own URL at will
	
	
/****** Content Form Constructor ******/
	public function __construct
	(
		string $baseURL		// <str> The base URL of the Content Form to submit to.
	,	int $contentID = 0	// <int> The ID of the content entry being worked on.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm = new ContentForm($baseURL, $contentID);
	{
		// Make sure a valid ID has been provided
		if(!$contentID)
		{
			return;
		}
		
		// Prepare Values
		$this->baseURL = "/" . trim($baseURL, "/");
		$this->contentID = (int) $contentID;
		
		// Set the Active Type
		if(isset($_GET['t']))
		{
			$this->type = Sanitize::variable($_GET['t']);
		}
		
		$this->blockID = (isset($_GET['block']) ? (int) $_GET['block'] : 0);
		$this->action = (isset($_GET['action']) ? Sanitize::variable($_GET['action']) : "");
		
		// Get the content data for the system
		$this->contentData = Content::get($this->contentID);
		
		if($this->urlFixed) { $this->contentData['url_slug'] = $this->urlFixed; }
	}
	
	
/****** Run basic form behaviors (such as when a link is clicked during editing) ******/
	public function runBehavior (
	): void				// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->runBehavior();
	{
		// Make sure the necessary data is provided for this method to work
		if($this->contentID == 0 or !$this->type) { return; }
		
		// Check if there is a custom behavior to load
		if(method_exists($this, "customBehavior" . $this->type))
		{
			$funcName = "customBehavior" . $this->type;
			
			$this->$funcName();
		}
		
		// Generate a block of the appropriate type
		else if(method_exists("Module" . $this->type, "behavior"))
		{
			// Make sure the link is protected
			if($link = Link::clicked() and $link == $this->contentData['uni_id'] . ":" . $this->contentID)
			{
				call_user_func(array("Module" . $this->type, "behavior"), $this);
			}
		}
		
		// Check for actions involved in the meta section
		else if($this->action == "meta")
		{
			// Run Meta Behaviors
			if($this->type == "Settings" and $link = Link::clicked())
			{
				// Delete the current thumbnail so that it will refresh
				if($link == "thumb")
				{
					$this->contentData['thumbnail'] = "";
					
					Database::query("UPDATE content_entries SET thumbnail=? WHERE id=? LIMIT 1", array("", $this->contentID));
					
					Alert::saveSuccess("Thumbnail Updated", "You have run the thumbnail update, which sets the thumbnail to the most relevant image.");
				}
			}
		}
	}
	
	
/****** Interpret a Form Submission ******/
	public function runInterpreter (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->runInterpreter();
	{
		if(!$this->type) { return; }
		
		// Check if there is a custom interpreter to load
		if(method_exists($this, "customInterpret" . $this->type))
		{
			$funcName = "customInterpret" . $this->type;
			
			$this->$funcName();
		}
		
		// Make sure we located an available interpreter
		else if(method_exists("Module" . $this->type, "interpret"))
		{
			call_user_func(array("Module" . $this->type, "interpret"), $this);
			
			$this->updateCache();
		}
		
		// Run interpreter for Settings
		if($this->type == "Settings")
		{
			$this->interpretSettings();
		}
		
		// Run interpreter for Delete
		else if($this->type == "Delete")
		{
			$this->interpretDelete();
		}
	}
	
	
/****** Run a custom update ******/
# This is a dummy function designed to be overwritten by any child plugins that would want to use it.
# This method runs during the entry update process.
	public function customUpdate
	(
		array <str, mixed> $customData		// <str:mixed> The custom data to submit to the method.
	): bool					// RETURNS <bool> TRUE to continue with update, FALSE to end update sequence.
	
	// $contentForm->customUpdate($customData);
	{
		return true;
	}
	
	
/****** Run a custom deletion ******/
# This is a dummy function designed to be overwritten by any child plugins that would want to use it.
# This method runs during the entry deletion process.
	public function customDelete (
	): bool					// RETURNS <bool> TRUE to continue with deletion, FALSE to end deletion sequence.
	
	// $contentForm->customDelete();
	{
		return true;
	}
	
	
/****** Verify access to modifying this content entry ******/
	public function verifyAccess
	(
		string $redirectTo		// <str> The URL to redirect to if verification fails.
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->verifyAccess($redirectTo);
	{
		// Make sure the content data was retrieved successfully
		if(!$this->contentData)
		{
			header("Location: " . $redirectTo); exit;
		}
		
		// Make sure you own this blog
		if($this->contentData['uni_id'] != Me::$id and Me::$clearance < 6)
		{
			Alert::error("Invalid User", "You do not have permissions to edit this content.", 7);
			
			header("Location: " . $redirectTo); exit;
		}
		
		// If Guest Posts are allowed on this site
		if($this->guestPosts)
		{
			// If this entry is set to official, guest submitters cannot change it any longer
			if($this->contentData['status'] >= Content::STATUS_OFFICIAL)
			{
				if(Me::$clearance < 6)
				{
					Alert::saveInfo("Editing Disabled", "This entry is now an official live post, and cannot be edited.");
					
					header("Location: " . $redirectTo); exit;
				}
			}
		}
	}
	
	
/****** Draw the Content ******/
	public function drawContent (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->drawContent();
	{
		// Get the list of content segments
		$results = Database::selectMultiple("SELECT * FROM content_block_segment WHERE content_id=? ORDER BY sort_order ASC", array($this->contentID));
		
		foreach($results as $result)
		{
			// Check if the Block ID is identical to the shown block ID - there may be editing necessary
			if($this->blockID == $result['block_id'] and $this->type == $result['type'] and $this->action == "edit")
			{
				$this->drawForm();
				
				continue;
			}
			
			// Prepare the common segment URL for the links in this segment
			$segURL = '&content=' . $this->contentID . '&t=' . $result['type'] . '&block=' . $result['block_id'] . "#block-" . $result['block_id'];
			
			echo '<fieldset id="block-' . $result['block_id'] . '" class="content-form">
				<legend>' . $result['type'] . ' Block</legend>
			<div class="content-form-opts"><a href="' . $this->baseURL . '?action=edit' . $segURL . '">Edit</a> <a href="' . $this->baseURL . '?action=delete&' . Link::prepare($this->contentData['uni_id'] . ":" . $this->contentID) . $segURL . '">Delete</a> <a href="' . $this->baseURL . '?action=moveUp&' . Link::prepare($this->contentData['uni_id'] . ":" . $this->contentID) . $segURL . '">Move Up</a></div>';
			
			// Display the appropriate block type
			if($result['type'])
			{
				if(method_exists("Module" . $result['type'], "get"))
				{
					echo call_user_func(array("Module" . $result['type'], "get"), (int) $result['block_id']);
				}
			}
			
			echo '</fieldset>';
		}
	}
	
	
/****** Draw the Editing Box for the Content Form ******/
	public function drawEditingBox (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->drawEditingBox();
	{
		// Draw the appropriate form block
		echo '
		<fieldset class="content-form"><legend>Content Editor</legend>
		<div class="content-form-opts">
			<a href="' . $this->baseURL . '?action=meta&content=' . $this->contentID . '&t=Settings">Settings</a>';
		
		// Show Modules
		foreach($this->modules as $module => $modType)
		{
			if($modType == self::MODULE_TYPE_SEGMENT)
			{
				echo '
				<a href="' . $this->baseURL . '?action=segment&content=' . $this->contentID . '&t=' . $module . '&' . Link::prepare($this->contentData['uni_id'] . ":" . $this->contentID) . '">New ' . $module . ' Block</a>';
			}
			
			else if($modType == self::MODULE_TYPE_META)
			{
				echo '
				<a href="' . $this->baseURL . '?action=meta&content=' . $this->contentID . '&t=' . $module . '">' . $module . '</a>';
			}
		}
		
		// Show Deletion Option (for moderators)
		if(Me::$clearance >= 6)
		{
			echo '
			<a href="' . $this->baseURL . '?action=meta&content=' . $this->contentID . '&t=Delete">Delete</a>';
		}
		
		echo '
		</div>';
		
		// If you are editing the metadata for the content entry
		if($this->action == "meta")
		{
			if($this->type and method_exists("Module" . $this->type, "drawForm"))
			{
				call_user_func(array("Module" . $this->type, "drawForm"), $this);
			}
			
			switch($this->type)
			{
				// Allow updating of the main settings
				case "Settings":		$this->drawFormSettings();			break;
				
				// Allow the deletion of the content entry
				case "Delete":			$this->drawFormDelete();			break;
			}
		}
		
		echo '</fieldset>';
	}
	
	
/****** Draw the Form for the active block ******/
	public function drawForm (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->drawForm();
	{
		// Make sure the necessary information is provided
		if(!$this->contentID or !$this->type or !$this->blockID)
		{
			return;
		}
		
		// Draw the appropriate form block
		echo '<fieldset id="block-' . $this->blockID . '" class="content-form"><legend>Edit This Content Block</legend>';
		
		// Check if there is a custom form
		if(method_exists($this, "customForm" . $this->type))
		{
			$funcName = "customForm" . $this->type;
			
			$this->$funcName();
		}
		
		// Draw the Module's Form
		else if(method_exists("Module" . $this->type, "drawForm"))
		{
			call_user_func(array("Module" . $this->type, "drawForm"), $this);
		}
		
		echo '
		</fieldset>';
	}
	
	
/****** Draw the Form for the active Text Block ******/
	public function drawFormSettings (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->drawFormSettings();
	{
		// Prepare Values
		$urlLength = 42 - (strlen($this->urlPrefix));
		$this->contentData['url_slug'] = str_replace($this->urlPrefix, "", $this->contentData['url_slug']);
		
		$this->contentData['title'] = isset($_POST['title']) ? Sanitize::safeword($_POST['title'], "'?") : $this->contentData['title'];
		$this->contentData['url_slug'] = isset($_POST['url_slug']) ? Sanitize::variable($_POST['url_slug'], "-") : $this->contentData['url_slug'];
		$this->contentData['status'] = isset($_POST['status']) ? ($_POST['status'] + 0) : $this->contentData['status'];
		
		// Determine information about hashtags, if applicable
		$hashtagLock = false;	// This value is set to TRUE if the URL is publicly locked due to hashtags.
		
		if(isset($this->modules["Hashtags"]))
		{
			// Get the list of hashtags for this content ID
			list($submittedHashtags, $unsubmittedHashtags) = ModuleHashtags::getBySub($this->contentID);
			
			if($submittedHashtags or $unsubmittedHashtags)
			{
				$hashtagLock = true;
			}
		}
		
		// Prepare the dropdown list for the statuses
		$statusDropdown = "";
		
		if(!$this->guestPosts or Me::$clearance >= 6)
		{
			$statusDropdown = '<option value="' . Content::STATUS_OFFICIAL . '">Official Post</option>';
		}
		
		if($this->guestPosts)
		{
			$statusDropdown .= '<option value="' . Content::STATUS_GUEST . '">Public Post</option>';
		}
		
		if(!$hashtagLock)
		{
			if($this->privatePosts)
			{
				$statusDropdown .= '
				<option value="' . Content::STATUS_PRIVATE . '">Private Post (Friends Can View)</option>';
			}
			
			$statusDropdown .= '
				<option value="' . Content::STATUS_DRAFT . '">Draft</option>';
		}
		
		// If a thumbnail is not selected, we should pull one once available)
		if(!$this->contentData['thumbnail'])
		{
			$this->updateThumbnail();
		}
		
		// Display the Metadata Block
		echo '
		<div style="margin-top:22px;">
		<form class="uniform" action="' . $this->baseURL . '?action=meta&content=' . ($this->contentID + 0) . '&t=Settings" method="post">' . Form::prepare(SITE_HANDLE . "-contentSettings");
		
		// Show the Thumbnail, if applicable
		if($this->contentData['thumbnail'])
		{
			echo '
			<p>
				<span style="font-weight:bold;">Current Thumbnail:</span><br />
				<img src="' . $this->contentData['thumbnail'] . '?cache=' . time() . '" /><br />
				<a href="' . $this->baseURL . '?action=meta&content=' . ($this->contentID + 0) . '&t=Settings&' . Link::prepare("thumb") . '">Update Thumbnail</a>
			</p>';
		}
		
		// Display the Status Dropdown
		echo '
			<p>
				<span style="font-weight:bold;">Status:</span><br />
				<select name="status">' . str_replace('value="' . $this->contentData['status'] . '"', 'value="' . $this->contentData['status'] . '" selected', $statusDropdown) . '
				</select>
			</p>';
		
		// Display the Title Field
		echo '
			<p>
				<span style="font-weight:bold;">Title:</span><br />
				<input type="text" name="title" value="' . $this->contentData['title'] . '" placeholder="Title of the post . . ." size="54" maxlength="72" tabindex="10" autofocus />
			</p>';
		
		// Display the URL Field
		$urlDisabled = " disabled";
		
		if($this->urlClearance == self::URL_ALLOW or ($this->contentData['url_slug'] == "" and $this->urlClearance == self::URL_ONCE))
		{
			// If hashtags are locking the URL, it cannot be re-enabled.
			// Note: If the URL has no default, we need to make sure the user sets it (enable in this case).
			if($hashtagLock and $this->contentData['url_slug'] != "")
			{
				$urlDisabled = " disabled";
			}
			else
			{
				$urlDisabled = "";
			}
		}
		
		echo '
		<p>
			<span style="font-weight:bold;">URL to save this content at:</span><br />
			' . SITE_URL . '/' . ($this->urlPrefix ? trim($this->urlPrefix, "/") . '/' : "") . ' <input type="text" name="url_slug" value="' . $this->contentData['url_slug'] . '" placeholder="URL . . ." size="32" maxlength="' . $urlLength . '" tabindex="20"' . $urlDisabled . ' />
		</p>';
		
		// Show several options that only editors are allowed to use
		if(Me::$clearance >= 6)
		{
			// Display the hashtag dropdown
			echo '
			<p>
				<span style="font-weight:bold;">Primary Hashtag:</span><br />
				<select name="primary_hashtag">
					<option value="0">-- Select Hashtag --</option>
					' . ContentHashtags::hashtagDropdown($this->contentData['primary_hashtag']) . '</select>
			</p>';
			
			// Show the comment dropdown, if allowed
			if($this->useComments)
			{
				echo '
				<p>
					<span style="font-weight:bold;">Comments:</span><br />
					<select name="comments">' . str_replace('value="' . $this->contentData['comments'] . '"', 'value="' . $this->contentData['comments'] . '" selected', '
						<option value="' . Content::COMMENTS_STANDARD . '">Standard Comments</option>
						<option value="' . Content::COMMENTS_NO_POSTING . '">Prevent Posting (But Show Comments)</option>
						<option value="' . Content::COMMENTS_DISABLED . '">Disabled</option>
					</select>') . '
				</p>';
			}
			
			// Show the voting dropdown, if allowed
			if($this->useVoting)
			{
				echo '
				<p>
					<span style="font-weight:bold;">Voting:</span><br />
					<select name="voting">' . str_replace('value="' . $this->contentData['voting'] . '"', 'value="' . $this->contentData['voting'] . '" selected', '
						<option value="' . Content::VOTING_STANDARD . '">Standard Voting</option>
						<option value="' . Content::VOTING_FREEZE . '">Freeze Voting</option>
						<option value="' . Content::VOTING_DISABLED . '">Disabled</option>
					</select>') . '
				</p>';
			}
		}
		
		// Show the hashtags that will be submitted
		if(isset($this->modules["Hashtags"]) and $unsubmittedHashtags)
		{
			echo '
			<p>
				<span style="font-weight:bold;">Unsubmitted Hashtags:</span><br />';
			
			foreach($unsubmittedHashtags as $key => $un)
			{
				echo '<span class="c-hashtag" style="font-size:1.1em;"><a href="' . URL::hashtag_unifaction_com() . '/' . $un . '">#' . $un . '</a></span> ';
			}
			
			echo '
			</p>';
		}
		
		echo '
			<input type="submit" name="submit" value="Update" />
		</form>
		</div>';
	}
	
	
/****** Draw the Content Entry Deletion Form ******/
	public function drawFormDelete (
	): void					// RETURNS <void> outputs the appropriate data.
	
	// $contentForm->drawFormDelete();
	{
		echo '
		<div style="margin-top:22px;">
			<h3>Delete this Entry</h3>
			<p style="color:red; font-weight:bold;">Warning: If you delete this entry, all of the contents will be permanently destroyed, and all links to it will be broken.</p>
			<p>If you are certain that you want to delete the entry, type "DELETE" into the text box below.</p>
			
			<form class="uniform" action="' . $this->baseURL . '?action=meta&content=' . ($this->contentID + 0) . '&t=Delete" method="post">' . Form::prepare(SITE_HANDLE . "-contentDelete") . '
				<p><input type="text" name="delete" value="" size="10" maxlength="6" autocomplete="off" tabindex="10" autofocus style="text-transform:uppercase" /></p>
				<p><input type="submit" name="submit" value="Delete This Entry" /></p>
			</form>
		</div>';
	}
	
	
/****** Update the Thumbnail ******/
	public function updateThumbnail (
	): void						// RETURNS <void>
	
	// $this->updateThumbnail();
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
			
			// Resize the image to the appropriate size
			if($image->width > 180 or $image->height > 220)
			{
				$image->autoWidth(180, 220);
			}
			
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
	
	
/****** Run Interpreter for Settings ******/
	public function interpretSettings (
	): void						// RETURNS <void>
	
	// $this->interpretSettings();
	{
		if(!Form::submitted(SITE_HANDLE . "-contentSettings")) { return false; }
		
		// Prepare Values
		$_POST['title'] = (isset($_POST['title']) ? $_POST['title'] : 'Untitled Blog');
		$_POST['status'] = (isset($_POST['status']) and $_POST['status'] != Content::STATUS_DRAFT) ? (int) $_POST['status'] : Content::STATUS_DRAFT;
		$_POST['url_slug'] = (isset($_POST['url_slug']) ? strtolower($_POST['url_slug']) : '');
		$_POST['primary_hashtag'] = (isset($_POST['primary_hashtag']) ? $_POST['primary_hashtag'] : '');
		
		$datePosted = 0;
		$minSlugLen = 0;
		
		$comments = (isset($_POST['comments']) ? ($_POST['comments'] + 0) : Content::COMMENTS_STANDARD);
		$voting = (isset($_POST['voting']) ? ($_POST['voting'] + 0) : Content::VOTING_STANDARD);
		
		// If you're not an editor (or otherwise have appropriate permissions)
		if(Me::$clearance < 6)
		{
			// Standard users cannot change certain settings
			$_POST['primary_hashtag'] = $this->contentData['primary_hashtag'];
			$comments = $this->contentData['comments'];
			$voting = $this->contentData['voting'];
			
			// Standard users are not allowed to change the post status once it has been made official
			if($this->contentData['status'] >= Content::STATUS_OFFICIAL)
			{
				$_POST['status'] = $this->contentData['status'];
			}
			
			// Standard users cannot affect the URL once it has been set publicly
			if($this->contentData['url_slug'] and $this->contentData['status'] >= Content::STATUS_GUEST)
			{
				$_POST['url_slug'] = trim(str_replace($this->urlPrefix, "", $this->contentData['url_slug']), "/");
			}
		}
		
		// If the URL clearance is designed to prevent you from an update, bypass it appropriately
		if($this->urlClearance == self::URL_DENY or ($this->contentData['url_slug'] != "" and $this->urlClearance == self::URL_ONCE))
		{
			$_POST['url_slug'] = $this->contentData['url_slug'];
		}
		
		// Set URL standards if your post is live (or at least visible in guest form)
		else if($_POST['status'] != Content::STATUS_DRAFT or $_POST['url_slug'])
		{
			$datePosted = time();
			
			// If the system is automatically applying a URL prefix, such as "/handle/", apply different rules
			if($this->urlPrefix)
			{
				$minSlugLen = 3;
			}
			
			// The standard rules apply to the URL if there is no URL prefix
			else if($_POST['url_slug'])
			{
				$minSlugLen = 10;
				
				if(!strpos($_POST['url_slug'], "-"))
				{
					$_POST['url_slug'] = "";
					
					Alert::error("URL Requirement", 'The URL must contain at least one "-" dash, such as "local-team-wins-game".');
				}
			}
		}
		
		// Validate the Form Values
		FormValidate::safeword("Title", $_POST['title'], 3, 72, "'?");
		FormValidate::variable("URL", $_POST['url_slug'], $minSlugLen, (42 - $this->urlPrefix), "-/");
		FormValidate::variable("Primary Hashtag", $_POST['primary_hashtag'], 0, 22);
		
		// Update the Settings
		if(FormValidate::pass())
		{
			// Update the Content Entry
			if($this->update($this->contentID, $_POST['title'], $this->urlPrefix . $_POST['url_slug'], $_POST['status'], 0, 6, $datePosted, $_POST['primary_hashtag'], $comments, $voting))
			{
				// Update the cache
				$this->updateCache();
				
				// Announce the success of the update
				Alert::saveSuccess("Settings Updated", "The settings for this entry have been updated.");
				
				// Reload the page
				header("Location: " . $this->baseURL . "?content=" . $this->contentID); exit;
			}
		}
	}
	
	
/****** Run Interpreter for Deletion ******/
	public function interpretDelete (
	): void						// RETURNS <void>
	
	// $this->interpretDelete();
	{
		if(!Form::submitted(SITE_HANDLE . "-contentDelete")) { return false; }
		
		if(isset($_POST['delete']) and strtolower($_POST['delete']) == "delete")
		{
			$this->delete($this->contentID);
			
			if(FormValidate::pass())
			{
				Alert::saveSuccess("Delete Successful", "The content entry has been successfully deleted.");
				
				header("Location: /"); exit;
			}
		}
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
	
	
/****** Update a Content Entry ******/
	public function update
	(
		int $contentID			// <int> The ID of the content entry to update.
	,	string $title				// <str> The title of the entry.
	,	string $urlSlug			// <str> The url slug to assign the entry to.
	,	int $status = 0			// <int> The current status to assign to the entry (e.g. Content::STATUS_DRAFT).
	,	int $clearanceView = 0	// <int> The clearance required to view this entry.
	,	int $clearanceEdit = 0	// <int> The clearance required to edit this entry.
	,	int $datePosted = 0		// <int> The date that this entry has been posted (0 is ignore this).
	,	int $primeHashtag = ""	// <int> Assigns a primary hashtag, if applicable ("" to ignore).
	,	int $comments = 2		// <int> The type of comments to set.
	,	int $voting = 2			// <int> The type of voting to set.
	): bool						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentForm->update($contentID, $title, $urlSlug, $status, $clearanceView, $clearanceEdit, $datePosted, [$primeHashtag], [$comments], [$voting]);
	{
		// Check the content entry data to see if it matches
		if(!$entry = Database::selectOne("SELECT * FROM content_entries WHERE id=? LIMIT 1", array($contentID)))
		{
			return false;
		}
		
		// Recognize Integers
		$entry['status'] = (int) $entry['status'];
		$entry['clearance_view'] = (int) $entry['clearance_view'];
		$entry['clearance_edit'] = (int) $entry['clearance_edit'];
		$entry['comments'] = (int) $entry['comments'];
		$entry['voting'] = (int) $entry['voting'];
		$entry['date_posted'] = (int) $entry['date_posted'];
		
		// Check if you're updating values that are already set
		if($entry['title'] == $title and $entry['url_slug'] == $urlSlug and $entry['status'] == $status and $entry['clearance_view'] == $clearanceView and $entry['clearance_edit'] == $clearanceEdit and $entry['comments'] == $comments and $entry['voting'] == $voting and $entry['primary_hashtag'] == $primeHashtag)
		{
			if($datePosted == 0 or $entry['date_posted'] == $datePosted)
			{
				return true;
			}
		}
		
		// Everything seems okay to continue - process the update
		Database::startTransaction();
		
		$pass = true;
		
		// Check if you've already been published on the current URL Slug
		if(Database::selectValue("SELECT url_slug FROM content_by_url WHERE url_slug=? LIMIT 1", array($entry['url_slug'])))
		{
			if($entry['url_slug'] != $urlSlug)
			{
				$pass = Database::query("DELETE FROM content_by_url WHERE url_slug=? LIMIT 1", array($entry['url_slug']));
			}
		}
		
		// If hashtags are being used, check if the entry is already posted there
		// Delete any old version so that we can update to a new value later
		if($pass and ($primeHashtag or $entry['primary_hashtag']))
		{
			if($entry['primary_hashtag'] != $primeHashtag)
			{
				Database::query("UPDATE IGNORE content_entries SET primary_hashtag=? WHERE id=? LIMIT 1", array($primeHashtag, $contentID));
				
				$pass = Database::query("DELETE IGNORE FROM content_by_hashtag WHERE hashtag=? AND content_id=? LIMIT 1", array($entry['primary_hashtag'], $contentID));
			}
			
			else if($status < Content::STATUS_OFFICIAL)
			{
				$pass = Database::query("DELETE IGNORE FROM content_by_hashtag WHERE hashtag=? AND content_id=? LIMIT 1", array($entry['primary_hashtag'], $contentID));
			}
		}
		
		// If search archetypes are allowed and updated
		if($pass and isset($entry['search_archetype']))
		{
			// If the update is official, we need to set the search data to live
			if($status >= Content::STATUS_OFFICIAL and $entry['search_archetype'])
			{
				ModuleSearch::liveSubmission($contentID, $entry['search_archetype']);
			}
			else
			{
				ModuleSearch::guestSubmission($contentID);
			}
		}
		
		// Update the entry
		if($pass)
		{
			if($pass = Database::query("UPDATE IGNORE content_entries SET title=?, primary_hashtag=?, url_slug=?, status=?, clearance_view=?, clearance_edit=?, comments=?, voting=?, date_posted=? WHERE id=? LIMIT 1", array($title, $primeHashtag, $urlSlug, $status, $clearanceView, $clearanceEdit, $comments, $voting, ($datePosted == 0 ? $entry['date_posted'] : $datePosted), $contentID)))
			{
				// Set the URL Slug if necessary
				if($entry['url_slug'] != $urlSlug)
				{
					$pass = Database::query("INSERT INTO content_by_url (url_slug, content_id) VALUES (?, ?)", array($urlSlug, $contentID));
				}
				
				// Update the hashtag handler, if applicable
				if($pass and $status >= Content::STATUS_OFFICIAL and $primeHashtag)
				{
					if($pass = ModuleHashtags::setHashtag($contentID, $primeHashtag))
					{
						$pass = Database::query("REPLACE INTO content_by_hashtag (hashtag, content_id) VALUES (?, ?)", array($primeHashtag, $contentID));
					}
				}
			}
		}
		
		// Run custom update methods
		if($pass)
		{
			// Prepare Custom Data
			$customData = array(
				'content_id'		=> $contentID
			,	'status'			=> $status
			,	'title'				=> $title
			,	'url_slug'			=> $urlSlug
			,	'primary_hashtag'	=> $primeHashtag
			);
			
			$pass = $this->customUpdate($customData);
		}
		
		// Add the entry to a queue if it is a guest post
		if($pass and $status == Content::STATUS_GUEST)
		{
			Content::queue($contentID);
		}
		
		// Finalize the update
		$success = Database::endTransaction($pass);
		
		// Submit hashtags, if applicable
		if($success and isset($this->modules["Hashtags"]) and ($status >= Content::STATUS_OFFICIAL or ($status >= Content::STATUS_GUEST and Content::$openPost == true)))
		{
			// Get the list of hashtags for this content ID
			list($submittedHashtags, $unsubmittedHashtags) = ModuleHashtags::getBySub($contentID);
			
			// If we've already submitted hashtags for this system, we're actually just building upon an existing set.
			// This means we can set the type to 'resubmitted' and reuse the attachments already there.
			$resubmit = ($submittedHashtags ? true : false);
			
			// Make sure there are hashtags that actually need to be submitted
			if($unsubmittedHashtags)
			{
				// Get the core data that the hashtag system will require
				$coreData = Content::scanForCoreData($contentID);
				
				// Submit the hashtags
				if(ModuleHashtags::setSubmitted($contentID, $unsubmittedHashtags))
				{
					ContentHashtags::tagEntry($contentID, $unsubmittedHashtags);
					
					Hashtag::submitContentEntry($entry['uni_id'], self::$contentType, $title, $coreData['body'], $unsubmittedHashtags, SITE_URL . "/" . trim($urlSlug, "/"), $coreData['image_url'], $coreData['mobile_url'], $coreData['video_url'], $resubmit);
				}
			}
		}
		
		return $success;
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
				$body .= call_user_func(array("Module" . $result['type'], "get"), (int) $result['block_id']);
			}
		}
		
		// Cache the text
		return Database::query("REPLACE INTO content_cache (content_id, body) VALUES (?, ?)", array($this->contentID, $body));
	}
	
	
/****** Delete a content entry and all of the other pieces related to it ******/
	public function delete
	(
		int $contentID		// <int> The ID of the content entry to delete
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $contentForm->delete($contentID);
	{
		// Get the necessary data
		$contentData = Content::get($contentID);
		
		// Begin the deletion process
		Database::startTransaction();
		
		// Delete the main entry
		if(!Database::query("DELETE FROM content_entries WHERE id=? LIMIT 1", array($contentID)))
		{
			Alert::error("Delete Error", "There was an error deleting the main content entry.");
			return Database::endTransaction(false);
		}
		
		// Delete the URL Data
		if(!Database::query("DELETE FROM content_by_url WHERE url_slug=? LIMIT 1", array($contentData['url_slug'])))
		{
			Alert::error("Delete Error", "Deletion halted: the URL section could not be deleted properly.");
			return Database::endTransaction(false);
		}
		
		// Delete the User Data
		if(!Database::query("DELETE FROM content_by_user WHERE uni_id=? AND content_id=? LIMIT 1", array($contentData['uni_id'], $contentID)))
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
		foreach($this->modules as $module => $bool)
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
		if(!$this->customDelete())
		{
			Alert::error("Delete Error", "Deletion halted: the custom deletion process failed.");
			return Database::endTransaction(false);
		}
		
		// Finalize the deletion process
		Database::endTransaction();
		
		// Delete the thumbnail for the content entry, if applicable
		if(isset($contentData['thumbnail']))
		{
			$urlData = URL::parse($contentData['thumbnail']);
			
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
	): int					// RETURNS <int> the sort order of the new segment, or 0 on failure.
	
	// $segmentID = ContentForm::createSegment($contentID, $type, $blockID);
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
		
		return $sortOrder;
	}
	
	
/****** Delete a Content Segment ******/
	public static function deleteSegment
	(
		int $contentID		// <int> The ID of the content entry assigned to this segment piece.
	,	int $type = 0		// <int> The type of the segment.
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
	
	
/****** Move a block up in the sort order of a segment list ******/
	public static function moveUp
	(
		int $contentID		// <int> The ID of the content entry assigned to this segment piece.
	,	int $type = 0		// <int> The type of the segment.
	,	int $blockID = 0	// <int> The ID of the block associated with this segment piece.
	): bool					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// ContentForm::moveUp($contentID, $type, $blockID);
	{
		// Find the current sort order of the designated segment
		if(!$sortOrder = (int) Database::selectValue("SELECT sort_order FROM content_block_segment WHERE content_id=? AND type=? AND block_id=? LIMIT 1", array($contentID, $type, $blockID)))
		{
			return false;
		}
		
		// Make sure there is upward mobility (a block higher than itself)
		if($sortOrder <= 1) { return false; }
		
		$swapOrder = $sortOrder - 1;
		
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array(0, $contentID, $sortOrder)))
		{
			if($pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array($sortOrder, $contentID, $swapOrder)))
			{
				$pass = Database::query("UPDATE content_block_segment SET sort_order=? WHERE content_id=? AND sort_order=? LIMIT 1", array($swapOrder, $contentID, 0));
			}
		}
		
		return Database::endTransaction($pass);
	}
	
}