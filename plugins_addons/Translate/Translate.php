<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

------------------------------
------ Translate Plugin ------
------------------------------

This plugin allows you to translate between multiple languages.

Use of the plugin is like this:

	$lang = new Translate("Spanish");
	
	$lang->Welcome		// Prints "Welcome" if there is no substitute in the language provided
	$lang->Yes			// Prints "Si" (Spanish equivalent) if the substitute is provided by the language


You can choose to disable the lookups when you launch Translate.

	Such as $lang->noTranslate();

	
------------------------------
------ Methods Available ------
------------------------------

$object = Translate::setLanguage();

*/

class Translate {
	
	
/****** Class Variables ******/
	public $language = "";
	public $vocab = array();
	
	
/****** Minimize an object's property name ******/
	public function __construct
	(
		$language			// <str> The language that you want to translate.
	,	$modules = array()	// <array> A list of the language modules to load. 
	)						// RETURNS <void>
	
	// $lang->setLanguage("French");		// Loads the French language with the standard vocab module only
	// $lang->setLanguage("French", array("standard", "navigation", "people"));		// Loads three vocabulary modules
	{
		$language = Sanitize::variable($language);
		
		// Load the class into this one
		if(class_exists("Language" . $language))
		{
			$class = "Language" . $language;
			
			// In case the function is loaded 
			if(is_string($modules))
			{
				$modules = array($modules);
			}
			
			// In case the function is called without 
			if(!$modules)
			{
				$modules = array("standard");
			}
			
			// Load each of the language modules that were chosen: e.g. $lang->setLanguage("Spanish", "weather");
			// This would load the LanguageSpanish::weather() module.
			foreach($modules as $module)
			{
				if(method_exists($class, $module))
				{
					$this->vocab = array_merge($this->vocab, call_user_func(array($class, $module)));
				}
			}
		}
	}
	
	
/****** Return vocabulary from the chosen language ******/
	public function __get
	(
		$word		// <str> The word that you want to translate.
	)				// RETURNS <str> The translated word.
	
	// $lang->hello;		// returns "hola" if the language is set to Spanish.
	{
		$lc = strtolower($word);
		
		if(isset($this->vocab[$lc]))
		{
			// If the word is all lowercase, return all caps
			// $lang->hello would return "hola"
			if(ctype_lower($word))
			{
				return $this->vocab[$lc];
			}
			
			// If the word is capitolized, return all caps
			// $lang->HELLO would return "HOLA"
			if(ctype_upper($word))
			{
				return strtoupper($this->vocab[$lc]);
			}
			
			// If the word's first letter is capitolized, return first character capped
			// $lang->Hello would return "Hola"
			if(ctype_upper(substr($word, 0, 1)))
			{
				return ucfirst($this->vocab[$lc]);
			}
			
			// There was a mix of uppercase; just default to the standard
			return $this->vocab[$lc];
		}
		
		return $word;
	}
}



