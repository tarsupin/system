<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------------
------ About the LangInterpret Plugin ------
--------------------------------------------

This plugin takes text and tries to understand it as much as possible.


// Breaks the phrase into words
isNoun				// thing or person
	isPerson			
	isPlace
	isObject
	isIdea
	isConcrete			// nouns you can perceive with senses: banana, light, sun
	isAbstract			// nouns you can't perceive
	isProperNoun		// names specific people, places or things
	isCommonNoun		// names places or things that are not specific
isPronoun			// replaces a noun
	isMe
	isYou
	isThirdPerson
isVerb				// action or state
	isAction		// lexical
	isAuxilary		// be, have must
isAdverb			// describes a verb, adjective, or adverb (very, quickly, silently, etc)
isAdjective			// describes a noun (two, good, interesting, etc)
isPreposition		// links a noun to another word (to, around, after, at, above, etc)
	isPosition
isConjunction		// joins clauses or sentences (and, but, when, though, however, etc)
isInterjection		// short exclamation (oh!, ouch!, well, etc)
isUnknown

isQuestion			// who, when, where, what, why, how, is/are, can, may

isAntonym
isSynonym
isAmbiguous


isCollective		// bunch, group, band, village
isSingular			// cat, hero, match
isPlural			// cats, heroes, matches

isPossessive		// Mom's car, student's book


// Potential List
isNegative			// negative tone, anger, hostility, etc
isPositive			// positive tone, uplifting, joyful, etc

isPassive
isActive

asksQuestion

hasCaps
hasPunctuation
numberWords
characterCount

hasMispelledWords
readingLevel

isFirstPerson
isSecondPerson
isThirdPerson

isInterrogative		// question, "where did it go?"
isExclamatory		// emotional expression
isDeclarative		// statement, such as "there is no spoon"
isImperative		// command, such as "go do this thing for me!"

isDirectQuestion		// "what time is it?"
isIndirectQuestion		// "I wonder what time it is" and "They asked whether I was nearby."

sentanceType		// exclamation, declaration, question


-------------------------------
------ Methods Available ------
-------------------------------



*/

class LangInterpret {


/****** Plugin Variables ******/
	
	// Important Storage
	public $text = "";						// <str> The text being interpreted.
	public $textSanitized = "";				// <str> The text after being sanitized.
	
	// General information about the text
	public $wordCount = 0;					// <int> The number of words in the text.
	
	// Stores Large Data
	public $wordList = array();				// <array> The list of words in the text.
	public $relevantWords = array();		// <array> The list of relevant words.
	public $relevantWordGroups = array();	// <array> The list of relevant word groupings.
	
	// General information about the text
	public $characterCount = 0;			// <int> The number of characters contained in the text.
	public $letterCount = 0;			// <int> The number of letters contained in the text.
	public $numberCount = 0;			// <int> The number of numbers contained in the text.
	public $punctuationCount = 0;		// <int> The number of simple punctuation characters in the text.
	public $symbolCount = 0;			// <int> The number of symbols in the text.
	
	public $vowelCount = 0;				// <int> The number of vowels in the text.
	public $consonantCount = 0;			// <int> The number of consonants in the text.
	
	public $capitalLetterCount = 0;		// <int> The number of capitalized letters.
	public $capitalWordCount = 0;		// <int> The number of capitalized words (first letter).
	public $allCapsWordCount = 0;		// <int> The number of fully capitalized words.
	
	public $spaceCount = 0;				// <int> The number of spaces in the text.
	public $tabCount = 0;				// <int> The number of tabs in the text.
	public $whitespaceCount = 0;		// <int> The number of whitespace entries in the text.
	
	// Tense
	public $pastTenseCount = 0;			// <int> The number of words that indicate past tense.
	public $presentTenseCount = 0;		// <int> The number of words that indicate present tense.
	public $futureTenseCount = 0;		// <int> The number of words that indicate future tense.
	
	// Attitude
	public $negativeCount = 0;			// <int> The number of words that indicate anger, hostility, negativity.
	public $positiveCount = 0;			// <int> The number of words that indicate positivity.
	
