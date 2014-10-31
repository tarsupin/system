<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------------
------ About the search-user-handle script ------
-------------------------------------------------

This script will allow the users registered on the site to be populated into an automatic dropdown list when you start typing their user handle. These results pull from the users list.

When using this function, you'll have to create a script like this:

	function UserHandle(handle)
	{
		var a = document.getElementById("someID");
		a.value = handle;
	}
	
The function UserHandle will then inject the user handle into the designated element.

*/

// Prepare a response
header('Access-Control-Allow-Origin: *');

$search = new Search($_POST['search'], "users");

echo '<ul>';

foreach($search->results as $result)
{
	echo '
	<li><a class="searchSel" href="javascript:UserHandle(\'' . $result['handle'] . '\');" onmousedown="javascript:UserHandle(\'' . $result['handle'] . '\');">' . $result['entry'] .  '</a></li>';
}

echo '</ul>';