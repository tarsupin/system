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
[left]		// Left aligns the text (on a new line).
[right]		// Right aligns the text (on a new line).
[note]		// Note: Provides a side-note with smaller text.
[code]		// Code: Provides a block of text that maintains spacing rules by code.
[url]		// Link: Creates a URL link to another page.
[size]		// Sets the size of text.
[font]		// Sets the font of text.
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
		// NoCode
		$text =  preg_replace_callback(
			'#\[nocode\]((?>(?R)|.)+)\[\/nocode\]#iUs',
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
		$text = preg_replace('#\[left\](.+)\[\/left\]#iUs', '<div style="text-align:left;">$1</div>', $text);
		$text = preg_replace('#\[right\](.+)\[\/right\]#iUs', '<div style="text-align:right;">$1</div>', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '<span style="font-size:0.8em;">$1</span>', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '<pre class="code">$1</pre>', $text);
		$text = preg_replace('#\[url\=(.+)\](.+)\[\/url\]#iUs', '<a href="$1" rel="nofollow">$2</a>', $text);
		$text = preg_replace('#\[url\](.+)\[\/url\]#iUs', '<a href="$1" rel="nofollow">$1</a>', $text);
		$text = preg_replace('#\[size\=(.+)\](.+)\[\/size\]#iUs', '<span style="font-size:$1px;">$2</span>', $text);
		$text = preg_replace('#\[font\=(.+)\](.+)\[\/font\]#iUs', '<span style="font-family:$1;">$2</span>', $text);
		$text = preg_replace('#\[img\](.+)\[\/img\]#iUs', '<img src="$1" alt="Image" />', $text);
		
		// The following tags are often nested. List, color and spoiler parsing is not order sensitive, so the ?R pattern is not needed here.
		do {
			$text = preg_replace('#\[list\](.+)\[\/list\]#iUs', '<ul>$1</ul>', $text, -1, $count);
		} while($count > 0);
		$text = preg_replace('#\[\*\]#iUs', '<li>', $text);
		
		do {
			$text = preg_replace('#\[color\=([\#a-z0-9A-Z]+)\](.+)\[\/color\]#iUs', '<span style="color:$1;">$2</span>', $text, -1, $count);
		} while($count > 0);
		
		do {
			$text = preg_replace('#\[spoiler\=(.+)\](.+)\[\/spoiler\]#iUs', '<div class="spoiler"><div class="spoiler-header" onclick="var el=this.nextSibling; el.style.display = (el.style.display == \'block\' ? \'none\' : \'block\');">$1</div><div class="spoiler-content">$2</div></div>', $text, -1, $count);
		} while($count > 0);
		do {
			$text = preg_replace('#\[spoiler\](.+)\[\/spoiler\]#iUs', '<div class="spoiler"><div class="spoiler-header" onclick="var el=this.nextSibling; el.style.display = (el.style.display == \'block\' ? \'none\' : \'block\');">Spoiler</div><div class="spoiler-content">$1</div></div>', $text, -1, $count);
		} while($count > 0);
		
		do {
			$text = preg_replace('#\[quote\=(.+)\](.+)\[\/quote\]#iUs', '<div class="quote"><div class="quote-by" onclick="var el=this.nextSibling; el.style.display = (el.style.display == \'none\' ? \'block\' : \'none\');">By: $1</div><div class="quote-content">$2</div></div>', $text, -1, $count);
		} while($count > 0);
		do {
			$text = preg_replace('#\[quote\](.+)\[\/quote\]#iUs', '<div class="quote"><div class="quote-by" onclick="var el=this.nextSibling; el.style.display = (el.style.display == \'none\' ? \'block\' : \'none\');">By: ?</div><div class="quote-content">$1</div></div>', $text, -1, $count);
		} while($count > 0);
		
		// Comment Syntax
		$text = preg_replace('#(^|\s)\#([\w-]+?)#iUs', '$1<a href="' . URL::hashtag_unifaction_com(). '/$2">#$2</a>', $text);
		$text = preg_replace('#(^|\s)\@(\w{4,22}?)#iUs', '$1<a href="' . URL::unifaction_social(). '/$2">@$2</a>', $text);
		
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
		// loop is not necessary because the outermost matches are captured and the content removed
		// done first because it eliminates some content
		$text = preg_replace('#\[spoiler\=.+\](?>(?R)|.)+\[\/spoiler\]#iUs', '', $text);
		$text = preg_replace('#\[spoiler\](?>(?R)|.)+\[\/spoiler\]#iUs', '', $text);
		$text = preg_replace('#\[quote\=.+\](?>(?R)|.)+\[\/quote\]#iUs', '', $text);
		$text = preg_replace('#\[quote\](?>(?R)|.)+\[\/quote\]#iUs', '', $text);
		
		// Strip the UniMarkup
		$text = preg_replace('#\[b\](.+)\[\/b\]#iUs', '$1', $text);
		$text = preg_replace('#\[u\](.+)\[\/u\]#iUs', '$1', $text);
		$text = preg_replace('#\[i\](.+)\[\/i\]#iUs', '$1', $text);
		$text = preg_replace('#\[s\](.+)\[\/s\]#iUs', '$1', $text);
		$text = preg_replace('#\[center\](.+)\[\/center\]#iUs', '$1', $text);
		$text = preg_replace('#\[left\](.+)\[\/left\]#iUs', '$1', $text);
		$text = preg_replace('#\[right\](.+)\[\/right\]#iUs', '$1', $text);
		$text = preg_replace('#\[note\](.+)\[\/note\]#iUs', '$1', $text);
		$text = preg_replace('#\[code\](.+)\[\/code\]#iUs', '$1', $text);
		$text = preg_replace('#\[url\=.+\](.+)\[\/url\]#iUs', '$1', $text);
		$text = preg_replace('#\[url\](.+)\[\/url\]#iUs', '$1', $text);
		$text = preg_replace('#\[size\=.+\](.+)\[\/size\]#iUs', '$1', $text);
		$text = preg_replace('#\[font\=.+\](.+)\[\/font\]#iUs', '$1', $text);
		$text = preg_replace('#\[img\].+\[\/img\]#iUs', '', $text);
		
		do {
			$text = preg_replace('#\[list\](.+)\[\/list\]#iUs', '$1', $text, -1, $count);
		} while($count > 0);
		
		do {
			$text = preg_replace('#\[color\=[\#a-z0-9A-Z]+\](.+)\[\/color\]#iUs', '$1', $text, -1, $count);
		} while($count > 0);
		
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
		<a href=\'javascript:UniMarkup("' . $elementID . '", "b")\'><span class="icon-bold" title="Bold [CRTL + B]"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "u")\'><span class="icon-underline" title="Underline [CRTL + U]"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "i")\'><span class="icon-italic" title="Italic [CRTL + I]"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "s")\'><span class="icon-strikethrough" style="font-size:1.3em;" title="Strikethrough [CRTL + S]"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Color Options
		$html .= '
		<span class="hover-wrap icon-paint" title="Color"><div class="hover-div color-draw">
			<div style="background-color:black;" onclick=\'UniMarkup("' . $elementID . '", "color", "black")\'></div>
			<div style="background-color:gray;" onclick=\'UniMarkup("' . $elementID . '", "color", "gray")\'></div>
			<div style="background-color:silver;" onclick=\'UniMarkup("' . $elementID . '", "color", "silver")\'></div>
			<div style="background-color:white;" onclick=\'UniMarkup("' . $elementID . '", "color", "white")\'></div>
			
			<div style="background-color:red;" onclick=\'UniMarkup("' . $elementID . '", "color", "red")\'></div>
			<div style="background-color:magenta;" onclick=\'UniMarkup("' . $elementID . '", "color", "magenta")\'></div>
			<div style="background-color:blue;" onclick=\'UniMarkup("' . $elementID . '", "color", "blue")\'></div>
			<div style="background-color:lime;" onclick=\'UniMarkup("' . $elementID . '", "color", "lime")\'></div>
			
			<div style="background-color:maroon;" onclick=\'UniMarkup("' . $elementID . '", "color", "maroon")\'></div>
			<div style="background-color:purple;" onclick=\'UniMarkup("' . $elementID . '", "color", "purple")\'></div>
			<div style="background-color:navy;" onclick=\'UniMarkup("' . $elementID . '", "color", "navy")\'></div>
			<div style="background-color:green;" onclick=\'UniMarkup("' . $elementID . '", "color", "green")\'></div>
			
			<div style="background-color:aqua;" onclick=\'UniMarkup("' . $elementID . '", "color", "aqua")\'></div>
			<div style="background-color:teal;" onclick=\'UniMarkup("' . $elementID . '", "color", "teal")\'></div>
			<div style="background-color:olive;" onclick=\'UniMarkup("' . $elementID . '", "color", "olive")\'></div>
			<div style="background-color:yellow;" onclick=\'UniMarkup("' . $elementID . '", "color", "yellow")\'></div>
			<div style="width:100%; text-align:center;"><a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "color")\'>Other Color</a></div>
		</div></span>';
		
		// Draw Font Options
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "size")\'><span class="icon-size" title="Size" style="font-size:1.3em;"></span></a>
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "font")\'><span class="icon-pen" title="Font"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Center Paragraph 
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "left")\'><span class="icon-paragraph-left" title="Left"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "center")\'><span class="icon-paragraph-center" title="Center"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "right")\'><span class="icon-paragraph-right" title="Right"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Image
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "img")\'><span class="icon-image" title="Image [CRTL + P]"></span></a>';
		
		// Draw URL
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "url")\'><span class="icon-link" title="Link [CRTL + L]"></span></a>';
		
		// Draw Quote
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "quote")\'><span class="icon-quote" title="Quote [CRTL + Q]"></span></a>
		&nbsp;&nbsp;';
		
		// Draw List
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "list")\'><span class="icon-list" title="List"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Spoiler
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "spoiler")\'><span class="icon-eye" title="Spoiler [CRTL + H]"></span></a>
		&nbsp;&nbsp;';
		
		// Draw Code
		$html .= '
		<a href=\'javascript:UniMarkup("' . $elementID . '", "code")\'><span class="icon-console" title="Code"></span></a>
		<a href=\'javascript:UniMarkup("' . $elementID . '", "nocode")\'><span class="icon-text-edit" title="NoCode"></span></a>
		&nbsp;&nbsp;';
		
		// Draw User and Hashtags
		$html .= '
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "user")\'><span class="icon-user" title="User"></span></a>
		<a href=\'javascript:UniMarkupAdvanced("' . $elementID . '", "tag")\'><span class="icon-tag" title="Hashtag"></span></a>';
		
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