	// Derived Information
	public $nounCount = 0;				// <int> People, places, or things.
	public $pronounCount = 0;			// <int> Replaces a noun.
	public $verbCount = 0;				// <int> Action or state.
	public $adverbCount = 0;			// <int> Describes a verb, adjective, or another adverb.
	public $adjectiveCount = 0;			// <int> Describes a noun.
	public $prepositionCount = 0;		// <int> Links a noun to another word.
	public $conjunctionCount = 0;		// <int> Joins clauses or sentances.
	public $interjectionCount = 0;		// <int> Short exclamation.
	
	// Specific Derivations for Nouns
	public $properNounCount = 0;		// <int> Names specific people, places, or things.
	public $commonNounCount = 0;		// <int> Names non-specific people, places, or things.
	public $concreteNounCount = 0;		// <int> Nouns you can perceive.
	public $abstractNounCount = 0;		// <int> Nouns you cannot percieve with your senses.
	
	public $placesCount = 0;			// <int> Nouns that are places.
	public $peopleCount = 0;			// <int> Nouns that are people.
	public $objectCount = 0;			// <int> Nouns that are objects.
	
	public $singularNounCount = 0;		// <int> Nouns that name one person, place, thing, or idea.
	public $pluralNounCount = 0;		// <int> Nouns that name multiple things.
	public $collectiveNounCount = 0;	// <int> Nouns that are a group of something (gaggle, flock, group, etc)
	public $possessiveNounCount = 0;	// <int> Nouns that show ownership.
	
	// Specific Derivations for Pronouns
	public $firstPersonCount = 0;		// <int> References to "me" or "I"
	public $secondPersonCount = 0;		// <int> References to "you"
	public $thirdPersonCount = 0;		// <int> References to third party words: "he", "she", "they"
	public $thirdPersonSingleCount = 0;	// <int> References to third party words: "he", "she"
	public $thirdPersonGroupCount = 0;	// <int> References to third party groups: "they"
	
	// Specific Derivations for Verbs
	public $lexicalVerbCount = 0;		// <int> Action verbs
	public $auxilaryVerbCount = 0;		// <int> be, must, have
	
	// Stores Large Data
	public $characterList = array();	// <array> The list of characters in the text.
	
	
/****** Plugin Constructor ******/
	public function __construct
	(
		$text		// <str> The text to interpret
	)				// RETURNS <void>
	
	// $phrase = new LangInterpret();
	{
		$this->text = $text;
		$this->textSanitized = Sanitize::variable($text, ",.<>/?;:'\"[]{}-=_+()!@#$%^&*`~ 	");
		
		// Get the word list
		$this->getWordList();
		$this->getRelevantWords();
	}
	
	
/****** Get the words in this text ******/
	public function getWordList (
	)				// RETURNS <void>
	
	// $phrase->getWordList()
	{
		// Separate into words
		$this->textSanitized = str_replace("	", " ", $this->textSanitized);
		$findDouble = true;
		
		while($findDouble == true)
		{
			$findDouble = false;
			
			if(strpos($this->textSanitized, "  "))
			{
				$this->textSanitized = str_replace("  ", " ", $this->textSanitized, $count);
				// $this->whitespaceCount -= $count;
				$findDouble = true;
			}
		}
		
		// Separate into individual words
		$stripText = Sanitize::word($this->textSanitized, " 1234567890");
		$this->wordList = explode(" ", $stripText);
		
		$this->wordCount = count($this->wordList);
	}
	
	
/****** Get relevant words from text ******/
	public function getRelevantWords (
	)				// RETURNS <void>
	
