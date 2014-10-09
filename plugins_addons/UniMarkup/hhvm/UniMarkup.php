<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the UniMarkup Plugin ------
----------------------------------------

This plugin allows content to be written with the UniMarkup language, enabling a variety of syntax that would not otherwise be allowed.


---------------------------------
------ UniMarkup Available ------
---------------------------------

[imp]		// Important: Indicates a strong SEO presence, as well as [generally] bolds the text.
[bold]		// Bold: Bolds the text without indicating any SEO relevance.
[em]		// Emphasize: Emphasizes the text, helping the reader to visually clarify it as important.
[note]		// Note: Provides a side-note with smaller text.
[code]		// Code: Provides a block of text that maintains spacing rules by code.
[link]		// Link: Creates a URL link to another page.
[color]		// Color: Assigns an HTML color or word color to the section.
[img]		// Image: Posts an image.
[quote]		// Quote: Provides a quote block, generally for quoting another user.


-------------------------------
------ Methods Available ------
-------------------------------

// Change standard text to UniMarkup
$text = UniMarkup::parse($text);

// Strip text from all UniMarkup
$text = UniMarkup::strip($text);

*/

abstract class UniMarkup {
	
	
/****** Run UniMarkup on a block of text ******/
# This code was modified from the original source at: http://thesinkfiles.hubpages.com/hub/Regex-for-BBCode-in-PHP
	public static function parse
	(
		string $text		// <str> The text with UniMarkup to parse through.
	): string				// RETURNS <str> The content with HTML tags provided.
	
	// $text = UniMarkup::parse($text);
	{
		// Parse the UniMarkup
		$text = preg_replace('#\[b\](.+)\[\/b\]#iUs', '<span style="font-weight:bold;">$1</span>', $text);
		$text = preg_replace('#\[u\](.+)\[\/u\]#iUs', '<span style="text-decoration:underline;">$1</span>', $text);
		$text = preg_replace('#\[i\](.+)\[\/i\]#iUs', '<span style="font-style:italic;">$1</span>', $text);
		$text = preg_replace('#\[center\](.+)\[\/center\]#iUs', '<div style="text-align:center;">$1</div>', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '<span style="font-size:0.8em;">$1</span>', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '<pre class="code">$1</pre>', $text);
		$text = preg_replace('#\[link\](.+)\[\/link\]#iUs', '<a href="$1" rel="nofollow">$1</a>', $text);
		$text = preg_replace('#\[link\=(.+)\](.+)\[\/link\]#iUs', '<a href="$1" rel="nofollow">$2</a>', $text);
		$text = preg_replace('#\[size\=(.+)\](.+)\[\/size\]#iUs', '<span style="font-size:$1px">$2</span>', $text);
		$text = preg_replace('#\[color\=([\#a-z0-9A-Z]+)\](.+)\[\/color\]#iUs', '<span style="color:$1">$2</span>', $text);
		$text = preg_replace('#\[img\](.+)\[\/img\]#iUs', '<img src="$1" alt="Image" />', $text); 
		$text = preg_replace('#\[quote\=(.+)\](.+)\[\/quote]#iUs', '<div class="quote">$2</div><div class="quote-by">By: $1</div>', $text);
		
		// Comment Syntax
		//$text = preg_replace('#(?<![:&])\#([\w]+?)#iUs', '<a href="' . URL::hashtag_unifaction_com(). '/$1">#$1</a>', $text);
		//$text = preg_replace('#\@([\w]+?)#iUs', '<a href="' . URL::fastchat_social(). '/$1">@$1</a>', $text);
		
		// Return Text
		return $text;
	}
	
	
/****** String UniMarkup from code so that only the original text is provided ******/
	public static function strip
	(
		string $text		// <str> The text to strip UniMarkup from.
	): string				// RETURNS <str> The content without any UniMarkup.
	
	// $text = UniMarkup::strip($text);
	{
		// Strip the UniMarkup
		$text = preg_replace('#\[b\](.+)\[\/b\]#iUs', '$1', $text);
		$text = preg_replace('#\[u\](.+)\[\/u\]#iUs', '$1', $text);
		$text = preg_replace('#\[i\](.+)\[\/i\]#iUs', '$1', $text);
		$text = preg_replace('#\[center\](.+)\[\/center\]#iUs', '$1', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '$1', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '$1', $text);
		$text = preg_replace('#\[link\](.+)\[\/link\]#iUs', '$1', $text);
		$text = preg_replace('#\[link\=(.+)\](.+)\[\/link\]#iUs', '$2', $text);
		$text = preg_replace('#\[size\=(.+)\](.+)\[\/size\]#iUs', '$2', $text);
		$text = preg_replace('#\[color\=([\#a-z0-9A-Z]+)\](.+)\[\/color\]#iUs', '$2', $text);
		$text = preg_replace('#\[img\](.+)\[\/img\]#iUs', '', $text); 
		$text = preg_replace('#\[quote\=(.+)\](.+)\[\/quote]#iUs', '', $text);
		
		// Return Text
		return $text;
	}
	
	
/****** Display a line of UniMarkup Buttons ******/
	public static function buttonLine
	(
		string $elementID = "core_text_box"	// <str> The ID of the element that these buttons will affect.
	): string									// RETURNS <str> OUTPUTS the HTML of a line of UniMarkup buttons.
	
