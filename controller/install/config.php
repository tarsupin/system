<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// If the form was not submitted, set the $_POST values to the default configuration values.
// This will allow us to auto-fill the form with useful data, rather than leaving them all empty.
if(!isset($_POST['db-type']) and isset($config['database']))
{
	$_POST['db-type'] = $config['database']['type'];
	$_POST['db-host'] = $config['database']['host'];
	$_POST['db-admin-user'] = $config['database']['admin-user'];
	$_POST['db-admin-pass'] = $config['database']['admin-pass'];
	$_POST['db-user'] = $config['database']['user'];
	$_POST['db-pass'] = $config['database']['pass'];
	
	$_POST['use-hhvm'] = (USE_HHVM ? true : false);
}

// Prepare Installation Values
$buildGlobal = "";
$postDBAdmin = false;
$postDBUser = false;

// Prepare POST Values: make sure that every $_POST value has a default value provided.
if(!isset($_POST['environment']) or !in_array($_POST['environment'], array("local", "development", "production")))
{
	$_POST['environment'] = "production";
}

$_POST['db-type'] = (isset($_POST['db-type']) ? Sanitize::variable($_POST['db-type']) : "mysql");
$_POST['db-host'] = (isset($_POST['db-host']) ? Sanitize::variable($_POST['db-host'], ".:/") : "localhost");

$_POST['db-admin-user'] = (isset($_POST['db-admin-user']) ? Sanitize::variable($_POST['db-admin-user']) : "");
$_POST['db-admin-pass'] = (isset($_POST['db-admin-pass']) ? Sanitize::text($_POST['db-admin-pass']) : "");

if($_POST['environment'] == "production" and $_POST['db-admin-user'] == "")
{
	$_POST['db-admin-user'] = "root";
}

$_POST['db-user'] = (isset($_POST['db-user']) ? Sanitize::variable($_POST['db-user']) : "");
$_POST['db-pass'] = (isset($_POST['db-pass']) ? Sanitize::text($_POST['db-pass']) : "");

$_POST['use-hhvm'] = (isset($_POST['use-hhvm']) and $_POST['use-hhvm'] == "yes" ? true : false);

// Check if the standard user is properly configured after POST values were used
if($connected = Database::initialize("", $_POST['db-user'], $_POST['db-pass'], $_POST['db-host'], $_POST['db-type']))
{
	$postDBUser = true;
	
	Alert::success("DB User Configured", "The standard user is properly configured!");
}

// Check if the admin user is properly configured after POST values were used
if($connected = Database::initialize("", $_POST['db-admin-user'], $_POST['db-admin-pass'], $_POST['db-host'], $_POST['db-type']))
{
	$postDBAdmin = true;
	
	Alert::success("DB Admin Configured", "The admin user is properly configured!");
}

