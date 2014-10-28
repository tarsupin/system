<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Installation Header
require(SYS_PATH . "/controller/includes/install_header.php");

// Make sure you have a designated handle
if(!$ownerHandle = Cookie::get("admin-handle", ""))
{
	header("Location: /install/connect-handle"); exit;
}

// Prepare Values
$_POST['unifaction-api-key'] = (isset($_POST['unifaction-api-key']) ? Sanitize::text($_POST['unifaction-api-key']) : "");

// If we are connected to UniFaction, we can go to the next page
if($siteData = Network::get("unifaction"))
{
	if($response = Connect::to("unifaction", "IsSiteConnected"))
	{
		Alert::saveSuccess("Connected", "You are connected to UniFaction!");
		header("Location: /install/app-custom"); exit;
	}
}

// Run the Form
if(Form::submitted("install-connect-auth"))
{
	// Create the necessary network_data value
	$key = Network::setData("unifaction", "UniFaction", URL::unifaction_com(), $_POST['unifaction-api-key'], true);
	
	// Set the appropriate clearance level for the auth server
	Network::setClearance("unifaction", 9);
	
	// Check if we are now connected to Auth
	$response = Connect::to("unifaction", "IsSiteConnected");
	
	// "/api/private", array("run" => "validate-site"), $siteData['site_key']);
	
	if($response == true)
	{
		Alert::saveSuccess("UniFaction Connection", "You have successfully connected to UniFaction!");
		header("Location: /install/app-custom"); exit;
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
<div id="content" class="content-open">' . Alert::display() . '
<form class="uniform" action="/install/connect-auth" method="post">' . Form::prepare("install-connect-auth");

// Display the Page
echo '
<h1>Installation: Confirm UniFaction Key</h1>

<h3>Step #1 - Log into @' . $ownerHandle . ' and retrieve your Site\'s API Key</h3>
<p>Now that you\'ve set up the database on your site, you will need to connect to UniFaction with your site\'s API key. To get the key, follow these steps:</p>
<p>&nbsp; &nbsp; &bull; <a href="http://unifaction.com/login">Log into your UniFaction account</a></p>
<p>&nbsp; &nbsp; &bull; Switch to @' . $ownerHandle . '\'s profile</p>
<p>&nbsp; &nbsp; &bull; Go to your <a href="http://unifaction.com/user-panel">User Panel</a></p>
<p>&nbsp; &nbsp; &bull; Click on <a href="http://unifaction.com/user-panel/my-sites">"My Sites"</a></p>
<p>&nbsp; &nbsp; &bull; Click on the <a href="http://unifaction.com/user-panel/my-sites?confirm=' . SITE_HANDLE . '">Confirm ' . SITE_HANDLE . '</a> button.</p>
<p>&nbsp; &nbsp; &bull; Copy the API Key.</p>

<h3>Step #2 - Enter your Site\'s API Key</h3>

<p>Once you\'ve acquired your Site\'s API key from the steps above, paste it into the textbox below.</p>

<p>
	Your UniFaction API Key:<br />
	<textarea name="unifaction-api-key" style="width:95%; height:80px;">' . htmlspecialchars($_POST['unifaction-api-key']) . '</textarea>
<p>

<p><input type="submit" name="submit" value="Verify API Key" /></p>';

echo '
</form>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");