<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Make sure you have a designated handle
if(!$ownerHandle = Cookie::get("admin-handle", ""))
{
	header("Location: /install/connect-handle"); exit;
}

// If the form was not submitted, set the $_POST values to the default configuration values.
// This will allow us to auto-fill the form with useful data, rather than leaving them all empty.
if(!isset($_POST['site-salt']) and isset($config['database']))
{
	$_POST['site-salt'] = SITE_SALT;
	$_POST['site-handle'] = SITE_HANDLE;
	$_POST['site-url'] = SITE_URL;
	
	$_POST['site-name'] = $config['site-name'];
	$_POST['site-domain'] = $config['site-domain'];
	$_POST['admin-email'] = $config['admin-email'];
	
	$_POST['site-database-name'] = $config['database']['name'];
}

// Prepare Installation Values
$buildApp = "";

$randSalt = Security::randHash(82, 80);
$randSalt = str_replace('"', '', $randSalt);
$randSalt = str_replace('$', '', $randSalt);

// Prepare POST Values: make sure that every $_POST value has a default value provided.
$_POST['site-salt'] = (isset($_POST['site-salt']) ? Sanitize::text($_POST['site-salt']) : $randSalt);
$_POST['site-handle'] = (isset($_POST['site-handle']) ? Sanitize::variable($_POST['site-handle']) : "");
$_POST['site-url'] = (isset($_POST['site-url']) ? Sanitize::variable($_POST['site-url'], ":/.") : $_SERVER['SERVER_NAME']);

$_POST['site-name'] = (isset($_POST['site-name']) ? Sanitize::text($_POST['site-name']) : "");
$_POST['site-domain'] = (isset($_POST['site-domain']) ? Sanitize::variable($_POST['site-domain'], ":/.") : "");
$_POST['admin-email'] = (isset($_POST['admin-email']) ? Sanitize::variable($_POST['admin-email'], ".@") : "");

$_POST['site-database-name'] = (isset($_POST['site-database-name']) ? Sanitize::variable($_POST['site-database-name']) : "");

// Run the Form
if(Form::submitted("install-app-config"))
{
	// Check if all of the input you sent is valid: 
	FormValidate::variable("Site Handle", $_POST['site-handle'], 3, 22);
	FormValidate::safeword("Site Name", $_POST['site-name'], 3, 42);
	FormValidate::url("URL", $_POST['site-url'], 3, 64);
	
	// Parse the URL input
	$siteURL = URL::parse($_POST['site-url']);
	
	if(FormValidate::pass())
	{
		// Make sure the site handle isn't taken
		$packet = array(
			"site-handle"	=> $_POST['site-handle']	// <str> The site handle to register.
		,	"uni-handle"	=> $ownerHandle				// <str> The UniFaction handle to set as the admin of the site.
		,	"site-name"		=> $_POST['site-name']		// <str> The name of the site to register.
		,	"site-url"		=> $siteURL['full']			// <str> The URL to register the site with.
		);
		
		// Call UniFaction's API to register a site with the system
		// If the site fails to register, it will provide an alert explaining why
		$response = Connect::call(URL::auth_unifaction_com() . "/api/RegisterSiteHandle", $packet);
		
		if($response)
		{
			// If the database users are provided and there is a database name that we can create, build config settings
			$siteTheme = "default";
			$siteThemeStyle = "default";
			
			$buildApp = '<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Site-Wide Salt
define("SITE_SALT", "' . $_POST['site-salt'] . '");

// A unique 10-22 alphanumeric value to represent your site
define("SITE_HANDLE", "' . $_POST['site-handle'] . '");

// Set the Application Path (in most cases, this is the same as CONF_PATH)
define("APP_PATH", CONF_PATH);


// Prepare Default Theme
Theme::set("' . $siteTheme . '", "' . $siteThemeStyle . '");

// Important Configurations
$config["site-name"] = "' . $_POST['site-name'] . '";
$config["database"]["name"] = "' . $_POST['site-database-name'] . '";

if(ENVIRONMENT == "' . ENVIRONMENT . '")
{
	// A full URL path to the domain
	define("SITE_URL", "http://' . $siteURL['host'] . '");
	
	$config["admin-email"] = "info@' . $siteURL['host'] . '";
	$config["site-domain"] = "' . $siteURL['host'] . '";
}
';
			
			// If you automatically updated the configuration files
			if(isset($_POST['auto-submit']))
			{
				if(File::move(CONF_PATH . "/config.php", CONF_PATH . "/config-backup.php"))
				{
					File::write(CONF_PATH . "/config.php", $buildApp);
					
					Alert::saveSuccess("Config Updated", "Configuration has been automatically updated.");
					
					header("Location: /install/setup-database"); exit;
				}
				else
				{
					Alert::error("Automatic Update", "Issue with Automatic Update: attempt to backup config.php failed. phpTesla does not have proper permissions to rename the file. You may need to perform a manual update instead.", 5);
				}
			}
			
			// If you manually updated the configuration files
			if(isset($_POST['manual-submit']))
			{
				Alert::saveSuccess("Config Updated", "You performed a manual update.");
				
				header("Location: /install/setup-database"); exit;
			}
		}
		else if(Connect::$alert != "")
		{
			Alert::error("API Issue", Connect::$alert);
		}
		else
		{
			Alert::error("API Connection", "Unable to connect to the Site Registration API. Please try again shortly.", 4);
		}
	}
}

