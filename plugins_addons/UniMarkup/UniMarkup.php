<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the UniMarkup Plugin ------
----------------------------------------

This plugin allows content to be written with the UniMarkup language, enabling a variety of syntax that would not otherwise be allowed.

Using UniMarkup instead of HTML directly will allow us to whitelist the content that gets added into the system. This is a much more secure way to handle the system, and also allows us to make the markup simpler.

There are two important UniMarkup methods to use.

	1. The UniMarkup::parse($text) method will take a box of text and convert it to special HTML.
	
	2. The UniMarkup::strip($text) method will convert it to text without any HTML or UniMarkup. This is used when you need to show a description or highlight of the full text before the actual page.
	

---------------------------------
------ UniMarkup Available ------
---------------------------------

[nocode]	// NoCode: Prevents the text from being parsed.
[b]			// Bold: Bolds the text without indicating any SEO relevance.
[u]			// Underlines the text.
[i]			// Italicizes the text.
[s]			// Strikes the text out.
[center]	// Centers the text (on a new line).
[note]		// Note: Provides a side-note with smaller text.
[code]		// Code: Provides a block of text that maintains spacing rules by code.
[url]		// Link: Creates a URL link to another page.
[link]		// Link: Creates a URL link to another page.
[size]		// Sets the size of text.
[color]		// Color: Assigns an HTML color or word color to the section.
[img]		// Image: Posts an image.
[quote]		// Quote: Provides a quote block, generally for quoting another user.
[list]		// Bulleted list.
[*]			// List item.
[spoiler]	// Spoiler: Hides content from view, can be opened by clicking on its header.


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
		$text		// <str> The text with UniMarkup to parse through.
	)				// RETURNS <str> The content with HTML tags provided.
	
	// $text = UniMarkup::parse($text);
	{
		//Benchmark::get();
	
		// NoCode
		$text =  preg_replace_callback(
			'#\[nocode\](.+)\[\/nocode\]#iUs',
			create_function(
				'$matches',
				'return str_replace(array("[","]"),array("&#91;","&#93;"),$matches[1]);'
			),
			$text
		);
	
		// Parse the UniMarkup
		$text = preg_replace('#\[b\](.+)\[\/b\]#iUs', '<span style="font-weight:bold;">$1</span>', $text);
		$text = preg_replace('#\[u\](.+)\[\/u\]#iUs', '<span style="text-decoration:underline;">$1</span>', $text);
		$text = preg_replace('#\[i\](.+)\[\/i\]#iUs', '<span style="font-style:italic;">$1</span>', $text);
		$text = preg_replace('#\[s\](.+)\[\/s\]#iUs', '<span style="text-decoration:line-through;">$1</span>', $text);
		$text = preg_replace('#\[center\](.+)\[\/center\]#iUs', '<div style="text-align:center;">$1</div>', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '<span style="font-size:0.8em;">$1</span>', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '<pre class="code">$1</pre>', $text);
		$text = preg_replace('#\[url\=(.+)\](.+)\[\/url\]#iUs', '<a href="$1" rel="nofollow">$2</a>', $text);
		$text = preg_replace('#\[link\=(.+)\](.+)\[\/link\]#iUs', '<a href="$1" rel="nofollow">$2</a>', $text);
		$text = preg_replace('#\[size\=(.+)\](.+)\[\/size\]#iUs', '<span style="font-size:$1px;">$2</span>', $text);
		$text = preg_replace('#\[img\](.+)\[\/img\]#iUs', '<img src="$1" alt="Image" />', $text);
		
		// Quotes, lists, colors and spoilers are often nested. List, color and spoiler parsing is not order sensitive, so the ?R pattern is not needed here.
		do {
			$text = preg_replace('#\[list\](.+)\[\/list\]#iUs', '<ul>$1</ul>', $text, -1, $count);
		} while($count > 0);
		$text = preg_replace('#\[\*\]#iUs', '<li>', $text);
		
		do {
			$text = preg_replace('#\[color\=([\#a-z0-9A-Z]+)\](.+)\[\/color\]#iUs', '<span style="color:$1;">$2</span>', $text, -1, $count);
		} while($count > 0);
		
		do {
			$text = preg_replace('#\[spoiler\=(.+)\](.+)\[\/spoiler\]#iUs', '<div class="spoiler-header" onclick="var el=this.nextSibling; el.style.display = (el.style.display == \'block\' ? \'none\' : \'block\');">$1</div><div class="spoiler-content">$2</div>', $text, -1, $count);
		} while($count > 0);
		
		do {
			$text = preg_replace('#\[quote\=(.+)\](((?R)|.)+)\[\/quote\]#iUs', '<div class="quote">$2</div><div class="quote-by">By: $1</div>', $text, -1, $count);
		} while($count > 0);
		
		// Comment Syntax
		//$text = preg_replace('#(?<![:&])\#([\w]+?)#iUs', '<a href="' . URL::hashtag_unifaction_com(). '/$1">#$1</a>', $text);
		//$text = preg_replace('#\@([\w]+?)#iUs', '<a href="' . URL::unifaction_social(). '/$1">@$1</a>', $text);
		
		//Benchmark::get();
		//Benchmark::graph();
		
		// Return Text
		return $text;
	}
	
	
