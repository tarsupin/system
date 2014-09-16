<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the search-bar script ------
-----------------------------------------

This script will allow search results to populate in an automatic dropdown list. These results pull from the search entries that you can create or modify in your search engine panel.

*/

$search = new Search($_POST['search']);

echo '<ul>';

foreach($search->results as $result)
{
	echo '
	<li><a class="searchSel" href="' . $result['url_path'] . '" onmousedown="window.location=\'' . $result['url_path'] . '\'">' . $result['entry'] .  '</a></li>';
}

echo '</ul>';