	// $phrase->getRelevantWords()
	{
		// Prepare the relevant words
		$relevant = array();
		$wordPull = "";
		
		// Retrieve the list of words to remove from consideration
		$eliminate = array("a", "able", "about", "above", "abroad", "according", "accordingly", "across", "actually", "adj", "after", "afterwards", "again", "against", "ago", "ahead", "ain't", "all", "allow", "allows", "almost", "alone", "along", "alongside", "already", "also", "although", "always", "am", "amid", "amidst", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't", "...");
		
		// Cycle through each word and retrieve information about it
		foreach($this->wordList as $word)
		{
			if(in_array(strtolower($word), $eliminate))
			{
				if($wordPull != "")
				{
					$this->relevantWordGroups[] = $wordPull;
				}
				
				$wordPull = "";
			}
			else
			{
				$this->relevantWords[] = $word;
				
				$wordPull = $wordPull == "" ? $word : $wordPull . " " . $word;
			}
		}
		
		if($wordPull != "")
		{
			$this->relevantWordGroups[] = $wordPull;
		}
		
		var_dump($this->relevantWordGroups);
	}
	
/****** Get Simple Counts ******/
	public function retrieveBase (
	)				// RETURNS <void>
	
	// $phrase->retrieveBase()
	{
		$charArray = array();
		$smallCharArray = array();
		
		// Get the Character List
		$quickList = count_chars($this->text, 0);
		
		foreach($quickList as $key => $count)
		{
			$charKey = chr($key);
			
			$charArray[$charKey] = $count;
			
			if($count > 0)
			{
				$this->characterList[$charKey] = $count;
			}
		}
		
		// Get the Small Character List
		$smallList = count_chars(strtolower(Sanitize::word($this->text)), 0);
		
		foreach($smallList as $key => $count)
		{
			$charKey = chr($key);
			
			$smallCharArray[$charKey] = $count;
		}
		
		// Get Character Type Counts
		$this->vowelCount +=
				$smallCharArray['a']
			+	$smallCharArray['e']
			+	$smallCharArray['i']
			+	$smallCharArray['o']
			+	$smallCharArray['u'];
		
		$this->consonantCount +=
				$smallCharArray['b']
			+	$smallCharArray['c']
			+	$smallCharArray['d']
			+	$smallCharArray['f']
			+	$smallCharArray['g']
			+	$smallCharArray['h']
			+	$smallCharArray['j']
			+	$smallCharArray['k']
			+	$smallCharArray['l']
			+	$smallCharArray['m']
			+	$smallCharArray['n']
			+	$smallCharArray['p']
			+	$smallCharArray['q']
			+	$smallCharArray['r']
			+	$smallCharArray['s']
			+	$smallCharArray['t']
			+	$smallCharArray['v']
			+	$smallCharArray['w']
			+	$smallCharArray['x']
			+	$smallCharArray['y']
			+	$smallCharArray['z'];
		
		$this->letterCount = $this->vowelCount + $this->consonantCount;
		
		$this->numberCount +=
				$charArray['1']
			+	$charArray['2']
			+	$charArray['3']
			+	$charArray['4']
			+	$charArray['5']
			+	$charArray['6']
			+	$charArray['7']
			+	$charArray['8']
			+	$charArray['9']
			+	$charArray['0'];
			
		$this->punctuationCount +=
				$charArray[',']
			+	$charArray['.']
			+	$charArray['?']
			+	$charArray[':']
			+	$charArray[';']
			+	$charArray['-']
			+	$charArray['\'']
			+	$charArray['"']
			+	$charArray['(']
			+	$charArray[')']
			+	$charArray['[']
			+	$charArray[']']
			+	$charArray['!'];
		
		$this->symbolCount +=
				$charArray['@']
			+	$charArray['#']
			+	$charArray['$']
			+	$charArray['%']
			+	$charArray['^']
			+	$charArray['&']
			+	$charArray['*']
			+	$charArray['{']
			+	$charArray['}']
			+	$charArray['_']
			+	$charArray['-']
			+	$charArray['+']
			+	$charArray['=']
			+	$charArray['|']
			+	$charArray['/']
			+	$charArray['\\']
			+	$charArray['<']
			+	$charArray['>']
			+	$charArray['`']
			+	$charArray['~'];
			
		$this->spaceCount = $charArray[" "];
		$this->tabCount = $charArray["	"];
		
		$this->whitespaceCount = $this->spaceCount + $this->tabCount;
		
		$this->characterCount = $this->letterCount + $this->numberCount + $this->punctuationCount + $this->symbolCount;
		
		$this->capitalLetterCount = strlen(Sanitize::whitelist($this->textSanitized, "ABCDEFGHIJKLMNOPQRSTUVWXYZ"));
	}
	
	
/****** Get Simple Counts ******/
	public function getWordData (
	)				// RETURNS <void>
	
	// $phrase->getWordData()
	{
		// Cycle through each word and retrieve information about it
		foreach($this->wordList as $word)
		{
			// Determine Capitalization
			if(ctype_upper($word[0]))
			{
				$this->capitalWordCount += 1;
				
				if(ctype_upper($word) and strlen($word) > 1)
				{
					$this->allCapsWordCount += 1;
				}
			}
		}
	}
}