/****** String UniMarkup from code so that only the original text is provided ******/
	public static function strip
	(
		$text		// <str> The text to strip UniMarkup from.
	)				// RETURNS <str> The content without any UniMarkup.
	
	// $text = UniMarkup::strip($text);
	{
		// Strip the UniMarkup
		$text = preg_replace('#\[b\](.+)\[\/b\]#iUs', '$1', $text);
		$text = preg_replace('#\[u\](.+)\[\/u\]#iUs', '$1', $text);
		$text = preg_replace('#\[i\](.+)\[\/i\]#iUs', '$1', $text);
		$text = preg_replace('#\[s\](.+)\[\/s\]#iUs', '$1', $text);
		$text = preg_replace('#\[center\](.+)\[\/center\]#iUs', '$1', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '$1', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '$1', $text);
		$text = preg_replace('#\[url\=(.+)\](.+)\[\/url\]#iUs', '$2', $text);
		$text = preg_replace('#\[link\=(.+)\](.+)\[\/link\]#iUs', '$2', $text);
		$text = preg_replace('#\[size\=(.+)\](.+)\[\/size\]#iUs', '$2', $text);
		$text = preg_replace('#\[img\](.+)\[\/img\]#iUs', '', $text); 
		do {
			$text = preg_replace('#\[list\](.+)\[\/list\]#iUs', '$1', $text);
		} while($count > 0);
		do {
			$text = preg_replace('#\[color\=([\#a-z0-9A-Z]+)\](((?R)|.)+)\[\/color\]#iUs', '$2', $text, -1, $count);
		} while($count > 0);
		$text = preg_replace('#\[spoiler\=(.+)\](((?R)|.)+)\[\/spoiler\]#iUs', '', $text);
		$text = preg_replace('#\[quote\=(.+)\](((?R)|.)+)\[\/quote\]#iUs', '', $text);
		
		// Return Text
		return $text;
	}
	
	
/****** Display a line of UniMarkup Buttons ******/
	public static function buttonLine
	(
		$elementID = "core_text_box"	// <str> The ID of the element that these buttons will affect.
	)									// RETURNS <str> OUTPUTS the HTML of a line of UniMarkup buttons.
	
	// echo UniMarkup::buttonLine($elementID);
	{
		$html = '
		<div style="font-size:1.3em; display:inline-block; vertical-align:bottom;">';
		
		// Draw Text Formatting Options
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "b")\'><span class="icon-bold"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "u")\'><span class="icon-underline"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "i")\'><span class="icon-italic"></span></a>
		&nbsp;&nbsp;';
		
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
		&nbsp;&nbsp;';
		
		/*
		// Draw Font Options
		$html .= '
		<span class="icon-pen"></span>
		&nbsp;&nbsp;';
		*/
		
		// Draw Center Paragraph 
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "center")\'><span class="icon-paragraph-center"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Image
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "img")\'><span class="icon-image"></span>';
		
		// Draw URL
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "url")\'><span class="icon-link"></span></a>';
		
		// Draw List
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "list")\'><span class="icon-list"></span>
		&nbsp;&nbsp;';
		
		// Draw Quote
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "quote")\'><span class="icon-quote"></span></a>';
		
		// Draw Spoiler
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "spoiler")\'><span class="icon-eye"></span></a>';
		
		// Draw Code
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "code")\'><span class="icon-console"></span></a>';
		
		// Draw User and Hashtags
		$html .= '
		&nbsp;&nbsp;
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "user")\'><span class="icon-user"></span></a>
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "tag")\'><span class="icon-tag"></span></a>';
		
		$html .= '
		</div>';
		
		return $html;
	}
	
	
/****** Run UniMarkup on a block of text using callbacks ******/
# "parse" was benchmarked and found to be faster, so we will use that instead for now. May optimize later.
	public static function runWithCallback
	(
		$text		// <str> The text with UniMarkup to parse through.
	)				// RETURNS <str> The content with HTML tags provided.
	
	// $text = UniMarkup::run($text);
	{
		return preg_replace_callback('#\[([\/a-z]+)\]#iUs', array("UniMarkup", "parseData"), $text);
	}
	
	
/****** Run Parsing Functions for UniMarkup ******/
	public static function parseData
	(
		$matches	// <int:str> The matches that are caught by the UniMarkup interpreter during parsing.
	)				// RETURNS <str> The replacement to use for the match.
	
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