// Run the Form
if(Form::submitted("install-db-connect"))
{
	// Generate the two textboxes with information on what to upload
	// We can offer to overwrite the files, or allow them to modify the files themselves
	
	// If the standard user doesn't exist but the admin does, try to create standard user
	// If there is no standard user to create, reuse the admin user as the standard user
	if($postDBAdmin and !$postDBUser)
	{
		// Attempt to create the standard user
		if($_POST['db-user'] != "")
		{
			FormValidate::variable("Standard Access User", $_POST['db-user'], 1, 22);
			
			if(FormValidate::pass())
			{
				Database::initRoot();
				
				if(DatabaseAdmin::createDBUser($_POST['db-user'], $_POST['db-pass'], $_POST['db-host']))
				{
					if($connected = Database::initialize("", $_POST['db-user'], $_POST['db-pass'], $_POST['db-host'], $_POST['db-type']))
					{
						$postDBUser = true;
					}
					else
					{
						Alert::error("User Cannot Connect", "That database user was not able to connect to the database.", 5);
					}
				}
				else
				{
					Alert::error("User Not Created", "That database user was not able to be created.", 9);
				}
			}
		}
		else
		{
			$postDBUser = true;
			
			$_POST['db-user'] = $_POST['db-admin-user'];
			$_POST['db-pass'] = $_POST['db-admin-pass'];
		}
	}
	
	// Test the connections that are provided in the configuration files directly
	// This will allow us to identify which stage in database configuration we should be at
	if($postDBAdmin and $postDBUser)
	{
		// Prepare Values
		$configAdmin = false;
		$configUser = false;
		
		// Check if the standard user is properly configured with CONFIG values alone
		if($connected = Database::initialize("", $config['database']['user'], $config['database']['pass'], $config['database']['host'], $config['database']['type']))
		{
			$configAdmin = true;
		}
		
		// Check if the admin user is properly configured with CONFIG values alone
		if($connected = Database::initialize("", $config['database']['admin-user'], $config['database']['admin-pass'], $config['database']['host'], $config['database']['type']))
		{
			$configUser = true;
		}
		
		// If both of the database users are prepared in the configuration files, we can proceed to application config
		if($configAdmin and $configUser)
		{
			Alert::saveSuccess("Config Set", "Your global-config.php file is properly configured for this application!");
			
			header("Location: /install/connect-handle"); exit;
		}
	}
	
	// If the post users are valid, but the configurations are not set yet, show the config updates to change
	if($postDBAdmin and $postDBUser)
	{
		$buildGlobal = '<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Choose Your Environment
define("ENVIRONMENT", "' . $_POST['environment'] . '");

// Choose the directories to load plugins from
define("CORE_PLUGIN_PATH", SYS_PATH . "/plugins_core");
define("ADDON_PLUGIN_PATH", SYS_PATH . "/plugins");

// Prepare the $config array
$config = array();

if(ENVIRONMENT == "' . $_POST['environment'] . '") {
	
	// Set Error Handling
	error_reporting(0);
	ini_set("display_errors", 0);
	
	// Does this server use HHVM as it\'s web server? If so, it can take advantage of the HACK language.
	// Setting this to true will affect how certain classes or files are loaded
	// If TRUE: the plugin paths will look in /{plugin}/hhvm/{plugin}.php before trying /{plugin}/{plugin}.php
	define("USE_HHVM", ' . ($_POST['use-hhvm'] ? "true" : "false") . ');
	
	// Set Database Configurations
	$config["database"] = array(
		"user"			=> "' . $_POST['db-user'] . '"
	,	"pass"			=> "' . $_POST['db-pass'] . '"
	,	"admin-user"	=> "' . $_POST['db-admin-user'] . '"
	,	"admin-pass"	=> "' . $_POST['db-admin-pass'] . '"
	,	"host"			=> "' . $_POST['db-host'] . '"
	,	"type"			=> "' . $_POST['db-type'] . '"
	);
}';
	}
	
	// If you attempted an automatic update
	if(isset($_POST['auto-submit']))
	{
		$coreDirectory = dirname(SYS_PATH);
		
		if(File::move($coreDirectory . "/global-config.php", $coreDirectory . "/global-config-backup.php"))
		{
			File::write($coreDirectory . "/global-config.php", $buildGlobal);
		}
		else
		{
			Alert::error("Automatic Update", "Issue with Automatic Update: attempt to backup global-config.php failed. phpTesla does not have proper permissions to rename the file. You may need to perform a manual update instead.", 5);
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
' . Alert::display();

echo '
<form class="uniform" action="/install/config" method="post">' . Form::prepare("install-db-connect");

if($buildGlobal == "")
{
	// Prepare Values
	$hhvmPrep = ($_POST['use-hhvm'] ? 'yes' : 'no');
	
	// Display the Page
	echo '
	<h1>Installation: Site Configuration</h1>
	
	<h3>Step #1 - Configure Environment</h3>
		<p><strong>IMPORTANT</strong>: These values are provided for advanced users and webmasters. If you don\'t know what these values do, leave them set to their default values.</p>
		
		<p>
			Environment:
			<select name="environment">' . str_replace('value="' . ENVIRONMENT . '"', 'value="' . ENVIRONMENT . '" selected', '
				<option value="local">local - your personal computer</option>
				<option value="development">development - for staging and collaborative work</option>
				<option value="production">production - for live sites</option>') . '
			</select>
		</p>
		<p>
			Use HHVM / Hack Language Hybrid:
			<select name="use-hhvm">' . str_replace('value="' . $hhvmPrep . '"', 'value="' . $hhvmPrep . '" selected', '
				<option value="no">NO</option>
				<option value="yes">YES, this server is running HHVM</option>') . '
			</select>
		</p>
		
	<h3>Step #2 - Connect to the Database</h3>
	
	<p>This site uses a database, which must be properly configured. It requires MySQL to be installed and a user with administrative privileges that can access that database.</p>
	
	<p>Your database configurations are set with the global-config.php file. If any of your database credentials need to be changed or updated, you can always refer to that file and update it manually.</p>
	
	<p style="color:green;">Note: the default options provided here will work in most cases. Don\'t change these unless you know what you\'re doing.</p>
	
	<p>Database Type: <input type="text" name="db-type" value="' . $_POST['db-type'] . '" /></p>
	<p>Database Host: <input type="text" name="db-host" value="' . $_POST['db-host'] . '" /></p>
	
	<h3>Step #3 - Connect with Admin User</h3>
	' . ($postDBAdmin == true ? '<p style="color:green;">The admin user is properly configured. No changes are necessary.</p>' : '') . '
	<p>Some admin functions on this site (including installation, upgrades, etc) require database admin privileges. A standard database user will be created next to handle all other activities on the site.</p>
	
	<p>Database User with Admin Privileges: <input type="text" name="db-admin-user" value="' . $_POST['db-admin-user'] . '" maxlength="18" /></p>
	<p>Password for Admin User: <input type="text" name="db-admin-pass" value="' . htmlspecialchars($_POST['db-admin-pass']) . '" maxlength="64" /></p>
	
	<h3>Step #4 - Connect with Standard User</h3>
	' . ($postDBUser == true ? '<p style="color:green;">The standard user is properly configured. No changes are necessary.</p>' : '') . '
	<p>Note: This section is recommended, but not required. You can leave this section blank. If you fill in a user that doesn\'t exist, the system will create it for you once the admin user is configured correctly.</p>
	
	<p>Database User with Standard Access: <input type="text" name="db-user" value="' . $_POST['db-user'] . '" maxlength="18" /></p>
	<p>Password for User: <input type="text" name="db-pass" value="' . htmlspecialchars($_POST['db-pass']) . '" maxlength="64" /></p>
	
	<p><input type="submit" name="submit" value="Continue" /></p>';
}
else
{
	echo '
	<h1>Installation: Site Configuration</h1>
	
	<h3>Step #1 - Update Global Configuration</h3>
	<p>The global configuration applies to ALL phpTesla applications that you\'re running from the same parent directory.</p>
	
	<p>You can locate the global-config.php file here: ' . dirname(SYS_PATH) . '/global-config.php</p>
	
	<h4>Option #1a: Automatic Update</h4>
	<p>If you want phpTesla to automatically update your configuration file, just press the "Automatic Update" button. Standard users that don\'t need any server-specific customization should use this option.</p>
	<p><input type="submit" name="auto-submit" value="Update Automatically" /></p>
	
	<h4>Option #1b: Manual Update</h4>
	<p>Advanced users might want to set their global configurations manually. To do this, open the global-config.php file and edit the file as necessary. You can base your configurations off of the values provided in the textbox below.</p>
	<p>
		<textarea style="width:100%; height:350px; tab-size:4; -moz-tab-size:4; -ms-tab-size:4; -webkit-tab-size:4;">' . $buildGlobal . '</textarea>
	</p>
	<p>You can find the global-config.php file in : ' . dirname(SYS_PATH) . '/global-config.php</p>
	<p><input type="submit" name="manual-submit" value="I have updated the file manually" /></p>
	';
	
	// Provide hidden post values
	$pList = array("environment", "db-type", "db-host", "db-admin-user", "db-admin-pass", "db-user", "db-pass", "use-hhvm");
	
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