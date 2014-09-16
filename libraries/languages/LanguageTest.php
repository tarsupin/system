<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class LanguageBrint {

/****** LanguageBrint Class ******
* This class provides the language translations for the "Brint" language.
* 
****** Example of using this class ******
	
	
****** Methods Available ******
* LanguageBrint::standard()
*/
	
	
/****** The "standard" module ******/
	public static function standard (
	)						// RETURNS <str:str>
	
	// $vocab = LanguageBrint::standard();
	{
		return array(
			"hello"			=> "wazzap"
		,	"interesting"	=> "like totally"
		,	"fun"			=> "zigzug"
		);
	}
}