// Run Global Script
require(SYS_PATH . "/controller/includes/install_global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Show Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="content" class="content-open">
<form class="uniform" action="/install/config-app" method="post">' . Form::prepare("install-app-config");

if($buildApp == "")
{
	// Display the Page
	echo Alert::display() . '
	
	<h1>Installation: Application Configuration</h1>
	
	<p>This step allows you to designate certain information about your site.</p>
	<p style="color:green;"></p>
	
	<h3>Step #1 - Site Details</h3>
	
	<p>
		<strong>Site Handle</strong>:
		<br /><input type="text" name="site-handle" value="' . htmlspecialchars($_POST['site-handle']) . '"  maxlength="22" />
		<br />This is the value that other UniFaction sites will use to interact with your site, such as with APIs. It can only contain letters, numbers, and underscores. It must be eight characters or longer. This value cannot be changed later.
	</p>
	
	<p>
		<strong>Site Name</strong>:
		<br /><input type="text" name="site-name" value="' . htmlspecialchars($_POST['site-name']) . '"  maxlength="42" />
		<br />This is what your site will be named. You can change this value later.
	<p>
	
	<p>
		<strong>Site Domain</strong>:
		<br /><input type="text" name="site-url" value="' . htmlspecialchars($_POST['site-url']) . '"  maxlength="56" />
		<br />This is the domain for your site, such as http://example.com. It MUST be the same as the domain you are hosting your site on.
	<p>
	
	<p>
		<strong>Site Email (optional)</strong>:
		<br /><input type="text" name="admin-email" value="' . htmlspecialchars($_POST['admin-email']) . '"  maxlength="64" />
		<br />This is the default email that your site will send emails to if they are not specified.
	<p>
	
	<h3>Step #2 - Site Salt</h3>
	<p>
		<input type="text" name="site-salt" value="' . htmlspecialchars($_POST['site-salt']) . '" size="70" maxlength="72" />
		<br />The site salt is used for various security functions. It will work across the entire site. This value should be a random string that is 65 to 80 characters in length.
	<p>
	
	<h3>Step #3 - Database Name</h3>
	
	<p>
		<input type="text" name="site-database-name" value="' . htmlspecialchars($_POST['site-database-name']) . '"  maxlength="22" />
		<br />This database will automatically be created if it does not exist.
	<p>
	
	<p><input type="submit" name="submit" value="Continue" /></p>';
}
else
{
	echo '
	<h1>Installation: Site Configuration</h1>
	
	<h3>Step #1 - Update Configuration</h3>
	<p>This configuration file applies to the phpTesla application that you\'re currently installing.</p>
	
	<p>You can locate the config.php file here: ' . CONF_PATH . '/config.php</p>
	
	<h4>Option #1a: Automatic Update</h4>
	<p>If you want phpTesla to automatically update the configuration file for your application, just press the "Update Automatically" button. Standard users that don\'t need any server-specific customization should use this option.</p>
	<p><input type="submit" name="asd-submit" value="Update Automatically" /></p>
	
	<h4>Option #1b: Manual Update</h4>
	<p>Advanced users might want to set the configuration file for the application manually. To do this, open the config.php file for the application. You can base your configurations off of the values provided in the textbox below.</p>
	<p>
		<textarea style="width:100%; height:250px; tab-size:4; -moz-tab-size:4; -ms-tab-size:4; -webkit-tab-size:4;">' . $buildApp . '</textarea>
	</p>
	<p>You can find the config.php file in : ' . CONF_PATH . '/config.php</p>
	<p><input type="submit" name="manual-submit" value="I have updated the file manually" /></p>
	';
	
	// Provide hidden post values
	$pList = array('site-salt', 'site-handle', 'site-url', 'site-name', 'site-domain', 'admin-email', 'site-database-name');
	
	foreach($pList as $pName)
	{
		$pName = Sanitize::variable($pName, "-");
		
		echo '
		<input type="hidden" name="' . $pName . '" value="' . htmlspecialchars(Sanitize::text($_POST[$pName])) . '" />';
	}
}

echo '
</form>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");