	// echo UniMarkup::buttonLine($elementID);
	{
		$html = '
		<div style="font-size:1.3em; display:inline-block; vertical-align:bottom;">';
		
		// Draw Text Formatting Options
		$html .= '
		<a onclick=\'UniMarkup("' . $elementID . '", "b")\'><span class="icon-bold"></span></a>
		<a onclick=\'UniMarkup("' . $elementID . '", "u")\'><span class="icon-underline"></span></a>
		<a onclick=\'UniMarkup("' . $elementID . '", "i")\'><span class="icon-italic"></span></a>
		&nbsp;';
		
		// Draw Color Options
		$html .= '
		<span class="hover-wrap icon-paint"><div class="hover-div color-draw">
				<div style="background-color:#000000;" onclick=\'UniMarkup("' . $elementID . '", "color", "#000000")\'></div>
				<div style="background-color:#808080;" onclick=\'UniMarkup("' . $elementID . '", "color", "#808080")\'></div>
				<div style="background-color:#c0c0c0;" onclick=\'UniMarkup("' . $elementID . '", "color", "#c0c0c0")\'></div>
				<div style="background-color:#ffffff;" onclick=\'UniMarkup("' . $elementID . '", "color", "#ffffff")\'></div>
				
				<div style="background-color:#ff0000;" onclick=\'UniMarkup("' . $elementID . '", "color", "#ff0000")\'></div>
				<div style="background-color:#ff00ff;" onclick=\'UniMarkup("' . $elementID . '", "color", "#ff00ff")\'></div>
				<div style="background-color:#0000ff;" onclick=\'UniMarkup("' . $elementID . '", "color", "#0000ff")\'></div>
				<div style="background-color:#00ff00;" onclick=\'UniMarkup("' . $elementID . '", "color", "#00ff00")\'></div>
				
				<div style="background-color:#800000;" onclick=\'UniMarkup("' . $elementID . '", "color", "#800000")\'></div>
				<div style="background-color:#800080;" onclick=\'UniMarkup("' . $elementID . '", "color", "#800080")\'></div>
				<div style="background-color:#000080;" onclick=\'UniMarkup("' . $elementID . '", "color", "#000080")\'></div>
				<div style="background-color:#008000;" onclick=\'UniMarkup("' . $elementID . '", "color", "#008000")\'></div>
				
				<div style="background-color:#00ffff;" onclick=\'UniMarkup("' . $elementID . '", "color", "#00ffff")\'></div>
				<div style="background-color:#008080;" onclick=\'UniMarkup("' . $elementID . '", "color", "#008080")\'></div>
				<div style="background-color:#808000;" onclick=\'UniMarkup("' . $elementID . '", "color", "#808000")\'></div>
				<div style="background-color:#ffff00;" onclick=\'UniMarkup("' . $elementID . '", "color", "#ffff00")\'></div>
			</div></span>
		&nbsp;';
		
		/*
		// Draw Font Options
		$html .= '
		<span class="icon-pen"></span>
		&nbsp;';
		*/
		
		// Draw Center Paragraph 
		$html .= '
		<a onclick=\'UniMarkup("' . $elementID . '", "center")\'><span class="icon-paragraph-center"></span></a>
		&nbsp;';
		
		// Draw Link
		$html .= '
		<a onclick=\'UniMarkupAdvanced("' . $elementID . '", "link")\'><span class="icon-link"></span></a>
		&nbsp;';
		
		// Draw Code
		$html .= '
		<a onclick=\'UniMarkup("' . $elementID . '", "code")\'><span class="icon-console"></span></a>';
		
		// Draw Quote
		$html .= '
		<a onclick=\'UniMarkupAdvanced("' . $elementID . '", "quote")\'><span class="icon-quote"></span></a>';
		
		// Draw User and Hashtags
		$html .= '
		&nbsp;
		<a onclick=\'UniMarkupAdvanced("' . $elementID . '", "user")\'><span class="icon-user"></span></a>
		<a onclick=\'UniMarkupAdvanced("' . $elementID . '", "tag")\'><span class="icon-tag"></span></a>';
		
		$html .= '
		</div>';
		
		return $html;
	}
	
	
/****** Run UniMarkup on a block of text using callbacks ******/
# "parse" was benchmarked and found to be faster, so we will use that instead for now. May optimize later.
	public static function runWithCallback
	(
		string $text		// <str> The text with UniMarkup to parse through.
	): string				// RETURNS <str> The content with HTML tags provided.
	
	// $text = UniMarkup::run($text);
	{
		return preg_replace_callback('#\[([\/a-z]+)\]#iUs', array("UniMarkup", "parseData"), $text);
	}
	
	
/****** Run Parsing Functions for UniMarkup ******/
	public static function parseData
	(
		array <int, str> $matches	// <int:str> The matches that are caught by the UniMarkup interpreter during parsing.
	): string				// RETURNS <str> The replacement to use for the match.
	
	// $replacement = UniMarkup::parseData($matches);
	{
		switch($matches[1])
		{
			case "bold": return '<strong>';
			case "/bold": return '</strong>';
		}
		
		return $matches[1];
	}
	
}