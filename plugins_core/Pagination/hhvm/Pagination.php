<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the Pagination Plugin ------
-----------------------------------------

This plugin is designed to simplify pagination. Pagination refers to the list of pages you can click through, such as a numerical page list from 1 to 5 (sometimes followed with " ... last", etc.).

To prepare this pagination, you need three inputs:
	
	1. The total number of results.
	2. The number of results to show per page.
	3. The current page being accessed.
	
	
Here's a simple working example to demonstrate it:
	
	// Show 20 rows of 530 rows total, starting from the first page.
	$paginate = new Pagination(530, 20, 1);
	var_dump($paginate);
	

----------------------------------------
------ Setting up your pagination ------
----------------------------------------

// Prepare Variables
$resultsPerPage = 20;
$currentPage = $_GET['page'];

// Next, we need the total number of results
$numberOfResults = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM table WHERE column=?", array("value"));

// Construct the pagination object
$paginate = new Pagination($numberOfResults, $resultsPerPage, $currentPage);


---------------------------------
------ The Pagination Mode ------
---------------------------------
If you want to customize the appearance of your pagination, you can switch modes. You can do this by running:

	$paginate->setMode("standard", 5);		// Sets "standard" mode with a range of "5"
	$paginate->setMode("division", 8);		// Sets "division" mode with range of "8"
	
	
-----[ Standard Mode ]-----

The standard mode shows a simple range of pages from your current page as a pointer.

For example, if you are on page 113 of 315:
	
	$page = new Pagination(315, 1, 113, "standard", 3);
	
	(Range of 3) 1, 110, 111, 112, {113}, 114, 115, 116, 315
	(Range of 5) 1, 108, 109, 110, 111, 112, {113}, 114, 115, 116, 117, 118, 315

	
-----[ Division Mode ]-----

The "division" mode instead breaks up the pagination into major segments between the lowest and highest pages available, making it easier to find content in the middle.

For example, if you are you on page 113 of 315:
	
	$page = new Pagination(315, 1, 113, "division", 4);
	
	(Range of 4) 1, 78, 112, {113}, 114, 157, 236, 315
	(Range of 7) 1, 45, 90, 112, {113}, 114, 135, 180, 225, 270, 315
	
Notice how there are variable pages (such as 78) that skip wide gaps.


-----[ Ranges ]------

Ranges are not a mode, but rather are relevant to each mode. The higher a range value is, the more pages will appear.

For example, a range of 1 may only show the first, last, and current pages. However, a range of 5 will show several more (in addition to those).


-----------------------------------
------ Displaying Pagination ------
-----------------------------------

foreach($paginate->pages as $page)
{
	if($paginate->currentPage == $page)
	{
		echo '[' . $page . ']';
	}
	else
	{
		echo '<a href="/this-page?page=' . $page . '">' . $page . '</a>';
	}
}

-------------------------------
------ Methods Available ------
-------------------------------

$page = new Pagination($numberOfResults, $resultsPerPage, $currentPage, [$mode], [$pageRange]);

$page->setMode($mode, [$pageRange]);

*/

class Pagination {
	
	
/****** Plugin Variables ******/
	public $pages = array();
	public $currentPage = 1;
	public $highestPage = 1;
	public $queryLimit = "";
	
	
/****** Construct the Pagination Object ******/
	public function __construct
	(
		int $numberOfResults		// <int> The total number of results to paginate.
	,	int $resultsPerPage			// <int> The number of results to show per page.
	,	int $currentPage			// <int> The current page.
	,	string $mode = "standard"		// <str> The pagination mode to run.
	,	int $pageRange = 4			// <int> The range of pagination to use.
	): void							// RETURNS <void>
	
	// $page = new Pagination($numberOfResults, $resultsPerPage, $currentPage, [$mode], [$pageRange]);
	{
		// Set Parameters
		$this->highestPage = max(1, (int) ceil($numberOfResults / $resultsPerPage));
		$this->currentPage = min(max(1, $currentPage), $this->highestPage);
		
		// Get the SQL segment that can be used to indicate paging limits
		$limitStart = min(($this->currentPage - 1) * $resultsPerPage, $this->highestPage * $resultsPerPage);
		$limitMax = min($resultsPerPage, $numberOfResults);
		
		$this->queryLimit = " LIMIT " . ($limitStart + 0) . ", " . ($resultsPerPage);
		
		// Run the appropriate paging algorithm
		$this->setMode($mode, $pageRange);
	}
	
	
/****** Set the type of pagination to use ******/
	public function setMode
	(
		string $mode				// <str> The pagination mode to run.
	,	int $pageRange = 4		// <int> The range of pagination to use.
	): void						// RETURNS <void>
	
	// $page->setMode($mode, [$pageRange]);
	{
		// Refresh the pages array
		$this->pages = array();
		
		// Add First Page
		$this->pages[1] = 1;
		
		// Add Last Page
		if($this->highestPage > 1)
		{
			$this->pages[$this->highestPage] = $this->highestPage;
		}
		
		// If the highest page is less than or equal to the range allowed, show all pages
		if($this->highestPage <= $pageRange)
		{
			for($a = 1;$a <= $this->highestPage;$a++)
			{
				$this->pages[$a] = $a;
			}
			
			sort($this->pages);
			return;
		}
		
		// For Standard Pagination
		if($mode == "standard")
		{
			// Set Local Page Tags
			for($a = max($this->currentPage - $pageRange, 1);$a <= min($this->currentPage + $pageRange, $this->highestPage);$a++)
			{
				$this->pages[$a] = $a;
			}
		}
		
		// For Divisional Pages
		else if($mode == "division")
		{
			// Set the Major Divisions
			$min = (int) max(1, floor($this->highestPage / $pageRange));
			$max = (int) max(1, ceil($this->highestPage / $pageRange));
			
			for($a = $min;$a < $this->highestPage;$a += $max)
			{
				//$a = (int) $a;	// $a is being set as (float) for some reason. This returns to int.
				$this->pages[$a] = $a;
			}
			
			// Set Local Page Tags
			$min = max($this->currentPage - 2, 1);
			$max = min($this->currentPage + 2, $this->highestPage);
			
			for($a = $min;$a <= $max;$a++)
			{
				$this->pages[$a] = $a;
			}
		}
		
		// Sort the Pagination Array
		sort($this->pages);
	}
}