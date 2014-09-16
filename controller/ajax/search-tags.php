<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------------------
------ About the search-tags script ------
------------------------------------------

This script will allow search results to populate in an automatic dropdown list. These results pull from the tags that are available.

*/

// Append a wildcard if the last character isn't whitespace
if(strlen($_POST['search']) > 2 and $_POST['search'] == rtrim($_POST['search']))
{
	$_POST['search'] .= "*";
}

// Retrieve a list of tags
$searchResults = Tags::search($_POST['search'], true, 0, 5);

// Prepare the search dropdown
echo '<ul>';

foreach($searchResults as $result)
{
	echo '
	<li><a class="searchSel" href="/?tag=' . $result['id'] . '" onmousedown="window.location=\'/?tag=' . $result['id'] . '\'">' . $result['title'] .  '</a></li>';
}

echo '</ul>';